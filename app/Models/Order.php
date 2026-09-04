<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'guest_name', 'guest_email', 'guest_mobile',
        'status', 'payment_method', 'payment_status', 'razorpay_order_id', 'razorpay_payment_id', 'subtotal', 'discount',
        'shipping', 'tax', 'total', 'coupon_code', 'referral_code',
        'shipping_address', 'billing_address', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(OrderTimeline::class);
    }
}
