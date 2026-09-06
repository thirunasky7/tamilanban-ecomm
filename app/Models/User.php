<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'avatar',
        'password',
        'type',
        'is_active',
        'wallet_balance',
        'email_verified_at',
        'mobile_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'mobile_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'wallet_balance' => 'decimal:2',
    ];

    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->type === 'customer';
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function productFavorites(): HasMany
    {
        return $this->hasMany(ProductFavorite::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    public function hasPurchased(): bool
    {
        return $this->orders()
            ->where('status', '!=', 'cancelled')
            ->exists();
    }
}
