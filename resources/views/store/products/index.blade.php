@extends('layouts.store')
@section('title', __('store.products'))
@section('header_title', __('store.products'))
@section('content')

<div class="msh-page-toolbar">
<form class="msh-search-field" action="{{ route('products.index') }}" method="GET">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M20 20l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
<input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('store.search_products') }}">
@if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
@if(request('tag'))<input type="hidden" name="tag" value="{{ request('tag') }}">@endif
</form>
</div>

@if($categories->isNotEmpty())
<div class="msh-chips" style="margin-bottom:8px">
<a class="msh-chip {{ !request('category') ? 'active' : '' }}" href="{{ route('products.index', array_filter(['q' => request('q'), 'tag' => request('tag')])) }}">{{ __('store.all') }}</a>
@foreach($categories as $cat)
<a class="msh-chip {{ request('category') === $cat->slug ? 'active' : '' }}" href="{{ route('products.index', array_filter(['category' => $cat->slug, 'q' => request('q'), 'tag' => request('tag')])) }}">{{ $cat->name }}</a>
@endforeach
</div>
@endif

@if($products->isEmpty())
<div class="msh-empty">
<p>{{ __('store.no_products') }}</p>
<a class="msh-btn msh-btn-primary msh-btn-sm" href="{{ route('home') }}" style="margin-top:12px">{{ __('store.back_home') }}</a>
</div>
@else
<div class="msh-product-grid">
@foreach($products as $product)
<x-product-card :product="$product" variant="scroll" />
@endforeach
</div>
<div class="msh-pagination">{{ $products->links() }}</div>
@endif

@endsection
