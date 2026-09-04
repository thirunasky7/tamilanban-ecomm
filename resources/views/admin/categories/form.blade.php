@extends('layouts.admin')
@section('title', $category->exists ? 'Edit Category' : 'Add Category')
@section('content')
@php
$currentImage = $category->exists ? $category->image : null;
$rawImage = $category->exists ? $category->getRawOriginal('image') : null;
@endphp
<div class="adm-topbar"><div class="adm-topbar-left"><button type="button" id="adm-menu-toggle" class="adm-menu-toggle" aria-label="Menu">☰</button>
<div><div class="adm-breadcrumb"><a href="{{ route('admin.categories.index') }}">Categories</a> / {{ $category->exists ? 'Edit' : 'Add' }}</div><h1>{{ $category->exists ? 'Edit' : 'Add' }} Category</h1></div></div></div>
<div class="adm-form-card">
<form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data">
@csrf @if($category->exists) @method('PUT') @endif
<div class="adm-field"><label>Name *</label><input name="name" value="{{ old('name', $category->name) }}" required></div>
<div class="adm-field"><label>Description</label><textarea name="description" rows="3">{{ old('description', $category->description) }}</textarea></div>
<div class="adm-field">
<label>Category Image</label>
@if($currentImage)
<div class="adm-image-preview"><img src="{{ $currentImage }}" alt="Current category image"></div>
@endif
<input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" class="adm-file-input">
<p class="adm-help">JPG, PNG, WEBP or GIF · max 4MB</p>
<label style="margin-top:10px">Or Image URL</label>
<input name="image" value="{{ old('image', (filled($rawImage) && str_starts_with((string) $rawImage, 'http')) ? $rawImage : '') }}" placeholder="https://...">
</div>
<div class="adm-field"><label>Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}"></div>
<div class="adm-checks"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))> Active</label></div>
<div class="adm-form-actions"><button class="adm-btn adm-btn-primary" type="submit">Save</button><a class="adm-btn adm-btn-ghost" href="{{ route('admin.categories.index') }}">Cancel</a></div>
</form>
</div>
@endsection
