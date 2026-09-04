@extends('layouts.store')
@section('title', $title)
@section('header_title', $title)
@section('content')
<article class="msh-static-page">
{{ $slot }}
</article>
@endsection
