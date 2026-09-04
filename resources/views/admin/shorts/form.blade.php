@extends('layouts.admin')
@section('title', $short->exists ? 'Edit Short' : 'Add Short')
@section('content')
<div class="adm-topbar"><div class="adm-topbar-left"><button type="button" id="adm-menu-toggle" class="adm-menu-toggle" aria-label="Menu">☰</button>
<div><div class="adm-breadcrumb"><a href="{{ route('admin.shorts.index') }}">Shorts</a> / {{ $short->exists ? 'Edit' : 'Add' }}</div><h1>{{ $short->exists ? 'Edit' : 'Add' }} Shop Short</h1></div></div></div>
<div class="adm-form-card"><form method="POST" action="{{ $short->exists ? route('admin.shorts.update', $short) : route('admin.shorts.store') }}">@csrf @if($short->exists) @method('PUT') @endif
<div class="adm-field"><label>Title *</label><input name="title" value="{{ old('title', $short->title) }}" required></div>
<div class="adm-field"><label>Description</label><textarea name="description" rows="2">{{ old('description', $short->description) }}</textarea></div>
<div class="adm-field"><label>Video URL *</label><input name="video_url" value="{{ old('video_url', $short->video_url) }}" required></div>
<div class="adm-field"><label>Thumbnail URL</label><input name="thumbnail" value="{{ old('thumbnail', $short->thumbnail) }}"></div>
<div class="adm-field"><label>Product</label><select name="product_id"><option value="">None</option>@foreach($products as $p)<option value="{{ $p->id }}" @selected(old('product_id',$short->product_id)==$p->id)>{{ $p->name }}</option>@endforeach</select></div>
<div class="adm-field"><label>Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', $short->sort_order ?? 0) }}"></div>
<div class="adm-checks"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $short->is_active ?? true))> Active</label></div>
<div class="adm-form-actions"><button class="adm-btn adm-btn-primary" type="submit">Save</button><a class="adm-btn adm-btn-ghost" href="{{ route('admin.shorts.index') }}">Cancel</a></div>
</form></div>
@endsection