<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\Defect;
use App\Models\SoftwareProject;
use App\Models\TestCase;
use App\Models\TestPlan;
use App\Services\ProjectStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TestingQaController extends Controller
{
    public function __construct(protected ProjectStatusService $statusService)
    {
    }

    /**
     * Step 09: Management Systems and Technology / QA's queue of requests
     * currently in Testing & QA.
     */
    public function index(): View
    {
        $this->authorize('viewAny', SoftwareProject::class);

        $projects = SoftwareProject::with('requestingDepartment')
            ->where('status', ProjectStatus::TestingQa->value)
            ->orderBy('updated_at')
            ->get();

        return view('workflow.testing-qa.index', [
            'projects' => $projects,
        ]);
    }

    public function show(SoftwareProject $softwareProject): View
    {
        $this->authorize('view', $softwareProject);

        $softwareProject->load([
            'testPlans.testCases.defects',
        ]);

        $openDefectCount = $softwareProject->defects()->where('status', '!=', 'VERIFIED')->count();

        return view('workflow.testing-qa.show', [
            'project' => $softwareProject,
            'openDefectCount' => $openDefectCount,
        ]);
    }

    public function storeTestPlan(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('manageTestingQa', $softwareProject);

        $validated = $request->validate([
            'test_plan_number' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'objectives' => ['nullable', 'string', 'max:2000'],
            'scope' => ['nullable', 'string', 'max:2000'],
            'test_environment' => ['nullable', 'string', 'max:100'],
        ]);

        $softwareProject->testPlans()->create([
            'id' => Str::uuid(),
            'test_plan_number' => $validated['test_plan_number'],
            'title' => $validated['title'],
            'status' => 'ACTIVE',
            'objectives' => $validated['objectives'] ?? null,
            'scope' => $validated['scope'] ?? null,
            'test_environment' => $validated['test_environment'] ?? null,
            'prepared_by' => $request->user()->id,
            'prepared_at' => now(),
        ]);

        return redirect()
            ->route('workflow.testing-qa.show', $softwareProject)
            ->with('success', 'Test plan created.');
    }

    public function storeTestCase(
        Request $request,
        SoftwareProject $softwareProject,
        TestPlan $testPlan
    ): RedirectResponse {
        $this->authorize('manageTestingQa', $softwareProject);

        $validated = $request->validate([
            'test_case_number' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'test_type' => ['required', 'string', 'max:30'],
            'test_steps' => ['required', 'string', 'max:4000'],
            'expected_result' => ['required', 'string', 'max:2000'],
        ]);

        $testPlan->testCases()->create([
            'id' => Str::uuid(),
            'test_case_number' => $validated['test_case_number'],
            'title' => $validated['title'],
            'test_type' => $validated['test_type'],
            'test_steps' => $validated['test_steps'],
            'expected_result' => $validated['expected_result'],
            'status' => 'NOT_EXECUTED',
        ]);

        return redirect()
            ->route('workflow.testing-qa.show', $softwareProject)
            ->with('success', 'Test case added.');
    }

    public function executeTestCase(
        Request $request,
        SoftwareProject $softwareProject,
        TestCase $testCase
    ): RedirectResponse {
        $this->authorize('manageTestingQa', $softwareProject);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['PASSED', 'FAILED', 'BLOCKED'])],
            'actual_result' => ['required', 'string', 'max:2000'],
        ]);

        $testCase->update([
            'status' => $validated['status'],
            'actual_result' => $validated['actual_result'],
            'executed_by' => $request->user()->id,
            'executed_at' => now(),
        ]);

        return redirect()
            ->route('workflow.testing-qa.show', $softwareProject)
            ->with('success', "Test case {$testCase->test_case_number} recorded as {$validated['status']}.");
    }

    public function storeDefect(
        Request $request,
        SoftwareProject $softwareProject,
        TestCase $testCase
    ): RedirectResponse {
        $this->authorize('manageTestingQa', $softwareProject);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:4000'],
            'severity' => ['nullable', Rule::in(['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])],
            'priority' => ['nullable', Rule::in(['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])],
            'assigned_to' => ['nullable', 'uuid', 'exists:users,id'],
        ]);

        $defectNumber = 'DEF-' . str_pad((string) ($softwareProject->defects()->count() + 1), 3, '0', STR_PAD_LEFT);

        $softwareProject->defects()->create([
            'id' => Str::uuid(),
            'test_case_id' => $testCase->id,
            'defect_number' => $defectNumber,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'severity' => $validated['severity'] ?? 'MEDIUM',
            'priority' => $validated['priority'] ?? 'MEDIUM',
            'status' => 'OPEN',
            'reported_by' => $request->user()->id,
            'reported_at' => now(),
            'assigned_to' => $validated['assigned_to'] ?? null,
        ]);

        return redirect()
            ->route('workflow.testing-qa.show', $softwareProject)
            ->with('success', "Defect {$defectNumber} logged.");
    }

    public function updateDefectStatus(
        Request $request,
        SoftwareProject $softwareProject,
        Defect $defect
    ): RedirectResponse {
        $this->authorize('manageTestingQa', $softwareProject);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['IN_PROGRESS', 'RESOLVED', 'VERIFIED', 'REOPENED'])],
            'resolution' => ['required_if:status,RESOLVED', 'nullable', 'string', 'max:2000'],
        ]);

        $attributes = ['status' => $validated['status']];

        if ($validated['status'] === 'RESOLVED') {
            $attributes['resolution'] = $validated['resolution'];
            $attributes['resolved_at'] = now();
        }

        if ($validated['status'] === 'VERIFIED') {
            $attributes['verified_by'] = $request->user()->id;
            $attributes['verified_at'] = now();
        }

        if ($validated['status'] === 'REOPENED') {
            $attributes['resolved_at'] = null;
            $attributes['verified_by'] = null;
            $attributes['verified_at'] = null;
        }

        $defect->update($attributes);

        return redirect()
            ->route('workflow.testing-qa.show', $softwareProject)
            ->with('success', "Defect {$defect->defect_number} updated.");
    }

    /**
     * "Passed testing?" (step 09's decision diamond). Blocked while any
     * defect isn't VERIFIED yet — the flowchart requires defects "are
     * corrected and retested before the request proceeds to UAT."
     */
    public function advance(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('manageTestingQa', $softwareProject);

        $openDefects = $softwareProject->defects()->where('status', '!=', 'VERIFIED')->count();

        if ($openDefects > 0) {
            throw ValidationException::withMessages([
                'defects' => "Cannot proceed to UAT: {$openDefects} defect(s) are not yet verified.",
            ]);
        }

        $this->statusService->transition(
            $softwareProject,
            ProjectStatus::UserAcceptanceTesting,
            $request->user(),
            $request->input('remarks'),
        );

        return redirect()
            ->route('workflow.testing-qa.index')
            ->with('success', "{$softwareProject->project_id} moved to User Acceptance Testing.");
    }
}
