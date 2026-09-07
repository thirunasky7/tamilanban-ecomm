@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="adm-topbar">
<div class="adm-topbar-left"><button type="button" id="adm-menu-toggle" class="adm-menu-toggle" aria-label="Menu">☰</button><h1>Dashboard Report</h1></div>
<span class="adm-user">{{ now()->format('l, d M Y') }} · {{ auth('admin')->user()->name ?? 'Admin' }}</span>
</div>
<div class="adm-stats">
<div class="adm-stat"><h3>₹{{ number_format($stats['revenue'], 0) }}</h3><p>Total Revenue</p></div>
<div class="adm-stat gold"><h3>₹{{ number_format($stats['today_revenue'], 0) }}</h3><p>Today's Sales</p></div>
<div class="adm-stat"><h3>{{ $stats['orders'] }}</h3><p>Total Orders</p></div>
<div class="adm-stat green"><h3>{{ $stats['pending_orders'] }}</h3><p>Pending Orders</p></div>
<div class="adm-stat"><h3>{{ $stats['products'] }}</h3><p>Products</p></div>
<div class="adm-stat"><h3>{{ $stats['customers'] }}</h3><p>Customers</p></div>
</div>
<div class="adm-charts">
<div class="adm-chart-box"><h3>Sales — Last 7 Days (₹)</h3><canvas id="salesChart" height="200"></canvas></div>
<div class="adm-chart-box"><h3>Orders by Status</h3><canvas id="statusChart" height="200"></canvas></div>
</div>
<div class="adm-dash-grid">
<div class="adm-card"><div class="adm-card-head"><h2>Top Selling Products</h2></div>
<table class="adm-table"><thead><tr><th>Product</th><th>Qty Sold</th><th>Revenue</th></tr></thead><tbody>
@forelse($topProducts as $p)<tr><td>{{ $p->product_name }}</td><td>{{ $p->qty }}</td><td>₹{{ number_format($p->revenue,0) }}</td></tr>
@empty<tr><td colspan="3">No sales data yet</td></tr>@endforelse
</tbody></table></div>
<div class="adm-card"><div class="adm-card-head"><h2>Recent Orders</h2><a class="adm-btn adm-btn-ghost adm-btn-sm" href="{{ route('admin.orders.index') }}">View All</a></div>
<table class="adm-table"><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead><tbody>
@foreach($recentOrders as $order)<tr>
<td><a href="{{ route('admin.orders.show',$order) }}">{{ $order->order_number }}</a></td>
<td>{{ $order->user?->name ?? $order->guest_name ?? 'Guest' }}</td>
<td>₹{{ number_format($order->total,0) }}</td>
<td><span class="adm-badge adm-badge-ok">{{ ucfirst($order->status) }}</span></td></tr>@endforeach
</tbody></table></div>
</div>
@endsection
@push('scripts')
<script>
const salesLabels = @json($salesLast7Days->pluck('label'));
const salesData = @json($salesLast7Days->pluck('total'));
const statusLabels = @json($ordersByStatus->keys()->values());
const statusData = @json($ordersByStatus->values());
new Chart(document.getElementById('salesChart'),{type:'line',data:{labels:salesLabels,datasets:[{label:'Revenue',data:salesData,borderColor:'#1E40AF',backgroundColor:'rgba(30,64,175,.1)',fill:true,tension:.4}]},options:{plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
new Chart(document.getElementById('statusChart'),{type:'doughnut',data:{labels:statusLabels,datasets:[{data:statusData,backgroundColor:['#1E40AF','#EA580C','#10b981','#3b82f6','#f59e0b','#ef4444']}]},options:{plugins:{legend:{position:'bottom'}}}});
</script>
@endpush

