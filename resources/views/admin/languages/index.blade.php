@extends('layouts.admin')
@section('title', __('admin.language_settings'))
@section('content')
<div class="adm-topbar">
<div class="adm-topbar-left">
<button type="button" class="adm-menu-toggle" id="adm-menu-toggle">Menu</button>
<div>
<div class="adm-breadcrumb"><a href="{{ route('admin.dashboard') }}">{{ __('admin.dashboard') }}</a> / {{ __('admin.languages') }}</div>
<h1>{{ __('admin.language_settings') }}</h1>
</div>
</div>
</div>

<div class="adm-card" style="max-width:720px">
<p style="margin:0 0 20px;color:var(--adm-muted);font-size:14px">{{ __('admin.language_settings_help') }}</p>

<form method="POST" action="{{ route('admin.languages.update') }}">
@csrf
@method('PUT')

<div class="adm-field">
<label>{{ __('admin.default_language') }}</label>
<select name="default_locale" required>
@foreach($locales as $code => $meta)
<option value="{{ $code }}" @selected($defaultLocale === $code)>{{ $meta['name'] }} ({{ $meta['native'] }})</option>
@endforeach
</select>
</div>

<div class="adm-field">
<label>{{ __('admin.enabled_languages') }}</label>
<div class="adm-checks">
<label><input type="checkbox" name="locale_en_enabled" value="1" @checked($enabled['en'])> {{ __('admin.enable_english') }}</label>
<label><input type="checkbox" name="locale_ar_enabled" value="1" @checked($enabled['ar'])> {{ __('admin.enable_arabic') }}</label>
</div>
</div>

<div class="adm-field">
<label class="adm-checks" style="margin:0">
<input type="checkbox" name="locale_switcher_enabled" value="1" @checked($switcherEnabled)>
<span>{{ __('admin.show_switcher') }}</span>
</label>
</div>

<div class="adm-table-wrap" style="margin:20px 0;border-radius:10px">
<table class="adm-table">
<thead>
<tr>
<th>Code</th>
<th>{{ __('admin.native_name') }}</th>
<th>{{ __('admin.direction') }}</th>
<th>Status</th>
</tr>
</thead>
<tbody>
@foreach($locales as $code => $meta)
<tr>
<td><strong>{{ strtoupper($code) }}</strong></td>
<td>{{ $meta['native'] }}</td>
<td>{{ $meta['dir'] === 'rtl' ? __('admin.rtl') : __('admin.ltr') }}</td>
<td>{{ $enabled[$code] ? '● '.__('admin.enabled') : '○ '.__('admin.disabled') }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<button class="adm-btn adm-btn-primary" type="submit">{{ __('admin.save_settings') }}</button>
</form>
</div>
@endsection
