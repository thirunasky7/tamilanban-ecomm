@extends('layouts.admin')
@section('title', 'Product Attributes')
@section('content')
<div class="adm-topbar">
<div class="adm-topbar-left"><button type="button" id="adm-menu-toggle" class="adm-menu-toggle" aria-label="Menu">☰</button>
<div><div class="adm-breadcrumb">Products / Attributes</div><h1>Product Attributes</h1></div></div>
</div>

<div class="adm-form-card" style="margin-bottom:20px">
<form method="POST" action="{{ route('admin.attributes.store') }}">@csrf
<div class="adm-form-grid">
<div class="adm-field"><label>Attribute Name *</label><input name="name" placeholder="e.g. Size, Color" required></div>
<div class="adm-field" style="display:flex;align-items:flex-end"><button class="adm-btn adm-btn-primary" type="submit">Add Attribute</button></div>
</div>
</form>
</div>

@foreach($attributes as $attribute)
<div class="adm-form-card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<h3 style="margin:0;font-size:16px">{{ $attribute->name }}</h3>
<form method="POST" action="{{ route('admin.attributes.destroy', $attribute) }}" onsubmit="return confirm('Delete this attribute?')">@csrf @method('DELETE')
<button class="adm-btn adm-btn-ghost" type="submit">Delete</button>
</form>
</div>
<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px">
@forelse($attribute->values as $value)
<form method="POST" action="{{ route('admin.attributes.values.destroy', $value) }}" style="display:inline-flex;align-items:center;gap:4px;background:var(--adm-surface-muted);padding:4px 8px;border-radius:6px">@csrf @method('DELETE')
<span>{{ $value->value }}</span>
<button type="submit" style="border:none;background:none;cursor:pointer;color:var(--adm-danger)">×</button>
</form>
@empty
<span style="font-size:13px;color:var(--adm-muted)">No values yet.</span>
@endforelse
</div>
<form method="POST" action="{{ route('admin.attributes.values.store', $attribute) }}" class="adm-form-grid">@csrf
<div class="adm-field"><label>Add Value</label><input name="value" placeholder="e.g. Red, XL" required></div>
<div class="adm-field"><label>Sort</label><input type="number" name="sort_order" value="0"></div>
<div class="adm-field" style="display:flex;align-items:flex-end"><button class="adm-btn adm-btn-primary" type="submit">Add</button></div>
</form>
</div>
@endforeach
@endsection
