@extends('layouts.store')
@section('title', $product->name)
@section('header_title', $product->name)
@section('content')
@php
$avg = (float) ($product->avg_rating ?? 0);
$cnt = (int) ($product->reviews_count ?? 0);
$avg = $avg > 0 ? round($avg, 1) : 4.2;
$isVariable = $product->isVariable();
$hasVariantOptions = $isVariable && ! empty($variantPayload['variants']);
$displayPrice = $isVariable
    ? ($variantPayload['price_min'] ?? $product->price)
    : $product->price;
$displayCompare = $isVariable
    ? ($hasVariantOptions ? null : $product->compare_at_price)
    : $product->compare_at_price;
$onSale = $displayCompare && $displayCompare > $displayPrice;
$discount = $onSale ? round((1 - $displayPrice / $displayCompare) * 100) : 0;
$inStock = $isVariable
    ? ($hasVariantOptions ? ! empty($variantPayload['in_stock']) : ($product->totalStock() > 0))
    : ($product->stock > 0);
@endphp
<div class="msh-pdp-layout">
<div>
<div class="msh-pdp-gallery">
<div class="msh-pdp-img"><img id="pdp-image" src="{{ $product->thumbnail }}" alt="{{ $product->name }}"></div>
<div class="msh-pdp-thumbs" id="pdp-thumbs" hidden></div>
</div>
</div>
<div>
<div class="msh-pdp-box" style="margin:0 0 12px">
@if($product->categories->isNotEmpty())<div class="msh-pdp-brand">{{ $product->categoryNamesLabel() }}</div>@endif
<h1 class="msh-pdp-title">{{ $product->name }}</h1>
@include('partials.store.rating-stars', ['score' => $avg, 'reviewCount' => $cnt])
<div class="msh-pdp-price" id="pdp-price-wrap">
<span id="pdp-price">@if($hasVariantOptions){{ $variantPayload['price_min'] == $variantPayload['price_max'] ? '₹'.number_format($variantPayload['price_min'], 0) : '₹'.number_format($variantPayload['price_min'], 0).' – ₹'.number_format($variantPayload['price_max'], 0) }}@else{{ $product->displayPriceLabel() }}@endif</span>
<span class="msh-mrp" id="pdp-compare" style="font-size:16px;margin-inline-start:8px;{{ $onSale ? '' : 'display:none' }}">@if($onSale)₹{{ number_format($displayCompare, 0) }}@endif</span>
<span class="msh-pdp-discount" id="pdp-discount" style="{{ $onSale ? '' : 'display:none' }}">{{ $discount }}% {{ __('store.off') }}</span>
</div>
<p style="font-size:14px;color:var(--text-secondary);margin:0 0 12px">{{ $product->short_description }}</p>

<div id="product-variant-root" data-payload='@json($variantPayload)'>
@if($isVariable && empty($variantPayload['attributes']))
<p style="font-size:13px;color:var(--error);margin:0 0 12px">This product has no variations configured yet.</p>
@endif
@if($isVariable && !empty($variantPayload['attributes']))
@foreach($variantPayload['attributes'] as $attribute)
<div class="msh-variant-group" data-attribute-id="{{ $attribute['id'] }}">
<div class="msh-variant-label">{{ $attribute['name'] }}</div>
<div class="msh-variant-options">
@foreach($attribute['values'] as $value)
<button type="button" class="msh-variant-option" data-value-id="{{ $value['id'] }}">{{ $value['value'] }}</button>
@endforeach
</div>
</div>
@endforeach
@endif
</div>

<p id="pdp-stock" style="font-size:13px;font-weight:600;color:{{ $inStock ? 'var(--success)' : 'var(--error)' }};margin-top:12px">
@if($isVariable)
{{ $inStock ? __('store.select_options') : __('store.out_of_stock') }}
@else
{{ $inStock ? __('store.in_stock') : __('store.out_of_stock') }}
@endif
</p>
</div>
<div class="msh-pdp-actions">
<form action="{{ route('cart.store') }}" method="POST" class="msh-cart-add-form" style="flex:1">@csrf
<input type="hidden" name="product_id" value="{{ $product->id }}">
<input type="hidden" name="variant_id" id="variant-id-input" value="">
<button class="msh-btn msh-btn-outline msh-btn-block" type="submit" @disabled(!$isVariable && !$inStock)>{{ __('store.add_to_cart') }}</button>
</form>
<a class="msh-btn msh-btn-primary msh-btn-block" style="flex:1" href="{{ route('checkout.show') }}">{{ __('store.buy_now') }}</a>
</div>
</div>
</div>

<div class="msh-pdp-box">
<h3 style="margin:0 0 8px;font-size:14px;font-weight:700">{{ __('store.description') }}</h3>
<p style="font-size:14px;color:var(--text-secondary);margin:0;line-height:1.5">{{ $product->description }}</p>
</div>

<div class="msh-reviews">
<h3 style="margin:0 0 12px;font-size:15px;font-weight:700">{{ __('store.reviews') }} ({{ $cnt }})</h3>
@forelse($product->approvedReviews as $review)
<div style="padding:12px 0;border-bottom:1px solid var(--border)">
<div style="display:flex;justify-content:space-between;margin-bottom:4px">
<span style="font-weight:700;font-size:13px">{{ $review->reviewer_name }}</span>
<span style="font-size:11px;color:var(--text-tertiary)">{{ $review->created_at->format('d M Y') }}</span>
</div>
@include('partials.store.rating-stars', ['score' => $review->rating, 'reviewCount' => null])
@if($review->title)<div style="font-weight:600;font-size:13px;margin:4px 0">{{ $review->title }}</div>@endif
<div style="font-size:13px;color:var(--text-secondary)">{{ $review->comment }}</div>
</div>
@empty
<p style="color:var(--text-tertiary);font-size:13px">{{ __('store.no_reviews') }}</p>
@endforelse
<div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
<h4 style="margin:0 0 12px;font-size:14px;font-weight:700">{{ __('store.write_review') }}</h4>
<form method="POST" action="{{ route('products.reviews.store', $product) }}">@csrf
<div class="msh-field"><label>{{ __('store.rating') }}</label><select name="rating" required>
<option value="5">5 - {{ __('store.excellent') }}</option><option value="4">4 - {{ __('store.good') }}</option><option value="3">3 - {{ __('store.average') }}</option><option value="2">2 - {{ __('store.poor') }}</option><option value="1">1 - {{ __('store.bad') }}</option>
</select></div>
<div class="msh-field"><label>{{ __('store.title') }}</label><input name="title" placeholder="{{ __('store.summarize') }}"></div>
<div class="msh-field"><label>{{ __('store.review') }}</label><textarea name="comment" rows="3"></textarea></div>
<button class="msh-btn msh-btn-primary msh-btn-block" type="submit">{{ __('store.submit_review') }}</button>
</form>
</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/product-variants.js') }}"></script>
@endpush
