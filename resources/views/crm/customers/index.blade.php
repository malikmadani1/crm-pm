<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Customers" description="Track active relationships, account ownership, and downstream delivery commitments.">
            @can('create', \App\Models\Customer::class)
                <a href="{{ route('customers.create') }}" class="btn-primary">{{ __('Create Customer') }}</a>
            @endcan
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <form class="panel grid gap-4 lg:grid-cols-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search customers') }}">

            <select name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(\App\Models\Customer::STATUSES as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ __(str($status)->replace('_', ' ')->title()->toString()) }}</option>
                @endforeach
            </select>

            <select name="owner_id">
                <option value="">{{ __('All owners') }}</option>
                @foreach($users as $owner)
                    <option value="{{ $owner->id }}" @selected((string) request('owner_id') === (string) $owner->id)>{{ $owner->name }}</option>
                @endforeach
            </select>

            <button class="btn-secondary">{{ __('Apply Filters') }}</button>
        </form>

        @if($customers->count())
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Owner') }}</th>
                            <th>{{ __('Portfolio') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td>
                                    <div class="font-semibold text-white">{{ $customer->name }}</div>
                                    <div class="text-xs text-slate-400">{{ collect([$customer->company_name, $customer->email])->filter()->join(' | ') }}</div>
                                </td>
                                <td><x-status-badge :value="$customer->status" :color="config('crm_pm.labels.customer_statuses.' . $customer->status . '.color', 'slate')" /></td>
                                <td>{{ $customer->owner?->name ?: __('N/A') }}</td>
                                <td class="text-xs text-slate-400">{{ $customer->deals_count }} {{ __('deals') }} | {{ $customer->projects_count }} {{ __('projects') }}</td>
                                <td class="text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('customers.show', $customer) }}" class="rounded-xl border border-cyan-400/20 bg-cyan-500/10 px-3 py-2 text-xs font-semibold text-cyan-300 transition hover:bg-cyan-500/20">
                                            {{ __('Open') }}
                                        </a>

                                        @can('update', $customer)
                                            <a href="{{ route('customers.edit', $customer) }}" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                                {{ __('Edit') }}
                                            </a>
                                        @endcan

                                        @can('delete', $customer)
                                            <x-delete-action
                                                :action="route('customers.destroy', $customer)"
                                                :title="__('Delete customer')"
                                                :message="__('Are you sure you want to delete this customer?')"
                                            />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $customers->links() }}
        @else
            <x-empty-state title="No customers found" message="Create a customer profile to connect CRM activity with projects and delivery." action="Create Customer" :href="route('customers.create')" />
        @endif
    </div>
</x-app-layout>
