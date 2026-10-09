<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\SoftwareProject;
use App\Services\ProjectStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeasibilityAssessmentController extends Controller
{
    public function __construct(protected ProjectStatusService $statusService)
    {
    }

    /**
     * Step 05: Management Systems and Technology's queue of requests
     * awaiting feasibility assessment.
     */
    public function index(): View
    {
        $this->authorize('viewAny', SoftwareProject::class);

        $projects = SoftwareProject::with('requestingDepartment')
            ->where('status', ProjectStatus::FeasibilityAssessment->value)
            ->orderBy('updated_at')
            ->get();

        return view('reviews.feasibility-assessment.index', [
            'projects' => $projects,
        ]);
    }

    public function show(SoftwareProject $softwareProject): View
    {
        $this->authorize('conductFeasibilityAssessment', $softwareProject);

        $softwareProject->load(['requestingDepartment', 'feasibilityAssessments']);

        return view('reviews.feasibility-assessment.show', [
            'project' => $softwareProject,
        ]);
    }

    public function store(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('conductFeasibilityAssessment', $softwareProject);

        $validated = $request->validate([
            'technical_feasibility' => ['required', 'string', 'max:4000'],
            'operational_feasibility' => ['required', 'string', 'max:4000'],
            'cost_benefit_analysis' => ['required', 'string', 'max:4000'],
            'overall_recommendation' => [
                'required',
                Rule::in(['FEASIBLE', 'NOT_FEASIBLE_RETURN', 'NOT_FEASIBLE_CLOSE']),
            ],
            'assessment_notes' => [
                'required_unless:overall_recommendation,FEASIBLE',
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        DB::transaction(function () use ($request, $softwareProject, $validated) {
            $softwareProject->feasibilityAssessments()->create([
                'id' => Str::uuid(),
                'assessed_by' => $request->user()->id,
                'technical_feasibility' => $validated['technical_feasibility'],
                'operational_feasibility' => $validated['operational_feasibility'],
                'cost_benefit_analysis' => $validated['cost_benefit_analysis'],
                'overall_recommendation' => $validated['overall_recommendation'],
                'assessment_notes' => $validated['assessment_notes'] ?? null,
                'assessed_at' => now(),
            ]);

            $nextStatus = match ($validated['overall_recommendation']) {
                'FEASIBLE' => ProjectStatus::PendingApproval,
                'NOT_FEASIBLE_RETURN' => ProjectStatus::ReturnedForRevision,
                'NOT_FEASIBLE_CLOSE' => ProjectStatus::Closed,
            };

            $this->statusService->transition(
                $softwareProject,
                $nextStatus,
                $request->user(),
                $validated['assessment_notes'] ?? null,
            );

            if ($nextStatus === ProjectStatus::PendingApproval) {
                $softwareProject->approvals()->create([
                    'id' => Str::uuid(),
                    'approval_type' => 'MANAGEMENT_APPROVAL',
                    'approver_id' => $softwareProject->project_sponsor_id,
                    'status' => 'PENDING',
                ]);
            }
        });

        $nextStatus = ProjectStatus::from($softwareProject->fresh()->status);

        return redirect()
            ->route('reviews.feasibility-assessment.index')
            ->with('success', "{$softwareProject->project_id} assessed: {$nextStatus->label()}.");
    }
}
