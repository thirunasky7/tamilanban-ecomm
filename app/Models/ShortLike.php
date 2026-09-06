<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortLike extends Model
{
    protected $fillable = ['user_id', 'short_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function short(): BelongsTo
    {
        return $this->belongsTo(Short::class);
    }
}
