@props(['label', 'value', 'hint' => null, 'accent' => 'cyan'])

<div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5 shadow-lg shadow-slate-950/10">
    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-{{ $accent }}-300">{{ __($label) }}</div>
    <div class="mt-4 text-3xl font-semibold text-white">{{ $value }}</div>
    @if ($hint)
        <div class="mt-3 text-sm text-slate-400">{{ __($hint) }}</div>
    @endif
</div>
