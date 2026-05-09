<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$customer->name" :description="$customer->company_name ?: 'Customer profile and related CRM/PM activity.'">
            @can('update', $customer)
                <a href="{{ route('customers.edit', $customer) }}" class="btn-secondary">{{ __('Edit') }}</a>
            @endcan
            @can('delete', $customer)
                <x-delete-action
                    :action="route('customers.destroy', $customer)"
                    :title="__('Delete customer')"
                    :message="__('Are you sure you want to delete this customer?')"
                />
            @endcan
        </x-page-header>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
        <div class="space-y-6">
            <div class="panel">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-400">{{ $customer->company_name ?: __('Individual account') }}</div>
                        <div class="mt-1 text-2xl font-semibold text-white">{{ $customer->name }}</div>
                    </div>
                    <x-status-badge :value="$customer->status" :color="config('crm_pm.labels.customer_statuses.' . $customer->status . '.color', 'slate')" />
                </div>

                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-400">{{ __('Email') }}</dt><dd>{{ $customer->email ?: __('N/A') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">{{ __('Phone') }}</dt><dd>{{ $customer->phone ?: __('N/A') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">{{ __('Owner') }}</dt><dd>{{ $customer->owner?->name ?: __('N/A') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">{{ __('Location') }}</dt><dd>{{ collect([$customer->city, $customer->country])->filter()->join(', ') ?: __('N/A') }}</dd></div>
                </dl>
            </div>

            <div class="panel">
                <h3 class="text-lg font-semibold text-white">{{ __('Add Interaction') }}</h3>

                <form method="POST" action="{{ route('customer-interactions.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                    <select name="type">
                        @foreach(\App\Models\CustomerInteraction::TYPES as $type)
                            <option value="{{ $type }}">{{ __(str($type)->replace('_', ' ')->title()->toString()) }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="subject" placeholder="{{ __('Subject') }}">
                    <textarea name="details" rows="4" placeholder="{{ __('Interaction details') }}"></textarea>
                    <input type="datetime-local" name="interaction_at" value="{{ now()->format('Y-m-d\\TH:i') }}">

                    <button class="btn-primary">{{ __('Add Interaction') }}</button>
                </form>
            </div>

            <div class="panel">
                <h3 class="text-lg font-semibold text-white">{{ __('Schedule Follow-up') }}</h3>

                <form method="POST" action="{{ route('follow-ups.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                    <input type="text" name="title" placeholder="{{ __('Follow-up title') }}">

                    <select name="assigned_to">
                        <option value="">{{ __('Assign to') }}</option>
                        @foreach(\App\Models\User::query()->active()->orderBy('name')->get() as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>

                    <div class="grid gap-4 md:grid-cols-2">
                        <select name="status">
                            @foreach(\App\Models\FollowUp::STATUSES as $status)
                                <option value="{{ $status }}">{{ __(str($status)->title()->toString()) }}</option>
                            @endforeach
                        </select>

                        <select name="priority">
                            @foreach(\App\Models\FollowUp::PRIORITIES as $priority)
                                <option value="{{ $priority }}">{{ __(str($priority)->title()->toString()) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="datetime-local" name="due_at" value="{{ now()->addDay()->format('Y-m-d\\TH:i') }}">
                    <textarea name="notes" rows="3" placeholder="{{ __('Notes') }}"></textarea>

                    <button class="btn-primary">{{ __('Create Follow-up') }}</button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel">
                <h3 class="text-lg font-semibold text-white">{{ __('Interaction Timeline') }}</h3>

                <div class="mt-4 space-y-4">
                    @forelse($customer->interactions->sortByDesc('interaction_at') as $interaction)
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold text-white">{{ $interaction->subject ?: __(str($interaction->type)->replace('_', ' ')->title()->toString()) }}</div>
                                <div class="text-xs text-slate-500">{{ $interaction->interaction_at?->format('Y-m-d H:i') }}</div>
                            </div>
                            <div class="mt-2 text-sm text-slate-300">{{ $interaction->details }}</div>
                            <div class="mt-2 text-xs text-slate-400">{{ __('By') }} {{ $interaction->user?->name ?: __('System') }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-400">{{ __('No interactions yet.') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h3 class="text-lg font-semibold text-white">{{ __('Open Follow-ups') }}</h3>

                <div class="mt-4 space-y-3">
                    @forelse($customer->followUps as $followUp)
                        <div class="rounded-2xl bg-white/5 px-4 py-4">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold text-white">{{ $followUp->title }}</div>
                                <x-status-badge :value="$followUp->status" :color="config('crm_pm.labels.follow_up_statuses.' . $followUp->status . '.color', 'slate')" />
                            </div>
                            <div class="mt-1 text-xs text-slate-400">{{ __('Due') }} {{ $followUp->due_at?->format('Y-m-d H:i') }} | {{ $followUp->assignee?->name ?: __('Unassigned') }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-400">{{ __('No follow-ups scheduled.') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h3 class="text-lg font-semibold text-white">{{ __('Connected Projects & Deals') }}</h3>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <div class="mb-3 text-sm font-semibold text-white">{{ __('Deals') }}</div>
                        @forelse($customer->deals as $deal)
                            <a href="{{ route('deals.show', $deal) }}" class="mb-3 block rounded-2xl bg-white/5 px-4 py-4 hover:bg-white/10">
                                <div class="font-semibold text-white">{{ $deal->title }}</div>
                                <div class="mt-1 text-xs text-slate-400">${{ number_format($deal->value, 2) }} | {{ __($deal->stage?->name ?? 'Unknown') }}</div>
                            </a>
                        @empty
                            <div class="text-sm text-slate-400">{{ __('No deals linked.') }}</div>
                        @endforelse
                    </div>

                    <div>
                        <div class="mb-3 text-sm font-semibold text-white">{{ __('Projects') }}</div>
                        @forelse($customer->projects as $project)
                            <a href="{{ route('projects.show', $project) }}" class="mb-3 block rounded-2xl bg-white/5 px-4 py-4 hover:bg-white/10">
                                <div class="font-semibold text-white">{{ $project->name }}</div>
                                <div class="mt-1 text-xs text-slate-400">{{ __(str($project->status)->replace('_', ' ')->title()->toString()) }} | {{ $project->progress }}%</div>
                            </a>
                        @empty
                            <div class="text-sm text-slate-400">{{ __('No projects linked.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
