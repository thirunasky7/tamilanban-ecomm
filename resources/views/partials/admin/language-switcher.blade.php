@php
    use App\Support\LocaleManager;
    $locales = LocaleManager::enabledLocales();
    $current = app()->getLocale();
@endphp
@if(count($locales) > 1)
<div class="adm-lang-switch">
@foreach($locales as $code => $meta)
<a href="{{ route('locale.switch', $code) }}" class="{{ $current === $code ? 'active' : '' }}">{{ strtoupper($code) }}</a>
@endforeach
</div>
@endif
