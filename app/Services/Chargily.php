<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Chargily Pay v2: CIB (SATIM) and Edahabia (Algérie Poste) card payments.
 * API reference: https://dev.chargily.com/pay-v2/introduction
 */
class Chargily
{
    public function configured(): bool
    {
        return filled(config('marketplace.chargily.secret_key'));
    }

    /** Create a hosted checkout and return the URL to redirect the customer to. */
    public function createCheckout(Payment $payment, string $successUrl, string $failureUrl, string $webhookUrl, string $locale): string
    {
        $response = $this->client()->post('/checkouts', [
            'amount' => $payment->amount,
            'currency' => 'dzd',
            'success_url' => $successUrl,
            'failure_url' => $failureUrl,
            'webhook_endpoint' => $webhookUrl,
            'locale' => in_array($locale, ['ar', 'en', 'fr'], true) ? $locale : 'fr',
            'description' => 'Indexa wallet top-up #'.$payment->id,
            'metadata' => ['payment_id' => (string) $payment->id],
        ]);

        if (! $response->successful() || ! $response->json('checkout_url')) {
            throw new RuntimeException('Chargily checkout creation failed: HTTP '.$response->status());
        }

        $payment->update(['provider_checkout_id' => $response->json('id')]);

        return $response->json('checkout_url');
    }

    /** Webhooks carry a `signature` header: hex HMAC-SHA256 of the raw body, keyed with the secret key. */
    public function validSignature(string $payload, ?string $signature): bool
    {
        if (! $signature || ! $this->configured()) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $payload, config('marketplace.chargily.secret_key')), $signature);
    }

    private function client()
    {
        $mode = config('marketplace.chargily.mode') === 'live' ? 'live' : 'test';

        return Http::baseUrl(config("marketplace.chargily.base_url.$mode"))
            ->withToken(config('marketplace.chargily.secret_key'))
            ->acceptJson()
            ->timeout(20);
    }
}
