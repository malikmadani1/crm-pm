<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Deals Pipeline" description="Visualize commercial opportunities by stage across the entire pipeline.">
            <a href="{{ route('deals.create') }}" class="btn-primary">{{ __('New Deal') }}</a>
        </x-page-header>
    </x-slot>

    <div class="grid gap-4 xl:grid-cols-7">
        @foreach($stages as $stage)
            <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-4">
                <div class="mb-4 flex items-center justify-between">
                    <div class="text-sm font-semibold text-white">{{ __($stage->name) }}</div>
                    <x-status-badge :value="$stage->deals->count()" :color="$stage->color" />
                </div>

                <div class="space-y-3">
                    @forelse($stage->deals as $deal)
                        <a href="{{ route('deals.show', $deal) }}" class="block rounded-2xl border border-white/10 bg-slate-950/60 p-4 hover:bg-slate-900">
                            <div class="text-sm font-semibold text-white">{{ $deal->title }}</div>
                            <div class="mt-1 text-xs text-slate-400">{{ $deal->customer?->name ?: $deal->lead?->name ?: __('N/A') }}</div>
                            <div class="mt-3 text-sm font-semibold text-cyan-300">${{ number_format($deal->value, 2) }}</div>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed border-white/10 px-4 py-6 text-center text-xs text-slate-500">{{ __('No deals in this stage.') }}</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
