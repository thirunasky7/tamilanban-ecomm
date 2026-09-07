@extends('layouts.store')
@section('title', __('store.orders'))
@section('header_title', __('store.my_orders'))
@section('content')
@guest
<div class="msh-empty"><p>{{ __('store.login_to_see_orders') }}</p><a class="msh-btn msh-btn-primary" href="{{ route('login') }}">{{ __('store.login') }}</a></div>
@else
@if($orders->isEmpty())
<div class="msh-empty"><p>{{ __('store.no_orders_yet') }}</p><a class="msh-btn msh-btn-primary msh-btn-sm" href="{{ route('products.index') }}" style="margin-top:12px">{{ __('store.start_shopping') }}</a></div>
@else
<div class="msh-orders-grid">
@foreach($orders as $order)
<a class="msh-order-card" href="{{ route('orders.show', $order->order_number) }}">
<div class="msh-order-card__id">{{ $order->order_number }}</div>
<div class="msh-order-card__meta">{{ $order->created_at->format('d M Y, h:i A') }}</div>
<span class="msh-order-status">{{ ucfirst($order->status) }}</span>
<div class="msh-price" style="margin-top:10px">₹{{ number_format($order->total, 0) }}</div>
</a>
@endforeach
</div>
<div class="msh-pagination">{{ $orders->links() }}</div>
@endif
@endguest
@endsection
