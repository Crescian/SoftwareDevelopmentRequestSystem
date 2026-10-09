<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkflowTrackerTest extends TestCase
{
    use RefreshDatabase;

    private function makeProject(array $overrides = []): SoftwareProject
    {
        $requestor = User::factory()->create();
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
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
            'project_summary' => 'Summary',
            'business_problem' => 'Problem',
            'proposed_solution' => 'Solution',
            'status' => 'SUBMITTED',
        ], $overrides));
    }

    public function test_non_mst_user_cannot_view_the_tracker(): void
    {
        $requestor = User::factory()->create();

        $this->actingAs($requestor)
            ->get(route('workflow.tracker.index'))
            ->assertForbidden();
    }

    public function test_mst_sees_projects_across_every_stage_excluding_draft(): void
    {
        $mst = User::factory()->mst()->create();

        $this->makeProject(['status' => 'DRAFT', 'project_name' => 'A Draft Project']);
        $this->makeProject(['status' => 'SUBMITTED', 'project_name' => 'A Submitted Project']);
        $this->makeProject(['status' => 'DEVELOPMENT', 'project_name' => 'A Development Project']);

        $response = $this->actingAs($mst)->get(route('workflow.tracker.index'));

        $response->assertOk();
        $response->assertDontSee('A Draft Project');
        $response->assertSee('A Submitted Project');
        $response->assertSee('A Development Project');
    }

    public function test_status_filter_narrows_the_list(): void
    {
        $mst = User::factory()->mst()->create();

        $this->makeProject(['status' => 'SUBMITTED', 'project_name' => 'Submitted One']);
        $this->makeProject(['status' => 'DEVELOPMENT', 'project_name' => 'Development One']);

        $response = $this->actingAs($mst)->get(route('workflow.tracker.index', ['status' => 'DEVELOPMENT']));

        $response->assertOk();
        $response->assertSee('Development One');
        $response->assertDontSee('Submitted One');
    }

    public function test_each_row_opens_the_correct_stage_action_page(): void
    {
        $mst = User::factory()->mst()->create();
        $project = $this->makeProject(['status' => 'DEVELOPMENT']);

        $response = $this->actingAs($mst)->get(route('workflow.tracker.index'));

        $response->assertOk();
        $response->assertSee(route('workflow.development.show', $project), false);
    }

    public function test_pending_approval_row_opens_the_management_approval_page(): void
    {
        $mst = User::factory()->mst()->create();
        $project = $this->makeProject(['status' => 'PENDING_APPROVAL']);

        $response = $this->actingAs($mst)->get(route('workflow.tracker.index'));

        $response->assertOk();
        $response->assertSee(route('approvals.management.show', $project), false);
    }
}
