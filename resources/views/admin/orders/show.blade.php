@extends('layouts.admin')
@section('title', 'Order '.$order->order_number)
@section('content')
<div class="adm-topbar">
<div class="adm-topbar-left"><button type="button" id="adm-menu-toggle" class="adm-menu-toggle" aria-label="Menu">☰</button>
<div><div class="adm-breadcrumb"><a href="{{ route('admin.orders.index') }}">Orders</a> / {{ $order->order_number }}</div><h1>Order {{ $order->order_number }}</h1></div></div>
<a class="adm-btn adm-btn-ghost" href="{{ route('admin.orders.index') }}">Back to Orders</a>
</div>
<div class="adm-detail-grid">
<div class="adm-info-box"><div class="label">Customer</div><div class="value">{{ $order->user?->name ?? $order->guest_name ?? 'Guest' }}</div></div>
<div class="adm-info-box"><div class="label">Mobile</div><div class="value">{{ $order->user?->mobile ?? $order->guest_mobile ?? '—' }}</div></div>
<div class="adm-info-box"><div class="label">Status</div><div class="value"><span class="adm-badge adm-badge-ok">{{ ucfirst($order->status) }}</span></div></div>
<div class="adm-info-box"><div class="label">Total</div><div class="value">₹{{ number_format($order->total, 2) }}</div></div>
</div>
<div class="adm-card" style="margin-bottom:20px"><h2 style="margin:0 0 14px;font-size:1rem">Update Status</h2>
<form method="POST" action="{{ route('admin.orders.status', $order) }}">@csrf @method('PATCH')
<div class="adm-form-grid">
<div class="adm-field"><label>Status</label><select name="status">@foreach(['pending','confirmed','processing','shipped','delivered','cancelled'] as $s)<option value="{{ $s }}" @selected($order->status===$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
<div class="adm-field"><label>Note</label><input name="note" placeholder="Optional note"></div>
</div>
<button class="adm-btn adm-btn-primary adm-btn-sm" type="submit">Update Status</button>
</form></div>
<div class="adm-card adm-card-table" style="margin-bottom:20px"><h2 style="margin:0 0 14px;padding:20px 20px 0;font-size:1rem">Order Items</h2>
<div class="adm-table-wrap" style="padding:0 20px 20px"><table class="adm-table"><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>
@foreach($order->items as $item)<tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>₹{{ number_format($item->price,0) }}</td><td>₹{{ number_format($item->total,0) }}</td></tr>@endforeach
</tbody></table></div></div>
<div class="adm-card"><h2 style="margin:0 0 14px;font-size:1rem">Timeline</h2>
<div class="adm-timeline">@foreach($order->timelines as $t)<div class="adm-timeline-item"><strong>{{ $t->title }}</strong><div class="time">{{ $t->created_at->format('d M Y, h:i A') }}</div>@if($t->note)<div style="font-size:13px;color:var(--adm-muted)">{{ $t->note }}</div>@endif</div>@endforeach</div>
</div>
@endsection