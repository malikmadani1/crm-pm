@props(['deal' => null, 'leads', 'customers', 'owners', 'stages'])

<div class="grid gap-6 lg:grid-cols-2">
    <div class="panel space-y-5">
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Title') }}</label>
            <input type="text" name="title" value="{{ old('title', $deal?->title) }}" required>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Lead') }}</label>
                <select name="lead_id">
                    <option value="">{{ __('No linked lead') }}</option>
                    @foreach($leads as $leadItem)
                        <option value="{{ $leadItem->id }}" @selected((string) old('lead_id', $deal?->lead_id) === (string) $leadItem->id)>{{ $leadItem->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Customer') }}</label>
                <select name="customer_id">
                    <option value="">{{ __('No linked customer') }}</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((string) old('customer_id', $deal?->customer_id) === (string) $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Owner') }}</label>
                <select name="owner_id">
                    <option value="">{{ __('Select owner') }}</option>
                    @foreach($owners as $owner)
                        <option value="{{ $owner->id }}" @selected((string) old('owner_id', $deal?->owner_id) === (string) $owner->id)>{{ $owner->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Stage') }}</label>
                <select name="stage_id">
                    @foreach($stages as $stage)
                        <option value="{{ $stage->id }}" @selected((string) old('stage_id', $deal?->stage_id ?? $stages->first()?->id) === (string) $stage->id)>{{ __($stage->name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="panel space-y-5">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Value') }}</label>
                <input type="number" step="0.01" name="value" value="{{ old('value', $deal?->value) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Probability %') }}</label>
                <input type="number" name="probability" value="{{ old('probability', $deal?->probability ?? 20) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Status') }}</label>
                <select name="status">
                    @foreach(\App\Models\Deal::STATUSES as $status)
                        <option value="{{ $status }}" @selected(old('status', $deal?->status ?? 'open') === $status)>{{ __(str($status)->title()->toString()) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Expected Close Date') }}</label>
            <input type="date" name="expected_close_date" value="{{ old('expected_close_date', optional($deal?->expected_close_date)->format('Y-m-d')) }}">
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Notes') }}</label>
            <textarea name="notes" rows="8">{{ old('notes', $deal?->notes) }}</textarea>
        </div>
    </div>
</div>
