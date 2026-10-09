<?php

namespace App\Http\Requests;

use App\Models\SimulationApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimulationApplicationIndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(SimulationApplication::STATUSES)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 30, 50])],
        ];
    }
}
