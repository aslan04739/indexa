<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRequest extends Model
{
    public const PENDING = 'pending';

    public const PAID = 'paid';

    public const REJECTED = 'rejected';

    public const METHODS = ['ccp', 'baridimob', 'bank'];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
