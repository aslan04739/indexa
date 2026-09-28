<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'locale', 'company', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const BUYER = 'buyer';

    public const PUBLISHER = 'publisher';

    public const ADMIN = 'admin';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isBuyer(): bool
    {
        return $this->role === self::BUYER;
    }

    public function isPublisher(): bool
    {
        return $this->role === self::PUBLISHER;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ADMIN;
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function buyerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function publisherOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'publisher_id');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function balance(): int
    {
        return (int) $this->walletTransactions()->sum('amount');
    }
}
