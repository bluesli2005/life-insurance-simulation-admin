<?php

namespace Tests\Unit;

use App\Models\SimulationApplication;
use PHPUnit\Framework\TestCase;

class SimulationApplicationTest extends TestCase
{
    public function test_model_exposes_supported_statuses_and_persistable_fields()
    {
        $model = new SimulationApplication();

        $this->assertSame(['draft', 'submitted', 'approved', 'rejected', 'cancelled'], SimulationApplication::STATUSES);
        $this->assertContains('application_number', $model->getFillable());
        $this->assertContains('notes', $model->getFillable());
        $this->assertNotContains('id', $model->getFillable());
    }

    public function test_model_casts_dates_and_money_fields()
    {
        $casts = (new SimulationApplication())->getCasts();

        $this->assertSame('date:Y-m-d', $casts['insured_birth_date']);
        $this->assertSame('date:Y-m-d', $casts['effective_date']);
        $this->assertSame('decimal:2', $casts['coverage_amount']);
        $this->assertSame('decimal:2', $casts['premium_amount']);
    }
}
