<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use App\Services\ProjectOwnerResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectTrackerTest extends TestCase
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
            'project_name' => 'Tracker Test Project',
            'requesting_department_id' => $department->id,
            'project_summary' => 'Summary',
            'business_problem' => 'Problem',
            'proposed_solution' => 'Solution',
            'status' => 'SUBMITTED',
        ], $overrides));
    }

    public function test_the_show_page_lists_every_status_history_entry_chronologically(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->makeProject([
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
        ]);

        $project->statusHistories()->create([
            'id' => Str::uuid(), 'from_status' => 'DRAFT', 'to_status' => 'SUBMITTED',
            'changed_by' => $requestor->id, 'remarks' => null, 'changed_at' => now()->subDays(2),
        ]);
        $project->statusHistories()->create([
            'id' => Str::uuid(), 'from_status' => 'SUBMITTED', 'to_status' => 'RETURNED_FOR_REVISION',
            'changed_by' => $mst->id, 'remarks' => 'Missing budget figures.', 'changed_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($requestor)->get(route('software-projects.show', $project));

        $response->assertOk();
        $response->assertSeeInOrder(['Draft', 'Submitted', 'Submitted', 'Returned for Revision']);
        $response->assertSee($mst->name);
        $response->assertSee('Missing budget figures.');
    }

    public function test_current_owner_resolves_to_the_named_sponsor_at_pending_approval(): void
    {
        $sponsor = User::factory()->create(['name' => 'Jev Galindez']);
        $project = $this->makeProject([
            'requestor_id' => $sponsor->id,
            'project_sponsor_id' => $sponsor->id,
            'status' => 'PENDING_APPROVAL',
        ]);

        $response = $this->actingAs($sponsor)->get(route('software-projects.show', $project));

        $response->assertOk();
        $response->assertSee('Sponsor: Jev Galindez');
    }

    public function test_current_owner_resolves_to_the_named_project_manager_at_requirements_design(): void
    {
        $pm = User::factory()->projectManager()->create(['name' => 'Pat Reyes']);
        $requestor = User::factory()->create();
        $project = $this->makeProject([
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
            'project_manager_id' => $pm->id,
            'status' => 'REQUIREMENTS_DESIGN',
        ]);

        $response = $this->actingAs($pm)->get(route('software-projects.show', $project));

        $response->assertOk();
        $response->assertSee('Project Manager: Pat Reyes');
    }

    public function test_current_owner_falls_back_when_no_project_manager_is_assigned_yet(): void
    {
        $requestor = User::factory()->create();
        $project = $this->makeProject([
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
            'project_manager_id' => null,
            'status' => 'REQUIREMENTS_DESIGN',
        ]);

        $response = $this->actingAs($requestor)->get(route('software-projects.show', $project));

        $response->assertOk();
        $response->assertSee('Project Manager: Not yet assigned');
    }

    public function test_current_owner_resolves_to_the_named_requestor_at_user_acceptance_testing(): void
    {
        $requestor = User::factory()->create(['name' => 'Ana Cruz']);
        $project = $this->makeProject([
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
            'status' => 'USER_ACCEPTANCE_TESTING',
        ]);

        $response = $this->actingAs($requestor)->get(route('software-projects.show', $project));

        $response->assertOk();
        $response->assertSee('Requestor: Ana Cruz');
    }

    public function test_current_owner_shows_the_generic_mst_team_label_at_a_department_wide_stage(): void
    {
        $mst = User::factory()->mst()->create();
        $project = $this->makeProject([
            'requestor_id' => $mst->id,
            'project_sponsor_id' => $mst->id,
            'status' => 'SUBMITTED',
        ]);

        $response = $this->actingAs($mst)->get(route('software-projects.show', $project));

        $response->assertOk();
        $response->assertSee('MST Team — Submitted');
    }

    public function test_resolver_unit_level_behavior_without_hitting_http(): void
    {
        $sponsor = User::factory()->create(['name' => 'Jev Galindez']);
        $project = $this->makeProject([
            'requestor_id' => $sponsor->id,
            'project_sponsor_id' => $sponsor->id,
            'status' => 'PENDING_APPROVAL',
        ])->load('projectSponsor');

        $owner = (new ProjectOwnerResolver())->resolve($project);

        $this->assertSame('Sponsor: Jev Galindez', $owner->label);
        $this->assertFalse($owner->isDepartmentWide);
        $this->assertSame($sponsor->id, $owner->user->id);
    }

    public function test_my_requests_sidebar_link_stays_active_when_viewing_own_project(): void
    {
        $requestor = User::factory()->create();
        $project = $this->makeProject([
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
        ]);

        $response = $this->actingAs($requestor)->get(route('software-projects.show', $project));

        $response->assertOk();
        $response->assertSee('href="' . route('software-projects.index') . '" class="active"', false);
    }

    public function test_my_requests_sidebar_link_is_not_active_when_viewing_someone_elses_project(): void
    {
        $mst = User::factory()->mst()->create();
        $otherRequestor = User::factory()->create();
        $project = $this->makeProject([
            'requestor_id' => $otherRequestor->id,
            'project_sponsor_id' => $otherRequestor->id,
        ]);

        $response = $this->actingAs($mst)->get(route('software-projects.show', $project));

        $response->assertOk();
        $response->assertDontSee('href="' . route('software-projects.index') . '" class="active"', false);
    }
}
