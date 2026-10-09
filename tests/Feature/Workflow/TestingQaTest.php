<?php

namespace Tests\Feature\Workflow;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\TestCase as AppTestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TestingQaTest extends TestCase
{
    use RefreshDatabase;

    private function projectInTesting(User $requestor): SoftwareProject
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
            'status' => 'TESTING_QA',
        ]);
    }

    private function testCaseFor(SoftwareProject $project, User $mst): AppTestCase
    {
        $this->actingAs($mst)->post(route('workflow.testing-qa.test-plans.store', $project), [
            'test_plan_number' => 'TP-001',
            'title' => 'Regression Suite',
        ]);

        $testPlan = $project->fresh()->testPlans->first();

        $this->actingAs($mst)->post(route('workflow.testing-qa.test-cases.store', [$project, $testPlan]), [
            'test_case_number' => 'TC-001',
            'title' => 'Login works',
            'test_type' => 'FUNCTIONAL',
            'test_steps' => 'Log in with valid credentials.',
            'expected_result' => 'User is authenticated.',
        ]);

        return $testPlan->fresh()->testCases->first();
    }

    public function test_non_mst_user_cannot_manage_testing(): void
    {
        $requestor = User::factory()->create();
        $project = $this->projectInTesting($requestor);

        $this->actingAs($requestor)
            ->post(route('workflow.testing-qa.test-plans.store', $project), [
                'test_plan_number' => 'TP-001',
                'title' => 'Regression Suite',
            ])
            ->assertForbidden();
    }

    public function test_mst_can_create_a_test_plan_and_test_case(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInTesting($requestor);

        $testCase = $this->testCaseFor($project, $mst);

        $this->assertSame('NOT_EXECUTED', $testCase->status);
        $this->assertCount(1, $project->fresh()->testPlans);
    }

    public function test_executing_a_test_case_records_the_result(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInTesting($requestor);
        $testCase = $this->testCaseFor($project, $mst);

        $this->actingAs($mst)->post(
            route('workflow.testing-qa.test-cases.execute', [$project, $testCase]),
            ['status' => 'FAILED', 'actual_result' => 'Login rejected valid credentials.'],
        )->assertRedirect();

        $fresh = $testCase->fresh();
        $this->assertSame('FAILED', $fresh->status);
        $this->assertSame($mst->id, $fresh->executed_by);
    }

    public function test_cannot_advance_while_defects_are_unverified(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInTesting($requestor);
        $testCase = $this->testCaseFor($project, $mst);

        $this->actingAs($mst)->post(
            route('workflow.testing-qa.test-cases.execute', [$project, $testCase]),
            ['status' => 'FAILED', 'actual_result' => 'Login rejected valid credentials.'],
        );

        $this->actingAs($mst)->post(route('workflow.testing-qa.defects.store', [$project, $testCase]), [
            'title' => 'Login bug',
            'description' => 'Valid credentials rejected under load.',
        ]);

        $this->actingAs($mst)
            ->post(route('workflow.testing-qa.advance', $project))
            ->assertSessionHasErrors('defects');

        $this->assertSame('TESTING_QA', $project->fresh()->status);
    }

    public function test_full_defect_lifecycle_then_advance_to_uat(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInTesting($requestor);
        $testCase = $this->testCaseFor($project, $mst);

        $this->actingAs($mst)->post(
            route('workflow.testing-qa.test-cases.execute', [$project, $testCase]),
            ['status' => 'FAILED', 'actual_result' => 'Login rejected valid credentials.'],
        );

        $this->actingAs($mst)->post(route('workflow.testing-qa.defects.store', [$project, $testCase]), [
            'title' => 'Login bug',
            'description' => 'Valid credentials rejected under load.',
        ]);

        $defect = $project->fresh()->defects->first();

        $this->actingAs($mst)
            ->post(route('workflow.testing-qa.defects.status', [$project, $defect]), ['status' => 'RESOLVED'])
            ->assertSessionHasErrors('resolution');

        $this->actingAs($mst)->post(route('workflow.testing-qa.defects.status', [$project, $defect]), [
            'status' => 'RESOLVED',
            'resolution' => 'Fixed the throttle middleware misconfiguration.',
        ])->assertRedirect();

        $this->assertSame('RESOLVED', $defect->fresh()->status);

        // Retest and pass.
        $this->actingAs($mst)->post(
            route('workflow.testing-qa.test-cases.execute', [$project, $testCase]),
            ['status' => 'PASSED', 'actual_result' => 'Login succeeded.'],
        );

        // Still blocked: defect resolved but not verified.
        $this->actingAs($mst)
            ->post(route('workflow.testing-qa.advance', $project))
            ->assertSessionHasErrors('defects');

        $this->actingAs($mst)->post(
            route('workflow.testing-qa.defects.status', [$project, $defect]),
            ['status' => 'VERIFIED'],
        )->assertRedirect();

        $this->assertSame('VERIFIED', $defect->fresh()->status);

        $this->actingAs($mst)
            ->post(route('workflow.testing-qa.advance', $project))
            ->assertRedirect(route('workflow.testing-qa.index'));

        $this->assertSame('USER_ACCEPTANCE_TESTING', $project->fresh()->status);
    }

    public function test_advance_with_no_defects_at_all_succeeds(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->projectInTesting($requestor);
        $testCase = $this->testCaseFor($project, $mst);

        $this->actingAs($mst)->post(
            route('workflow.testing-qa.test-cases.execute', [$project, $testCase]),
            ['status' => 'PASSED', 'actual_result' => 'Login succeeded.'],
        );

        $this->actingAs($mst)
            ->post(route('workflow.testing-qa.advance', $project))
            ->assertRedirect(route('workflow.testing-qa.index'));

        $this->assertSame('USER_ACCEPTANCE_TESTING', $project->fresh()->status);
    }
}
