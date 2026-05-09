<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$lead->name" :description="$lead->company_name ?: 'Lead profile and conversion pipeline.'">
            @if($lead->status !== 'converted')
                <form method="POST" action="{{ route('leads.convert', $lead) }}">
                    @csrf
                    <button class="btn-primary">{{ __('Convert to Customer') }}</button>
                </form>
            @endif

            @can('update', $lead)
                <a href="{{ route('leads.edit', $lead) }}" class="btn-secondary">{{ __('Edit') }}</a>
            @endcan
            @can('delete', $lead)
                <x-delete-action
                    :action="route('leads.destroy', $lead)"
                    :title="__('Delete lead')"
                    :message="__('Are you sure you want to delete this lead?')"
                />
            @endcan
        </x-page-header>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
        <div class="space-y-6">
            <div class="panel">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-400">{{ $lead->company_name ?: __('N/A') }}</div>
                        <div class="mt-1 text-2xl font-semibold text-white">{{ $lead->name }}</div>
                    </div>
                    <x-status-badge :value="$lead->stage" :color="config('crm_pm.labels.lead_stages.' . $lead->stage . '.color', 'slate')" />
                </div>

                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-400">{{ __('Owner') }}</dt><dd>{{ $lead->owner?->name ?: __('N/A') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">{{ __('Converted Customer') }}</dt><dd>{{ $lead->customer?->name ?: __('N/A') }}</dd></div>
                </dl>
            </div>

            <div class="panel">
                <h3 class="text-lg font-semibold text-white">{{ __('Follow-ups') }}</h3>

                <div class="mt-4 space-y-3">
                    @forelse($lead->followUps as $followUp)
                        <div class="rounded-2xl bg-white/5 px-4 py-4">
                            <div class="font-semibold text-white">{{ $followUp->title }}</div>
                            <div class="mt-1 text-xs text-slate-400">{{ $followUp->due_at?->format('Y-m-d H:i') }} | {{ $followUp->assignee?->name ?: __('Unassigned') }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-400">{{ __('No follow-ups yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="panel">
            <h3 class="text-lg font-semibold text-white">{{ __('Related Deals') }}</h3>

            <div class="mt-4 space-y-3">
                @forelse($lead->deals as $deal)
                    <a href="{{ route('deals.show', $deal) }}" class="block rounded-2xl bg-white/5 px-4 py-4 hover:bg-white/10">
                        <div class="flex items-center justify-between">
                            <div class="font-semibold text-white">{{ $deal->title }}</div>
                            <x-status-badge :value="$deal->status" :color="$deal->status === 'won' ? 'emerald' : ($deal->status === 'lost' ? 'rose' : 'amber')" />
                        </div>
                        <div class="mt-1 text-xs text-slate-400">${{ number_format($deal->value, 2) }} | {{ __($deal->stage?->name ?? 'Unknown') }}</div>
                    </a>
                @empty
                    <div class="text-sm text-slate-400">{{ __('No deals linked to this lead.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
