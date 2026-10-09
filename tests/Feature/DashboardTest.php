<?php

namespace Tests\Feature;

use App\Models\Defect;
use App\Models\Department;
use App\Models\DevelopmentTask;
use App\Models\RequirementsDocument;
use App\Models\SoftwareProject;
use App\Models\TestCase as TestCaseModel;
use App\Models\TestPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeProject(array $overrides = []): SoftwareProject
    {
        $requestor = User::factory()->create();
        $department = Department::create([
            'id' => Str::uuid(),
            'code' => 'FIN-' . Str::random(6),
            'name' => 'Finance',
            'is_active' => true,
        ]);

        return SoftwareProject::create(array_merge([
            'id' => Str::uuid(),
            'project_id' => 'SDR-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'project_name' => 'Test Project',
            'requesting_department_id' => $department->id,
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
            'project_summary' => 'Summary',
            'business_problem' => 'Problem',
            'proposed_solution' => 'Solution',
            'status' => 'SUBMITTED',
        ], $overrides));
    }

    public function test_default_requestor_gets_the_requestor_dashboard(): void
    {
        $requestor = User::factory()->create();

        $this->actingAs($requestor)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My Recent Requests');
    }

    public function test_mst_reviewer_dashboard_shows_queue_counts_and_links(): void
    {
        $mst = User::factory()->mst()->create();
        $this->makeProject(['status' => 'SUBMITTED']);
        $this->makeProject(['status' => 'FEASIBILITY_ASSESSMENT']);

        $response = $this->actingAs($mst)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Initial Review');
        $response->assertSee('Feasibility Assessment');
        $response->assertSee(route('reviews.initial-review.index'), false);
        $response->assertSee(route('workflow.monitoring.index'), false);
    }

    public function test_developer_dashboard_shows_only_tasks_assigned_to_them(): void
    {
        $developer = User::factory()->developer()->create();
        $project = $this->makeProject(['status' => 'DEVELOPMENT']);

        DevelopmentTask::create([
            'id' => Str::uuid(),
            'software_project_id' => $project->id,
            'task_code' => 'DEV-001',
            'title' => 'My assigned task',
            'status' => 'TODO',
            'assigned_to' => $developer->id,
            'created_by' => $developer->id,
        ]);

        DevelopmentTask::create([
            'id' => Str::uuid(),
            'software_project_id' => $project->id,
            'task_code' => 'DEV-002',
            'title' => 'Someone elses task',
            'status' => 'TODO',
            'assigned_to' => Str::uuid(),
            'created_by' => $developer->id,
        ]);

        $response = $this->actingAs($developer)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('My assigned task');
        $response->assertDontSee('Someone elses task');
    }

    public function test_project_manager_dashboard_shows_pending_decisions_for_managed_projects_only(): void
    {
        $pm = User::factory()->projectManager()->create();
        $myProject = $this->makeProject(['status' => 'REQUIREMENTS_DESIGN', 'project_manager_id' => $pm->id]);
        $otherProject = $this->makeProject(['status' => 'REQUIREMENTS_DESIGN']);

        RequirementsDocument::create([
            'id' => Str::uuid(),
            'software_project_id' => $myProject->id,
            'document_number' => 'RD-001',
            'title' => 'My Requirements Doc',
            'version' => '1.0',
            'status' => 'DRAFT',
            'prepared_by' => $pm->id,
            'prepared_at' => now(),
        ]);

        RequirementsDocument::create([
            'id' => Str::uuid(),
            'software_project_id' => $otherProject->id,
            'document_number' => 'RD-002',
            'title' => 'Not My Requirements Doc',
            'version' => '1.0',
            'status' => 'DRAFT',
            'prepared_by' => $pm->id,
            'prepared_at' => now(),
        ]);

        $response = $this->actingAs($pm)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('My Requirements Doc');
        $response->assertDontSee('Not My Requirements Doc');
        $response->assertSee('Projects Managed');
    }

    public function test_qa_dashboard_shows_only_their_assigned_defects_and_their_pending_test_cases(): void
    {
        $qa = User::factory()->qa()->create();
        $project = $this->makeProject(['status' => 'TESTING_QA']);

        $testPlan = TestPlan::create([
            'id' => Str::uuid(),
            'software_project_id' => $project->id,
            'test_plan_number' => 'TP-001',
            'title' => 'Test Plan',
            'status' => 'ACTIVE',
            'prepared_by' => $qa->id,
            'prepared_at' => now(),
        ]);

        $testCase = TestCaseModel::create([
            'id' => Str::uuid(),
            'test_plan_id' => $testPlan->id,
            'test_case_number' => 'TC-001',
            'title' => 'Test Case',
            'test_type' => 'FUNCTIONAL',
            'test_steps' => 'Do the thing',
            'expected_result' => 'It works',
            'status' => 'NOT_EXECUTED',
        ]);

        Defect::create([
            'id' => Str::uuid(),
            'test_case_id' => $testCase->id,
            'software_project_id' => $project->id,
            'defect_number' => 'DEF-001',
            'title' => 'My assigned defect',
            'description' => 'Broken',
            'status' => 'OPEN',
            'reported_by' => $qa->id,
            'assigned_to' => $qa->id,
        ]);

        Defect::create([
            'id' => Str::uuid(),
            'test_case_id' => $testCase->id,
            'software_project_id' => $project->id,
            'defect_number' => 'DEF-002',
            'title' => 'Someone elses defect',
            'description' => 'Also broken',
            'status' => 'OPEN',
            'reported_by' => $qa->id,
            'assigned_to' => Str::uuid(),
        ]);

        $response = $this->actingAs($qa)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('My assigned defect');
        $response->assertDontSee('Someone elses defect');
        $response->assertSee('Test Cases Pending');
    }

    public function test_system_administrator_dashboard_shows_system_wide_totals(): void
    {
        $admin = User::factory()->systemAdministrator()->create();
        $this->makeProject(['status' => 'SUBMITTED']);
        $this->makeProject(['status' => 'MONITORING_MAINTENANCE']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Total Projects');
        $response->assertSee('2');
        $response->assertSee('Projects by Department');
    }

    public function test_sponsor_sees_management_approval_in_needs_your_decision_panel_regardless_of_dashboard_role(): void
    {
        $sponsor = User::factory()->create();
        $project = $this->makeProject(['status' => 'PENDING_APPROVAL', 'project_sponsor_id' => $sponsor->id]);

        $response = $this->actingAs($sponsor)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Needs Your Decision');
        $response->assertSee('Management Approval');
        $response->assertSee(route('approvals.management.show', $project), false);
    }

    public function test_project_manager_identity_sees_decision_even_when_dashboard_role_is_not_project_manager(): void
    {
        $developer = User::factory()->developer()->create();
        $project = $this->makeProject(['status' => 'REQUIREMENTS_DESIGN', 'project_manager_id' => $developer->id]);

        RequirementsDocument::create([
            'id' => Str::uuid(),
            'software_project_id' => $project->id,
            'document_number' => 'RD-010',
            'title' => 'PM-Identity Doc',
            'version' => '1.0',
            'status' => 'DRAFT',
            'prepared_by' => $developer->id,
            'prepared_at' => now(),
        ]);

        $response = $this->actingAs($developer)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Needs Your Decision');
        $response->assertSee('PM-Identity Doc');
    }

    public function test_requestor_sees_uat_signoff_as_actionable(): void
    {
        $requestor = User::factory()->create();
        $project = $this->makeProject(['status' => 'USER_ACCEPTANCE_TESTING', 'requestor_id' => $requestor->id]);

        $response = $this->actingAs($requestor)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Needs Your Decision');
        $response->assertSee('UAT Sign-off');
        $response->assertSee(route('workflow.uat.show', $project), false);
    }

    public function test_mst_reviewer_dashboard_shows_unassigned_task_and_defect_counts(): void
    {
        $mst = User::factory()->mst()->create();
        $project = $this->makeProject(['status' => 'DEVELOPMENT']);

        DevelopmentTask::create([
            'id' => Str::uuid(),
            'software_project_id' => $project->id,
            'task_code' => 'DEV-900',
            'title' => 'Unassigned task',
            'status' => 'TODO',
            'assigned_to' => null,
            'created_by' => $mst->id,
        ]);

        $testPlan = TestPlan::create([
            'id' => Str::uuid(),
            'software_project_id' => $project->id,
            'test_plan_number' => 'TP-901',
            'title' => 'Test Plan',
            'status' => 'ACTIVE',
            'prepared_by' => $mst->id,
            'prepared_at' => now(),
        ]);

        $testCase = TestCaseModel::create([
            'id' => Str::uuid(),
            'test_plan_id' => $testPlan->id,
            'test_case_number' => 'TC-901',
            'title' => 'Test Case',
            'test_type' => 'FUNCTIONAL',
            'test_steps' => 'Do the thing',
            'expected_result' => 'It works',
            'status' => 'NOT_EXECUTED',
        ]);

        Defect::create([
            'id' => Str::uuid(),
            'test_case_id' => $testCase->id,
            'software_project_id' => $project->id,
            'defect_number' => 'DEF-900',
            'title' => 'Unassigned defect',
            'description' => 'Broken',
            'status' => 'OPEN',
            'reported_by' => $mst->id,
            'assigned_to' => null,
        ]);

        $response = $this->actingAs($mst)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Unassigned Tasks');
        $response->assertSee('Unassigned Defects');
    }

    public function test_qa_dashboard_counts_not_executed_test_cases_from_plans_prepared_by_others(): void
    {
        $qa = User::factory()->qa()->create();
        $otherQa = User::factory()->qa()->create();
        $project = $this->makeProject(['status' => 'TESTING_QA']);

        $testPlan = TestPlan::create([
            'id' => Str::uuid(),
            'software_project_id' => $project->id,
            'test_plan_number' => 'TP-900',
            'title' => "Someone Else's Plan",
            'status' => 'ACTIVE',
            'prepared_by' => $otherQa->id,
            'prepared_at' => now(),
        ]);

        TestCaseModel::create([
            'id' => Str::uuid(),
            'test_plan_id' => $testPlan->id,
            'test_case_number' => 'TC-900',
            'title' => 'Not Mine But Still Pending',
            'test_type' => 'FUNCTIONAL',
            'test_steps' => 'Do the thing',
            'expected_result' => 'It works',
            'status' => 'NOT_EXECUTED',
        ]);

        $response = $this->actingAs($qa)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Test Cases Pending');
        $this->assertSame(
            1,
            TestCaseModel::where('status', 'NOT_EXECUTED')
                ->whereHas('testPlan.softwareProject', fn ($query) => $query->where('status', 'TESTING_QA'))
                ->count()
        );
    }
}
