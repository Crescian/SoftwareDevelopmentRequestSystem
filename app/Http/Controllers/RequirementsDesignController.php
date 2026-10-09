<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\RequirementsDocument;
use App\Models\SecurityAssessment;
use App\Models\SoftwareProject;
use App\Models\SystemDesign;
use App\Models\User;
use App\Services\ProjectStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RequirementsDesignController extends Controller
{
    public function __construct(protected ProjectStatusService $statusService)
    {
    }

    /**
     * Step 07: Management Systems and Technology's queue of requests
     * awaiting a Requirements Document / System Design.
     */
    public function index(): View
    {
        $this->authorize('viewAny', SoftwareProject::class);

        $projects = SoftwareProject::with('requestingDepartment')
            ->where('status', ProjectStatus::RequirementsDesign->value)
            ->orderBy('updated_at')
            ->get();

        return view('workflow.requirements-design.index', [
            'projects' => $projects,
        ]);
    }

    public function show(Request $request, SoftwareProject $softwareProject): View
    {
        $this->authorize('view', $softwareProject);

        $softwareProject->load([
            'requestingDepartment',
            'requirementsDocuments',
            'systemDesigns',
            'securityAssessments' => fn ($query) => $query->where('assessment_type', 'DESIGN_REVIEW'),
        ]);

        return view('workflow.requirements-design.show', [
            'project' => $softwareProject,
            'canManage' => $request->user()->can('manageRequirementsDesign', $softwareProject),
            'canDecide' => $request->user()->can('decideRequirementsDesign', $softwareProject),
        ]);
    }

    public function storeRequirementsDocument(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('manageRequirementsDesign', $softwareProject);

        $validated = $request->validate([
            'document_number' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'version' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:4000'],
            'document_date' => ['nullable', 'date'],
        ]);

        $softwareProject->requirementsDocuments()->create([
            'id' => Str::uuid(),
            'document_number' => $validated['document_number'],
            'title' => $validated['title'],
            'version' => $validated['version'] ?? '1.0',
            'status' => 'DRAFT',
            'document_date' => $validated['document_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'prepared_by' => $request->user()->id,
            'prepared_at' => now(),
        ]);

        return redirect()
            ->route('workflow.requirements-design.show', $softwareProject)
            ->with('success', 'Requirements document drafted.');
    }

    public function decideRequirementsDocument(
        Request $request,
        SoftwareProject $softwareProject,
        RequirementsDocument $requirementsDocument
    ): RedirectResponse {
        $this->authorize('decideRequirementsDesign', $softwareProject);

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['APPROVED', 'RETURNED'])],
            'remarks' => ['required_if:decision,RETURNED', 'nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $softwareProject, $requirementsDocument, $validated) {
            $requirementsDocument->update([
                'status' => $validated['decision'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'approved_by' => $validated['decision'] === 'APPROVED' ? $request->user()->id : null,
                'approved_at' => $validated['decision'] === 'APPROVED' ? now() : null,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $this->advanceIfReady($softwareProject, $request->user());
        });

        return redirect()
            ->route('workflow.requirements-design.show', $softwareProject)
            ->with('success', "Requirements document {$requirementsDocument->document_number} " . strtolower($validated['decision']) . '.');
    }

    public function storeSystemDesign(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('manageRequirementsDesign', $softwareProject);

        $validated = $request->validate([
            'document_number' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'version' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:4000'],
            'document_date' => ['nullable', 'date'],
            'architecture_overview' => ['nullable', 'string', 'max:4000'],
            'database_design' => ['nullable', 'string', 'max:4000'],
            'integration_design' => ['nullable', 'string', 'max:4000'],
            'security_design' => ['nullable', 'string', 'max:4000'],
            'access_control_design' => ['nullable', 'string', 'max:4000'],
        ]);

        $softwareProject->systemDesigns()->create([
            'id' => Str::uuid(),
            'document_number' => $validated['document_number'],
            'title' => $validated['title'],
            'version' => $validated['version'] ?? '1.0',
            'status' => 'DRAFT',
            'document_date' => $validated['document_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'architecture_overview' => $validated['architecture_overview'] ?? null,
            'database_design' => $validated['database_design'] ?? null,
            'integration_design' => $validated['integration_design'] ?? null,
            'security_design' => $validated['security_design'] ?? null,
            'access_control_design' => $validated['access_control_design'] ?? null,
            'prepared_by' => $request->user()->id,
            'prepared_at' => now(),
        ]);

        return redirect()
            ->route('workflow.requirements-design.show', $softwareProject)
            ->with('success', 'System design drafted.');
    }

    public function decideSystemDesign(
        Request $request,
        SoftwareProject $softwareProject,
        SystemDesign $systemDesign
    ): RedirectResponse {
        $this->authorize('decideRequirementsDesign', $softwareProject);

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['APPROVED', 'RETURNED'])],
            'remarks' => ['required_if:decision,RETURNED', 'nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $softwareProject, $systemDesign, $validated) {
            $systemDesign->update([
                'status' => $validated['decision'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'approved_by' => $validated['decision'] === 'APPROVED' ? $request->user()->id : null,
                'approved_at' => $validated['decision'] === 'APPROVED' ? now() : null,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $this->advanceIfReady($softwareProject, $request->user());
        });

        return redirect()
            ->route('workflow.requirements-design.show', $softwareProject)
            ->with('success', "System design {$systemDesign->document_number} " . strtolower($validated['decision']) . '.');
    }

    public function storeSecurityAssessment(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('manageRequirementsDesign', $softwareProject);

        $validated = $this->validateSecurityAssessment($request);

        $softwareProject->securityAssessments()->create([
            'id' => Str::uuid(),
            'assessment_number' => $validated['assessment_number'],
            'assessment_type' => 'DESIGN_REVIEW',
            'status' => 'COMPLETED',
            'authentication_review' => $validated['authentication_review'] ?? null,
            'authorization_review' => $validated['authorization_review'] ?? null,
            'data_privacy_review' => $validated['data_privacy_review'] ?? null,
            'vulnerability_assessment' => $validated['vulnerability_assessment'] ?? null,
            'security_risks' => $validated['security_risks'] ?? null,
            'mitigation_actions' => $validated['mitigation_actions'] ?? null,
            'overall_result' => $validated['overall_result'],
            'assessed_by' => $request->user()->id,
            'assessed_at' => now(),
        ]);

        return redirect()
            ->route('workflow.requirements-design.show', $softwareProject)
            ->with('success', 'Security assessment recorded.');
    }

    /**
     * The Project Manager decides this one too — security is designed in
     * up front, alongside the Requirements Document and System Design,
     * not a separate authority.
     */
    public function decideSecurityAssessment(
        Request $request,
        SoftwareProject $softwareProject,
        SecurityAssessment $securityAssessment
    ): RedirectResponse {
        $this->authorize('decideRequirementsDesign', $softwareProject);

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['APPROVED', 'RETURNED'])],
            'remarks' => ['required_if:decision,RETURNED', 'nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $softwareProject, $securityAssessment, $validated) {
            $securityAssessment->update([
                'status' => $validated['decision'],
                'approved_by' => $validated['decision'] === 'APPROVED' ? $request->user()->id : null,
                'approved_at' => $validated['decision'] === 'APPROVED' ? now() : null,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $this->advanceIfReady($softwareProject, $request->user());
        });

        return redirect()
            ->route('workflow.requirements-design.show', $softwareProject)
            ->with('success', "Security assessment {$securityAssessment->assessment_number} " . strtolower($validated['decision']) . '.');
    }

    /**
     * @return array{assessment_number:string,authentication_review:?string,authorization_review:?string,data_privacy_review:?string,vulnerability_assessment:?string,security_risks:?string,mitigation_actions:?string,overall_result:string}
     */
    private function validateSecurityAssessment(Request $request): array
    {
        return $request->validate([
            'assessment_number' => ['required', 'string', 'max:50'],
            'authentication_review' => ['nullable', 'string', 'max:4000'],
            'authorization_review' => ['nullable', 'string', 'max:4000'],
            'data_privacy_review' => ['nullable', 'string', 'max:4000'],
            'vulnerability_assessment' => ['nullable', 'string', 'max:4000'],
            'security_risks' => ['nullable', 'string', 'max:4000'],
            'mitigation_actions' => ['nullable', 'string', 'max:4000'],
            'overall_result' => ['required', Rule::in(['PASS', 'PASS_WITH_CONDITIONS', 'FAIL'])],
        ]);
    }

    /**
     * Once an approved Requirements Document, an approved System Design,
     * and an approved design-stage Security Assessment all exist, the
     * project automatically proceeds to Development — the flowchart has
     * no separate decision diamond for this step.
     */
    private function advanceIfReady(SoftwareProject $project, User $actor): void
    {
        if ($project->status !== ProjectStatus::RequirementsDesign->value) {
            return;
        }

        $hasApprovedRequirements = $project->requirementsDocuments()
            ->where('status', 'APPROVED')
            ->exists();

        $hasApprovedDesign = $project->systemDesigns()
            ->where('status', 'APPROVED')
            ->exists();

        $hasApprovedSecurityReview = $project->securityAssessments()
            ->where('assessment_type', 'DESIGN_REVIEW')
            ->where('status', 'APPROVED')
            ->exists();

        if ($hasApprovedRequirements && $hasApprovedDesign && $hasApprovedSecurityReview) {
            $this->statusService->transition(
                $project,
                ProjectStatus::Development,
                $actor,
                'Requirements document, system design, and security assessment approved.',
            );
        }
    }
}
