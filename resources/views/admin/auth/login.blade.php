@extends('layouts.admin')
@section('title', __('admin.admin_login'))
@section('content')
<div class="adm-login-wrap"><div class="adm-login-card">
<div class="adm-brand" style="color:var(--adm-pri)">shop<span style="color:var(--adm-accent)">ease</span></div>
<p class="sub">{{ __('admin.control_panel') }}</p>
@if($errors->any())<div class="adm-alert" style="background:var(--adm-danger-bg);color:var(--adm-danger-text)">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('admin.login.store') }}">@csrf
<div class="adm-field"><label>{{ __('store.email') }}</label><input type="email" name="email" value="{{ old('email') }}" placeholder="admin@shopease.test" required autofocus></div>
<div class="adm-field"><label>{{ __('admin.password') }}</label><input type="password" name="password" placeholder="••••••••" required></div>
<button class="adm-btn adm-btn-primary" style="width:100%;margin-top:8px;justify-content:center" type="submit">{{ __('admin.sign_in') }}</button>
</form></div></div>
@endsection
