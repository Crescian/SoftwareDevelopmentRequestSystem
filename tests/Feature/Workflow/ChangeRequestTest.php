<?php

namespace Tests\Feature\Workflow;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChangeRequestTest extends TestCase
{
    use RefreshDatabase;

    private function projectInMonitoring(User $requestor): SoftwareProject
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
            'status' => 'MONITORING_MAINTENANCE',
        ]);
    }

    public function test_someone_unrelated_to_the_project_cannot_propose_a_change(): void
    {
        $requestor = User::factory()->create();
        $unrelated = User::factory()->create();
        $project = $this->projectInMonitoring($requestor);
        $approver = User::factory()->create();

        $this->actingAs($unrelated)
            ->post(route('workflow.change-requests.store', $project), [
                'change_request_number' => 'CR-001',
                'title' => 'Add export filter',
                'description' => 'Add a date filter to the export.',
                'reason' => 'Requested by finance.',
                'approvals' => [['approval_type' => 'SPONSOR', 'approver_id' => $approver->id]],
            ])
            ->assertForbidden();
    }

    public function test_single_step_approval_moves_the_change_request_to_approved(): void
    {
        $requestor = User::factory()->create();
        $approver = User::factory()->create();
        $project = $this->projectInMonitoring($requestor);

        $this->actingAs($requestor)->post(route('workflow.change-requests.store', $project), [
            'change_request_number' => 'CR-001',
            'title' => 'Add export filter',
            'description' => 'Add a date filter to the export.',
            'reason' => 'Requested by finance.',
            'approvals' => [['approval_type' => 'SPONSOR', 'approver_id' => $approver->id]],
        ]);

        $changeRequest = $project->fresh()->changeRequests->first();
        $approval = $changeRequest->approvals->first();

        $response = $this->actingAs($approver)->post(
            route('workflow.change-requests.decide', [$project, $changeRequest, $approval]),
            ['decision' => 'APPROVED'],
        );

        $response->assertRedirect(route('workflow.change-requests.show', $project));
        $this->assertSame('APPROVED', $changeRequest->fresh()->status);

        // The project itself is untouched by change management.
        $this->assertSame('MONITORING_MAINTENANCE', $project->fresh()->status);
    }

    public function test_second_approver_cannot_act_before_the_first_approves(): void
    {
        $requestor = User::factory()->create();
        $approver1 = User::factory()->create();
        $approver2 = User::factory()->create();
        $project = $this->projectInMonitoring($requestor);

        $this->actingAs($requestor)->post(route('workflow.change-requests.store', $project), [
            'change_request_number' => 'CR-001',
            'title' => 'Add export filter',
            'description' => 'Add a date filter to the export.',
            'reason' => 'Requested by finance.',
            'approvals' => [
                ['approval_type' => 'IT_ADMIN', 'approver_id' => $approver1->id],
                ['approval_type' => 'SPONSOR', 'approver_id' => $approver2->id],
            ],
        ]);

        $changeRequest = $project->fresh()->changeRequests->first();
        $secondApproval = $changeRequest->approvals->sortBy('approval_sequence')->last();

        $this->actingAs($approver2)
            ->post(
                route('workflow.change-requests.decide', [$project, $changeRequest, $secondApproval]),
                ['decision' => 'APPROVED'],
            )
            ->assertForbidden();

        $this->assertSame('SUBMITTED', $changeRequest->fresh()->status);
    }

    public function test_full_two_step_chain_approves_in_sequence(): void
    {
        $requestor = User::factory()->create();
        $approver1 = User::factory()->create();
        $approver2 = User::factory()->create();
        $project = $this->projectInMonitoring($requestor);

        $this->actingAs($requestor)->post(route('workflow.change-requests.store', $project), [
            'change_request_number' => 'CR-001',
            'title' => 'Add export filter',
            'description' => 'Add a date filter to the export.',
            'reason' => 'Requested by finance.',
            'approvals' => [
                ['approval_type' => 'IT_ADMIN', 'approver_id' => $approver1->id],
                ['approval_type' => 'SPONSOR', 'approver_id' => $approver2->id],
            ],
        ]);

        $changeRequest = $project->fresh()->changeRequests->first();
        $sorted = $changeRequest->approvals->sortBy('approval_sequence')->values();

        $this->actingAs($approver1)->post(
            route('workflow.change-requests.decide', [$project, $changeRequest, $sorted[0]]),
            ['decision' => 'APPROVED'],
        )->assertRedirect();

        $this->assertSame('SUBMITTED', $changeRequest->fresh()->status);

        $this->actingAs($approver2)->post(
            route('workflow.change-requests.decide', [$project, $changeRequest, $sorted[1]]),
            ['decision' => 'APPROVED'],
        )->assertRedirect();

        $this->assertSame('APPROVED', $changeRequest->fresh()->status);
    }

    public function test_rejection_is_terminal(): void
    {
        $requestor = User::factory()->create();
        $approver = User::factory()->create();
        $project = $this->projectInMonitoring($requestor);

        $this->actingAs($requestor)->post(route('workflow.change-requests.store', $project), [
            'change_request_number' => 'CR-001',
            'title' => 'Add export filter',
            'description' => 'Add a date filter to the export.',
            'reason' => 'Requested by finance.',
            'approvals' => [['approval_type' => 'SPONSOR', 'approver_id' => $approver->id]],
        ]);

        $changeRequest = $project->fresh()->changeRequests->first();
        $approval = $changeRequest->approvals->first();

        $this->actingAs($approver)->post(
            route('workflow.change-requests.decide', [$project, $changeRequest, $approval]),
            ['decision' => 'REJECTED', 'remarks' => 'Out of scope for this system.'],
        )->assertRedirect();

        $this->assertSame('REJECTED', $changeRequest->fresh()->status);

        // Rejected approval can't be re-decided (no longer PENDING).
        $this->actingAs($approver)
            ->post(
                route('workflow.change-requests.decide', [$project, $changeRequest, $approval]),
                ['decision' => 'APPROVED'],
            )
            ->assertForbidden();
    }

    public function test_returned_change_request_can_be_resubmitted_by_the_requestor(): void
    {
        $requestor = User::factory()->create();
        $approver = User::factory()->create();
        $project = $this->projectInMonitoring($requestor);

        $this->actingAs($requestor)->post(route('workflow.change-requests.store', $project), [
            'change_request_number' => 'CR-001',
            'title' => 'Add export filter',
            'description' => 'Add a date filter to the export.',
            'reason' => 'Requested by finance.',
            'approvals' => [['approval_type' => 'SPONSOR', 'approver_id' => $approver->id]],
        ]);

        $changeRequest = $project->fresh()->changeRequests->first();
        $approval = $changeRequest->approvals->first();

        $this->actingAs($approver)->post(
            route('workflow.change-requests.decide', [$project, $changeRequest, $approval]),
            ['decision' => 'RETURNED', 'remarks' => 'Please clarify the cost impact.'],
        );

        $this->assertSame('RETURNED_FOR_REVISION', $changeRequest->fresh()->status);

        $this->actingAs($requestor)
            ->post(route('workflow.change-requests.resubmit', [$project, $changeRequest]))
            ->assertRedirect();

        $fresh = $changeRequest->fresh();
        $this->assertSame('SUBMITTED', $fresh->status);
        $this->assertSame('PENDING', $fresh->approvals->first()->status);

        // The chain can now be approved again.
        $this->actingAs($approver)->post(
            route('workflow.change-requests.decide', [$project, $changeRequest, $fresh->approvals->first()]),
            ['decision' => 'APPROVED'],
        )->assertRedirect();

        $this->assertSame('APPROVED', $changeRequest->fresh()->status);
    }

    public function test_mst_implements_and_closes_an_approved_change_request(): void
    {
        $requestor = User::factory()->create();
        $approver = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInMonitoring($requestor);

        $this->actingAs($requestor)->post(route('workflow.change-requests.store', $project), [
            'change_request_number' => 'CR-001',
            'title' => 'Add export filter',
            'description' => 'Add a date filter to the export.',
            'reason' => 'Requested by finance.',
            'approvals' => [['approval_type' => 'SPONSOR', 'approver_id' => $approver->id]],
        ]);

        $changeRequest = $project->fresh()->changeRequests->first();

        $this->actingAs($approver)->post(
            route('workflow.change-requests.decide', [$project, $changeRequest, $changeRequest->approvals->first()]),
            ['decision' => 'APPROVED'],
        );

        $this->actingAs($requestor)
            ->post(route('workflow.change-requests.implement', [$project, $changeRequest]))
            ->assertForbidden();

        $this->actingAs($mst)
            ->post(route('workflow.change-requests.implement', [$project, $changeRequest]))
            ->assertRedirect();

        $this->assertSame('IMPLEMENTED', $changeRequest->fresh()->status);
        $this->assertNotNull($changeRequest->fresh()->implemented_at);

        $this->actingAs($mst)
            ->post(route('workflow.change-requests.close', [$project, $changeRequest]))
            ->assertRedirect();

        $this->assertSame('CLOSED', $changeRequest->fresh()->status);
        $this->assertNotNull($changeRequest->fresh()->closed_at);
    }
}
