<nav class="msh-bottom">
<a class="msh-nav-item {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
<svg viewBox="0 0 24 24" fill="none"><path d="M3 10.5L12 3l9 7.5V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-9.5z" stroke="currentColor" stroke-width="1.5"/></svg>
{{ __('store.home') }}</a>
<a class="msh-nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
<svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.5"/></svg>
{{ __('store.products') }}</a>
<a class="msh-nav-item {{ request()->routeIs('cart.*') ? 'active' : '' }}" href="{{ route('cart.index') }}">
<svg viewBox="0 0 24 24" fill="none"><path d="M6 6h14l-1 8H7L6 6z" stroke="currentColor" stroke-width="1.5"/><circle cx="9" cy="19" r="1.5" fill="currentColor"/><circle cx="17" cy="19" r="1.5" fill="currentColor"/></svg>
@if(($cartCount ?? 0) > 0)<span class="msh-badge-dot" id="msh-cart-count-nav">{{ $cartCount }}</span>@endif
{{ __('store.cart') }}</a>
<a class="msh-nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
<svg viewBox="0 0 24 24" fill="none"><path d="M6 4h12v16H6z" stroke="currentColor" stroke-width="1.5"/><path d="M9 8h6M9 12h6M9 16h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
{{ __('store.orders') }}</a>
<a class="msh-nav-item {{ request()->routeIs('login*') ? 'active' : '' }}" href="{{ auth()->check() ? route('orders.index') : route('login') }}">
<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.5"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6" stroke="currentColor" stroke-width="1.5"/></svg>
{{ __('store.profile') }}</a>
</nav>
