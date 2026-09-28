<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Wallet
{
    public function credit(User $user, int $amount, string $type, ?Model $reference = null, ?string $description = null): WalletTransaction
    {
        return $this->record($user, abs($amount), $type, $reference, $description);
    }

    /** Debit only if the balance covers it. Throws InsufficientFunds otherwise. */
    public function debit(User $user, int $amount, string $type, ?Model $reference = null, ?string $description = null): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $reference, $description) {
            // Serialize concurrent debits for the same user (no-op on SQLite, row lock on PostgreSQL/MySQL).
            User::whereKey($user->id)->lockForUpdate()->first();

            if ($user->balance() < $amount) {
                throw new InsufficientFunds;
            }

            return $this->record($user, -abs($amount), $type, $reference, $description);
        });
    }

    private function record(User $user, int $amount, string $type, ?Model $reference, ?string $description): WalletTransaction
    {
        return $user->walletTransactions()->create([
            'amount' => $amount,
            'type' => $type,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'description' => $description,
        ]);
    }
}
