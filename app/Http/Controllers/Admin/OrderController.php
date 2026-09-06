<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTimeline;
use App\Services\Sms\OrderSmsNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with('user')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->get();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'timelines', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,processing,shipped,delivered,cancelled'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $order->update(['status' => $data['status']]);

        OrderTimeline::query()->create([
            'order_id' => $order->id,
            'status' => $data['status'],
            'title' => 'Status updated to '.ucfirst($data['status']),
            'note' => $data['note'] ?? null,
        ]);

        try {
            app(OrderSmsNotifier::class)->statusUpdated($order->fresh(['user']));
        } catch (Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Order status updated.');
    }
}
