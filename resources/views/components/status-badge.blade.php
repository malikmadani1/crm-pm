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

    $normalized = str((string) $value)->replace('-', '_')->snake()->lower()->toString();
    $labels = [
        'potential' => 'محتمل',
        'active' => 'نشط',
        'not_interested' => 'غير مهتم',
        'new_lead' => 'عميل محتمل جديد',
        'contacted' => 'تم التواصل',
        'qualified' => 'مؤهل',
        'proposal_sent' => 'تم إرسال العرض',
        'negotiation' => 'تفاوض',
        'won' => 'رابحة',
        'lost' => 'خاسرة',
        'in_progress' => 'قيد التنفيذ',
        'completed' => 'مكتمل',
        'paused' => 'متوقف',
        'on_hold' => 'معلّق',
        'todo' => 'للعمل',
        'to_do' => 'للعمل',
        'review' => 'قيد المراجعة',
        'done' => 'مكتملة',
        'low' => 'منخفضة',
        'medium' => 'متوسطة',
        'high' => 'عالية',
        'pending' => 'معلّق',
        'cancelled' => 'ملغى',
        'inactive' => 'غير نشط',
        'open' => 'مفتوحة',
        'closed' => 'مغلقة',
        'yes' => 'نعم',
        'no' => 'لا',
        'live_data' => 'بيانات مباشرة',
    ];

    $label = is_numeric($value)
        ? $value
        : __($labels[$normalized] ?? str((string) $value)->replace('_', ' ')->title()->toString());
@endphp

<span
    class="status-badge inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $palette[$color] ?? $palette['slate'] }}"
    data-color="{{ array_key_exists($color, $palette) ? $color : 'slate' }}"
>
    {{ $label }}
</span>
