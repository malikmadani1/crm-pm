@props(['title', 'description' => null])

@php
    $eyebrow = $attributes->get('eyebrow', 'Workspace');
@endphp

<div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <div class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300">{{ __($eyebrow) }}</div>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-white">{{ __($title) }}</h1>
        @if ($description)
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-400">{{ __($description) }}</p>
        @endif
    </div>
    @if (trim($slot))
        <div class="flex flex-wrap items-center gap-3">
            {{ $slot }}
        </div>
    @endif
</div>
