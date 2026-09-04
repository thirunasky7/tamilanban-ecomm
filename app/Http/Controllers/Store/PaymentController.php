<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTimeline;
use App\Services\CartService;
use App\Services\ProductVariantService;
use App\Services\RazorpayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private RazorpayService $razorpay,
        private CartService $cart,
        private ProductVariantService $variants,
    ) {
    }

    public function show(string $orderNumber): View|RedirectResponse
    {
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->where('payment_status', 'pending')
            ->firstOrFail();

        if (! in_array($order->payment_method, ['upi', 'netbanking', 'card'], true)) {
            return redirect()->route('orders.show', $order->order_number);
        }

        if (! $this->razorpay->isConfigured() || ! $order->razorpay_order_id) {
            return redirect()->route('checkout.show')->with('error', 'Online payment is not available right now.');
        }

        $address = $order->shipping_address ?? [];

        return view('store.checkout.pay', [
            'order' => $order,
            'keyId' => $this->razorpay->keyId(),
            'checkoutConfig' => $this->razorpay->checkoutConfig($order->payment_method),
            'prefill' => [
                'name' => $address['name'] ?? '',
                'email' => $address['email'] ?? '',
                'contact' => $address['mobile'] ?? '',
            ],
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_number' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $order = Order::query()
            ->where('order_number', $data['order_number'])
            ->where('payment_status', 'pending')
            ->firstOrFail();

        if (! $this->razorpay->verifyPayment(
            $data['razorpay_order_id'],
            $data['razorpay_payment_id'],
            $data['razorpay_signature'],
        )) {
            return redirect()->route('checkout.pay', $order->order_number)
                ->with('error', 'Payment verification failed. Please try again.');
        }

        $order->update([
            'payment_status' => 'paid',
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'status' => 'confirmed',
        ]);

        OrderTimeline::query()->create([
            'order_id' => $order->id,
            'status' => 'confirmed',
            'title' => 'Payment received',
            'note' => 'Payment completed via Razorpay.',
        ]);

        $this->variants->decrementOrderStock($order);
        $this->cart->clear();

        return redirect()->route('orders.show', $order->order_number)
            ->with('success', 'Payment successful. Your order is confirmed.');
    }
}
