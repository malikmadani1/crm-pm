<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Time Entry" description="Detailed record of logged work.">
            @if(auth()->user()->hasPermissionTo('time_entries.update'))
                <a href="{{ route('time-entries.edit', $timeEntry) }}" class="btn-secondary">{{ __('Edit') }}</a>
            @endif
            @if(auth()->user()->hasPermissionTo('time_entries.delete'))
                <x-delete-action
                    :action="route('time-entries.destroy', $timeEntry)"
                    :title="__('Delete time entry')"
                    :message="__('Are you sure you want to delete this time entry?')"
                />
            @endif
        </x-page-header>
    </x-slot>

    <div class="panel max-w-3xl">
        <dl class="grid gap-4 text-sm md:grid-cols-2">
            <div class="rounded-2xl bg-white/5 p-4"><dt class="text-slate-400">{{ __('User') }}</dt><dd class="mt-2 font-semibold text-white">{{ $timeEntry->user?->name }}</dd></div>
            <div class="rounded-2xl bg-white/5 p-4"><dt class="text-slate-400">{{ __('Project') }}</dt><dd class="mt-2 font-semibold text-white">{{ $timeEntry->project?->name }}</dd></div>
            <div class="rounded-2xl bg-white/5 p-4"><dt class="text-slate-400">{{ __('Task') }}</dt><dd class="mt-2 font-semibold text-white">{{ $timeEntry->task?->title ?: __('N/A') }}</dd></div>
            <div class="rounded-2xl bg-white/5 p-4"><dt class="text-slate-400">{{ __('Tracked') }}</dt><dd class="mt-2 font-semibold text-white">{{ round($timeEntry->minutes / 60, 1) }} {{ __('hours') }}</dd></div>
            <div class="rounded-2xl bg-white/5 p-4"><dt class="text-slate-400">{{ __('Started') }}</dt><dd class="mt-2 font-semibold text-white">{{ $timeEntry->started_at?->format('Y-m-d H:i') }}</dd></div>
            <div class="rounded-2xl bg-white/5 p-4"><dt class="text-slate-400">{{ __('Ended') }}</dt><dd class="mt-2 font-semibold text-white">{{ $timeEntry->ended_at?->format('Y-m-d H:i') ?: __('Open') }}</dd></div>
        </dl>

        <div class="mt-6 rounded-2xl bg-white/5 p-4 text-sm text-slate-300">{{ $timeEntry->description ?: __('No description provided.') }}</div>
    </div>
</x-app-layout>
