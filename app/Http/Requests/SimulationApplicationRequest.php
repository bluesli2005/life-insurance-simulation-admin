<?php

namespace App\Http\Requests;

use App\Models\SimulationApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimulationApplicationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $application = $this->route('simulation_application');
        $required = $this->isMethod('PATCH') ? 'sometimes' : 'required';
        $currencyRequired = $this->isMethod('POST') ? 'sometimes' : $required;
        $effectiveDate = $this->input(
            'effective_date',
            $application ? $application->effective_date->format('Y-m-d') : 'today'
        );

        return [
            'application_number' => [$required, 'string', 'max:50', Rule::unique('simulation_applications')->ignore($application)],
            'applicant_name' => [$required, 'string', 'max:100'],
            'insured_name' => [$required, 'string', 'max:100'],
            'insured_birth_date' => [$required, 'date', 'before_or_equal:today'],
            'beneficiary_name' => ['nullable', 'string', 'max:100'],
            'coverage_amount' => [$required, 'numeric', 'gt:0', 'max:9999999999999.99'],
            'premium_amount' => [$required, 'numeric', 'gt:0', 'max:9999999999999.99'],
            'currency' => [$currencyRequired, 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'status' => [$required, Rule::in(SimulationApplication::STATUSES)],
            'effective_date' => [$required, 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:'.$effectiveDate],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
