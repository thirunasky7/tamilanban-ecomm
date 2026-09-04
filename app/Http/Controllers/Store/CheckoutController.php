<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTimeline;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\ProductVariantService;
use App\Services\RazorpayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private RazorpayService $razorpay,
        private ProductVariantService $variants,
    ) {
    }

    public function show(): View|RedirectResponse
    {
        if ($this->cart->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $paymentMethods = $this->availablePaymentMethods();

        if ($paymentMethods === []) {
            return redirect()->route('cart.index')->with('error', 'No payment methods are enabled. Please contact support.');
        }

        return view('store.checkout', [
            'items' => $this->cart->items(),
            'subtotal' => $this->cart->subtotal(),
            'user' => Auth::guard('web')->user(),
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($this->cart->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $paymentMethods = $this->availablePaymentMethods();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'line1' => ['required', 'string', 'max:200'],
            'line2' => ['nullable', 'string', 'max:200'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:20'],
            'payment_method' => ['required', 'in:'.implode(',', array_keys($paymentMethods))],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $cartItems = $this->cart->items();

        foreach ($cartItems as $item) {
            $variant = $this->variants->resolveForCart($item['product_id'], $item['variant_id'] ?? null);
            $this->variants->assertStock($variant, (int) $item['quantity']);
        }

        $user = Auth::guard('web')->user();
        $subtotal = $this->cart->subtotal();
        $shipping = $subtotal >= 999 ? 0 : 49;
        $total = $subtotal + $shipping;

        $address = [
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'] ?? null,
            'line1' => $data['line1'],
            'line2' => $data['line2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'],
            'pincode' => $data['pincode'],
            'country' => 'India',
        ];

        $order = DB::transaction(function () use ($data, $user, $subtotal, $shipping, $total, $address, $cartItems) {
            $order = Order::query()->create([
                'order_number' => 'SE-'.strtoupper(Str::random(8)),
                'user_id' => $user?->id,
                'guest_name' => $user ? null : $data['name'],
                'guest_email' => $user ? null : ($data['email'] ?? null),
                'guest_mobile' => $user ? null : $data['mobile'],
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => 0,
                'shipping' => $shipping,
                'tax' => 0,
                'total' => $total,
                'coupon_code' => session('cart.coupon'),
                'referral_code' => session('cart.referral'),
                'shipping_address' => $address,
                'billing_address' => $address,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'product_name' => $item['name'],
                    'sku' => $item['sku'] ?? null,
                    'variant' => $item['variant'] ?? null,
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
            $this->cart->clear();

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Order placed successfully.');
        }

        try {
            $this->razorpay->createOrder($order);
        } catch (\Throwable $exception) {
            $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);

            return redirect()->route('checkout.show')
                ->with('error', $exception->getMessage());
        }

        return redirect()->route('checkout.pay', $order->order_number);
    }

    protected function availablePaymentMethods(): array
    {
        $methods = [];

        if ($this->razorpay->isCodEnabled()) {
            $methods['cod'] = 'Cash on Delivery';
        }

        if ($this->razorpay->isMethodEnabled('upi')) {
            $methods['upi'] = 'UPI (Razorpay)';
        }

        if ($this->razorpay->isMethodEnabled('netbanking')) {
            $methods['netbanking'] = 'Net Banking (Razorpay)';
        }

        if ($this->razorpay->isMethodEnabled('card')) {
            $methods['card'] = 'Debit / Credit Card (Razorpay)';
        }

        return $methods;
    }
}
