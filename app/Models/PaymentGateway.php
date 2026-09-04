<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name', 'code', 'is_enabled', 'is_sandbox', 'credentials',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_sandbox' => 'boolean',
        'credentials' => 'array',
    ];

    protected $hidden = [
        'credentials',
    ];

    public function credential(string $key, mixed $default = null): mixed
    {
        return ($this->credentials ?? [])[$key] ?? $default;
    }

    public function hasApiKeys(): bool
    {
        return filled($this->credential('key_id')) && filled($this->credential('key_secret'));
    }

    public function isMethodOn(string $method): bool
    {
        return (bool) $this->credential($method, false);
    }
}
