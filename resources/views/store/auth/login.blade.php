@extends('layouts.store')
@section('title', __('store.login'))
@section('content')
<div class="msh-auth-card">
<div class="msh-auth-brand">Shop<span>Ease</span></div>
<p class="msh-auth-sub">{{ __('store.login_with_mobile') }}</p>
<p style="font-size:12px;color:var(--muted);text-align:center;margin:-12px 0 20px;background:var(--gold-light);padding:8px;border-radius:8px">{{ __('store.demo_otp') }}</p>
<form method="POST" action="{{ route('login.otp') }}">@csrf
<div class="msh-field"><label>{{ __('store.mobile_number') }}</label><input name="mobile" required placeholder="{{ __('store.mobile_placeholder') }}" inputmode="numeric"></div>
<div class="msh-field"><label>{{ __('store.name_optional') }}</label><input name="name" placeholder="{{ __('store.your_name') }}"></div>
<button class="msh-btn msh-btn-primary msh-btn-block" type="submit">{{ __('store.get_otp') }}</button>
</form>
</div>
@endsection
