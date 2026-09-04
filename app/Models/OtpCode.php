<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = [
        'mobile', 'code', 'expires_at', 'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function isValid(string $code): bool
    {
        return $this->code === $code
            && $this->expires_at->isFuture()
            && $this->verified_at === null;
    }
}
