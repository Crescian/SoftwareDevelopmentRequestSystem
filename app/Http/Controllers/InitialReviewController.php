<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\SoftwareProject;
use App\Services\ProjectStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InitialReviewController extends Controller
{
    public function __construct(protected ProjectStatusService $statusService)
    {
    }

    /**
     * Step 04: Management Systems and Technology's queue of requests
     * awaiting completeness review.
     */
    public function index(): View
    {
        $this->authorize('viewAny', SoftwareProject::class);

        $projects = SoftwareProject::with('requestingDepartment')
            ->where('status', ProjectStatus::Submitted->value)
            ->orderBy('submitted_at')
            ->get();

        return view('reviews.initial-review.index', [
            'projects' => $projects,
        ]);
    }

    public function show(SoftwareProject $softwareProject): View
    {
        $this->authorize('conductInitialReview', $softwareProject);

        $softwareProject->load([
            'requestingDepartment',
            'objectives',
            'scopes',
            'requirements',
            'requiredAttachments',
        ]);

        return view('reviews.initial-review.show', [
            'project' => $softwareProject,
        ]);
    }

    public function pass(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('conductInitialReview', $softwareProject);

        $this->statusService->transition(
            $softwareProject,
            ProjectStatus::FeasibilityAssessment,
            $request->user(),
            $request->input('remarks'),
        );

        return redirect()
            ->route('reviews.initial-review.index')
            ->with('success', "{$softwareProject->project_id} moved to Feasibility Assessment.");
    }

    public function returnForRevision(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('conductInitialReview', $softwareProject);

        $validated = $request->validate([
            'remarks' => ['required', 'string', 'max:2000'],
        ]);

        $this->statusService->transition(
            $softwareProject,
            ProjectStatus::ReturnedForRevision,
            $request->user(),
            $validated['remarks'],
        );

        return redirect()
            ->route('reviews.initial-review.index')
            ->with('success', "{$softwareProject->project_id} returned to the requestor for revision.");
    }
}
