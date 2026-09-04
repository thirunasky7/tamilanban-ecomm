@php
$score = min(5, max(0, (float) ($score ?? 0)));
$reviewCount = isset($reviewCount) ? (int) $reviewCount : null;
$filled = (int) round($score);
@endphp
<div class="msh-rating">
<span class="msh-rating-stars">
@for($i = 1; $i <= 5; $i++)
<span class="{{ $i <= $filled ? '' : 'empty' }}">{{ $i <= $filled ? '★' : '☆' }}</span>
@endfor
</span>
<span class="msh-rating-value">{{ number_format($score, 1) }}</span>
@if($reviewCount !== null)<span class="msh-rating-count">({{ number_format($reviewCount) }})</span>@endif
</div>
