@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
<div class="adm-topbar"><div class="adm-topbar-left"><button type="button" id="adm-menu-toggle" class="adm-menu-toggle" aria-label="Menu">☰</button><h1>Orders</h1></div></div>
<div class="adm-card adm-card-table"><div class="adm-table-wrap"><table class="adm-table adm-datatable">
<thead><tr><th>Order #</th><th>Customer</th><th>Mobile</th><th>Status</th><th>Payment</th><th>Total</th><th>Date</th><th>Actions</th></tr></thead>
<tbody>@foreach($orders as $order)<tr>
<td>{{ $order->order_number }}</td><td>{{ $order->user?->name ?? $order->guest_name ?? 'Guest' }}</td><td>{{ $order->user?->mobile ?? $order->guest_mobile ?? '—' }}</td>
<td><span class="adm-badge adm-badge-ok">{{ ucfirst($order->status) }}</span></td><td>{{ strtoupper($order->payment_method) }}</td><td>₹{{ number_format($order->total,0) }}</td><td>{{ $order->created_at->format('d M Y') }}</td>
<td><a class="adm-btn adm-btn-ghost adm-btn-sm" href="{{ route('admin.orders.show',$order) }}">View</a></td></tr>@endforeach</tbody></table></div></div>
@endsection