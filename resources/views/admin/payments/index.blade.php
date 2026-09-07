@extends('layouts.admin')
@section('title', 'Payment Settings')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-product-form.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-payments.css') }}">
@endpush

@section('content')
@php
$credentials = $razorpay?->credentials ?? [];
$razorpayReady = ($razorpay?->is_enabled ?? false) && ($credentialsValid ?? false);
$maskedKeyId = filled($credentials['key_id'] ?? null)
    ? substr($credentials['key_id'], 0, 8).'••••'.substr($credentials['key_id'], -4)
    : null;
@endphp

<div class="adm-product-editor adm-payments-editor">
<header class="adm-product-header">
<div class="adm-product-header__left">
<button type="button" id="adm-menu-toggle" class="adm-menu-toggle" aria-label="Menu">☰</button>
<div>
<div class="adm-breadcrumb">Settings / Payments</div>
<h1 class="adm-product-header__title">Payment Settings</h1>
</div>
</div>
<div class="adm-product-header__actions">
<button class="adm-btn adm-btn-primary" type="submit" form="payments-form">Save Settings</button>
</div>
</header>

@if(session('error'))
<div class="adm-alert adm-alert--error">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('admin.payments.update') }}" id="payments-form" class="adm-product-form">
@csrf
@method('PUT')

<div class="adm-product-layout">
<div class="adm-product-main">

<section class="adm-panel">
<div class="adm-panel__head">
<div class="adm-panel__icon">💵</div>
<div>
<h2 class="adm-panel__title">Cash on Delivery</h2>
<p class="adm-panel__desc">Let customers pay when the order is delivered.</p>
</div>
</div>
<div class="adm-panel__body">
<input type="hidden" name="cod_enabled" value="0">
<div class="adm-toggle-list">
<label class="adm-toggle-row">
<div class="adm-toggle-row__text">
<strong>Enable Cash on Delivery</strong>
<span>Show COD as a payment option at checkout</span>
</div>
<span class="adm-toggle-control">
<input type="checkbox" class="adm-toggle" name="cod_enabled" value="1" @checked(old('cod_enabled', $cod?->is_enabled))>
<span class="adm-toggle-ui" aria-hidden="true"></span>
</span>
</label>
</div>
</div>
</section>

<section class="adm-panel">
<div class="adm-panel__head">
<div class="adm-panel__icon">⚡</div>
<div>
<h2 class="adm-panel__title">Razorpay Gateway</h2>
<p class="adm-panel__desc">Accept UPI, net banking, and card payments online via Razorpay.</p>
</div>
</div>
<div class="adm-panel__body">
<input type="hidden" name="razorpay_enabled" value="0">
<input type="hidden" name="razorpay_sandbox" value="0">
<input type="hidden" name="upi_enabled" value="0">
<input type="hidden" name="netbanking_enabled" value="0">
<input type="hidden" name="card_enabled" value="0">

<div class="adm-toggle-list" style="margin-bottom:22px">
<label class="adm-toggle-row">
<div class="adm-toggle-row__text">
<strong>Enable Razorpay</strong>
<span>Turn on online payments at checkout</span>
</div>
<span class="adm-toggle-control">
<input type="checkbox" class="adm-toggle" name="razorpay_enabled" value="1" @checked(old('razorpay_enabled', $razorpay?->is_enabled))>
<span class="adm-toggle-ui" aria-hidden="true"></span>
</span>
</label>
<label class="adm-toggle-row">
<div class="adm-toggle-row__text">
<strong>Sandbox / Test mode</strong>
<span>Use Razorpay test keys (rzp_test_…)</span>
</div>
<span class="adm-toggle-control">
<input type="checkbox" class="adm-toggle" name="razorpay_sandbox" value="1" @checked(old('razorpay_sandbox', $razorpay?->is_sandbox ?? true))>
<span class="adm-toggle-ui" aria-hidden="true"></span>
</span>
</label>
</div>

<div class="adm-payments-credentials">
<div class="adm-field">
<label>Key ID</label>
<input name="key_id" value="{{ old('key_id', $credentials['key_id'] ?? '') }}" placeholder="rzp_test_xxxxxxxx" autocomplete="off">
@if($maskedKeyId)
<p class="adm-field-hint">Current: {{ $maskedKeyId }}</p>
@endif
</div>
<div class="adm-field">
<label>Key Secret</label>
<input type="password" name="key_secret" value="" placeholder="{{ filled($credentials['key_secret'] ?? null) ? 'Saved — leave blank to keep current' : 'Paste secret from Razorpay (not rzp_…)' }}" autocomplete="new-password">
<p class="adm-field-hint">Copy the <strong>Key Secret</strong> shown once when you generate API keys. It is different from Key ID and usually does not start with <code>rzp_</code>.</p>
</div>
</div>

<div class="adm-payments-methods">
<h3 class="adm-payments-methods__title">Online payment methods</h3>
<div class="adm-payments-method-grid">
<label class="adm-payments-method">
<span class="adm-payments-method__icon" aria-hidden="true">📱</span>
<span class="adm-payments-method__label">UPI</span>
<span class="adm-toggle-control">
<input type="checkbox" class="adm-toggle" name="upi_enabled" value="1" @checked(old('upi_enabled', $credentials['upi'] ?? true))>
<span class="adm-toggle-ui" aria-hidden="true"></span>
</span>
</label>
<label class="adm-payments-method">
<span class="adm-payments-method__icon" aria-hidden="true">🏦</span>
<span class="adm-payments-method__label">Net Banking</span>
<span class="adm-toggle-control">
<input type="checkbox" class="adm-toggle" name="netbanking_enabled" value="1" @checked(old('netbanking_enabled', $credentials['netbanking'] ?? true))>
<span class="adm-toggle-ui" aria-hidden="true"></span>
</span>
</label>
<label class="adm-payments-method">
<span class="adm-payments-method__icon" aria-hidden="true">💳</span>
<span class="adm-payments-method__label">Cards</span>
<span class="adm-toggle-control">
<input type="checkbox" class="adm-toggle" name="card_enabled" value="1" @checked(old('card_enabled', $credentials['card'] ?? true))>
<span class="adm-toggle-ui" aria-hidden="true"></span>
</span>
</label>
</div>
</div>

<div class="adm-payments-note">
<p>Get your API keys from the <a href="https://dashboard.razorpay.com/app/keys" target="_blank" rel="noopener">Razorpay Dashboard</a>. Use test keys while sandbox mode is on.</p>
</div>
</div>
</section>

</div>

<aside class="adm-product-aside">
<section class="adm-panel adm-panel--meta adm-panel--sticky">
<div class="adm-panel__head adm-panel__head--sm">
<div>
<h2 class="adm-panel__title">Status</h2>
</div>
</div>
<div class="adm-panel__body">
<ul class="adm-payments-status">
<li>
<span class="adm-payments-status__label">COD</span>
<span class="adm-payments-badge {{ $cod?->is_enabled ? 'is-on' : 'is-off' }}">{{ $cod?->is_enabled ? 'Enabled' : 'Disabled' }}</span>
</li>
<li>
<span class="adm-payments-status__label">Razorpay</span>
<span class="adm-payments-badge {{ $razorpay?->is_enabled ? 'is-on' : 'is-off' }}">{{ $razorpay?->is_enabled ? 'Enabled' : 'Disabled' }}</span>
</li>
<li>
<span class="adm-payments-status__label">API Keys</span>
<span class="adm-payments-badge {{ ($credentialsValid ?? false) ? 'is-on' : ($hasCredentials ? 'is-warn' : 'is-off') }}">{{ ($credentialsValid ?? false) ? 'Verified' : ($hasCredentials ? 'Invalid' : 'Missing') }}</span>
</li>
<li>
<span class="adm-payments-status__label">Mode</span>
<span class="adm-payments-badge {{ ($razorpay?->is_sandbox ?? true) ? 'is-warn' : 'is-on' }}">{{ ($razorpay?->is_sandbox ?? true) ? 'Sandbox' : 'Live' }}</span>
</li>
<li>
<span class="adm-payments-status__label">Checkout</span>
<span class="adm-payments-badge {{ $razorpayReady ? 'is-on' : 'is-off' }}">{{ $razorpayReady ? 'Ready' : 'Not ready' }}</span>
</li>
</ul>

@if(count($activeMethods) > 0)
<div class="adm-payments-active">
<p class="adm-payments-active__title">Active online methods</p>
<div class="adm-payments-chips">
@foreach($activeMethods as $method)
<span class="adm-payments-chip">{{ strtoupper($method) }}</span>
@endforeach
</div>
</div>
@endif
</div>
</section>

<section class="adm-panel adm-panel--meta">
<div class="adm-panel__head adm-panel__head--sm">
<div>
<h2 class="adm-panel__title">Test flow</h2>
</div>
</div>
<div class="adm-panel__body adm-payments-steps">
<ol>
<li>Enable Razorpay and add test keys</li>
<li>Turn on UPI / Net Banking / Cards</li>
<li>Checkout on storefront with an online method</li>
<li>Complete payment in Razorpay modal</li>
</ol>
</div>
</section>
</aside>
</div>
</form>
</div>
@endsection
