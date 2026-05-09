<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDealStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('deals.pipeline');
    }

    public function rules(): array
    {
        return [
            'stage_id' => ['required', 'integer', 'exists:deal_stages,id'],
        ];
    }
}
