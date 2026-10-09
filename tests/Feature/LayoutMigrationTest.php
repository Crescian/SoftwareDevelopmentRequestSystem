<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LayoutMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_pages_render_inside_the_shared_sidebar_layout_with_correct_titles(): void
    {
        $mst = User::factory()->mst()->create();
        $requestor = User::factory()->create();

        $department = Department::create([
            'id' => Str::uuid(),
            'code' => 'FIN',
            'name' => 'Finance',
            'is_active' => true,
        ]);

        $project = SoftwareProject::create([
            'id' => Str::uuid(),
            'project_id' => 'SDR-2026-001',
            'project_name' => 'Test Project',
            'requesting_department_id' => $department->id,
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
            'project_summary' => 'Summary',
            'business_problem' => 'Problem',
            'proposed_solution' => 'Solution',
            'status' => 'SUBMITTED',
        ]);

        // Index page: static title + sidebar present.
        $indexResponse = $this->actingAs($mst)->get(route('reviews.initial-review.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('<title>Initial Review Queue', false);
        $indexResponse->assertSee('sidebar-brand', false);
        $indexResponse->assertSee('Workflow Tracker');

        // Show page: dynamic title actually interpolated, not literal Blade syntax.
        $showResponse = $this->actingAs($mst)->get(route('reviews.initial-review.show', $project));
        $showResponse->assertOk();
        $showResponse->assertSee('<title>Initial Review — SDR-2026-001', false);
        $showResponse->assertDontSee('@section');
        $showResponse->assertDontSee('{{ $project->project_id }}', false);
        $showResponse->assertSee('sidebar-brand', false);
    }

    public function test_the_main_project_show_page_also_uses_the_shared_layout(): void
    {
        $requestor = User::factory()->create();
        $department = Department::create([
            'id' => Str::uuid(),
            'code' => 'FIN',
            'name' => 'Finance',
            'is_active' => true,
        ]);
        $project = SoftwareProject::create([
            'id' => Str::uuid(),
            'project_id' => 'SDR-2026-002',
            'project_name' => 'Another Project',
            'requesting_department_id' => $department->id,
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
            'project_summary' => 'Summary',
            'business_problem' => 'Problem',
            'proposed_solution' => 'Solution',
            'status' => 'DRAFT',
        ]);

        $response = $this->actingAs($requestor)->get(route('software-projects.show', $project));

        $response->assertOk();
        $response->assertSee('<title>Another Project', false);
        $response->assertSee('sidebar-brand', false);
    }
}
