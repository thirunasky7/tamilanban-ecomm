@php
$onSale = $product->compare_at_price && $product->compare_at_price > $product->price;
$discount = $onSale ? round((1 - $product->price / $product->compare_at_price) * 100) : 0;
$brand = $product->categoryNamesLabel() ?: 'ShopEase';
$productUrl = $product->slug ? route('products.show', $product->slug) : '#';
@endphp
<article class="msh-card msh-card--{{ $variant }}">
<a href="{{ $productUrl }}" class="msh-card-img">
@if($onSale)<span class="msh-off-badge">{{ $discount }}% OFF</span>@endif
<img src="{{ $product->thumbnail }}" alt="{{ $product->name }}" loading="lazy">
</a>
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
