@extends('layouts.admin')
@section('title', $product->exists ? 'Edit Product' : 'Add Product')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-product-form.css') }}">
@endpush

@section('content')
@php
$selectedAttributes = old('attribute_ids');
if ($selectedAttributes === null && $product->exists) {
    $selectedAttributes = $product->attributes->pluck('id')->all();
    if ($selectedAttributes === [] && $product->productVariants->isNotEmpty()) {
        $selectedAttributes = $product->productVariants
            ->flatMap(fn ($variant) => $variant->attributeValues->pluck('attribute_id'))
            ->unique()->values()->all();
    }
}
$selectedAttributes = $selectedAttributes ?? [];
$selectedCategories = old('category_ids', $product->exists ? $product->categories->pluck('id')->all() : []);
$variations = old('variations', $product->exists
    ? $product->productVariants->map(function ($variant) {
        return [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'price' => $variant->price,
            'compare_at_price' => $variant->compare_at_price,
            'stock' => $variant->stock,
            'thumbnail' => $variant->getRawOriginal('thumbnail'),
            'gallery' => $variant->galleryPaths(),
            'is_active' => $variant->is_active,
            'attribute_value_ids' => $variant->attributeValues->pluck('id')->all(),
        ];
    })->values()->all()
    : []);
$currentThumb = $product->exists ? $product->thumbnail : null;
$rawThumb = $product->exists ? $product->getRawOriginal('thumbnail') : null;
$productType = old('product_type', $product->product_type ?? 'simple');
$defaultVariantStock = old('variable_default_stock', $product->exists && $product->isVariable()
    ? ($product->totalStock() ?: ($product->productVariants->max('stock') ?? 0))
    : 0);
@endphp

<div class="adm-product-editor">
<header class="adm-product-header">
<div class="adm-product-header__left">
<button type="button" id="adm-menu-toggle" class="adm-menu-toggle" aria-label="Menu">☰</button>
<div>
<nav class="adm-breadcrumb"><a href="{{ route('admin.products.index') }}">Products</a> / {{ $product->exists ? 'Edit' : 'Create' }}</nav>
<h1 class="adm-product-header__title" data-default-title="{{ $product->exists ? $product->name : 'New Product' }}">{{ $product->exists ? $product->name : 'New Product' }}</h1>
</div>
</div>
<div class="adm-product-header__actions">
<a class="adm-btn adm-btn-ghost" href="{{ route('admin.products.index') }}">Cancel</a>
<button class="adm-btn adm-btn-primary" type="submit" form="product-form">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
Save Product
</button>
</div>
</header>

@if($errors->any())
<div class="adm-callout adm-callout--error" style="margin-bottom:20px">
<strong>Please fix the following:</strong>
<ul style="margin:8px 0 0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" id="product-form" enctype="multipart/form-data" class="adm-product-form">
@csrf
@if($product->exists) @method('PUT') @endif

<div class="adm-product-layout">
<div class="adm-product-main">

{{-- General --}}
<section class="adm-panel">
<div class="adm-panel__head">
<div class="adm-panel__icon">📝</div>
<div>
<h2 class="adm-panel__title">General Information</h2>
<p class="adm-panel__desc">Name, type and descriptions shown on the storefront.</p>
</div>
</div>
<div class="adm-panel__body">
<div class="adm-field">
<label>Product Name <span class="adm-required">*</span></label>
<input name="name" value="{{ old('name', $product->name) }}" placeholder="e.g. Banarasi Silk Saree" required class="adm-input-lg">
</div>

<div class="adm-field">
<label>Product Type</label>
<div class="adm-type-toggle" role="group" aria-label="Product type">
<button type="button" class="adm-type-toggle__btn {{ $productType === 'simple' ? 'is-active' : '' }}" data-type="simple">
<span class="adm-type-toggle__label">Simple</span>
<span class="adm-type-toggle__hint">Single price & stock</span>
</button>
<button type="button" class="adm-type-toggle__btn {{ $productType === 'variable' ? 'is-active' : '' }}" data-type="variable">
<span class="adm-type-toggle__label">Variable</span>
<span class="adm-type-toggle__hint">Sizes, colors & more</span>
</button>
</div>
<select name="product_type" id="product-type" class="adm-sr-only" aria-hidden="true" tabindex="-1">
<option value="simple" @selected($productType === 'simple')>Simple product</option>
<option value="variable" @selected($productType === 'variable')>Variable product</option>
</select>
</div>

<div class="adm-field">
<label>Short Description</label>
<textarea name="short_description" rows="2" placeholder="Brief summary for product cards">{{ old('short_description', $product->short_description) }}</textarea>
</div>
<div class="adm-field">
<label>Full Description</label>
<textarea name="description" rows="5" placeholder="Detailed product information">{{ old('description', $product->description) }}</textarea>
</div>
</div>
</section>

{{-- Media --}}
<section class="adm-panel">
<div class="adm-panel__head">
<div class="adm-panel__icon">🖼️</div>
<div>
<h2 class="adm-panel__title">Product Media</h2>
<p class="adm-panel__desc">Main image used on listings and product page.</p>
</div>
</div>
<div class="adm-panel__body">
<div class="adm-upload-zone" id="main-upload-zone">
<div class="adm-upload-zone__preview" id="main-image-preview" @if(!$currentThumb) hidden @endif>
<img src="{{ $currentThumb }}" alt="Product preview" id="main-preview-img">
<button type="button" class="adm-upload-zone__clear" id="clear-main-preview" aria-label="Remove preview">&times;</button>
</div>
<div class="adm-upload-zone__drop" id="main-upload-drop" @if($currentThumb) hidden @endif>
<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
<p class="adm-upload-zone__title">Drop image here or click to upload</p>
<p class="adm-upload-zone__hint">JPG, PNG, WEBP or GIF · max 4MB</p>
</div>
<input type="file" name="thumbnail_file" id="thumbnail_file" accept="image/jpeg,image/png,image/webp,image/gif" class="adm-upload-zone__input">
</div>
<div class="adm-field" style="margin-top:16px">
<label>Or paste image URL</label>
<input name="thumbnail" value="{{ old('thumbnail', (filled($rawThumb) && str_starts_with((string) $rawThumb, 'http')) ? $rawThumb : '') }}" placeholder="https://...">
</div>
</div>
</section>

{{-- Simple pricing --}}
<section class="adm-panel" id="simple-fields">
<div class="adm-panel__head">
<div class="adm-panel__icon">💰</div>
<div>
<h2 class="adm-panel__title">Pricing & Inventory</h2>
<p class="adm-panel__desc">Set price, compare-at price and stock for simple products.</p>
</div>
</div>
<div class="adm-panel__body">
<div class="adm-field-grid adm-field-grid--4">
<div class="adm-field"><label>SKU</label><input name="sku" value="{{ old('sku', $product->sku) }}" placeholder="SKU-001"></div>
<div class="adm-field"><label>Price <span class="adm-required">*</span></label><div class="adm-input-prefix"><span>₹</span><input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" placeholder="0"></div></div>
<div class="adm-field"><label>Compare Price</label><div class="adm-input-prefix"><span>₹</span><input type="number" step="0.01" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}"></div></div>
<div class="adm-field"><label>Stock <span class="adm-required">*</span></label><input type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" min="0"></div>
</div>
</div>
</section>

{{-- Variable --}}
<section class="adm-panel adm-panel--variations" id="variable-fields" hidden>
<div class="adm-panel__head">
<div class="adm-panel__icon">🎨</div>
<div>
<h2 class="adm-panel__title">Variations</h2>
<p class="adm-panel__desc">Attributes, pricing and up to 3 images per variation.</p>
</div>
</div>
<div class="adm-panel__body">

<div class="adm-subsection">
<h3 class="adm-subsection__title">Attributes</h3>
@if($attributes->isEmpty())
<div class="adm-callout adm-callout--warn">
No attributes yet. <a href="{{ route('admin.attributes.index') }}">Create attributes</a> (e.g. Size, Color) first.
</div>
@else
<p class="adm-help">Choose which attributes this product uses for variations.</p>
<div class="adm-chip-grid">
@foreach($attributes as $attribute)
<label class="adm-chip {{ in_array($attribute->id, $selectedAttributes) ? 'is-selected' : '' }}">
<input type="checkbox" name="attribute_ids[]" value="{{ $attribute->id }}" class="js-product-attribute" @checked(in_array($attribute->id, $selectedAttributes))>
<span>{{ $attribute->name }}</span>
</label>
@endforeach
</div>
<p class="adm-help"><a href="{{ route('admin.attributes.index') }}">Manage attributes</a></p>
@endif
</div>

<div class="adm-subsection">
<h3 class="adm-subsection__title">Default Stock</h3>
<div class="adm-field adm-field--compact">
<input type="number" name="variable_default_stock" min="0" value="{{ $defaultVariantStock }}" style="max-width:140px">
<p class="adm-help">Applied to variations without their own stock value.</p>
</div>
</div>

<div class="adm-subsection">
<div class="adm-subsection__head">
<h3 class="adm-subsection__title">Variation Rows</h3>
<button type="button" class="adm-btn adm-btn-ghost adm-btn-sm" id="add-variation-row">
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
Add Variation
</button>
</div>
@error('variations')<div class="adm-callout adm-callout--error">{{ $message }}</div>@enderror
<div class="adm-variations-wrap">
<table class="adm-variations-table" id="variations-table">
<thead>
<tr>
<th>Options</th>
<th>SKU</th>
<th>Price</th>
<th>Compare</th>
<th>Stock</th>
<th>Images</th>
<th>Active</th>
<th></th>
</tr>
</thead>
<tbody id="variations-body">
@forelse($variations as $index => $variation)
@include('admin.products.partials.variation-row', ['index' => $index, 'variation' => $variation, 'attributes' => $attributes, 'selectedAttributes' => $selectedAttributes])
@empty
@include('admin.products.partials.variation-row', ['index' => 0, 'variation' => [], 'attributes' => $attributes, 'selectedAttributes' => $selectedAttributes])
@endforelse
</tbody>
</table>
</div>
</div>
</div>
</section>

</div>

<aside class="adm-product-aside">

<section class="adm-panel adm-panel--sticky">
<div class="adm-panel__head adm-panel__head--sm">
<h2 class="adm-panel__title">Categories</h2>
</div>
<div class="adm-panel__body">
@if($categories->isEmpty())
<div class="adm-callout adm-callout--warn">
<a href="{{ route('admin.categories.index') }}">Create categories</a> to organize products.
</div>
@else
<div class="adm-chip-grid adm-chip-grid--vertical">
@foreach($categories as $c)
<label class="adm-chip {{ in_array($c->id, $selectedCategories) ? 'is-selected' : '' }}">
<input type="checkbox" name="category_ids[]" value="{{ $c->id }}" @checked(in_array($c->id, $selectedCategories))>
<span>{{ $c->name }}</span>
</label>
@endforeach
</div>
@endif
</div>
</section>

<section class="adm-panel">
<div class="adm-panel__head adm-panel__head--sm">
<h2 class="adm-panel__title">Visibility</h2>
</div>
<div class="adm-panel__body adm-toggle-list">
<label class="adm-toggle-row">
<div class="adm-toggle-row__text"><strong>Active</strong><span>Visible on storefront</span></div>
<input type="checkbox" name="is_active" value="1" class="adm-toggle" @checked(old('is_active', $product->is_active ?? true))>
</label>
<label class="adm-toggle-row">
<div class="adm-toggle-row__text"><strong>Featured</strong><span>Show on homepage</span></div>
<input type="checkbox" name="is_featured" value="1" class="adm-toggle" @checked(old('is_featured', $product->is_featured))>
</label>
<label class="adm-toggle-row">
<div class="adm-toggle-row__text"><strong>New Arrival</strong><span>Mark as new</span></div>
<input type="checkbox" name="is_new" value="1" class="adm-toggle" @checked(old('is_new', $product->is_new))>
</label>
<label class="adm-toggle-row">
<div class="adm-toggle-row__text"><strong>Bestseller</strong><span>Highlight popular item</span></div>
<input type="checkbox" name="is_bestseller" value="1" class="adm-toggle" @checked(old('is_bestseller', $product->is_bestseller))>
</label>
</div>
</section>

@if($product->exists)
<section class="adm-panel adm-panel--meta">
<div class="adm-panel__body">
<dl class="adm-meta-list">
<dt>Product ID</dt><dd>#{{ $product->id }}</dd>
<dt>Type</dt><dd>{{ $product->isVariable() ? 'Variable' : 'Simple' }}</dd>
<dt>Slug</dt><dd class="adm-meta-mono">{{ $product->slug }}</dd>
</dl>
</div>
</section>
@endif

</aside>
</div>
</form>
</div>

<template id="variation-row-template">
@include('admin.products.partials.variation-row', ['index' => '__INDEX__', 'variation' => [], 'attributes' => $attributes, 'selectedAttributes' => $selectedAttributes])
</template>
@endsection

@push('scripts')
<script>window.productAttributes = @json($attributesJson);</script>
<script src="{{ asset('js/admin-product-variants.js') }}"></script>
<script src="{{ asset('js/admin-product-form.js') }}"></script>
@endpush
