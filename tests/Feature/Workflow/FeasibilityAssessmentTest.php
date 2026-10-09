<?php

namespace Tests\Feature\Workflow;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FeasibilityAssessmentTest extends TestCase
{
    use RefreshDatabase;

    private function projectAtFeasibility(User $requestor): SoftwareProject
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
            'status' => 'FEASIBILITY_ASSESSMENT',
        ]);
    }

    private function assessmentPayload(array $overrides = []): array
    {
        return array_merge([
            'technical_feasibility' => 'Feasible with current stack.',
            'operational_feasibility' => 'Operations can support this.',
            'cost_benefit_analysis' => 'Benefits outweigh cost.',
            'overall_recommendation' => 'FEASIBLE',
        ], $overrides);
    }

    public function test_non_mst_user_cannot_view_the_queue_or_assess(): void
    {
        $requestor = User::factory()->create();
        $project = $this->projectAtFeasibility($requestor);

        $this->actingAs($requestor)
            ->get(route('reviews.feasibility-assessment.index'))
            ->assertForbidden();

        $this->actingAs($requestor)
            ->post(route('reviews.feasibility-assessment.store', $project), $this->assessmentPayload())
            ->assertForbidden();
    }

    public function test_feasible_recommendation_moves_project_to_pending_approval(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectAtFeasibility($requestor);

        $response = $this->actingAs($mst)->post(
            route('reviews.feasibility-assessment.store', $project),
            $this->assessmentPayload(['overall_recommendation' => 'FEASIBLE']),
        );

        $response->assertRedirect(route('reviews.feasibility-assessment.index'));

        $fresh = $project->fresh();
        $this->assertSame('PENDING_APPROVAL', $fresh->status);
        $this->assertCount(1, $fresh->feasibilityAssessments);
        $this->assertSame('FEASIBLE', $fresh->feasibilityAssessments->first()->overall_recommendation);
        $this->assertSame($mst->id, $fresh->feasibilityAssessments->first()->assessed_by);

        $approval = $fresh->approvals()->where('approval_type', 'MANAGEMENT_APPROVAL')->first();
        $this->assertNotNull($approval, 'Passing feasibility should create a pending management approval.');
        $this->assertSame('PENDING', $approval->status);
        $this->assertSame($fresh->project_sponsor_id, $approval->approver_id);
    }

    public function test_not_feasible_return_requires_notes_and_returns_the_project(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectAtFeasibility($requestor);

        $this->actingAs($mst)
            ->post(
                route('reviews.feasibility-assessment.store', $project),
                $this->assessmentPayload(['overall_recommendation' => 'NOT_FEASIBLE_RETURN']),
            )
            ->assertSessionHasErrors('assessment_notes');

        $this->assertSame('FEASIBILITY_ASSESSMENT', $project->fresh()->status);

        $this->actingAs($mst)->post(
            route('reviews.feasibility-assessment.store', $project),
            $this->assessmentPayload([
                'overall_recommendation' => 'NOT_FEASIBLE_RETURN',
                'assessment_notes' => 'Budget figures are missing.',
            ]),
        )->assertRedirect(route('reviews.feasibility-assessment.index'));

        $fresh = $project->fresh();
        $this->assertSame('RETURNED_FOR_REVISION', $fresh->status);
        $this->assertSame('Budget figures are missing.', $fresh->statusHistories->last()->remarks);
    }

    public function test_not_feasible_close_requires_notes_and_closes_the_project(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectAtFeasibility($requestor);

        $this->actingAs($mst)->post(
            route('reviews.feasibility-assessment.store', $project),
            $this->assessmentPayload([
                'overall_recommendation' => 'NOT_FEASIBLE_CLOSE',
                'assessment_notes' => 'Duplicate of an existing in-house tool.',
            ]),
        )->assertRedirect(route('reviews.feasibility-assessment.index'));

        $fresh = $project->fresh();
        $this->assertSame('CLOSED', $fresh->status);
        $this->assertSame('Duplicate of an existing in-house tool.', $fresh->statusHistories->last()->remarks);
    }

    public function test_cannot_assess_a_project_not_in_feasibility_assessment_status(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectAtFeasibility($requestor);
        $project->update(['status' => 'SUBMITTED']);

        $this->actingAs($mst)
            ->post(route('reviews.feasibility-assessment.store', $project), $this->assessmentPayload())
            ->assertForbidden();
    }
}
