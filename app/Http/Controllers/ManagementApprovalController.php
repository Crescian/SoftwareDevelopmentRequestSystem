<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\SoftwareProject;
use App\Services\ProjectStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManagementApprovalController extends Controller
{
    public function __construct(protected ProjectStatusService $statusService)
    {
    }

    /**
     * Step 06: requests awaiting a decision from this specific user, who
     * is the project's sponsor (the confirmed, named-per-request approver
     * — not a general role-based approver list).
     */
    public function index(Request $request): View
    {
        $projects = SoftwareProject::with('requestingDepartment')
            ->where('status', ProjectStatus::PendingApproval->value)
            ->where('project_sponsor_id', $request->user()->id)
            ->orderBy('updated_at')
            ->get();

        return view('approvals.management.index', [
            'projects' => $projects,
        ]);
    }

    public function show(SoftwareProject $softwareProject): View
    {
        $this->authorize('decideManagementApproval', $softwareProject);

        $softwareProject->load('requestingDepartment');

        return view('approvals.management.show', [
            'project' => $softwareProject,
        ]);
    }

    public function store(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('decideManagementApproval', $softwareProject);

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['APPROVED', 'RETURNED', 'REJECTED'])],
            'remarks' => ['required_unless:decision,APPROVED', 'nullable', 'string', 'max:2000'],
        ]);

        $approval = $softwareProject->approvals()
            ->where('approval_type', 'MANAGEMENT_APPROVAL')
            ->where('status', 'PENDING')
            ->latest('created_at')
            ->firstOrFail();

        $nextStatus = match ($validated['decision']) {
            'APPROVED' => ProjectStatus::RequirementsDesign,
            'RETURNED' => ProjectStatus::ReturnedForRevision,
            'REJECTED' => ProjectStatus::Rejected,
        };

        DB::transaction(function () use ($request, $softwareProject, $validated, $approval, $nextStatus) {
            $approval->update([
                'status' => $validated['decision'],
                'remarks' => $validated['remarks'] ?? null,
                'approved_at' => now(),
            ]);

            $this->statusService->transition(
                $softwareProject,
                $nextStatus,
                $request->user(),
                $validated['remarks'] ?? null,
            );
        });

        return redirect()
            ->route('approvals.management.index')
            ->with('success', "{$softwareProject->project_id} decision recorded: {$nextStatus->label()}.");
    }
}
