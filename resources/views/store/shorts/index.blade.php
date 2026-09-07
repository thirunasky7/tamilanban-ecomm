@extends('layouts.store')
@section('title', __('store.shop_shorts'))
@section('content')
<h1 class="msh-page-title">{{ __('store.shop_shorts') }}</h1>
<p style="font-size:13px;color:var(--msh-muted);margin:-8px 0 12px">{{ __('store.watch_shop') }}</p>
<div class="msh-shorts-grid">
@foreach($shorts as $short)
<div class="msh-short-full">
<img src="{{ $short->thumbnail }}" alt="{{ $short->title }}">
<div style="position:absolute;bottom:0;left:0;right:0;padding:10px;background:linear-gradient(transparent,rgba(0,0,0,.8));color:#fff;font-size:12px;font-weight:600">{{ $short->title }}</div>
@if($short->product?->slug)<a href="{{ route('products.show', $short->product->slug) }}" style="position:absolute;inset:0"></a>@endif
</div>
@endforeach
</div>
@endsection
