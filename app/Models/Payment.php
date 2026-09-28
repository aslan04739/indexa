<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const PENDING = 'pending';

    public const PAID = 'paid';

    public const FAILED = 'failed';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    /** Sequential per year for readability, unique through the payment id: INV-2026-000042. */
    public function invoiceNumber(): string
    {
        return 'INV-'.($this->paid_at ?? $this->created_at)->format('Y').'-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
