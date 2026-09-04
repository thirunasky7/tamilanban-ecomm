<?php

namespace App\Http\Resources\Mobile;

use App\Models\Order;
use App\Support\MobileUrl;

class OrderTransformer
{
    public static function mapStatus(string $status): string
    {
        return match ($status) {
            'pending', 'confirmed', 'processing' => 'processing',
            'shipped', 'out_for_delivery' => 'shipped',
            'delivered', 'completed' => 'delivered',
            'cancelled', 'refunded', 'failed' => 'cancelled',
            default => 'processing',
        };
    }

    public static function order(Order $order): array
    {
        $order->loadMissing(['items.product', 'timelines']);
        $address = $order->shipping_address ?? [];

        return [
            'id' => $order->order_number,
            'status' => self::mapStatus((string) $order->status),
            'createdAt' => optional($order->created_at)?->toIso8601String(),
            'deliveredAt' => $order->status === 'delivered'
                ? optional($order->updated_at)?->toIso8601String()
                : null,
            'paymentMethod' => (string) $order->payment_method,
            'address' => [
                'id' => 'addr-'.$order->id,
                'label' => 'Delivery',
                'fullName' => (string) ($address['name'] ?? $order->guest_name ?? ''),
                'phone' => (string) ($address['mobile'] ?? $order->guest_mobile ?? ''),
                'line1' => (string) ($address['line1'] ?? ''),
                'line2' => (string) ($address['line2'] ?? ''),
                'city' => (string) ($address['city'] ?? ''),
                'state' => (string) ($address['state'] ?? ''),
                'postalCode' => (string) ($address['pincode'] ?? ''),
                'country' => (string) ($address['country'] ?? 'India'),
                'isDefault' => true,
            ],
            'items' => $order->items->map(fn ($item) => [
                'productId' => (string) ($item->product_id ?? ''),
                'name' => (string) $item->product_name,
                'imageUrl' => MobileUrl::absolute(optional($item->product)->thumbnail) ?? '',
                'unitPrice' => (float) $item->price,
                'quantity' => (int) $item->quantity,
                'variant' => $item->variant,
            ])->values()->all(),
            'subtotal' => (float) $order->subtotal,
            'discount' => (float) $order->discount,
            'deliveryFee' => (float) $order->shipping,
            'tax' => (float) $order->tax,
            'grandTotal' => (float) $order->total,
            'timeline' => $order->timelines->map(fn ($event) => [
                'status' => self::mapStatus((string) $event->status),
                'label' => (string) $event->title,
                'at' => optional($event->created_at)?->toIso8601String(),
            ])->values()->all(),
        ];
    }
}
