<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\WalletTransaction;
use App\Services\InsufficientFunds;
use App\Services\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function index(Request $request): View
    {
        return view('app.payouts', [
            'balance' => $request->user()->balance(),
            'payouts' => $request->user()->payoutRequests()->latest()->get(),
        ]);
    }

    public function store(Request $request, Wallet $wallet): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:'.config('marketplace.min_payout_dzd')],
            'method' => ['required', Rule::in(PayoutRequest::METHODS)],
            'account_details' => ['required', 'string', 'max:255'],
        ]);

        try {
            DB::transaction(function () use ($request, $wallet, $data) {
                $payout = $request->user()->payoutRequests()->create($data);
                // Funds leave the balance now, so they cannot be requested twice.
                $wallet->debit($request->user(), $data['amount'], WalletTransaction::PAYOUT, $payout, 'Payout #'.$payout->id);
            });
        } catch (InsufficientFunds) {
            return back()->withInput()->withErrors(['amount' => __('Le montant dépasse votre solde disponible.')]);
        }

        return back()->with('status', __('Demande de retrait envoyée.'));
    }
}
