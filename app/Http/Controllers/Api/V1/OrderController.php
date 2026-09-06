<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\OrderTransformer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTimeline;
use App\Models\Product;
use App\Services\ProductVariantService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(
        private RazorpayService $razorpay,
        private ProductVariantService $variants,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with(['items.product', 'timelines'])
            ->latest()
            ->get()
            ->map(fn (Order $order) => OrderTransformer::order($order, $this->razorpay))
            ->values();

        return response()->json(['orders' => $orders]);
    }

    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'timelines'])
            ->firstOrFail();

        return response()->json(OrderTransformer::order($order, $this->razorpay));
    }

    public function paymentMethods(): JsonResponse
    {
        $methods = [];

        if ($this->razorpay->isCodEnabled()) {
            $methods[] = [
                'id' => 'cod',
                'type' => 'cashOnDelivery',
                'title' => 'Cash on Delivery',
                'subtitle' => 'Pay when your order arrives',
                'iconName' => 'payments',
            ];
        }

        if ($this->razorpay->isMethodEnabled('upi')) {
            $methods[] = [
                'id' => 'upi',
                'type' => 'upiWallet',
                'title' => 'UPI',
                'subtitle' => 'Pay via UPI (Razorpay)',
                'iconName' => 'account_balance_wallet',
            ];
        }

        if ($this->razorpay->isMethodEnabled('netbanking')) {
            $methods[] = [
                'id' => 'netbanking',
                'type' => 'netbanking',
                'title' => 'Net Banking',
                'subtitle' => 'Pay via Net Banking (Razorpay)',
                'iconName' => 'account_balance',
            ];
        }

        if ($this->razorpay->isMethodEnabled('card')) {
            $methods[] = [
                'id' => 'card',
                'type' => 'card',
                'title' => 'Card',
                'subtitle' => 'Debit / Credit Card (Razorpay)',
                'iconName' => 'credit_card',
            ];
        }

        if ($methods === []) {
            $methods[] = [
                'id' => 'cod',
                'type' => 'cashOnDelivery',
                'title' => 'Cash on Delivery',
                'subtitle' => 'Pay when your order arrives',
                'iconName' => 'payments',
            ];
        }

        return response()->json(['paymentMethods' => $methods]);
    }

    public function store(Request $request): JsonResponse
    {
        $allowedMethods = $this->razorpay->availableCheckoutMethods();
        if ($allowedMethods === []) {
            $allowedMethods = ['cod'];
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'line1' => ['required', 'string', 'max:200'],
            'line2' => ['nullable', 'string', 'max:200'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:20'],
            'payment_method' => ['required', 'string', 'in:'.implode(',', $allowedMethods)],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.variant_id' => ['nullable'],
        ]);

        $user = $request->user();
        $lineItems = [];
        $subtotal = 0;

        foreach ($data['items'] as $row) {
            $productId = (int) $row['product_id'];
            $variantId = isset($row['variant_id']) && $row['variant_id'] !== '' && $row['variant_id'] !== null
                ? (int) $row['variant_id']
                : null;

            $product = Product::query()->where('is_active', true)->findOrFail($productId);

            if ($product->isVariable() && ! $variantId) {
                $variantId = $product->productVariants()
                    ->where('is_active', true)
                    ->where('stock', '>', 0)
                    ->orderBy('sort_order')
                    ->value('id');
            }

            $variant = $this->variants->resolveForCart($productId, $variantId ? (int) $variantId : null);
            $this->variants->assertStock($variant, (int) $row['quantity']);

            $price = (float) $variant->price;
            $qty = (int) $row['quantity'];
            $subtotal += $price * $qty;
            $isPersistedVariant = filled($variant->id);

            $lineItems[] = [
                'product_id' => $product->id,
                'variant_id' => $isPersistedVariant ? $variant->id : null,
                'name' => $product->name,
                'sku' => $variant->sku ?: $product->sku,
                'variant' => $isPersistedVariant ? $variant->label() : null,
                'price' => $price,
                'quantity' => $qty,
                'thumbnail' => $variant->thumbnail ?: $product->thumbnail,
            ];
        }

        $shipping = $subtotal >= 999 ? 0 : 49;
        $total = $subtotal + $shipping;

        $address = [
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'] ?? $user->email,
            'line1' => $data['line1'],
            'line2' => $data['line2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'],
            'pincode' => $data['pincode'],
            'country' => 'India',
        ];

        $order = DB::transaction(function () use ($data, $user, $subtotal, $shipping, $total, $address, $lineItems) {
            $order = Order::query()->create([
                'order_number' => 'SE-'.strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => 0,
                'shipping' => $shipping,
                'tax' => 0,
                'total' => $total,
                'shipping_address' => $address,
                'billing_address' => $address,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($lineItems as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'product_name' => $item['name'],
                    'sku' => $item['sku'],
                    'variant' => $item['variant'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['price'] * $item['quantity'],
                ]);
            }

            OrderTimeline::query()->create([
                'order_id' => $order->id,
                'status' => 'pending',
                'title' => 'Order placed',
                'note' => 'Order received successfully.',
            ]);

            return $order;
        });

        if ($data['payment_method'] === 'cod') {
            $this->variants->decrementOrderStock($order);
            $order->update([
                'status' => 'confirmed',
                'payment_status' => 'cod',
            ]);

            try {
                app(\App\Services\Sms\OrderSmsNotifier::class)->orderConfirmed($order->fresh(['user']));
            } catch (\Throwable $exception) {
                report($exception);
            }
        } else {
            try {
                $this->razorpay->createOrder($order);
            } catch (\Throwable $exception) {
                $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);
                throw ValidationException::withMessages([
                    'payment_method' => [$exception->getMessage()],
                ]);
            }
        }

        $order->load(['items.product', 'timelines']);

        return response()->json(OrderTransformer::order($order, $this->razorpay), 201);
    }
}
