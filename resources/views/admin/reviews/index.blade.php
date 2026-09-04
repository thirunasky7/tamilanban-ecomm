@extends('layouts.admin')
@section('title', 'Reviews')
@section('content')
<div class="adm-topbar"><div class="adm-topbar-left"><button type="button" id="adm-menu-toggle" class="adm-menu-toggle" aria-label="Menu">☰</button><h1>Product Reviews</h1></div></div>
<div class="adm-card adm-card-table"><div class="adm-table-wrap"><table class="adm-table adm-datatable">
<thead><tr><th>#</th><th>Product</th><th>Reviewer</th><th>Rating</th><th>Title</th><th>Comment</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
<tbody>@foreach($reviews as $i=>$r)<tr>
<td>{{ $i+1 }}</td><td>{{ $r->product?->name }}</td><td>{{ $r->reviewer_name }}</td><td>{{ str_repeat('★',$r->rating) }}</td><td>{{ $r->title }}</td><td>{{ Str::limit($r->comment,40) }}</td>
<td>@if($r->is_approved)<span class="adm-badge adm-badge-ok">Approved</span>@else<span class="adm-badge adm-badge-no">Hidden</span>@endif</td><td>{{ $r->created_at->format('d M Y') }}</td>
<td style="white-space:nowrap"><form action="{{ route('admin.reviews.toggle',$r) }}" method="POST" style="display:inline">@csrf @method('PATCH')<button class="adm-btn adm-btn-ghost adm-btn-sm" type="submit">Toggle</button></form>
<form action="{{ route('admin.reviews.destroy',$r) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="adm-btn adm-btn-danger adm-btn-sm" type="submit">Del</button></form></td>
</tr>@endforeach</tbody></table></div></div>
@endsection