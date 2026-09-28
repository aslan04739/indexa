<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\WalletTransaction;
use App\Services\Chargily;
use App\Services\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class WalletController extends Controller
{
    public function __construct(private Wallet $wallet, private Chargily $chargily) {}

    public function show(Request $request): View
    {
        return view('app.wallet', [
            'balance' => $request->user()->balance(),
            'transactions' => $request->user()->walletTransactions()->with('reference')->latest('id')->paginate(30),
        ]);
    }

    public function topup(Request $request): RedirectResponse
    {
        $amount = $request->validate([
            'amount' => ['required', 'integer', 'min:'.config('marketplace.min_topup_dzd'), 'max:5000000'],
        ])['amount'];

        $payment = $request->user()->payments()->create([
            'amount' => $amount,
            'provider' => $this->chargily->configured() ? 'chargily' : 'fake',
        ]);

        if (! $this->chargily->configured()) {
            // Local development only: no gateway, credit immediately.
            abort_unless(config('marketplace.fake_payments'), 503, 'Payments are not configured.');
            $this->markPaid($payment);

            return redirect()->route('app.wallet')->with('status', __('Rechargement de test crédité.'));
        }

        try {
            $url = $this->chargily->createCheckout(
                $payment,
                successUrl: route('app.wallet.return', $payment),
                failureUrl: route('app.wallet.return', $payment),
                webhookUrl: route('webhooks.chargily'),
                locale: app()->getLocale(),
            );
        } catch (Throwable $e) {
            Log::error('Chargily checkout failed', ['payment' => $payment->id, 'error' => $e->getMessage()]);
            $payment->update(['status' => Payment::FAILED]);

            return back()->withErrors(['amount' => __('Le service de paiement est indisponible. Réessayez dans quelques minutes.')]);
        }

        return redirect()->away($url);
    }

    /** Where Chargily sends the customer back. The webhook, not this page, credits the wallet. */
    public function return(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->user_id === $request->user()->id, 404);

        return redirect()->route('app.wallet')->with('status', $payment->fresh()->status === Payment::PAID
            ? __('Paiement reçu. Votre solde a été crédité.')
            : __('Paiement en cours de confirmation. Votre solde sera crédité dès réception de la confirmation.'));
    }

    public function invoice(Request $request, Payment $payment): View
    {
        abort_unless($payment->user_id === $request->user()->id && $payment->status === Payment::PAID, 404);

        return view('app.invoice', ['payment' => $payment, 'user' => $request->user()]);
    }

    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();

        if (! $this->chargily->validSignature($payload, $request->header('signature'))) {
            return response('invalid signature', 403);
        }

        $event = json_decode($payload, true);
        $checkoutId = $event['data']['id'] ?? null;
        $payment = $checkoutId ? Payment::where('provider_checkout_id', $checkoutId)->first() : null;

        if (! $payment) {
            return response('unknown checkout', 200);
        }

        match ($event['type'] ?? null) {
            'checkout.paid' => $this->markPaid($payment, (int) ($event['data']['amount'] ?? 0)),
            'checkout.failed', 'checkout.canceled', 'checkout.expired' => $payment->status === Payment::PENDING
                ? $payment->update(['status' => Payment::FAILED]) : null,
            default => null,
        };

        return response('ok', 200);
    }

    /** Idempotent: Chargily may deliver the same webhook more than once. */
    private function markPaid(Payment $payment, ?int $paidAmount = null): void
    {
        DB::transaction(function () use ($payment, $paidAmount) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            if ($payment->status === Payment::PAID) {
                return;
            }
            if ($paidAmount !== null && $paidAmount !== $payment->amount) {
                Log::warning('Chargily amount mismatch', ['payment' => $payment->id, 'paid' => $paidAmount]);

                return;
            }

            $payment->update(['status' => Payment::PAID, 'paid_at' => now()]);
            $this->wallet->credit($payment->user, $payment->amount, WalletTransaction::TOPUP, $payment, 'Top-up #'.$payment->id);
        });
    }
}
