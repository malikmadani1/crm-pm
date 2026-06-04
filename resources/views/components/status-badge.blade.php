@props(['value', 'color' => 'slate'])

@php
    $palette = [
        'slate' => 'border-slate-400/20 bg-slate-500/10 text-slate-200',
        'cyan' => 'border-cyan-400/20 bg-cyan-500/10 text-cyan-200',
        'sky' => 'border-sky-400/20 bg-sky-500/10 text-sky-200',
        'indigo' => 'border-indigo-400/20 bg-indigo-500/10 text-indigo-200',
        'emerald' => 'border-emerald-400/20 bg-emerald-500/10 text-emerald-200',
        'amber' => 'border-amber-400/20 bg-amber-500/10 text-amber-200',
        'orange' => 'border-orange-400/20 bg-orange-500/10 text-orange-200',
        'rose' => 'border-rose-400/20 bg-rose-500/10 text-rose-200',
    ];

    $label = \App\Support\Labels::status($value);
@endphp

<span
    class="status-badge inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $palette[$color] ?? $palette['slate'] }}"
    data-color="{{ array_key_exists($color, $palette) ? $color : 'slate' }}"
>
    {{ $label }}
</span>
