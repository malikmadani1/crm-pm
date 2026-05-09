<?php

namespace App\Http\Requests;

use App\Models\CustomerInteraction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerInteractionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('customers.update');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'type' => ['required', Rule::in(CustomerInteraction::TYPES)],
            'subject' => ['nullable', 'string', 'max:255'],
            'details' => ['required', 'string'],
            'interaction_at' => ['required', 'date'],
        ];
    }
}
