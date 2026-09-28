<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['target_url', 'anchor_text', 'content', 'brief'])]
class Order extends Model
{
    use HasFactory;

    public const PENDING = 'pending';      // paid, waiting for the publisher

    public const ACCEPTED = 'accepted';    // publisher is writing / publishing

    public const PUBLISHED = 'published';  // live URL submitted, waiting for buyer validation

    public const COMPLETED = 'completed';  // publisher paid, link monitored

    public const DISPUTED = 'disputed';    // buyer contested, admin decides

    public const REFUSED = 'refused';      // publisher refused, buyer refunded

    public const CANCELLED = 'cancelled';  // expired or cancelled, buyer refunded

    public const OPEN_STATUSES = [self::PENDING, self::ACCEPTED, self::PUBLISHED, self::DISPUTED];

    protected function casts(): array
    {
        return [
            'deadline_at' => 'datetime',
            'accepted_at' => 'datetime',
            'published_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_checked_at' => 'datetime',
            'monitor_until' => 'datetime',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function commission(): int
    {
        return $this->buyer_price - $this->publisher_price;
    }
}
