@props(['lead' => null, 'owners'])

<div class="grid gap-6 lg:grid-cols-2">
    <div class="panel space-y-5">
        <h3 class="text-lg font-semibold text-white">{{ __('Lead Profile') }}</h3>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Name') }}</label>
            <input type="text" name="name" value="{{ old('name', $lead?->name) }}" required>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $lead?->phone) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email', $lead?->email) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Company') }}</label>
                <input type="text" name="company_name" value="{{ old('company_name', $lead?->company_name) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Job Title') }}</label>
                <input type="text" name="job_title" value="{{ old('job_title', $lead?->job_title) }}">
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Address') }}</label>
            <input type="text" name="address" value="{{ old('address', $lead?->address) }}">
        </div>
    </div>

    <div class="panel space-y-5">
        <h3 class="text-lg font-semibold text-white">{{ __('Qualification') }}</h3>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('City') }}</label>
                <input type="text" name="city" value="{{ old('city', $lead?->city) }}">
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Country') }}</label>
                <input type="text" name="country" value="{{ old('country', $lead?->country) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Owner') }}</label>
                <select name="owner_id">
                    <option value="">{{ __('Select owner') }}</option>
                    @foreach($owners as $owner)
                        <option value="{{ $owner->id }}" @selected((string) old('owner_id', $lead?->owner_id) === (string) $owner->id)>{{ $owner->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Stage') }}</label>
                <select name="stage">
                    @foreach(\App\Models\Lead::STAGES as $stage)
                        <option value="{{ $stage }}" @selected(old('stage', $lead?->stage ?? 'new_lead') === $stage)>{{ __(str($stage)->replace('_', ' ')->title()->toString()) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('Status') }}</label>
                <select name="status">
                    @foreach(\App\Models\Lead::STATUSES as $status)
                        <option value="{{ $status }}" @selected(old('status', $lead?->status ?? 'open') === $status)>{{ __(str($status)->title()->toString()) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('Notes') }}</label>
            <textarea name="notes" rows="6">{{ old('notes', $lead?->notes) }}</textarea>
        </div>
    </div>
</div>
