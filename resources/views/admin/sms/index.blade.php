<?php
// placeholder - blade content below via redirect
?>
@extends('layouts.admin')
@section('title', 'SMS Settings')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-product-form.css') }}">
@endpush
@section('content')
<div class="adm-product-editor" style="max-width:960px">
<header class="adm-product-header">
<div class="adm-product-header__left">
<button type="button" id="adm-menu-toggle" class="adm-menu-toggle" aria-label="Menu">☰</button>
<div><div class="adm-breadcrumb">Settings / SMS</div><h1 class="adm-product-header__title">SMS & OTP Settings</h1></div>
</div>
<div class="adm-product-header__actions"><button class="adm-btn adm-btn-primary" type="submit" form="sms-form">Save Settings</button></div>
</header>
<form method="POST" action="{{ route('admin.sms.update') }}" id="sms-form" class="adm-product-form">
@csrf
@method('PUT')
<section class="adm-panel" style="margin-bottom:20px">
<div class="adm-panel__head"><div class="adm-panel__icon">📨</div><div><h2 class="adm-panel__title">Gateway</h2><p class="adm-panel__desc">Zennexs SMS for OTP and orders. Failures never block checkout.</p></div></div>
<div class="adm-panel__body">
<input type="hidden" name="sms_enabled" value="0"><input type="hidden" name="sms_use_dummy_otp" value="0">
<div class="adm-toggle-list" style="margin-bottom:18px">
<label class="adm-toggle-row"><div class="adm-toggle-row__text"><strong>Enable SMS gateway</strong><span>Send real SMS when credentials are set</span></div><input type="checkbox" class="adm-toggle" name="sms_enabled" value="1" @checked($sms_enabled)></label>
<label class="adm-toggle-row"><div class="adm-toggle-row__text"><strong>Use dummy OTP (123456)</strong><span>On for testing; off in production</span></div><input type="checkbox" class="adm-toggle" name="sms_use_dummy_otp" value="1" @checked($sms_use_dummy_otp)></label>
</div>
<div class="adm-field"><label>API Base URL</label><input name="sms_base_url" value="{{ old('sms_base_url', $sms_base_url) }}" required></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
<div class="adm-field"><label>API Key</label><input name="sms_api_key" value="{{ old('sms_api_key', $sms_api_key) }}" placeholder="smk_..." autocomplete="off"></div>
<div class="adm-field"><label>API Secret</label><input type="password" name="sms_api_secret" value="" placeholder="{{ $sms_api_secret_set ? 'Saved — leave blank to keep' : 'Enter API secret' }}" autocomplete="new-password"></div>
</div>
<div class="adm-field"><label>Country code</label><input name="sms_country_code" value="{{ old('sms_country_code', $sms_country_code) }}" style="max-width:120px" required></div>
<p style="margin:0;font-size:13px;color:#64748b">Status: <strong style="color:{{ $gateway_ready ? '#166534' : '#92400e' }}">{{ $gateway_ready ? 'Ready' : 'Not ready' }}</strong></p>
</div>
</section>
<section class="adm-panel" style="margin-bottom:20px">
<div class="adm-panel__head"><div class="adm-panel__icon">✏️</div><div><h2 class="adm-panel__title">Templates</h2><p class="adm-panel__desc">Placeholders: {otp} {order} {total} {name} {status}</p></div></div>
<div class="adm-panel__body">
<div class="adm-field"><label>OTP</label><textarea name="tpl_otp" rows="2">{{ old('tpl_otp', $tpl_otp) }}</textarea></div>
<div class="adm-field"><label>Order confirmed</label><textarea name="tpl_order_confirmed" rows="2">{{ old('tpl_order_confirmed', $tpl_order_confirmed) }}</textarea></div>
<div class="adm-field"><label>Payment received</label><textarea name="tpl_order_paid" rows="2">{{ old('tpl_order_paid', $tpl_order_paid) }}</textarea></div>
<div class="adm-field"><label>Shipped</label><textarea name="tpl_order_shipped" rows="2">{{ old('tpl_order_shipped', $tpl_order_shipped) }}</textarea></div>
<div class="adm-field"><label>Delivered</label><textarea name="tpl_order_delivered" rows="2">{{ old('tpl_order_delivered', $tpl_order_delivered) }}</textarea></div>
</div>
</section>
</form>
<section class="adm-panel">
<div class="adm-panel__head"><div class="adm-panel__icon">🧪</div><div><h2 class="adm-panel__title">Test SMS</h2></div></div>
<div class="adm-panel__body">
<form method="POST" action="{{ route('admin.sms.test') }}" class="adm-product-form" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
@csrf
<div class="adm-field" style="margin:0;flex:1;min-width:200px"><label>Mobile</label><input name="test_mobile" placeholder="9876543210" required></div>
<button class="adm-btn adm-btn-primary" type="submit">Send test</button>
</form>
</div>
</section>
</div>
@endsection
