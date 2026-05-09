@props(['customer' => null, 'owners'])

<div class="grid gap-6 lg:grid-cols-2">
    <div class="panel space-y-5">
        <h3 class="text-lg font-semibold text-white">{{ __('Customer Details') }}</h3>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Name') }}</label>
            <input type="text" name="name" value="{{ old('name', $customer?->name) }}" required>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $customer?->phone) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email', $customer?->email) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Company') }}</label>
                <input type="text" name="company_name" value="{{ old('company_name', $customer?->company_name) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Job Title') }}</label>
                <input type="text" name="job_title" value="{{ old('job_title', $customer?->job_title) }}">
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Address') }}</label>
            <input type="text" name="address" value="{{ old('address', $customer?->address) }}">
        </div>
    </div>

    <div class="panel space-y-5">
        <h3 class="text-lg font-semibold text-white">{{ __('Classification') }}</h3>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('City') }}</label>
                <input type="text" name="city" value="{{ old('city', $customer?->city) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Country') }}</label>
                <input type="text" name="country" value="{{ old('country', $customer?->country) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Source') }}</label>
                <input type="text" name="source" value="{{ old('source', $customer?->source) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Status') }}</label>
                <select name="status">
                    @foreach(\App\Models\Customer::STATUSES as $status)
                        <option value="{{ $status }}" @selected(old('status', $customer?->status ?? 'active') === $status)>{{ __(str($status)->replace('_', ' ')->title()->toString()) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Owner') }}</label>
            <select name="owner_id">
                <option value="">{{ __('Select owner') }}</option>
                @foreach($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((string) old('owner_id', $customer?->owner_id) === (string) $owner->id)>{{ $owner->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Notes') }}</label>
            <textarea name="notes" rows="8">{{ old('notes', $customer?->notes) }}</textarea>
        </div>
    </div>
</div>
