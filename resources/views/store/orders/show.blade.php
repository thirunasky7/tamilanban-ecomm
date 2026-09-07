@extends('layouts.store')
@section('title', __('store.order'))
@section('header_title', __('store.order_details'))
@section('content')
<div class="msh-order-detail">
<div class="msh-pdp-box">
<h2 style="margin:0;font-size:17px;word-break:break-word">{{ $order->order_number }}</h2>
<p style="font-size:13px;color:var(--text-secondary);margin:6px 0 0">{{ ucfirst($order->status) }} · ₹{{ number_format($order->total,0) }}</p>
</div>
@foreach($order->items as $item)
<div class="msh-cart-item">
<div class="msh-cart-body">
<div style="font-weight:600;font-size:14px">{{ $item->product_name }}</div>
@if($item->variant)<div style="font-size:12px;color:var(--text-tertiary);margin-top:2px">{{ $item->variant }}</div>@endif
<div style="font-size:13px;margin-top:6px">{{ __('store.qty') }} {{ $item->quantity }} · ₹{{ number_format($item->total,0) }}</div>
</div>
</div>
@endforeach
</div>
<div class="msh-order-timeline">
<h3>{{ __('store.tracking') }}</h3>
@forelse($order->timelines as $t)
<div class="msh-order-timeline__item">
<strong>{{ $t->title }}</strong>
<div class="msh-order-timeline__time">{{ $t->created_at->format('d M Y H:i') }}</div>
</div>
@empty
<p style="font-size:13px;color:var(--text-secondary)">{{ __('store.no_tracking') }}</p>
@endforelse
</div>
@endsection
