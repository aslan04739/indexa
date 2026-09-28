<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletTransaction extends Model
{
    public const TOPUP = 'topup';

    public const ORDER_PAYMENT = 'order_payment';

    public const REFUND = 'refund';

    public const EARNING = 'earning';

    public const PAYOUT = 'payout';

    public const PAYOUT_REVERSAL = 'payout_reversal';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
