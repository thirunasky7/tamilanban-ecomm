@extends('layouts.admin')
@section('title', 'Products')
@section('content')
<div class="adm-topbar">
<div class="adm-topbar-left"><button type="button" id="adm-menu-toggle" class="adm-menu-toggle" aria-label="Menu">☰</button><h1>Products</h1></div>
<a class="adm-btn adm-btn-primary" href="{{ route('admin.products.create') }}">+ Add Product</a>
</div>
<div class="adm-card adm-card-table">
<div class="adm-table-wrap">
<table class="adm-table adm-datatable">
<thead><tr><th>#</th><th>Name</th><th>Category</th><th>SKU</th><th>Type</th><th>Price</th><th>Stock</th><th>Featured</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>@foreach($products as $i=>$product)<tr>
<td>{{ $i+1 }}</td><td>{{ $product->name }}</td><td>{{ $product->categoryNamesLabel() ?: '—' }}</td><td>{{ $product->sku ?: '—' }}</td>
<td>{{ $product->isVariable() ? 'Variable' : 'Simple' }}</td>
<td>{{ $product->isVariable() ? $product->displayPriceLabel() : '₹'.number_format($product->price,0) }}</td><td>{{ $product->isVariable() ? $product->totalStock() : $product->stock }}</td><td>{{ $product->is_featured?'Yes':'No' }}</td>
<td>@if($product->is_active)<span class="adm-badge adm-badge-ok">Active</span>@else<span class="adm-badge adm-badge-no">Hidden</span>@endif</td>
<td style="white-space:nowrap"><a class="adm-btn adm-btn-ghost adm-btn-sm" href="{{ route('admin.products.edit',$product) }}">Edit</a>
<form action="{{ route('admin.products.destroy',$product) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="adm-btn adm-btn-danger adm-btn-sm" type="submit">Del</button></form></td>
</tr>@endforeach</tbody></table></div></div>
@endsection