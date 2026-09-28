<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'url', 'domain', 'language', 'category', 'description', 'price_dzd', 'link_attribute', 'turnaround_days', 'monthly_traffic'])]
class Site extends Model
{
    use HasFactory;

    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    public const LINK_ATTRIBUTES = ['sponsored', 'nofollow', 'dofollow'];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (Site $site) {
            $site->verification_token ??= Str::random(32);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', self::APPROVED)->whereNotNull('verified_at');
    }

    /** Price the buyer pays: publisher price plus platform commission. */
    public function buyerPrice(): int
    {
        return (int) round($this->price_dzd * (1 + config('marketplace.commission_rate')));
    }

    public function verificationMetaTag(): string
    {
        return '<meta name="indexa-verification" content="'.$this->verification_token.'">';
    }

    public static function domainFromUrl(string $url): string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return preg_replace('/^www\./', '', $host);
    }
}
