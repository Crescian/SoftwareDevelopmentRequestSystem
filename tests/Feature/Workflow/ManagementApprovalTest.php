<?php

namespace Tests\Feature\Workflow;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ManagementApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function projectPendingApproval(User $requestor, User $sponsor): SoftwareProject
    {
        $department = Department::create([
            'id' => Str::uuid(),
            'code' => 'FIN',
            'name' => 'Finance',
            'is_active' => true,
        ]);

        $project = SoftwareProject::create([
            'id' => Str::uuid(),
            'project_id' => 'SDR-2026-' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'project_name' => 'Test Project',
            'requesting_department_id' => $department->id,
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $sponsor->id,
            'project_summary' => 'Summary',
            'business_problem' => 'Problem',
            'proposed_solution' => 'Solution',
            'status' => 'PENDING_APPROVAL',
        ]);

        $project->approvals()->create([
            'id' => Str::uuid(),
            'approval_type' => 'MANAGEMENT_APPROVAL',
            'approver_id' => $sponsor->id,
            'status' => 'PENDING',
        ]);

        return $project;
    }

    public function test_only_the_project_sponsor_can_decide(): void
    {
        $requestor = User::factory()->create();
        $sponsor = User::factory()->create();
        $someoneElse = User::factory()->create();
        $project = $this->projectPendingApproval($requestor, $sponsor);

        $this->actingAs($someoneElse)
            ->post(route('approvals.management.store', $project), ['decision' => 'APPROVED'])
            ->assertForbidden();

        $this->actingAs($requestor)
            ->post(route('approvals.management.store', $project), ['decision' => 'APPROVED'])
            ->assertForbidden();

        $this->assertSame('PENDING_APPROVAL', $project->fresh()->status);
    }

    public function test_sponsor_sees_the_project_in_their_queue(): void
    {
        $requestor = User::factory()->create();
        $sponsor = User::factory()->create();
        $project = $this->projectPendingApproval($requestor, $sponsor);

        $response = $this->actingAs($sponsor)->get(route('approvals.management.index'));

        $response->assertOk();
        $response->assertSee($project->project_id);
    }

    public function test_approval_moves_project_to_requirements_design(): void
    {
        $requestor = User::factory()->create();
        $sponsor = User::factory()->create();
        $project = $this->projectPendingApproval($requestor, $sponsor);

        $response = $this->actingAs($sponsor)->post(
            route('approvals.management.store', $project),
            ['decision' => 'APPROVED'],
        );

        $response->assertRedirect(route('approvals.management.index'));

        $fresh = $project->fresh();
        $this->assertSame('REQUIREMENTS_DESIGN', $fresh->status);

        $approval = $fresh->approvals()->where('approval_type', 'MANAGEMENT_APPROVAL')->first();
        $this->assertSame('APPROVED', $approval->status);
        $this->assertNotNull($approval->approved_at);
    }

    public function test_return_requires_remarks_and_returns_the_project(): void
    {
        $requestor = User::factory()->create();
        $sponsor = User::factory()->create();
        $project = $this->projectPendingApproval($requestor, $sponsor);

        $this->actingAs($sponsor)
            ->post(route('approvals.management.store', $project), ['decision' => 'RETURNED'])
            ->assertSessionHasErrors('remarks');

        $this->assertSame('PENDING_APPROVAL', $project->fresh()->status);

        $this->actingAs($sponsor)->post(
            route('approvals.management.store', $project),
            ['decision' => 'RETURNED', 'remarks' => 'Please clarify the budget line.'],
        )->assertRedirect(route('approvals.management.index'));

        $fresh = $project->fresh();
        $this->assertSame('RETURNED_FOR_REVISION', $fresh->status);
        $this->assertSame('RETURNED', $fresh->approvals()->first()->status);
    }

    public function test_rejection_requires_remarks_and_rejects_the_project(): void
    {
        $requestor = User::factory()->create();
        $sponsor = User::factory()->create();
        $project = $this->projectPendingApproval($requestor, $sponsor);

        $this->actingAs($sponsor)->post(
            route('approvals.management.store', $project),
            ['decision' => 'REJECTED', 'remarks' => 'No longer aligned with priorities.'],
        )->assertRedirect(route('approvals.management.index'));

        $fresh = $project->fresh();
        $this->assertSame('REJECTED', $fresh->status);
        $this->assertSame('REJECTED', $fresh->approvals()->first()->status);
        $this->assertSame('No longer aligned with priorities.', $fresh->statusHistories->last()->remarks);
    }

    public function test_cannot_decide_a_project_not_pending_approval(): void
    {
        $requestor = User::factory()->create();
        $sponsor = User::factory()->create();
        $project = $this->projectPendingApproval($requestor, $sponsor);
        $project->update(['status' => 'FEASIBILITY_ASSESSMENT']);

        $this->actingAs($sponsor)
            ->post(route('approvals.management.store', $project), ['decision' => 'APPROVED'])
            ->assertForbidden();
    }
}
