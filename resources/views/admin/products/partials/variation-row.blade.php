@php
use App\Support\Media;
$valueIds = $variation['attribute_value_ids'] ?? [];
$galleryPaths = $variation['gallery'] ?? [];
if (empty($galleryPaths) && ! empty($variation['thumbnail'])) {
    $galleryPaths[0] = $variation['thumbnail'];
}
$imageLabels = ['1', '2', '3'];
@endphp
<tr class="variation-row">
<td class="adm-var-col-options">
@foreach($attributes as $attribute)
<div class="js-variation-attr adm-var-attr" data-attribute-id="{{ $attribute->id }}" @if(!in_array($attribute->id, $selectedAttributes)) hidden @endif>
<label class="adm-var-attr__label">{{ $attribute->name }}</label>
<select name="variations[{{ $index }}][attribute_value_ids][]" class="adm-var-input">
<option value="">Select</option>
@foreach($attribute->values as $value)
<option value="{{ $value->id }}" @selected(in_array($value->id, $valueIds))>{{ $value->value }}</option>
@endforeach
</select>
</div>
@endforeach
@if($index !== '__INDEX__' && !empty($variation['id']))
<input type="hidden" name="variations[{{ $index }}][id]" value="{{ $variation['id'] }}">
@endif
</td>
<td><input class="adm-var-input" name="variations[{{ $index }}][sku]" value="{{ $variation['sku'] ?? '' }}" placeholder="SKU"></td>
<td><div class="adm-input-prefix adm-input-prefix--sm"><span>₹</span><input class="adm-var-input" type="number" step="0.01" name="variations[{{ $index }}][price]" value="{{ $variation['price'] ?? '' }}" placeholder="0"></div></td>
<td><div class="adm-input-prefix adm-input-prefix--sm"><span>₹</span><input class="adm-var-input" type="number" step="0.01" name="variations[{{ $index }}][compare_at_price]" value="{{ $variation['compare_at_price'] ?? '' }}"></div></td>
<td><input class="adm-var-input adm-var-input--stock" type="number" name="variations[{{ $index }}][stock]" value="{{ $variation['stock'] ?? 0 }}" min="0"></td>
<td class="adm-var-col-images">
<div class="adm-var-images">
@foreach($imageLabels as $slot => $label)
@php
$rawImage = $galleryPaths[$slot] ?? null;
$imageUrl = filled($rawImage) ? Media::url($rawImage) : null;
$urlValue = (filled($rawImage) && str_starts_with((string) $rawImage, 'http')) ? $rawImage : '';
@endphp
<div class="adm-var-img-slot">
<span class="adm-var-img-slot__label">Img {{ $label }}</span>
@if($imageUrl)
<div class="adm-var-img-slot__preview"><img src="{{ $imageUrl }}" alt="Image {{ $label }}"></div>
@endif
@if($slot === 0)
<input type="file" name="variations[{{ $index }}][thumbnail_file]" accept="image/jpeg,image/png,image/webp,image/gif" class="adm-var-file js-var-file" title="Upload image {{ $label }}">
<input class="adm-var-input adm-var-input--url" name="variations[{{ $index }}][thumbnail]" value="{{ $urlValue }}" placeholder="URL">
@else
<input type="file" name="variations[{{ $index }}][gallery_files][{{ $slot }}]" accept="image/jpeg,image/png,image/webp,image/gif" class="adm-var-file js-var-file" title="Upload image {{ $label }}">
<input class="adm-var-input adm-var-input--url" name="variations[{{ $index }}][gallery][{{ $slot }}]" value="{{ $urlValue }}" placeholder="URL">
@endif
</div>
@endforeach
</div>
</td>
<td class="adm-var-col-active">
<input type="hidden" name="variations[{{ $index }}][is_active]" value="0">
<label class="adm-var-active">
<input type="checkbox" name="variations[{{ $index }}][is_active]" value="1" @checked(($variation['is_active'] ?? true))>
<span></span>
</label>
</td>
<td class="adm-var-col-remove">
<button type="button" class="adm-var-remove js-remove-variation" title="Remove variation" aria-label="Remove variation">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
</button>
</td>
</tr>
