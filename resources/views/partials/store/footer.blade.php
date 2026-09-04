<footer class="msh-footer">
<div class="msh-footer-inner">
<div><h4>ShopEase</h4><p style="font-size:13px;margin:0">{{ __('store.footer_tagline') }}</p></div>
<div><h4>{{ __('store.shop') }}</h4><ul><li><a href="{{ route('products.index') }}">{{ __('store.all_products') }}</a></li><li><a href="{{ route('shorts.index') }}">{{ __('store.shop_shorts') }}</a></li><li><a href="{{ route('cart.index') }}">{{ __('store.cart') }}</a></li></ul></div>
<div><h4>{{ __('store.account') }}</h4><ul><li><a href="{{ route('orders.index') }}">{{ __('store.my_orders') }}</a></li><li><a href="{{ route('login') }}">{{ __('store.login') }}</a></li></ul></div>
<div><h4>{{ __('store.support') }}</h4><ul><li><a href="{{ route('pages.refund') }}">{{ __('store.returns_refunds') }}</a></li><li><a href="{{ route('pages.contact') }}">{{ __('store.contact_us') }}</a></li></ul></div>
<div><h4>{{ __('store.legal') }}</h4><ul><li><a href="{{ route('pages.privacy') }}">{{ __('store.privacy_policy') }}</a></li><li><a href="{{ route('pages.terms') }}">{{ __('store.terms_conditions') }}</a></li><li><a href="{{ route('pages.refund') }}">{{ __('store.refund_policy') }}</a></li></ul></div>
</div>
<div class="msh-footer-copy">&copy; {{ date('Y') }} ShopEase. {{ __('store.all_rights_reserved') }}</div>
</footer>
