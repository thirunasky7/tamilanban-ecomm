@extends('layouts.store')
@section('title', __('store.cart'))
@section('header_title', __('store.cart'))
@section('content')
@if(empty($items))
<div class="msh-empty">
<p>{{ __('store.cart_empty') }}</p>
<a class="msh-btn msh-btn-primary" href="{{ route('products.index') }}" style="margin-top:12px">{{ __('store.start_shopping') }}</a>
</div>
@else
<h1 class="msh-page-title">{{ __('store.my_cart') }} ({{ collect($items)->sum('quantity') }})</h1>
<div class="msh-cart-layout">
<div>
@foreach($items as $key => $item)
<div class="msh-cart-item">
<img src="{{ $item['thumbnail'] }}" alt="">
<div class="msh-cart-body">
<div style="font-weight:600;font-size:14px">{{ $item['name'] }}</div>
@if(!empty($item['variant']))<div style="font-size:12px;color:var(--text-tertiary);margin-top:2px">{{ $item['variant'] }}</div>@endif
<div class="msh-price" style="margin-top:4px">₹{{ number_format($item['price'], 0) }}</div>
<form method="POST" action="{{ route('cart.update') }}" class="msh-cart-qty">
@csrf
@method('PATCH')
<input type="hidden" name="key" value="{{ $key }}">
<select name="quantity" onchange="this.form.submit()" aria-label="{{ __('store.qty') }}">
@for($q = 0; $q <= 10; $q++)
<option value="{{ $q }}" {{ (int) $item['quantity'] === $q ? 'selected' : '' }}>{{ __('store.qty') }}: {{ $q }}</option>
@endfor
</select>
</form>
</div>
</div>
@endforeach
</div>
<aside class="msh-summary">
<div class="msh-summary-note">✓ {{ __('store.free_delivery_note') }}</div>
<div class="msh-summary-row msh-summary-row--total">
<span>{{ __('store.total') }}</span>
<span>₹{{ number_format($subtotal, 0) }}</span>
</div>
<a class="msh-btn msh-btn-primary msh-btn-block" href="{{ route('checkout.show') }}">{{ __('store.proceed_checkout') }}</a>
</aside>
</div>
@endif
@endsection
