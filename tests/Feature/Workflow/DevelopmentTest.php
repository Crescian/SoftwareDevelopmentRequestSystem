<?php

namespace Tests\Feature\Workflow;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DevelopmentTest extends TestCase
{
    use RefreshDatabase;

    private function projectInDevelopment(User $requestor): SoftwareProject
    {
        $department = Department::create([
            'id' => Str::uuid(),
            'code' => 'FIN',
            'name' => 'Finance',
            'is_active' => true,
        ]);

        return SoftwareProject::create([
            'id' => Str::uuid(),
            'project_id' => 'SDR-2026-' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'project_name' => 'Test Project',
            'requesting_department_id' => $department->id,
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
            'project_summary' => 'Summary',
            'business_problem' => 'Problem',
            'proposed_solution' => 'Solution',
            'status' => 'DEVELOPMENT',
        ]);
    }

    public function test_non_mst_user_cannot_manage_development(): void
    {
        $requestor = User::factory()->create();
        $project = $this->projectInDevelopment($requestor);

        $this->actingAs($requestor)
            ->post(route('workflow.development.repositories.store', $project), [
                'name' => 'Repo',
                'repository_url' => 'https://example.com/repo.git',
            ])
            ->assertForbidden();
    }

    public function test_mst_can_add_a_repository_and_a_task(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInDevelopment($requestor);

        $this->actingAs($mst)->post(route('workflow.development.repositories.store', $project), [
            'name' => 'sdr-backend',
            'provider' => 'GitHub',
            'repository_url' => 'https://github.com/example/sdr-backend',
        ])->assertRedirect();

        $this->actingAs($mst)->post(route('workflow.development.tasks.store', $project), [
            'task_code' => 'DEV-001',
            'title' => 'Build submission form',
            'priority' => 'HIGH',
        ])->assertRedirect();

        $fresh = $project->fresh();
        $this->assertCount(1, $fresh->repositories);
        $this->assertCount(1, $fresh->developmentTasks);
        $this->assertSame('TODO', $fresh->developmentTasks->first()->status);
        $this->assertSame($mst->id, $fresh->developmentTasks->first()->created_by);
    }

    public function test_updating_task_status_to_in_progress_and_done_stamps_dates(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInDevelopment($requestor);

        $this->actingAs($mst)->post(route('workflow.development.tasks.store', $project), [
            'task_code' => 'DEV-001',
            'title' => 'Build submission form',
        ]);

        $task = $project->fresh()->developmentTasks->first();

        $this->actingAs($mst)->post(
            route('workflow.development.tasks.status', [$project, $task]),
            ['status' => 'IN_PROGRESS'],
        )->assertRedirect();

        $this->assertNotNull($task->fresh()->actual_start_date);
        $this->assertNull($task->fresh()->actual_end_date);

        $this->actingAs($mst)->post(
            route('workflow.development.tasks.status', [$project, $task]),
            ['status' => 'DONE'],
        )->assertRedirect();

        $this->assertNotNull($task->fresh()->actual_end_date);
    }

    public function test_mst_can_advance_the_project_to_testing_qa(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInDevelopment($requestor);

        $response = $this->actingAs($mst)->post(route('workflow.development.advance', $project));

        $response->assertRedirect(route('workflow.development.index'));
        $this->assertSame('TESTING_QA', $project->fresh()->status);
    }

    public function test_cannot_advance_a_project_not_in_development(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInDevelopment($requestor);
        $project->update(['status' => 'TESTING_QA']);

        $this->actingAs($mst)
            ->post(route('workflow.development.advance', $project))
            ->assertForbidden();
    }
}
