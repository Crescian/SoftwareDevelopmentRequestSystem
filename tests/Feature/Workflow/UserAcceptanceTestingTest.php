<?php

namespace Tests\Feature\Workflow;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserAcceptanceTestingTest extends TestCase
{
    use RefreshDatabase;

    private function projectInUat(User $requestor): SoftwareProject
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
            'status' => 'USER_ACCEPTANCE_TESTING',
        ]);
    }

    public function test_only_mst_can_schedule_a_uat_session(): void
    {
        $requestor = User::factory()->create();
        $project = $this->projectInUat($requestor);

        $this->actingAs($requestor)
            ->post(route('workflow.uat.sessions.store', $project), [
                'uat_number' => 'UAT-001',
                'title' => 'Round 1',
            ])
            ->assertForbidden();
    }

    public function test_only_the_requestor_can_sign_off(): void
    {
        $requestor = User::factory()->create();
        $someoneElse = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInUat($requestor);

        $this->actingAs($mst)->post(route('workflow.uat.sessions.store', $project), [
            'uat_number' => 'UAT-001',
            'title' => 'Round 1',
        ]);

        $session = $project->fresh()->uatSessions->first();

        $this->actingAs($someoneElse)
            ->post(route('workflow.uat.sessions.signoff', [$project, $session]), ['decision' => 'ACCEPTED'])
            ->assertForbidden();
    }

    public function test_acceptance_moves_the_project_to_deployment(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInUat($requestor);

        $this->actingAs($mst)->post(route('workflow.uat.sessions.store', $project), [
            'uat_number' => 'UAT-001',
            'title' => 'Round 1',
        ]);

        $session = $project->fresh()->uatSessions->first();

        $response = $this->actingAs($requestor)->post(
            route('workflow.uat.sessions.signoff', [$project, $session]),
            ['decision' => 'ACCEPTED'],
        );

        $response->assertRedirect(route('workflow.uat.show', $project));

        $fresh = $project->fresh();
        $this->assertSame('DEPLOYMENT', $fresh->status);

        $signoff = $session->fresh()->signoffs->first();
        $this->assertSame('ACCEPTED', $signoff->decision);
        $this->assertSame($requestor->id, $signoff->signatory_id);
        $this->assertSame('COMPLETED', $session->fresh()->status);
    }

    public function test_rejection_requires_remarks_and_does_not_advance(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInUat($requestor);

        $this->actingAs($mst)->post(route('workflow.uat.sessions.store', $project), [
            'uat_number' => 'UAT-001',
            'title' => 'Round 1',
        ]);

        $session = $project->fresh()->uatSessions->first();

        $this->actingAs($requestor)
            ->post(route('workflow.uat.sessions.signoff', [$project, $session]), ['decision' => 'REJECTED'])
            ->assertSessionHasErrors('remarks');

        $this->assertSame('USER_ACCEPTANCE_TESTING', $project->fresh()->status);

        $this->actingAs($requestor)->post(
            route('workflow.uat.sessions.signoff', [$project, $session]),
            ['decision' => 'REJECTED', 'remarks' => 'The report export is missing a column.'],
        )->assertRedirect(route('workflow.uat.show', $project));

        $this->assertSame('USER_ACCEPTANCE_TESTING', $project->fresh()->status);
        $this->assertSame('REJECTED', $session->fresh()->signoffs->first()->decision);
    }

    public function test_a_rejected_session_can_be_followed_by_a_new_session_and_acceptance(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInUat($requestor);

        $this->actingAs($mst)->post(route('workflow.uat.sessions.store', $project), [
            'uat_number' => 'UAT-001',
            'title' => 'Round 1',
        ]);
        $session1 = $project->fresh()->uatSessions->first();

        $this->actingAs($requestor)->post(
            route('workflow.uat.sessions.signoff', [$project, $session1]),
            ['decision' => 'REJECTED', 'remarks' => 'Needs fixes.'],
        );

        $this->actingAs($mst)->post(route('workflow.uat.sessions.store', $project), [
            'uat_number' => 'UAT-002',
            'title' => 'Round 2',
        ]);
        $session2 = $project->fresh()->uatSessions()->where('uat_number', 'UAT-002')->first();

        $this->actingAs($requestor)->post(
            route('workflow.uat.sessions.signoff', [$project, $session2]),
            ['decision' => 'ACCEPTED'],
        )->assertRedirect();

        $this->assertSame('DEPLOYMENT', $project->fresh()->status);
    }
}
