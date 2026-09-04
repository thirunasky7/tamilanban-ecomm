@php
    use App\Support\LocaleManager;
    $locales = LocaleManager::enabledLocales();
    $current = app()->getLocale();
@endphp
@if(LocaleManager::switcherEnabled() && count($locales) > 1)
<div class="msh-lang-switch {{ $compact ?? false ? 'msh-lang-switch--compact' : '' }}">
@foreach($locales as $code => $meta)
<a
    href="{{ route('locale.switch', $code) }}"
    class="msh-lang-btn {{ $current === $code ? 'active' : '' }}"
    hreflang="{{ $code }}"
    title="{{ $meta['native'] }}"
>{{ strtoupper($code) }}</a>
@endforeach
</div>
@endif
