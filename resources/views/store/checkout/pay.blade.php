@extends('layouts.store')
@section('title', __('store.complete_payment'))
@section('header_title', __('store.payment'))
@section('content')
<div class="msh-checkout-layout">
@if(session('error'))
<div class="msh-alert msh-alert-err" style="max-width:520px;margin:0 auto 16px">{{ session('error') }}</div>
@endif
<div class="msh-pdp-box" style="max-width:520px;margin:0 auto;text-align:center">
<h1 style="margin:0 0 8px;font-size:20px">{{ __('store.complete_payment') }}</h1>
<p style="margin:0 0 16px;color:var(--text-secondary);font-size:14px">{{ __('store.order') }} {{ $order->order_number }} · ₹{{ number_format($order->total, 0) }}</p>
<p style="margin:0 0 20px;color:var(--text-tertiary);font-size:13px">{{ __('store.secure_razorpay') }}</p>
<button type="button" class="msh-btn msh-btn-primary msh-btn-block" id="rzp-pay-btn">{{ __('store.pay_now') }}</button>
<a class="msh-btn msh-btn-outline msh-btn-block" href="{{ route('checkout.show') }}" style="margin-top:10px">{{ __('store.back_to_checkout') }}</a>
</div>
</div>

<form id="payment-verify-form" method="POST" action="{{ route('payment.verify') }}" style="display:none">
@csrf
<input type="hidden" name="order_number" value="{{ $order->order_number }}">
<input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
<input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
<input type="hidden" name="razorpay_signature" id="razorpay_signature">
</form>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('rzp-pay-btn');
  if (!btn) return;

  const options = {
    key: @json($keyId),
    amount: {{ (int) round($order->total * 100) }},
    currency: 'INR',
    name: 'ShopEase',
    description: 'Order {{ $order->order_number }}',
    order_id: @json($order->razorpay_order_id),
    prefill: @json($prefill),
    theme: { color: '#1D4ED8' },
    config: @json($checkoutConfig),
    handler: function (response) {
      document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
      document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
      document.getElementById('razorpay_signature').value = response.razorpay_signature;
      document.getElementById('payment-verify-form').submit();
    },
    modal: {
      ondismiss: function () {}
    }
  };

  const rzp = new Razorpay(options);
  btn.addEventListener('click', () => rzp.open());
  rzp.open();
});
</script>
@endpush
