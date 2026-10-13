<?php

namespace App\Http\Requests;

use App\Models\SimulationApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** 申込の登録・更新データを検証する。 */
class SimulationApplicationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $application = $this->route('simulation_application');
        // PATCH は送信項目だけを検証し、作成時は必須項目を要求する。
        $required = $this->isMethod('PATCH') ? 'sometimes' : 'required';
        $currencyRequired = $this->isMethod('POST') ? 'sometimes' : $required;
        // PATCH で省略された日付は既存値を使い、更新後の日付範囲を検証する。
        $effectiveDate = $this->input(
            'effective_date',
            $application ? $application->effective_date->format('Y-m-d') : 'today'
        );
        // 明示的な null は期限の解除として扱い、省略時だけ既存値を使う。
        $expiryDate = $this->exists('expiry_date')
            ? $this->input('expiry_date')
            : ($application && $application->expiry_date ? $application->expiry_date->format('Y-m-d') : null);

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
            'effective_date' => array_filter([$required, 'date', $expiryDate ? 'before_or_equal:'.$expiryDate : null]),
            'expiry_date' => ['nullable', 'date', 'after_or_equal:'.$effectiveDate],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
