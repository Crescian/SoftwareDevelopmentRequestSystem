<?php

namespace Tests\Feature\Workflow;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RequirementsDesignTest extends TestCase
{
    use RefreshDatabase;

    private function projectAtRequirementsDesign(User $requestor, ?User $pm = null): SoftwareProject
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
            'status' => 'REQUIREMENTS_DESIGN',
        ]);
    }

    public function test_only_mst_can_draft_a_requirements_document(): void
    {
        $requestor = User::factory()->create();
        $project = $this->projectAtRequirementsDesign($requestor);

        $this->actingAs($requestor)
            ->post(route('workflow.requirements-design.requirements-documents.store', $project), [
                'document_number' => 'RD-001',
                'title' => 'Requirements v1',
            ])
            ->assertForbidden();
    }

    public function test_mst_can_draft_documents_and_pm_approves_all_three_to_advance_to_development(): void
    {
        $requestor = User::factory()->create();
        $pm = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectAtRequirementsDesign($requestor, $pm);

        $this->actingAs($mst)->post(route('workflow.requirements-design.requirements-documents.store', $project), [
            'document_number' => 'RD-001',
            'title' => 'Requirements v1',
        ])->assertRedirect();

        $this->actingAs($mst)->post(route('workflow.requirements-design.system-designs.store', $project), [
            'document_number' => 'SD-001',
            'title' => 'System Design v1',
        ])->assertRedirect();

        $this->actingAs($mst)->post(route('workflow.requirements-design.security-assessments.store', $project), [
            'assessment_number' => 'SEC-001',
            'overall_result' => 'PASS',
        ])->assertRedirect();

        $project->refresh();
        $requirementsDoc = $project->requirementsDocuments->first();
        $systemDesign = $project->systemDesigns->first();
        $securityAssessment = $project->securityAssessments->first();

        // Approving only one of three shouldn't advance the project yet.
        $this->actingAs($pm)->post(
            route('workflow.requirements-design.requirements-documents.decide', [$project, $requirementsDoc]),
            ['decision' => 'APPROVED'],
        )->assertRedirect();

        $this->assertSame('REQUIREMENTS_DESIGN', $project->fresh()->status);

        // Approving a second of three still isn't enough.
        $this->actingAs($pm)->post(
            route('workflow.requirements-design.system-designs.decide', [$project, $systemDesign]),
            ['decision' => 'APPROVED'],
        )->assertRedirect();

        $this->assertSame('REQUIREMENTS_DESIGN', $project->fresh()->status);

        // Approving the security assessment (the third) should now advance it.
        $this->actingAs($pm)->post(
            route('workflow.requirements-design.security-assessments.decide', [$project, $securityAssessment]),
            ['decision' => 'APPROVED'],
        )->assertRedirect();

        $fresh = $project->fresh();
        $this->assertSame('DEVELOPMENT', $fresh->status);
        $this->assertSame('APPROVED', $fresh->requirementsDocuments->first()->status);
        $this->assertSame('APPROVED', $fresh->systemDesigns->first()->status);
        $this->assertSame('APPROVED', $fresh->securityAssessments->first()->status);
        $this->assertSame($pm->id, $fresh->requirementsDocuments->first()->approved_by);
    }

    public function test_returning_a_document_requires_remarks_and_does_not_advance(): void
    {
        $requestor = User::factory()->create();
        $pm = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectAtRequirementsDesign($requestor, $pm);

        $this->actingAs($mst)->post(route('workflow.requirements-design.requirements-documents.store', $project), [
            'document_number' => 'RD-001',
            'title' => 'Requirements v1',
        ]);

        $document = $project->fresh()->requirementsDocuments->first();

        $this->actingAs($pm)
            ->post(
                route('workflow.requirements-design.requirements-documents.decide', [$project, $document]),
                ['decision' => 'RETURNED'],
            )
            ->assertSessionHasErrors('remarks');

        $this->actingAs($pm)->post(
            route('workflow.requirements-design.requirements-documents.decide', [$project, $document]),
            ['decision' => 'RETURNED', 'remarks' => 'Needs more detail on non-functional requirements.'],
        )->assertRedirect();

        $fresh = $document->fresh();
        $this->assertSame('RETURNED', $fresh->status);
        $this->assertSame('REQUIREMENTS_DESIGN', $project->fresh()->status);
    }

    public function test_someone_other_than_the_project_manager_cannot_decide(): void
    {
        $requestor = User::factory()->create();
        $pm = User::factory()->create();
        $someoneElse = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectAtRequirementsDesign($requestor, $pm);

        $this->actingAs($mst)->post(route('workflow.requirements-design.requirements-documents.store', $project), [
            'document_number' => 'RD-001',
            'title' => 'Requirements v1',
        ]);

        $document = $project->fresh()->requirementsDocuments->first();

        $this->actingAs($someoneElse)
            ->post(
                route('workflow.requirements-design.requirements-documents.decide', [$project, $document]),
                ['decision' => 'APPROVED'],
            )
            ->assertForbidden();
    }

    public function test_a_project_with_no_assigned_project_manager_cannot_have_decisions_made(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectAtRequirementsDesign($requestor, null);

        $this->actingAs($mst)->post(route('workflow.requirements-design.requirements-documents.store', $project), [
            'document_number' => 'RD-001',
            'title' => 'Requirements v1',
        ]);

        $document = $project->fresh()->requirementsDocuments->first();

        // Even the MST drafter (who is not the PM) cannot decide.
        $this->actingAs($mst)
            ->post(
                route('workflow.requirements-design.requirements-documents.decide', [$project, $document]),
                ['decision' => 'APPROVED'],
            )
            ->assertForbidden();
    }
}
