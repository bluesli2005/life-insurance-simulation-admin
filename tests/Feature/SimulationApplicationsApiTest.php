<?php

namespace Tests\Feature;

use App\Models\SimulationApplication;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulationApplicationsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]));
    }

    public function test_authenticated_user_can_create_read_update_and_delete_an_application()
    {
        $payload = $this->validPayload();

        $created = $this->postJson('/admin/api/v1/simulation-applications', $payload)
            ->assertCreated()
            ->assertJsonPath('data.application_number', 'SIM-TEST-0001')
            ->assertJsonPath('data.currency', 'JPY')
            ->json('data');

        $this->getJson('/admin/api/v1/simulation-applications/'.$created['id'])
            ->assertOk()
            ->assertJsonPath('data.applicant_name', '申込 太郎');

        $this->patchJson('/admin/api/v1/simulation-applications/'.$created['id'], [
            'beneficiary_name' => '受取 花子',
            'expiry_date' => '2035-01-01',
        ])->assertOk()
            ->assertJsonPath('data.beneficiary_name', '受取 花子');

        $this->deleteJson('/admin/api/v1/simulation-applications/'.$created['id'])
            ->assertOk()
            ->assertJsonPath('data', null);

        $this->assertDatabaseMissing('simulation_applications', ['id' => $created['id']]);
    }

    public function test_invalid_application_data_returns_japanese_validation_errors()
    {
        $this->postJson('/admin/api/v1/simulation-applications', array_merge($this->validPayload(), [
            'insured_birth_date' => now()->addDay()->toDateString(),
            'coverage_amount' => 0,
            'status' => 'unknown',
            'expiry_date' => '2020-01-01',
        ]))->assertStatus(422)
            ->assertJsonValidationErrors([
                'insured_birth_date',
                'coverage_amount',
                'status',
                'expiry_date',
            ]);

        $this->assertSame('申込番号は必須です。', trans('validation.required', ['attribute' => '申込番号']));
    }

    public function test_application_number_must_be_unique()
    {
        factory(SimulationApplication::class)->create(['application_number' => 'SIM-DUPLICATE']);

        $this->postJson('/admin/api/v1/simulation-applications', array_merge($this->validPayload(), [
            'application_number' => 'SIM-DUPLICATE',
        ]))->assertStatus(422)
            ->assertJsonValidationErrors(['application_number']);
    }

    public function test_list_supports_keyword_search_status_filter_and_pagination()
    {
        factory(SimulationApplication::class)->create([
            'application_number' => 'SIM-SEARCH-001',
            'applicant_name' => '検索対象 太郎',
            'status' => 'submitted',
        ]);
        factory(SimulationApplication::class)->create([
            'application_number' => 'SIM-SEARCH-002',
            'applicant_name' => '検索対象 花子',
            'status' => 'approved',
        ]);

        $this->getJson('/admin/api/v1/simulation-applications?search='.urlencode('検索対象').'&status=submitted&per_page=10')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('data.0.application_number', 'SIM-SEARCH-001')
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'last_page', 'total'], 'links' => ['first', 'last', 'prev', 'next']]);
    }

    public function test_pagination_size_must_be_in_the_allowed_list()
    {
        $this->getJson('/admin/api/v1/simulation-applications?per_page=15')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_business_api_requires_authentication_and_returns_json_404()
    {
        auth()->logout();

        $this->getJson('/admin/api/v1/simulation-applications')->assertUnauthorized();
        $this->actingAs(User::where('email', 'admin@example.com')->first());
        $this->getJson('/admin/api/v1/simulation-applications/999')->assertNotFound();
    }

    public function test_sample_seeder_is_repeatable_and_does_not_overwrite_existing_rows()
    {
        $this->seed('SimulationApplicationSeeder');
        $this->assertSame(120, SimulationApplication::count());

        $application = SimulationApplication::where('application_number', 'SIM-2026-0001')->firstOrFail();
        $application->update(['notes' => '利用者が更新した内容']);

        $this->seed('SimulationApplicationSeeder');

        $this->assertSame(120, SimulationApplication::count());
        $this->assertSame('利用者が更新した内容', $application->fresh()->notes);
        $this->assertSame(24, SimulationApplication::where('status', 'draft')->count());
        $this->assertSame(24, SimulationApplication::where('status', 'cancelled')->count());
    }

    private function validPayload()
    {
        return [
            'application_number' => 'SIM-TEST-0001',
            'applicant_name' => '申込 太郎',
            'insured_name' => '被保険者 太郎',
            'insured_birth_date' => '1980-01-01',
            'beneficiary_name' => null,
            'coverage_amount' => 10000000,
            'premium_amount' => 25000,
            'status' => 'draft',
            'effective_date' => '2030-01-01',
            'expiry_date' => null,
            'notes' => null,
        ];
    }
}
