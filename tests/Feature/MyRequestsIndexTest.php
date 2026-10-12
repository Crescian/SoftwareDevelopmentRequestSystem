<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MyRequestsIndexTest extends TestCase
{
    use RefreshDatabase;

    private function makeProject(array $overrides = []): SoftwareProject
    {
        $department = Department::create([
            'id' => Str::uuid(),
            'code' => 'FIN-' . Str::random(6),
            'name' => 'Finance',
            'is_active' => true,
        ]);

        return SoftwareProject::create(array_merge([
            'id' => Str::uuid(),
            'project_id' => 'SDR-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'project_name' => 'My Requests Test Project',
            'requesting_department_id' => $department->id,
            'project_summary' => 'Summary',
            'business_problem' => 'Problem',
            'proposed_solution' => 'Solution',
            'status' => 'SUBMITTED',
        ], $overrides));
    }

    public function test_a_requestor_sees_only_their_own_projects(): void
    {
        $requestor = User::factory()->create();
        $other = User::factory()->create();

        $mine = $this->makeProject([
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
            'project_name' => 'My Own Project',
        ]);

        $this->makeProject([
            'requestor_id' => $other->id,
            'project_sponsor_id' => $other->id,
            'project_name' => 'Someone Elses Project',
        ]);

        $response = $this->actingAs($requestor)->get(route('software-projects.index'));

        $response->assertOk();
        $response->assertSee('My Own Project');
        $response->assertDontSee('Someone Elses Project');
        $response->assertSee($mine->project_id);
    }

    public function test_status_and_owner_render_for_each_project(): void
    {
        $sponsor = User::factory()->create(['name' => 'Sam Sponsor']);
        $requestor = User::factory()->create();
        $this->makeProject([
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $sponsor->id,
            'status' => 'PENDING_APPROVAL',
        ]);

        $response = $this->actingAs($requestor)->get(route('software-projects.index'));

        $response->assertOk();
        $response->assertSee('Pending Management Approval');
        $response->assertSee('Sponsor: Sam Sponsor');
    }

    public function test_the_list_paginates(): void
    {
        $requestor = User::factory()->create();

        for ($i = 0; $i < 20; $i++) {
            $this->makeProject([
                'requestor_id' => $requestor->id,
                'project_sponsor_id' => $requestor->id,
            ]);
        }

        $response = $this->actingAs($requestor)->get(route('software-projects.index'));

        $response->assertOk();
        $response->assertViewHas('projects', fn ($projects) => $projects->count() === 15 && $projects->total() === 20);
    }

    public function test_an_empty_list_shows_a_helpful_message(): void
    {
        $requestor = User::factory()->create();

        $response = $this->actingAs($requestor)->get(route('software-projects.index'));

        $response->assertOk();
        $response->assertSee('submitted any requests yet');
    }

    public function test_default_requestor_sees_the_home_treatment(): void
    {
        $requestor = User::factory()->create(['name' => 'Nia Sanchez']);
        $this->makeProject([
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
            'status' => 'SUBMITTED',
        ]);

        $response = $this->actingAs($requestor)->get(route('software-projects.index'));

        $response->assertOk();
        $response->assertSee('Nia Sanchez');
        $response->assertSee('For Review');
        $response->assertSee('Completed');
        $response->assertDontSee('Back to Dashboard');
    }

    public function test_non_requestor_role_does_not_see_the_home_treatment_on_their_own_requests(): void
    {
        $mst = User::factory()->mst()->create();

        $response = $this->actingAs($mst)->get(route('software-projects.index'));

        $response->assertOk();
        $response->assertSee('Back to Dashboard');
        $response->assertDontSee('Needs Your Decision');
    }
}
