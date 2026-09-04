<header class="msh-header">
@if(request()->routeIs('home'))
<a class="msh-logo" href="{{ route('home') }}">ShopEase</a>
@else
<h1 class="msh-header-title">@yield('header_title', 'ShopEase')</h1>
@endif
<nav class="msh-desktop-nav">
<a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">{{ __('store.home') }}</a>
<a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">{{ __('store.products') }}</a>
<a href="{{ route('cart.index') }}">{{ __('store.cart') }}</a>
<a href="{{ route('orders.index') }}">{{ __('store.orders') }}</a>
<a href="{{ route('login') }}">{{ __('store.profile') }}</a>
</nav>
<div class="msh-header-actions">
@include('partials.store.language-switcher')
<a class="msh-icon-btn" href="{{ route('products.index') }}" title="{{ __('store.search') }}" aria-label="{{ __('store.search') }}">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M20 20l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
</a>
<a class="msh-icon-btn" href="{{ route('cart.index') }}" title="{{ __('store.cart') }}" aria-label="{{ __('store.cart') }}">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M6 6h14l-1 8H7L6 6z" stroke="currentColor" stroke-width="1.5"/><circle cx="9" cy="19" r="1.5" fill="currentColor"/><circle cx="17" cy="19" r="1.5" fill="currentColor"/></svg>
@if(($cartCount ?? 0) > 0)<span class="msh-badge-dot" id="msh-cart-count-header">{{ $cartCount }}</span>@endif
</a>
</div>
</header>
