<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Renders the workflow pages no other test opens, so a broken Blade view
 * shows up as a failure instead of a 500 in production.
 */
class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function projectAt(string $status): SoftwareProject
    {
        $requestor = User::factory()->create();

        $department = Department::create([
            'id' => Str::uuid(),
            'code' => 'FIN',
            'name' => 'Finance',
            'is_active' => true,
        ]);

        return SoftwareProject::create([
            'id' => Str::uuid(),
            'project_id' => 'SDR-2026-001',
            'project_name' => 'Test Project',
            'requesting_department_id' => $department->id,
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
            'project_summary' => 'Summary',
            'business_problem' => 'Problem',
            'proposed_solution' => 'Solution',
            'status' => $status,
        ]);
    }

    public function test_feasibility_assessment_show_renders(): void
    {
        $project = $this->projectAt('FEASIBILITY_ASSESSMENT');

        $this->actingAs(User::factory()->mst()->create())
            ->get(route('reviews.feasibility-assessment.show', $project))
            ->assertOk()
            ->assertSee('Test Project');
    }

    public function test_requirements_design_index_and_show_render(): void
    {
        $project = $this->projectAt('REQUIREMENTS_DESIGN');
        $mst = User::factory()->mst()->create();

        $this->actingAs($mst)
            ->get(route('workflow.requirements-design.index'))
            ->assertOk()
            ->assertSee('Test Project');

        $this->actingAs($mst)
            ->get(route('workflow.requirements-design.show', $project))
            ->assertOk();
    }

    public function test_testing_qa_show_renders(): void
    {
        $project = $this->projectAt('TESTING_QA');

        $this->actingAs(User::factory()->mst()->create())
            ->get(route('workflow.testing-qa.show', $project))
            ->assertOk();
    }

    public function test_deployment_index_renders(): void
    {
        $this->projectAt('DEPLOYMENT');

        $this->actingAs(User::factory()->mst()->create())
            ->get(route('workflow.deployment.index'))
            ->assertOk()
            ->assertSee('Test Project');
    }

    public function test_uat_index_renders(): void
    {
        $this->projectAt('USER_ACCEPTANCE_TESTING');

        $this->actingAs(User::factory()->mst()->create())
            ->get(route('workflow.uat.index'))
            ->assertOk()
            ->assertSee('Test Project');
    }
}
