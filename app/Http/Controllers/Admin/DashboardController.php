<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $orders = Order::query();
        $paidStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

        $totalRevenue = (float) Order::query()
            ->whereIn('status', $paidStatuses)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $todayRevenue = (float) Order::query()
            ->whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $salesLast7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->startOfDay();

            return [
                'label' => $date->format('D'),
                'total' => (float) Order::query()
                    ->whereDate('created_at', $date)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total'),
                'count' => Order::query()
                    ->whereDate('created_at', $date)
                    ->where('status', '!=', 'cancelled')
                    ->count(),
            ];
        });

        $ordersByStatus = Order::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $topProducts = OrderItem::query()
            ->select('product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(total) as revenue'))
            ->groupBy('product_name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => [
                'products' => Product::query()->count(),
                'categories' => Category::query()->count(),
                'orders' => $orders->count(),
                'customers' => User::query()->where('type', 'customer')->count(),
                'revenue' => $totalRevenue,
                'today_revenue' => $todayRevenue,
                'pending_orders' => Order::query()->where('status', 'pending')->count(),
            ],
            'salesLast7Days' => $salesLast7Days,
            'ordersByStatus' => $ordersByStatus,
            'topProducts' => $topProducts,
            'recentOrders' => Order::query()->with('user')->latest()->limit(10)->get(),
        ]);
    }
}
