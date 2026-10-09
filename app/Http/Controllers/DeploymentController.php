<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\Deployment;
use App\Models\DeploymentChecklist;
use App\Models\SecurityAssessment;
use App\Models\SoftwareProject;
use App\Services\ProjectStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DeploymentController extends Controller
{
    public function __construct(protected ProjectStatusService $statusService)
    {
    }

    /**
     * Step 11: Management Systems and Technology's queue of requests
     * currently in Deployment.
     */
    public function index(): View
    {
        $this->authorize('viewAny', SoftwareProject::class);

        $projects = SoftwareProject::with('requestingDepartment')
            ->where('status', ProjectStatus::Deployment->value)
            ->orderBy('updated_at')
            ->get();

        return view('workflow.deployment.index', [
            'projects' => $projects,
        ]);
    }

    public function show(SoftwareProject $softwareProject): View
    {
        $this->authorize('view', $softwareProject);

        $softwareProject->load([
            'deployments.checklistItems',
            'deployments.releaseNotes',
            'securityAssessments' => fn ($query) => $query->where('assessment_type', 'PRE_DEPLOYMENT'),
        ]);

        return view('workflow.deployment.show', [
            'project' => $softwareProject,
        ]);
    }

    public function storeDeployment(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('manageDeployment', $softwareProject);

        $validated = $request->validate([
            'deployment_number' => ['required', 'string', 'max:50'],
            'version' => ['required', 'string', 'max:50'],
            'environment' => ['required', 'string', 'max:30'],
            'deployment_type' => ['nullable', 'string', 'max:30'],
            'release_summary' => ['nullable', 'string', 'max:4000'],
            'changes_summary' => ['nullable', 'string', 'max:4000'],
            'rollback_plan' => ['nullable', 'string', 'max:4000'],
            'planned_date' => ['nullable', 'date'],
        ]);

        $softwareProject->deployments()->create([
            'id' => Str::uuid(),
            'deployment_number' => $validated['deployment_number'],
            'version' => $validated['version'],
            'environment' => $validated['environment'],
            'deployment_type' => $validated['deployment_type'] ?? 'RELEASE',
            'status' => 'PLANNED',
            'release_summary' => $validated['release_summary'] ?? null,
            'changes_summary' => $validated['changes_summary'] ?? null,
            'rollback_plan' => $validated['rollback_plan'] ?? null,
            'planned_date' => $validated['planned_date'] ?? null,
            'prepared_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('workflow.deployment.show', $softwareProject)
            ->with('success', 'Deployment planned.');
    }

    public function storeChecklistItem(
        Request $request,
        SoftwareProject $softwareProject,
        Deployment $deployment
    ): RedirectResponse {
        $this->authorize('manageDeployment', $softwareProject);

        $validated = $request->validate([
            'category' => ['required', 'string', 'max:50'],
            'item' => ['required', 'string', 'max:255'],
            'is_required' => ['nullable', 'boolean'],
        ]);

        $nextItemNo = $deployment->checklistItems()->max('item_no') + 1;

        $deployment->checklistItems()->create([
            'id' => Str::uuid(),
            'item_no' => $nextItemNo,
            'category' => $validated['category'],
            'item' => $validated['item'],
            'is_required' => $request->boolean('is_required', true),
            'status' => 'PENDING',
        ]);

        return redirect()
            ->route('workflow.deployment.show', $softwareProject)
            ->with('success', 'Checklist item added.');
    }

    public function checkChecklistItem(
        Request $request,
        SoftwareProject $softwareProject,
        DeploymentChecklist $deploymentChecklist
    ): RedirectResponse {
        $this->authorize('manageDeployment', $softwareProject);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['DONE', 'FAILED', 'N_A'])],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $deploymentChecklist->update([
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? null,
            'checked_by' => $request->user()->id,
            'checked_at' => now(),
        ]);

        return redirect()
            ->route('workflow.deployment.show', $softwareProject)
            ->with('success', "Checklist item #{$deploymentChecklist->item_no} updated.");
    }

    public function storeReleaseNote(
        Request $request,
        SoftwareProject $softwareProject,
        Deployment $deployment
    ): RedirectResponse {
        $this->authorize('manageDeployment', $softwareProject);

        $validated = $request->validate([
            'document_number' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:50'],
            'release_summary' => ['nullable', 'string', 'max:4000'],
            'new_features' => ['nullable', 'string', 'max:4000'],
            'bug_fixes' => ['nullable', 'string', 'max:4000'],
            'known_issues' => ['nullable', 'string', 'max:4000'],
        ]);

        $deployment->releaseNotes()->create([
            'id' => Str::uuid(),
            'document_number' => $validated['document_number'],
            'title' => $validated['title'],
            'version' => $validated['version'],
            'release_summary' => $validated['release_summary'] ?? null,
            'new_features' => $validated['new_features'] ?? null,
            'bug_fixes' => $validated['bug_fixes'] ?? null,
            'known_issues' => $validated['known_issues'] ?? null,
            'prepared_by' => $request->user()->id,
            'prepared_at' => now(),
        ]);

        return redirect()
            ->route('workflow.deployment.show', $softwareProject)
            ->with('success', 'Release note added.');
    }

    public function storeSecurityAssessment(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('manageDeployment', $softwareProject);

        $validated = $request->validate([
            'assessment_number' => ['required', 'string', 'max:50'],
            'authentication_review' => ['nullable', 'string', 'max:4000'],
            'authorization_review' => ['nullable', 'string', 'max:4000'],
            'data_privacy_review' => ['nullable', 'string', 'max:4000'],
            'vulnerability_assessment' => ['nullable', 'string', 'max:4000'],
            'security_risks' => ['nullable', 'string', 'max:4000'],
            'mitigation_actions' => ['nullable', 'string', 'max:4000'],
            'overall_result' => ['required', Rule::in(['PASS', 'PASS_WITH_CONDITIONS', 'FAIL'])],
        ]);

        $softwareProject->securityAssessments()->create([
            'id' => Str::uuid(),
            'assessment_number' => $validated['assessment_number'],
            'assessment_type' => 'PRE_DEPLOYMENT',
            'status' => $validated['overall_result'] === 'FAIL' ? 'COMPLETED' : 'APPROVED',
            'authentication_review' => $validated['authentication_review'] ?? null,
            'authorization_review' => $validated['authorization_review'] ?? null,
            'data_privacy_review' => $validated['data_privacy_review'] ?? null,
            'vulnerability_assessment' => $validated['vulnerability_assessment'] ?? null,
            'security_risks' => $validated['security_risks'] ?? null,
            'mitigation_actions' => $validated['mitigation_actions'] ?? null,
            'overall_result' => $validated['overall_result'],
            'assessed_by' => $request->user()->id,
            'assessed_at' => now(),
            'approved_by' => $validated['overall_result'] === 'FAIL' ? null : $request->user()->id,
            'approved_at' => $validated['overall_result'] === 'FAIL' ? null : now(),
        ]);

        return redirect()
            ->route('workflow.deployment.show', $softwareProject)
            ->with('success', 'Pre-deployment security assessment recorded.');
    }

    /**
     * "Deployment successful?" (step 11's decision diamond). Blocked
     * while any required checklist item isn't DONE, or the pre-deployment
     * security assessment hasn't passed — the flowchart requires
     * deployment checks "completed before release."
     * A FAILED outcome stays in Deployment for rollback/retry, per
     * "apply corrective action or rollback process... before the
     * release is reconsidered" — it does not return to the requestor.
     */
    public function recordOutcome(
        Request $request,
        SoftwareProject $softwareProject,
        Deployment $deployment
    ): RedirectResponse {
        $this->authorize('manageDeployment', $softwareProject);

        $validated = $request->validate([
            'outcome' => ['required', Rule::in(['SUCCESSFUL', 'FAILED'])],
            'remarks' => ['required_if:outcome,FAILED', 'nullable', 'string', 'max:2000'],
        ]);

        if ($validated['outcome'] === 'SUCCESSFUL') {
            $incompleteRequired = $deployment->checklistItems()
                ->where('is_required', true)
                ->where('status', '!=', 'DONE')
                ->count();

            if ($incompleteRequired > 0) {
                throw ValidationException::withMessages([
                    'checklist' => "Cannot deploy: {$incompleteRequired} required checklist item(s) are not done.",
                ]);
            }

            $hasPassedSecurityAssessment = $softwareProject->securityAssessments()
                ->where('assessment_type', 'PRE_DEPLOYMENT')
                ->where('status', 'APPROVED')
                ->exists();

            if (! $hasPassedSecurityAssessment) {
                throw ValidationException::withMessages([
                    'security' => 'Cannot deploy: a passed pre-deployment security assessment is required.',
                ]);
            }
        }

        DB::transaction(function () use ($request, $softwareProject, $deployment, $validated) {
            $deployment->update([
                'status' => $validated['outcome'] === 'SUCCESSFUL' ? 'COMPLETED' : 'FAILED',
                'deployed_by' => $request->user()->id,
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'started_at' => $deployment->started_at ?? now(),
                'completed_at' => now(),
                'remarks' => $validated['remarks'] ?? null,
            ]);

            if ($validated['outcome'] === 'SUCCESSFUL') {
                $this->statusService->transition(
                    $softwareProject,
                    ProjectStatus::MonitoringMaintenance,
                    $request->user(),
                    $validated['remarks'] ?? null,
                );
            }
        });

        $message = $validated['outcome'] === 'SUCCESSFUL'
            ? "{$softwareProject->project_id} deployed successfully — now in Monitoring & Maintenance."
            : "{$softwareProject->project_id} deployment failed — apply rollback/corrective action and retry.";

        return redirect()
            ->route('workflow.deployment.show', $softwareProject)
            ->with('success', $message);
    }
}
