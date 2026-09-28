<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\Site;
use App\Models\WalletTransaction;
use App\Services\OrderWorkflow;
use App\Services\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('app.admin.index', [
            'pendingSites' => Site::where('status', Site::PENDING)->with('user')->oldest()->get(),
            'disputes' => Order::where('status', Order::DISPUTED)->with('site', 'buyer')->oldest()->get(),
            'payouts' => PayoutRequest::where('status', PayoutRequest::PENDING)->with('user')->oldest()->get(),
            'stats' => [
                'gmv' => (int) Order::where('status', Order::COMPLETED)->sum('buyer_price'),
                'commission' => (int) Order::where('status', Order::COMPLETED)->selectRaw('SUM(buyer_price - publisher_price) as c')->value('c'),
                'sites' => Site::approved()->count(),
                'open_orders' => Order::whereIn('status', Order::OPEN_STATUSES)->count(),
            ],
        ]);
    }

    public function approveSite(Request $request, Site $site): RedirectResponse
    {
        $data = $request->validate(['domain_rating' => ['nullable', 'integer', 'min:0', 'max:100']]);
        abort_unless($site->verified_at, 422, 'Ownership not verified yet.');
        $site->forceFill(['status' => Site::APPROVED, 'rejection_reason' => null, 'domain_rating' => $data['domain_rating'] ?? $site->domain_rating])->save();

        return back()->with('status', __('Site approuvé.'));
    }

    public function rejectSite(Request $request, Site $site): RedirectResponse
    {
        $reason = $request->validate(['rejection_reason' => ['required', 'string', 'max:255']])['rejection_reason'];
        $site->forceFill(['status' => Site::REJECTED, 'rejection_reason' => $reason])->save();

        return back()->with('status', __('Site refusé.'));
    }

    public function resolveDispute(Request $request, Order $order, OrderWorkflow $workflow): RedirectResponse
    {
        $decision = $request->validate(['decision' => ['required', Rule::in(['refund', 'complete'])]])['decision'];
        $decision === 'refund' ? $workflow->cancel($order) : $workflow->complete($order);

        return back()->with('status', __('Litige résolu.'));
    }

    public function markPayoutPaid(Request $request, PayoutRequest $payout): RedirectResponse
    {
        abort_unless($payout->status === PayoutRequest::PENDING, 422);
        $note = $request->validate(['admin_note' => ['nullable', 'string', 'max:255']])['admin_note'] ?? null;
        $payout->update(['status' => PayoutRequest::PAID, 'processed_at' => now(), 'admin_note' => $note]);

        return back()->with('status', __('Retrait marqué comme payé.'));
    }

    public function rejectPayout(Request $request, PayoutRequest $payout, Wallet $wallet): RedirectResponse
    {
        abort_unless($payout->status === PayoutRequest::PENDING, 422);
        $note = $request->validate(['admin_note' => ['required', 'string', 'max:255']])['admin_note'];

        DB::transaction(function () use ($payout, $wallet, $note) {
            $payout->update(['status' => PayoutRequest::REJECTED, 'processed_at' => now(), 'admin_note' => $note]);
            $wallet->credit($payout->user, $payout->amount, WalletTransaction::PAYOUT_REVERSAL, $payout, 'Payout #'.$payout->id.' rejected');
        });

        return back()->with('status', __('Retrait refusé, montant recrédité.'));
    }
}
