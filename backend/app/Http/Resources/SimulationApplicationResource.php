<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SimulationApplicationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'application_number' => $this->application_number,
            'applicant_name' => $this->applicant_name,
            'insured_name' => $this->insured_name,
            'insured_birth_date' => optional($this->insured_birth_date)->format('Y-m-d'),
            'beneficiary_name' => $this->beneficiary_name,
            'coverage_amount' => $this->coverage_amount,
            'premium_amount' => $this->premium_amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'effective_date' => optional($this->effective_date)->format('Y-m-d'),
            'expiry_date' => optional($this->expiry_date)->format('Y-m-d'),
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
