<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $user = Auth::guard('web')->user();

        $orders = Order::query()
            ->when($user, fn ($q) => $q->where('user_id', $user->id))
            ->when(! $user, fn ($q) => $q->whereRaw('1 = 0'))
            ->latest()
            ->paginate(10);

        return view('store.orders.index', compact('orders'));
    }

    public function show(string $orderNumber, Request $request): View
    {
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->with(['items', 'timelines'])
            ->firstOrFail();

        $user = Auth::guard('web')->user();
        if ($order->user_id && (! $user || $user->id !== $order->user_id)) {
            abort(403);
        }

        return view('store.orders.show', compact('order'));
    }
}
