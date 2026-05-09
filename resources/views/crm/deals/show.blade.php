<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$deal->title" description="Deal detail, owner, linked customer, and stage management.">
            @can('update', $deal)
                <a href="{{ route('deals.edit', $deal) }}" class="btn-secondary">{{ __('Edit') }}</a>
            @endcan
            @can('delete', $deal)
                <x-delete-action
                    :action="route('deals.destroy', $deal)"
                    :title="__('Delete deal')"
                    :message="__('Are you sure you want to delete this deal?')"
                />
            @endcan
        </x-page-header>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <div class="panel">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-slate-400">{{ $deal->customer?->name ?: $deal->lead?->name ?: __('Standalone Deal') }}</div>
                    <div class="mt-1 text-3xl font-semibold text-white">${{ number_format($deal->value, 2) }}</div>
                </div>
                <x-status-badge :value="$deal->stage?->name ?? $deal->status" :color="$deal->stage?->color ?? 'slate'" />
            </div>

            <dl class="mt-6 space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-400">{{ __('Owner') }}</dt><dd>{{ $deal->owner?->name ?: __('N/A') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">{{ __('Probability') }}</dt><dd>{{ $deal->probability }}%</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">{{ __('Expected Close') }}</dt><dd>{{ optional($deal->expected_close_date)->format('Y-m-d') ?: __('N/A') }}</dd></div>
            </dl>
        </div>

        <div class="panel">
            <h3 class="text-lg font-semibold text-white">{{ __('Update Stage') }}</h3>

            <form method="POST" action="{{ route('deals.update-stage', $deal) }}" class="mt-4 space-y-4">
                @csrf
                @method('PATCH')

                <select name="stage_id">
                    @foreach(\App\Models\DealStage::query()->orderBy('position')->get() as $stage)
                        <option value="{{ $stage->id }}" @selected($deal->stage_id === $stage->id)>{{ __($stage->name) }}</option>
                    @endforeach
                </select>

                <button class="btn-primary">{{ __('Move Deal') }}</button>
            </form>

            <div class="mt-6 rounded-2xl bg-white/5 p-4 text-sm text-slate-300">{{ $deal->notes ?: __('No notes for this deal yet.') }}</div>
        </div>
    </div>
</x-app-layout>
