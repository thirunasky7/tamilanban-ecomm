@extends('layouts.store')
@section('title', __('store.contact_us'))
@section('header_title', __('store.contact_us'))
@section('content')

<div class="msh-contact-layout">
<div class="msh-contact-info msh-pdp-box">
<h2 style="margin:0 0 12px;font-size:16px;color:var(--pri)">{{ __('store.get_in_touch') }}</h2>
<p style="font-size:14px;color:var(--text-secondary);margin:0 0 16px;line-height:1.5">{{ __('store.contact_intro') }}</p>
<div class="msh-contact-item">
<span class="msh-contact-icon">✉️</span>
<div>
<div class="msh-contact-label">{{ __('store.email') }}</div>
<a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
</div>
</div>
<div class="msh-contact-item">
<span class="msh-contact-icon">📞</span>
<div>
<div class="msh-contact-label">{{ __('store.phone') }}</div>
<a href="tel:{{ preg_replace('/\s+/', '', $supportPhone) }}">{{ $supportPhone }}</a>
</div>
</div>
<div class="msh-contact-item">
<span class="msh-contact-icon">🕐</span>
<div>
<div class="msh-contact-label">{{ __('store.support_hours') }}</div>
<span>{{ __('store.support_hours_value') }}</span>
</div>
</div>
</div>

<div class="msh-pdp-box">
<h2 style="margin:0 0 16px;font-size:16px;color:var(--pri)">{{ __('store.send_message') }}</h2>
<form method="POST" action="{{ route('pages.contact.submit') }}">
@csrf
<div class="msh-field"><label>{{ __('store.name') }} *</label><input name="name" value="{{ old('name', auth()->user()?->name) }}" required></div>
<div class="msh-field"><label>{{ __('store.email') }} *</label><input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required></div>
<div class="msh-field"><label>{{ __('store.mobile') }}</label><input name="mobile" value="{{ old('mobile', auth()->user()?->mobile) }}"></div>
<div class="msh-field"><label>{{ __('store.subject') }} *</label><input name="subject" value="{{ old('subject') }}" placeholder="{{ __('store.subject_placeholder') }}" required></div>
<div class="msh-field"><label>{{ __('store.message') }} *</label><textarea name="message" rows="5" required>{{ old('message') }}</textarea></div>
<button class="msh-btn msh-btn-primary msh-btn-block" type="submit">{{ __('store.send_message_btn') }}</button>
</form>
</div>
</div>

@endsection
