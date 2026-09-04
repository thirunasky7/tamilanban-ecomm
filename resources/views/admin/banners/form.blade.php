@extends('layouts.admin')
@section('title', $banner->exists ? 'Edit Banner' : 'Add Banner')
@section('content')
<div class="adm-topbar"><div class="adm-topbar-left"><button type="button" id="adm-menu-toggle" class="adm-menu-toggle" aria-label="Menu">☰</button>
<div><div class="adm-breadcrumb"><a href="{{ route('admin.banners.index') }}">Banners</a> / {{ $banner->exists ? 'Edit' : 'Add' }}</div><h1>{{ $banner->exists ? 'Edit' : 'Add' }} Banner</h1></div></div></div>
<div class="adm-form-card"><form method="POST" action="{{ $banner->exists ? route('admin.banners.update', $banner) : route('admin.banners.store') }}">@csrf @if($banner->exists) @method('PUT') @endif
<div class="adm-field"><label>Title</label><input name="title" value="{{ old('title', $banner->title) }}"></div>
<div class="adm-field"><label>Subtitle</label><input name="subtitle" value="{{ old('subtitle', $banner->subtitle) }}"></div>
<div class="adm-field"><label>Image URL *</label><input name="image" value="{{ old('image', $banner->image) }}" required></div>
<div class="adm-field"><label>Link</label><input name="link" value="{{ old('link', $banner->link) }}"></div>
<div class="adm-field"><label>Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order ?? 0) }}"></div>
<div class="adm-checks"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner->is_active ?? true))> Active</label></div>
<div class="adm-form-actions"><button class="adm-btn adm-btn-primary" type="submit">Save</button><a class="adm-btn adm-btn-ghost" href="{{ route('admin.banners.index') }}">Cancel</a></div>
</form></div>
@endsection