<?php

namespace Tests\Feature\Workflow;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use RefreshDatabase;

    private function projectAtDeployment(User $requestor): SoftwareProject
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
            'status' => 'DEPLOYMENT',
        ]);
    }

    public function test_non_mst_user_cannot_manage_deployment(): void
    {
        $requestor = User::factory()->create();
        $project = $this->projectAtDeployment($requestor);

        $this->actingAs($requestor)
            ->post(route('workflow.deployment.deployments.store', $project), [
                'deployment_number' => 'DEP-001',
                'version' => '1.0.0',
                'environment' => 'PRODUCTION',
            ])
            ->assertForbidden();
    }

    public function test_cannot_mark_successful_while_required_checklist_items_are_incomplete(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectAtDeployment($requestor);

        $this->actingAs($mst)->post(route('workflow.deployment.deployments.store', $project), [
            'deployment_number' => 'DEP-001',
            'version' => '1.0.0',
            'environment' => 'PRODUCTION',
        ]);
        $deployment = $project->fresh()->deployments->first();

        $this->actingAs($mst)->post(route('workflow.deployment.checklist-items.store', [$project, $deployment]), [
            'category' => 'Backup',
            'item' => 'Database backup taken',
            'is_required' => '1',
        ]);

        $this->actingAs($mst)
            ->post(route('workflow.deployment.outcome', [$project, $deployment]), ['outcome' => 'SUCCESSFUL'])
            ->assertSessionHasErrors('checklist');

        $this->assertSame('DEPLOYMENT', $project->fresh()->status);
    }

    public function test_successful_outcome_after_checklist_complete_advances_to_monitoring(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectAtDeployment($requestor);

        $this->actingAs($mst)->post(route('workflow.deployment.deployments.store', $project), [
            'deployment_number' => 'DEP-001',
            'version' => '1.0.0',
            'environment' => 'PRODUCTION',
        ]);
        $deployment = $project->fresh()->deployments->first();

        $this->actingAs($mst)->post(route('workflow.deployment.checklist-items.store', [$project, $deployment]), [
            'category' => 'Backup',
            'item' => 'Database backup taken',
            'is_required' => '1',
        ]);
        $item = $deployment->fresh()->checklistItems->first();

        $this->actingAs($mst)->post(route('workflow.deployment.checklist-items.check', [$project, $item]), [
            'status' => 'DONE',
        ]);

        $this->actingAs($mst)->post(route('workflow.deployment.security-assessments.store', $project), [
            'assessment_number' => 'SEC-002',
            'overall_result' => 'PASS',
        ]);

        $response = $this->actingAs($mst)->post(
            route('workflow.deployment.outcome', [$project, $deployment]),
            ['outcome' => 'SUCCESSFUL'],
        );

        $response->assertRedirect(route('workflow.deployment.show', $project));

        $fresh = $project->fresh();
        $this->assertSame('MONITORING_MAINTENANCE', $fresh->status);
        $this->assertSame('COMPLETED', $deployment->fresh()->status);
        $this->assertNotNull($deployment->fresh()->completed_at);
    }

    public function test_failed_outcome_requires_remarks_and_stays_in_deployment(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectAtDeployment($requestor);

        $this->actingAs($mst)->post(route('workflow.deployment.deployments.store', $project), [
            'deployment_number' => 'DEP-001',
            'version' => '1.0.0',
            'environment' => 'PRODUCTION',
        ]);
        $deployment = $project->fresh()->deployments->first();

        $this->actingAs($mst)
            ->post(route('workflow.deployment.outcome', [$project, $deployment]), ['outcome' => 'FAILED'])
            ->assertSessionHasErrors('remarks');

        $this->actingAs($mst)->post(
            route('workflow.deployment.outcome', [$project, $deployment]),
            ['outcome' => 'FAILED', 'remarks' => 'Migration failed halfway; rolled back.'],
        )->assertRedirect();

        $fresh = $project->fresh();
        $this->assertSame('DEPLOYMENT', $fresh->status);
        $this->assertSame('FAILED', $deployment->fresh()->status);
    }

    public function test_a_failed_deployment_can_be_retried_and_succeed(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectAtDeployment($requestor);

        $this->actingAs($mst)->post(route('workflow.deployment.deployments.store', $project), [
            'deployment_number' => 'DEP-001',
            'version' => '1.0.0',
            'environment' => 'PRODUCTION',
        ]);
        $deployment = $project->fresh()->deployments->first();

        $this->actingAs($mst)->post(
            route('workflow.deployment.outcome', [$project, $deployment]),
            ['outcome' => 'FAILED', 'remarks' => 'Server ran out of disk space.'],
        );

        $this->actingAs($mst)->post(route('workflow.deployment.security-assessments.store', $project), [
            'assessment_number' => 'SEC-003',
            'overall_result' => 'PASS',
        ]);

        $this->actingAs($mst)->post(
            route('workflow.deployment.outcome', [$project, $deployment]),
            ['outcome' => 'SUCCESSFUL'],
        )->assertRedirect();

        $this->assertSame('MONITORING_MAINTENANCE', $project->fresh()->status);
        $this->assertSame('COMPLETED', $deployment->fresh()->status);
    }

    public function test_mst_can_add_release_note_but_requestor_cannot(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectAtDeployment($requestor);

        $this->actingAs($mst)->post(route('workflow.deployment.deployments.store', $project), [
            'deployment_number' => 'DEP-001',
            'version' => '1.0.0',
            'environment' => 'PRODUCTION',
        ]);
        $deployment = $project->fresh()->deployments->first();

        $note = [
            'document_number' => 'RN-001',
            'title' => 'Initial release',
            'version' => '1.0.0',
            'new_features' => 'Expense submission',
        ];

        $this->actingAs($requestor)
            ->post(route('workflow.deployment.release-notes.store', [$project, $deployment]), $note)
            ->assertForbidden();

        $this->actingAs($mst)
            ->post(route('workflow.deployment.release-notes.store', [$project, $deployment]), $note)
            ->assertRedirect();

        $releaseNote = $deployment->releaseNotes()->sole();
        $this->assertSame('RN-001', $releaseNote->document_number);
        $this->assertSame($mst->id, $releaseNote->prepared_by);

        // A double-submit is a validation error, not a 500.
        $this->actingAs($mst)
            ->post(route('workflow.deployment.release-notes.store', [$project, $deployment]), $note)
            ->assertSessionHasErrors('document_number');
        $this->assertSame(1, $deployment->releaseNotes()->count());
    }

    public function test_cannot_change_another_projects_deployment_through_this_project(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectAtDeployment($requestor);

        $other = $project->replicate()->fill([
            'id' => (string) Str::uuid(),
            'project_id' => 'SDR-2026-999',
            'status' => 'MONITORING_MAINTENANCE',
        ]);
        $other->save();
        $otherDeployment = $other->deployments()->create([
            'id' => (string) Str::uuid(),
            'deployment_number' => 'DEP-OTHER',
            'version' => '1.0.0',
            'environment' => 'PRODUCTION',
            'prepared_by' => $mst->id,
        ]);

        $this->actingAs($mst)
            ->post(route('workflow.deployment.release-notes.store', [$project, $otherDeployment]), [
                'document_number' => 'RN-001',
                'title' => 'Sneaky',
                'version' => '1.0.0',
            ])
            ->assertNotFound();

        $this->actingAs($mst)
            ->post(route('workflow.deployment.outcome', [$project, $otherDeployment]), ['outcome' => 'FAILED', 'remarks' => 'x'])
            ->assertNotFound();

        $this->assertSame(0, $otherDeployment->releaseNotes()->count());
    }
}
