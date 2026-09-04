@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
<div class="adm-topbar"><div class="adm-topbar-left"><button type="button" id="adm-menu-toggle" class="adm-menu-toggle" aria-label="Menu">☰</button><h1>Categories</h1></div><a class="adm-btn adm-btn-primary" href="{{ route('admin.categories.create') }}">+ Add Category</a></div>
<div class="adm-card adm-card-table"><div class="adm-table-wrap"><table class="adm-table adm-datatable">
<thead><tr><th>#</th><th>Name</th><th>Slug</th><th>Sort</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>@foreach($categories as $i=>$cat)<tr><td>{{ $i+1 }}</td><td>{{ $cat->name }}</td><td>{{ $cat->slug }}</td><td>{{ $cat->sort_order }}</td>
<td>@if($cat->is_active)<span class="adm-badge adm-badge-ok">Active</span>@else<span class="adm-badge adm-badge-no">Hidden</span>@endif</td>
<td><a class="adm-btn adm-btn-ghost adm-btn-sm" href="{{ route('admin.categories.edit',$cat) }}">Edit</a>
<form action="{{ route('admin.categories.destroy',$cat) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="adm-btn adm-btn-danger adm-btn-sm" type="submit">Del</button></form></td></tr>@endforeach</tbody></table></div></div>
@endsection