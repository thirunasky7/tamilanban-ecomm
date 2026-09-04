@extends('layouts.store')
@section('title', __('store.verify_otp'))
@section('content')
<div class="msh-auth-card">
<h2 style="margin:0 0 8px;text-align:center">{{ __('store.enter_otp') }}</h2>
<p style="font-size:13px;color:var(--muted);margin:0 0 20px;text-align:center">{{ __('store.sent_to', ['mobile' => session('otp.mobile')]) }}</p>
<form method="POST" action="{{ route('login.verify.submit') }}">@csrf
<div class="msh-field"><label>{{ __('store.otp_label') }}</label><input name="code" maxlength="6" required placeholder="123456" style="text-align:center;font-size:20px;letter-spacing:6px" inputmode="numeric"></div>
<button class="msh-btn msh-btn-primary msh-btn-block" type="submit">{{ __('store.verify_login') }}</button>
</form>
</div>
@endsection
