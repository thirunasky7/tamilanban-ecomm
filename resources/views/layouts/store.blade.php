<!DOCTYPE html>
<html lang="{{ \App\Support\LocaleManager::htmlLang() }}" dir="{{ \App\Support\LocaleManager::dir() }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'ShopEase')</title>
<link rel="stylesheet" href="{{ asset('css/store.css') }}">
@stack('styles')
</head>
<body class="{{ \App\Support\LocaleManager::isRtl() ? 'is-rtl' : 'is-ltr' }}">
<div class="msh-shell">
@include('partials.store.header')
<div class="msh-main">
@include('partials.store.alerts')
@yield('content')
</div>
@include('partials.store.footer')
@include('partials.store.bottom-nav')
</div>
<div id="msh-toast-root" class="msh-toast-root" aria-live="polite"></div>
<script src="{{ asset('js/store.js') }}" defer></script>
@stack('scripts')
</body>
</html>
