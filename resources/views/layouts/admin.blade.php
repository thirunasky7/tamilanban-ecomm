<!DOCTYPE html>
<html lang="{{ \App\Support\LocaleManager::htmlLang() }}" dir="{{ \App\Support\LocaleManager::dir() }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title') - ShopEase Admin</title>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@stack('styles')
</head>
<body class="{{ \App\Support\LocaleManager::isRtl() ? 'is-rtl' : 'is-ltr' }}">
<div id="adm-sidebar-overlay" class="adm-sidebar-overlay"></div>
<div class="adm-layout">
@auth('admin')
<aside class="adm-sidebar">
<div class="adm-brand">shop<span>ease</span></div>
<div class="adm-brand-sub">{{ __('admin.control_panel') }}</div>
@include('partials.admin.language-switcher')
<nav class="adm-nav">
<a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard')?'active':'' }}">{{ __('admin.dashboard') }}</a>
<a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*')?'active':'' }}">{{ __('admin.products') }}</a>
<a href="{{ route('admin.attributes.index') }}" class="{{ request()->routeIs('admin.attributes.*')?'active':'' }}">{{ __('admin.attributes') }}</a>
<a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*')?'active':'' }}">{{ __('admin.categories') }}</a>
<a href="{{ route('admin.banners.index') }}" class="{{ request()->routeIs('admin.banners.*')?'active':'' }}">{{ __('admin.banners') }}</a>
<a href="{{ route('admin.shorts.index') }}" class="{{ request()->routeIs('admin.shorts.*')?'active':'' }}">{{ __('admin.shop_shorts') }}</a>
<a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*')?'active':'' }}">{{ __('admin.reviews') }}</a>
<a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*')?'active':'' }}">{{ __('admin.orders') }}</a>
@if(auth('admin')->user()?->hasRole('super_admin'))
<a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.*')?'active':'' }}">{{ __('admin.payments') }}</a>
<a href="{{ route('admin.languages.index') }}" class="{{ request()->routeIs('admin.languages.*')?'active':'' }}">{{ __('admin.languages') }}</a>
@endif
<a href="{{ route('home') }}" target="_blank">{{ __('admin.view_store') }}</a>
<form action="{{ route('admin.logout') }}" method="POST" style="margin-top:16px">@csrf<button class="adm-btn adm-btn-ghost" style="width:100%;color:#94a3b8!important" type="submit">{{ __('admin.logout') }}</button></form>
</nav>
</aside>
@endauth
<main class="adm-main @guest('admin') adm-main--guest @endguest">
@if(session('success'))<div class="adm-alert">{{ session('success') }}</div>@endif
@if(session('error'))<div class="adm-alert" style="background:var(--adm-danger-bg);color:var(--adm-danger-text)">{{ session('error') }}</div>@endif
@yield('content')
</main>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/admin.js') }}"></script>
@stack('scripts')
</body>
</html>
