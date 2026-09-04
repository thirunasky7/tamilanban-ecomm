@extends('layouts.store')
@section('title', __('store.home'))
@section('content')

@if($banners->isNotEmpty())
<div class="msh-banner-wrap">
<div class="msh-banner">
@foreach($banners as $i => $banner)
<div class="msh-banner-slide {{ $i === 0 ? 'active' : '' }}">
<img src="{{ $banner->image }}" alt="{{ $banner->title }}">
<div class="msh-banner-overlay">
<h2>{{ $banner->title }}</h2>
@if($banner->subtitle)<p>{{ $banner->subtitle }}</p>@endif
</div>
</div>
@endforeach
</div>
@if($banners->count() > 1)
<div class="msh-banner-dots">
@foreach($banners as $i => $b)
<button type="button" class="msh-dot {{ $i === 0 ? 'active' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
@endforeach
</div>
@endif
</div>
@endif

@if($categories->isNotEmpty())
<h2 class="msh-section-label">{{ __('store.categories') }}</h2>
<div class="msh-chips">
@foreach($categories as $cat)
<a class="msh-chip" href="{{ route('products.index', ['category' => $cat->slug]) }}">{{ $cat->name }}</a>
@endforeach
</div>
@endif

@if($shorts->isNotEmpty())
<section class="msh-section">
<div class="msh-section-head"><h2>{{ __('store.shop_shorts') }}</h2><a class="msh-link" href="{{ route('shorts.index') }}">{{ __('store.view_all') }} <span aria-hidden="true">›</span></a></div>
<div class="msh-shorts-rail">
@foreach($shorts as $short)
<a class="msh-short-card" href="{{ route('shorts.index') }}">
<img src="{{ $short->thumbnail }}" alt="{{ $short->title }}">
<span class="msh-short-play"><svg width="20" height="20" viewBox="0 0 24 24" fill="#fff"><path d="M8 5v14l11-7z"/></svg></span>
<div class="msh-short-cap">
{{ $short->title }}
@if($short->product)<span class="msh-short-price">₹{{ number_format($short->product->price, 0) }}</span>@endif
</div>
</a>
@endforeach
</div>
</section>
@endif

<a class="msh-refer msh-section" href="{{ route('login') }}" style="display:flex;text-decoration:none">
<span class="msh-refer-icon">🎁</span>
<div class="msh-refer-text">
<h3>{{ __('store.refer_earn') }}</h3>
<p>{{ __('store.share_products') }}</p>
</div>
<span class="msh-refer-cta">{{ __('store.invite') }} <span aria-hidden="true">›</span></span>
</a>

@foreach([
    ['title' => __('store.featured'), 'products' => $featured, 'tag' => 'featured'],
    ['title' => __('store.new_arrivals'), 'products' => $newest, 'tag' => 'new'],
    ['title' => __('store.bestsellers'), 'products' => $bestsellers, 'tag' => 'bestseller'],
] as $section)
@if($section['products']->isNotEmpty())
<section class="msh-section">
<div class="msh-section-head">
<h2>{{ $section['title'] }}</h2>
<a class="msh-link" href="{{ route('products.index', ['tag' => $section['tag']]) }}">{{ __('store.view_all') }} <span aria-hidden="true">›</span></a>
</div>
<div class="msh-product-rail">
@foreach($section['products'] as $product)
<x-product-card :product="$product" variant="scroll" />
@endforeach
</div>
</section>
@endif
@endforeach

@endsection
