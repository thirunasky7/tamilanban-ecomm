@extends('layouts.store')

@section('title', 'Favorites')

@section('content')
<section class="msh-section">
  <div class="msh-container">
    <h1 class="msh-section-title">Your favorites</h1>
    @if($products->isEmpty())
      <p class="msh-muted">No favorite products yet. Tap the heart on any product card to save it.</p>
    @else
      <div class="msh-product-grid">
        @foreach($products as $product)
          @include('components.product-card', [
            'product' => $product,
            'variant' => 'grid',
            'score' => $product->averageRating(),
            'reviewCount' => $product->reviewsCount(),
            'showAdd' => true,
          ])
        @endforeach
      </div>
    @endif
  </div>
</section>
@endsection