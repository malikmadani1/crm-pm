<?php

namespace App\Http\Requests;

use App\Models\Deal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDealRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'value' => $this->input('value') ?? 0,
            'probability' => $this->input('probability') ?? 20,
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('deal'));
    }

    public function rules(): array
    {
        return [
            'lead_id' => ['nullable', 'integer', 'exists:leads,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'stage_id' => ['required', 'integer', 'exists:deal_stages,id'],
            'title' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'integer', 'between:0,100'],
            'expected_close_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(Deal::STATUSES)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
