<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Leads" description="Capture and qualify opportunities before converting them into customers and deals.">
            <a href="{{ route('leads.create') }}" class="btn-primary">{{ __('New Lead') }}</a>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <form class="panel grid gap-4 lg:grid-cols-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search leads') }}">

            <select name="stage">
                <option value="">{{ __('All stages') }}</option>
                @foreach(\App\Models\Lead::STAGES as $stage)
                    <option value="{{ $stage }}" @selected(request('stage') === $stage)>{{ __(str($stage)->replace('_', ' ')->title()->toString()) }}</option>
                @endforeach
            </select>

            <select name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(\App\Models\Lead::STATUSES as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ __(str($status)->title()->toString()) }}</option>
                @endforeach
            </select>

            <button class="btn-secondary">{{ __('Apply Filters') }}</button>
        </form>

        @if($leads->count())
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Lead') }}</th>
                            <th>{{ __('Stage') }}</th>
                            <th>{{ __('Owner') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                            <tr>
                                <td>
                                    <div class="font-semibold text-white">{{ $lead->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $lead->company_name }}</div>
                                </td>
                                <td><x-status-badge :value="$lead->stage" :color="config('crm_pm.labels.lead_stages.' . $lead->stage . '.color', 'slate')" /></td>
                                <td>{{ $lead->owner?->name ?: __('N/A') }}</td>
                                <td class="text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('leads.show', $lead) }}" class="rounded-xl border border-cyan-400/20 bg-cyan-500/10 px-3 py-2 text-xs font-semibold text-cyan-300 transition hover:bg-cyan-500/20">
                                            {{ __('Open') }}
                                        </a>
                                        @can('update', $lead)
                                            <a href="{{ route('leads.edit', $lead) }}" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                                {{ __('Edit') }}
                                            </a>
                                        @endcan
                                        @can('delete', $lead)
                                            <x-delete-action
                                                :action="route('leads.destroy', $lead)"
                                                :title="__('Delete lead')"
                                                :message="__('Are you sure you want to delete this lead?')"
                                            />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $leads->links() }}
        @else
            <x-empty-state title="No leads yet" message="Leads will appear here as your team starts prospecting." action="Create Lead" :href="route('leads.create')" />
        @endif
    </div>
</x-app-layout>
