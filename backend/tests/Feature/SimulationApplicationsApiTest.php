<?php

namespace Tests\Feature;

use App\Models\SimulationApplication;
use App\Role;
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
            'role_id' => Role::where('code', User::ROLE_SUPER_ADMIN)->value('id'),
        ]));
    }

    public function test_authenticated_user_can_create_read_update_and_delete_an_application()
    {
        $payload = $this->validPayload();

        $created = $this->postJson('/api/v1/simulation-applications', $payload)
            ->assertCreated()
            ->assertJsonPath('data.application_number', 'SIM-TEST-0001')
            ->assertJsonPath('data.currency', 'JPY')
            ->json('data');

        $this->getJson('/api/v1/simulation-applications/'.$created['id'])
            ->assertOk()
            ->assertJsonPath('data.applicant_name', '申込 太郎');

        $this->patchJson('/api/v1/simulation-applications/'.$created['id'], [
            'beneficiary_name' => '受取 花子',
            'expiry_date' => '2035-01-01',
        ])->assertOk()
            ->assertJsonPath('data.beneficiary_name', '受取 花子');

        $this->deleteJson('/api/v1/simulation-applications/'.$created['id'])
            ->assertOk()
            ->assertJsonPath('data', null);

        $this->assertSoftDeleted('simulation_applications', ['id' => $created['id']]);
        $this->getJson('/api/v1/simulation-applications/'.$created['id'])->assertNotFound();
        $this->getJson('/api/v1/simulation-applications')->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/simulation-applications/'.$created['id'].'/history')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.action', 'created')
            ->assertJsonPath('data.1.action', 'updated')
            ->assertJsonPath('data.1.before.beneficiary_name', null)
            ->assertJsonPath('data.1.after.beneficiary_name', '受取 花子')
            ->assertJsonPath('data.2.action', 'deleted')
            ->assertJsonPath('data.2.actor_user_id', auth()->id());
    }

    public function test_create_ignores_fields_outside_the_validated_allowlist()
    {
        $created = $this->postJson('/api/v1/simulation-applications', array_merge($this->validPayload(), [
            'id' => 999999,
            'deleted_at' => now()->toDateTimeString(),
        ]))->assertCreated()->json('data');

        $this->assertNotSame(999999, $created['id']);
        $this->assertDatabaseHas('simulation_applications', [
            'id' => $created['id'],
            'deleted_at' => null,
        ]);
    }

    public function test_audit_history_is_restricted_to_users_with_delete_permission()
    {
        $application = factory(SimulationApplication::class)->create();
        $viewer = User::create([
            'name' => '閲覧者',
            'email' => 'viewer@example.com',
            'password' => bcrypt('password'),
            'role_id' => Role::where('code', User::ROLE_VIEWER)->value('id'),
        ]);

        $this->actingAs($viewer)->getJson('/api/v1/simulation-applications/'.$application->id.'/history')
            ->assertForbidden();
    }

    public function test_partial_update_validates_effective_date_against_existing_expiry_date()
    {
        $application = factory(SimulationApplication::class)->create([
            'effective_date' => '2030-01-01',
            'expiry_date' => '2035-01-01',
        ]);

        $this->patchJson('/api/v1/simulation-applications/'.$application->id, [
            'effective_date' => '2036-01-01',
        ])->assertStatus(422)->assertJsonValidationErrors('effective_date');
        $this->assertSame('2030-01-01', $application->fresh()->effective_date->format('Y-m-d'));

        $this->patchJson('/api/v1/simulation-applications/'.$application->id, [
            'effective_date' => '2036-01-01',
            'expiry_date' => null,
        ])->assertOk();
        $this->assertNull($application->fresh()->expiry_date);
    }

    public function test_partial_update_validates_expiry_date_against_existing_effective_date()
    {
        $application = factory(SimulationApplication::class)->create([
            'effective_date' => '2030-01-01',
            'expiry_date' => null,
        ]);

        $this->patchJson('/api/v1/simulation-applications/'.$application->id, [
            'expiry_date' => '2029-12-31',
        ])->assertStatus(422)->assertJsonValidationErrors('expiry_date');
        $this->assertNull($application->fresh()->expiry_date);
    }

    public function test_invalid_application_data_returns_japanese_validation_errors()
    {
        $this->postJson('/api/v1/simulation-applications', array_merge($this->validPayload(), [
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

        $this->postJson('/api/v1/simulation-applications', array_merge($this->validPayload(), [
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

        $this->getJson('/api/v1/simulation-applications?search='.urlencode('検索対象').'&status=submitted&per_page=10')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('data.0.application_number', 'SIM-SEARCH-001')
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'last_page', 'total'], 'links' => ['first', 'last', 'prev', 'next']]);
    }

    public function test_search_treats_like_wildcards_as_literal_characters()
    {
        factory(SimulationApplication::class)->create([
            'application_number' => 'SIM-LITERAL-001',
            'notes' => 'Coverage is 100% guaranteed.',
        ]);
        factory(SimulationApplication::class)->create([
            'application_number' => 'SIM-LITERAL-002',
            'notes' => 'Coverage is guaranteed.',
        ]);

        $this->getJson('/api/v1/simulation-applications?search='.urlencode('%'))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.application_number', 'SIM-LITERAL-001');
    }

    public function test_search_sql_injection_payload_is_treated_as_search_text()
    {
        factory(SimulationApplication::class)->create(['application_number' => 'SIM-SQL-001']);
        factory(SimulationApplication::class)->create(['application_number' => 'SIM-SQL-002']);

        $payload = "%' OR 1=1 --";
        $this->getJson('/api/v1/simulation-applications?search='.urlencode($payload))
            ->assertOk()
            ->assertJsonPath('meta.total', 0)
            ->assertJsonCount(0, 'data');
    }

    public function test_pagination_size_must_be_in_the_allowed_list()
    {
        $this->getJson('/api/v1/simulation-applications?per_page=15')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_business_api_requires_authentication_and_returns_json_404()
    {
        auth()->logout();

        $this->getJson('/api/v1/simulation-applications')->assertUnauthorized();
        $this->actingAs(User::where('email', 'admin@example.com')->first());
        $this->getJson('/api/v1/simulation-applications/999')->assertNotFound();
    }

    public function test_sample_seeder_is_repeatable_and_refreshes_japanese_sample_text()
    {
        $this->seed('SimulationApplicationSeeder');
        $this->assertSame(120, SimulationApplication::count());

        $application = SimulationApplication::where('application_number', 'SIM-2026-0001')->firstOrFail();
        $application->update([
            'notes' => '利用者による備考変更',
            'coverage_amount' => 1234567.89,
        ]);

        $this->seed('SimulationApplicationSeeder');

        $this->assertSame(120, SimulationApplication::count());
        $this->assertSame('申込書類を受領し、内容を確認しています。', $application->fresh()->notes);
        $this->assertSame(1234567.89, (float) $application->fresh()->coverage_amount);
        $this->assertSame(24, SimulationApplication::where('status', 'draft')->count());
        $this->assertSame(24, SimulationApplication::where('status', 'cancelled')->count());

        $application->delete();
        $this->seed('SimulationApplicationSeeder');
        $this->assertSame(119, SimulationApplication::count());
        $this->assertSame(120, SimulationApplication::withTrashed()->count());
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
