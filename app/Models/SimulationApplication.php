<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulationApplication extends Model
{
    const STATUSES = ['draft', 'submitted', 'approved', 'rejected', 'cancelled'];

    protected $fillable = [
        'application_number',
        'applicant_name',
        'insured_name',
        'insured_birth_date',
        'beneficiary_name',
        'coverage_amount',
        'premium_amount',
        'currency',
        'status',
        'effective_date',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'insured_birth_date' => 'date:Y-m-d',
        'effective_date' => 'date:Y-m-d',
        'expiry_date' => 'date:Y-m-d',
        'coverage_amount' => 'decimal:2',
        'premium_amount' => 'decimal:2',
    ];
}
