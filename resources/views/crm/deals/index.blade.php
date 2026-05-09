<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Deals" description="Manage active revenue opportunities and closing momentum.">
            <a href="{{ route('deals.pipeline') }}" class="btn-secondary">{{ __('Open Pipeline') }}</a>
            <a href="{{ route('deals.create') }}" class="btn-primary">{{ __('New Deal') }}</a>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <form class="panel grid gap-4 lg:grid-cols-5">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search deals') }}">

            <select name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(\App\Models\Deal::STATUSES as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ __(str($status)->title()->toString()) }}</option>
                @endforeach
            </select>

            <select name="stage_id">
                <option value="">{{ __('All stages') }}</option>
                @foreach($stages as $stage)
                    <option value="{{ $stage->id }}" @selected((string) request('stage_id') === (string) $stage->id)>{{ __($stage->name) }}</option>
                @endforeach
            </select>

            <select name="owner_id">
                <option value="">{{ __('All owners') }}</option>
                @foreach($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((string) request('owner_id') === (string) $owner->id)>{{ $owner->name }}</option>
                @endforeach
            </select>

            <button class="btn-secondary">{{ __('Apply Filters') }}</button>
        </form>

        @if($deals->count())
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Deal') }}</th>
                            <th>{{ __('Stage') }}</th>
                            <th>{{ __('Owner') }}</th>
                            <th>{{ __('Value') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deals as $deal)
                            <tr>
                                <td>
                                    <div class="font-semibold text-white">{{ $deal->title }}</div>
                                    <div class="text-xs text-slate-400">{{ $deal->customer?->name ?: $deal->lead?->name ?: __('N/A') }}</div>
                                </td>
                                <td><x-status-badge :value="$deal->stage?->name ?? 'unknown'" :color="$deal->stage?->color ?? 'slate'" /></td>
                                <td>{{ $deal->owner?->name ?: __('N/A') }}</td>
                                <td>${{ number_format($deal->value, 2) }}</td>
                                <td class="text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('deals.show', $deal) }}" class="rounded-xl border border-cyan-400/20 bg-cyan-500/10 px-3 py-2 text-xs font-semibold text-cyan-300 transition hover:bg-cyan-500/20">
                                            {{ __('Open') }}
                                        </a>
                                        @can('update', $deal)
                                            <a href="{{ route('deals.edit', $deal) }}" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                                {{ __('Edit') }}
                                            </a>
                                        @endcan
                                        @can('delete', $deal)
                                            <x-delete-action
                                                :action="route('deals.destroy', $deal)"
                                                :title="__('Delete deal')"
                                                :message="__('Are you sure you want to delete this deal?')"
                                            />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $deals->links() }}
        @else
            <x-empty-state title="No deals yet" message="Create a deal to start tracking revenue stages and expected close dates." action="Create Deal" :href="route('deals.create')" />
        @endif
    </div>
</x-app-layout>
