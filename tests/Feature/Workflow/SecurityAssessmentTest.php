<?php

namespace Tests\Feature\Workflow;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityAssessmentTest extends TestCase
{
    use RefreshDatabase;

    private function project(User $requestor, string $status, ?User $pm = null): SoftwareProject
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
            'project_manager_id' => $pm?->id,
            'project_summary' => 'Summary',
            'business_problem' => 'Problem',
            'proposed_solution' => 'Solution',
            'status' => $status,
        ]);
    }

    public function test_design_review_returned_by_pm_blocks_development(): void
    {
        $requestor = User::factory()->create();
        $pm = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->project($requestor, 'REQUIREMENTS_DESIGN', $pm);

        $this->actingAs($mst)->post(route('workflow.requirements-design.requirements-documents.store', $project), [
            'document_number' => 'RD-001',
            'title' => 'Requirements v1',
        ]);
        $this->actingAs($mst)->post(route('workflow.requirements-design.system-designs.store', $project), [
            'document_number' => 'SD-001',
            'title' => 'System Design v1',
        ]);
        $this->actingAs($mst)->post(route('workflow.requirements-design.security-assessments.store', $project), [
            'assessment_number' => 'SEC-001',
            'overall_result' => 'FAIL',
        ]);

        $project->refresh();

        $this->actingAs($pm)->post(
            route('workflow.requirements-design.requirements-documents.decide', [$project, $project->requirementsDocuments->first()]),
            ['decision' => 'APPROVED'],
        );
        $this->actingAs($pm)->post(
            route('workflow.requirements-design.system-designs.decide', [$project, $project->systemDesigns->first()]),
            ['decision' => 'APPROVED'],
        );

        $securityAssessment = $project->securityAssessments->first();

        $this->actingAs($pm)
            ->post(
                route('workflow.requirements-design.security-assessments.decide', [$project, $securityAssessment]),
                ['decision' => 'RETURNED'],
            )
            ->assertSessionHasErrors('remarks');

        $this->actingAs($pm)->post(
            route('workflow.requirements-design.security-assessments.decide', [$project, $securityAssessment]),
            ['decision' => 'RETURNED', 'remarks' => 'Fix the SQL injection risk before proceeding.'],
        )->assertRedirect();

        // Requirements & Design and System Design are both approved, but
        // the project must not advance since security was returned.
        $this->assertSame('REQUIREMENTS_DESIGN', $project->fresh()->status);
    }

    public function test_non_mst_user_cannot_record_a_design_review_assessment(): void
    {
        $requestor = User::factory()->create();
        $pm = User::factory()->create();
        $project = $this->project($requestor, 'REQUIREMENTS_DESIGN', $pm);

        $this->actingAs($requestor)
            ->post(route('workflow.requirements-design.security-assessments.store', $project), [
                'assessment_number' => 'SEC-001',
                'overall_result' => 'PASS',
            ])
            ->assertForbidden();
    }

    public function test_deployment_cannot_succeed_without_a_passed_pre_deployment_assessment(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->project($requestor, 'DEPLOYMENT');

        $this->actingAs($mst)->post(route('workflow.deployment.deployments.store', $project), [
            'deployment_number' => 'DEP-001',
            'version' => '1.0.0',
            'environment' => 'PRODUCTION',
        ]);
        $deployment = $project->fresh()->deployments->first();

        $this->actingAs($mst)
            ->post(route('workflow.deployment.outcome', [$project, $deployment]), ['outcome' => 'SUCCESSFUL'])
            ->assertSessionHasErrors('security');

        $this->assertSame('DEPLOYMENT', $project->fresh()->status);
    }

    public function test_a_failed_pre_deployment_assessment_also_blocks_success(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->project($requestor, 'DEPLOYMENT');

        $this->actingAs($mst)->post(route('workflow.deployment.deployments.store', $project), [
            'deployment_number' => 'DEP-001',
            'version' => '1.0.0',
            'environment' => 'PRODUCTION',
        ]);
        $deployment = $project->fresh()->deployments->first();

        $this->actingAs($mst)->post(route('workflow.deployment.security-assessments.store', $project), [
            'assessment_number' => 'SEC-001',
            'overall_result' => 'FAIL',
        ]);

        $assessment = $project->fresh()->securityAssessments->first();
        $this->assertSame('COMPLETED', $assessment->status);
        $this->assertNull($assessment->approved_by);

        $this->actingAs($mst)
            ->post(route('workflow.deployment.outcome', [$project, $deployment]), ['outcome' => 'SUCCESSFUL'])
            ->assertSessionHasErrors('security');
    }
}
