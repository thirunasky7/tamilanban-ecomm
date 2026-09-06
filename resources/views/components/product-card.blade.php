@php
$onSale = $product->compare_at_price && $product->compare_at_price > $product->price;
$discount = $onSale ? round((1 - $product->price / $product->compare_at_price) * 100) : 0;
$brand = $product->categoryNamesLabel() ?: 'ShopEase';
$productUrl = $product->slug ? route('products.show', $product->slug) : '#';
$isFavorite = auth()->check()
    ? \App\Models\ProductFavorite::query()
        ->where('user_id', auth()->id())
        ->where('product_id', $product->id)
        ->exists()
    : false;
@endphp
<article class="msh-card msh-card--{{ $variant }}">
<div class="msh-card-img">
<a href="{{ $productUrl }}" class="msh-card-img-link">
@if($onSale)<span class="msh-off-badge">{{ $discount }}% OFF</span>@endif
<img src="{{ $product->thumbnail }}" alt="{{ $product->name }}" loading="lazy">
</a>
@auth
<form action="{{ route('favorites.toggle', $product) }}" method="POST" class="msh-fav-form">
@csrf
<button type="submit" class="msh-fav-btn {{ $isFavorite ? 'is-active' : '' }}" aria-label="{{ $isFavorite ? 'Remove from favorites' : 'Add to favorites' }}">
<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
@if($isFavorite)
<path fill="currentColor" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
@else
<path fill="none" stroke="currentColor" stroke-width="2" d="M12.1 18.55l-.1.1-.11-.1C7.14 14.24 4 11.39 4 8.5 4 6.5 5.5 5 7.5 5c1.54 0 3.04.99 3.57 2.36h1.87C13.46 5.99 14.96 5 16.5 5c2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05z"/>
@endif
</svg>
</button>
</form>
@else
<a href="{{ route('login') }}" class="msh-fav-btn" aria-label="Login to favorite">
<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
<path fill="none" stroke="currentColor" stroke-width="2" d="M12.1 18.55l-.1.1-.11-.1C7.14 14.24 4 11.39 4 8.5 4 6.5 5.5 5 7.5 5c1.54 0 3.04.99 3.57 2.36h1.87C13.46 5.99 14.96 5 16.5 5c2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05z"/>
</svg>
</a>
@endauth
</div>
<div class="msh-card-body">
<a href="{{ $productUrl }}">
<div class="msh-card-brand">{{ $brand }}</div>
<div class="msh-card-title">{{ $product->name }}</div>
</a>
@include('partials.store.rating-stars', ['score' => $score, 'reviewCount' => $reviewCount])
<div class="msh-card-footer">
<div class="msh-card-price-wrap">
@if($product->isVariable())
<div class="msh-price">{{ $product->displayPriceLabel() }}</div>
@else
<div class="msh-price">₹{{ number_format($product->price, 0) }}</div>
@if($onSale)<div class="msh-mrp">₹{{ number_format($product->compare_at_price, 0) }}</div>@endif
@endif
</div>
@if($showAdd && !$product->isVariable())
<form action="{{ route('cart.store') }}" method="POST" class="msh-cart-add-form" onclick="event.stopPropagation()">
@csrf
<input type="hidden" name="product_id" value="{{ $product->id }}">
<button type="submit" class="msh-card-add" aria-label="Add to cart">+</button>
</form>
@endif
</div>
</div>
</article>