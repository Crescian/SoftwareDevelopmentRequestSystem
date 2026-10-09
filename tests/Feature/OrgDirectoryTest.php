<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrgDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_units_are_fetched_and_mirrored_locally(): void
    {
        $bu1 = (string) Str::uuid();
        $bu2 = (string) Str::uuid();

        Http::fake([
            '*/org/business-units' => Http::response([
                ['id' => $bu1, 'name' => 'Corporate'],
                ['id' => $bu2, 'name' => 'Retail'],
            ], 200),
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('org.business-units'));

        $response->assertOk();
        $response->assertJson([
            ['id' => $bu1, 'name' => 'Corporate'],
            ['id' => $bu2, 'name' => 'Retail'],
        ]);

        $this->assertDatabaseHas('business_units', ['id' => $bu1, 'name' => 'Corporate']);
        $this->assertDatabaseHas('business_units', ['id' => $bu2, 'name' => 'Retail']);
    }

    public function test_companies_are_filtered_by_business_unit_and_mirrored_locally(): void
    {
        $buId = (string) Str::uuid();
        $coId = (string) Str::uuid();

        BusinessUnit::create(['id' => $buId, 'name' => 'Corporate']);

        Http::fake([
            '*/org/companies*' => Http::response([
                ['id' => $coId, 'name' => 'Leonio Corp', 'business_unit_id' => $buId, 'business_unit' => 'Corporate'],
            ], 200),
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('org.companies', ['business_unit_id' => $buId]));

        $response->assertOk();
        $response->assertJson([
            ['id' => $coId, 'name' => 'Leonio Corp'],
        ]);

        $this->assertDatabaseHas('companies', ['id' => $coId, 'name' => 'Leonio Corp', 'business_unit_id' => $buId]);
    }

    public function test_departments_are_filtered_by_company_and_mirrored_locally(): void
    {
        $buId = (string) Str::uuid();
        $coId = (string) Str::uuid();
        $deptId = (string) Str::uuid();

        BusinessUnit::create(['id' => $buId, 'name' => 'Corporate']);
        Company::create(['id' => $coId, 'name' => 'Leonio Corp', 'business_unit_id' => $buId]);

        Http::fake([
            '*/org/departments*' => Http::response([
                [
                    'id' => $deptId,
                    'name' => 'Information Technology',
                    'company_id' => $coId,
                    'company' => 'Leonio Corp',
                    'business_unit_id' => $buId,
                    'business_unit' => 'Corporate',
                ],
            ], 200),
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('org.departments', ['company_id' => $coId]));

        $response->assertOk();
        $response->assertJson([
            ['id' => $deptId, 'name' => 'Information Technology'],
        ]);

        $this->assertDatabaseHas('departments', [
            'id' => $deptId,
            'name' => 'Information Technology',
            'company_id' => $coId,
            'business_unit_id' => $buId,
        ]);
    }

    public function test_companies_requires_a_business_unit_id(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('org.companies'))
            ->assertInvalid(['business_unit_id']);
    }

    public function test_upstream_failure_returns_a_clean_error(): void
    {
        Http::fake([
            '*/org/business-units' => Http::response([], 500),
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('org.business-units'))
            ->assertStatus(502);
    }

    public function test_guest_cannot_access_org_directory(): void
    {
        $this->get(route('org.business-units'))->assertRedirect(route('login'));
    }
}
