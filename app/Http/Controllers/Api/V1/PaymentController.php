<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\OrderTransformer;
use App\Models\Order;
use App\Services\ProductVariantService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private RazorpayService $razorpay,
        private ProductVariantService $variants,
    ) {
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_number' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $order = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('order_number', $data['order_number'])
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            $order->load(['items.product', 'timelines']);

            return response()->json(OrderTransformer::order($order, $this->razorpay));
        }

        if ($order->payment_status !== 'pending') {
            return response()->json([
                'message' => 'This order cannot accept payment.',
            ], 422);
        }

        if ($order->razorpay_order_id !== $data['razorpay_order_id']) {
            return response()->json([
                'message' => 'Razorpay order mismatch.',
            ], 422);
        }

        if (! $this->razorpay->verifyPayment(
            $data['razorpay_order_id'],
            $data['razorpay_payment_id'],
            $data['razorpay_signature'],
        )) {
            return response()->json([
                'message' => 'Payment verification failed. Please try again.',
            ], 422);
        }

        $this->razorpay->markOrderPaid($order, $data['razorpay_payment_id']);
        $this->variants->decrementOrderStock($order->fresh());

        $order->refresh()->load(['items.product', 'timelines']);

        return response()->json(OrderTransformer::order($order, $this->razorpay));
    }
}
