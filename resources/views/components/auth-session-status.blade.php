@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'hidden']) }} aria-hidden="true"></div>
@endif
