@extends('layouts.admin')
@section('title', 'Banners')
@section('content')
<div class="adm-topbar"><div class="adm-topbar-left"><button type="button" id="adm-menu-toggle" class="adm-menu-toggle" aria-label="Menu">☰</button><h1>Banners</h1></div><a class="adm-btn adm-btn-primary" href="{{ route('admin.banners.create') }}">+ Add Banner</a></div>
<div class="adm-card adm-card-table"><div class="adm-table-wrap"><table class="adm-table adm-datatable">
<thead><tr><th>#</th><th>Title</th><th>Subtitle</th><th>Sort</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>@foreach($banners as $i=>$b)<tr><td>{{ $i+1 }}</td><td>{{ $b->title }}</td><td>{{ $b->subtitle }}</td><td>{{ $b->sort_order }}</td>
<td>@if($b->is_active)<span class="adm-badge adm-badge-ok">Yes</span>@else<span class="adm-badge adm-badge-no">No</span>@endif</td>
<td><a class="adm-btn adm-btn-ghost adm-btn-sm" href="{{ route('admin.banners.edit',$b) }}">Edit</a>
<form action="{{ route('admin.banners.destroy',$b) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="adm-btn adm-btn-danger adm-btn-sm" type="submit">Del</button></form></td></tr>@endforeach</tbody></table></div></div>
@endsection