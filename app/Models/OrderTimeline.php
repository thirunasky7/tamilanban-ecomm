<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTimeline extends Model
{
    protected $fillable = [
        'order_id', 'status', 'title', 'note',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
