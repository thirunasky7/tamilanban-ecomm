@extends('layouts.store')
@section('title', __('store.checkout'))
@section('header_title', __('store.checkout'))
@section('content')
@php $ship = $subtotal >= 999 ? 0 : 49; @endphp
<div class="msh-checkout-layout">
<form method="POST" action="{{ route('checkout.store') }}" id="checkout-form" style="display:contents">@csrf
<div>
<div class="msh-pdp-box" style="margin-bottom:12px"><h3 style="margin:0 0 14px;color:var(--pri)">{{ __('store.delivery_address') }}</h3>
<div class="msh-field"><label>{{ __('store.name') }}</label><input name="name" value="{{ old('name',$user?->name) }}" required autocomplete="name"></div>
<div class="msh-field"><label>{{ __('store.mobile') }}</label><input name="mobile" value="{{ old('mobile',$user?->mobile) }}" required inputmode="tel" autocomplete="tel"></div>
<div class="msh-field"><label>{{ __('store.email') }}</label><input type="email" name="email" value="{{ old('email',$user?->email) }}" autocomplete="email"></div>
<div class="msh-field"><label>{{ __('store.address') }}</label><input name="line1" value="{{ old('line1') }}" required autocomplete="address-line1"></div>
<div class="msh-field"><label>{{ __('store.city') }}</label><input name="city" value="{{ old('city') }}" required autocomplete="address-level2"></div>
<div class="msh-field"><label>{{ __('store.state') }}</label><input name="state" value="{{ old('state') }}" required autocomplete="address-level1"></div>
<div class="msh-field"><label>{{ __('store.pincode') }}</label><input name="pincode" value="{{ old('pincode') }}" required inputmode="numeric" autocomplete="postal-code"></div>
<input type="hidden" name="line2" value="">
</div>
<div class="msh-pdp-box"><h3 style="margin:0 0 14px;color:var(--pri)">{{ __('store.payment') }}</h3>
@foreach($paymentMethods as $value => $label)
<label class="msh-payment-option">
<input type="radio" name="payment_method" value="{{ $value }}" @checked(old('payment_method', array_key_first($paymentMethods)) === $value)>
{{ $label }}
</label>
@endforeach
</div>
</div>
<aside class="msh-summary">
<h3 style="margin:0 0 14px;font-size:16px">{{ __('store.order_summary') }}</h3>
<div class="msh-summary-row"><span>{{ __('store.subtotal') }}</span><span>₹{{ number_format($subtotal,0) }}</span></div>
<div class="msh-summary-row"><span>{{ __('store.shipping') }}</span><span>{{ $ship ? '₹'.$ship : __('store.free') }}</span></div>
<div class="msh-summary-row msh-summary-row--total"><span>{{ __('store.total') }}</span><span>₹{{ number_format($subtotal+$ship,0) }}</span></div>
<button class="msh-btn msh-btn-primary msh-btn-block" type="submit">{{ __('store.place_order') }}</button>
</aside>
</form>
</div>
@endsection
