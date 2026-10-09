<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\SoftwareProject;
use App\Models\UatSession;
use App\Services\ProjectStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserAcceptanceTestingController extends Controller
{
    public function __construct(protected ProjectStatusService $statusService)
    {
    }

    /**
     * Step 10: Management Systems and Technology's queue of requests
     * awaiting User Acceptance Testing.
     */
    public function index(): View
    {
        $this->authorize('viewAny', SoftwareProject::class);

        $projects = SoftwareProject::with('requestingDepartment')
            ->where('status', ProjectStatus::UserAcceptanceTesting->value)
            ->orderBy('updated_at')
            ->get();

        return view('workflow.uat.index', [
            'projects' => $projects,
        ]);
    }

    public function show(SoftwareProject $softwareProject): View
    {
        $this->authorize('view', $softwareProject);

        $softwareProject->load('uatSessions.signoffs');

        return view('workflow.uat.show', [
            'project' => $softwareProject,
        ]);
    }

    public function storeSession(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('manageUat', $softwareProject);

        $validated = $request->validate([
            'uat_number' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'scope' => ['nullable', 'string', 'max:2000'],
            'environment' => ['nullable', 'string', 'max:2000'],
            'planned_date' => ['nullable', 'date'],
        ]);

        $softwareProject->uatSessions()->create([
            'id' => Str::uuid(),
            'uat_number' => $validated['uat_number'],
            'title' => $validated['title'],
            'status' => 'PLANNED',
            'scope' => $validated['scope'] ?? null,
            'environment' => $validated['environment'] ?? null,
            'planned_date' => $validated['planned_date'] ?? null,
            'facilitated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('workflow.uat.show', $softwareProject)
            ->with('success', 'UAT session scheduled.');
    }

    /**
     * "UAT approved?" (step 10's decision diamond) — the requestor's
     * formal accept/reject. Accepted advances to Deployment; rejected
     * stays in UAT for another round, per "address concerns and repeat
     * UAT as required."
     */
    public function storeSignoff(
        Request $request,
        SoftwareProject $softwareProject,
        UatSession $uatSession
    ): RedirectResponse {
        $this->authorize('decideUat', $softwareProject);

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['ACCEPTED', 'REJECTED'])],
            'remarks' => ['required_if:decision,REJECTED', 'nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $softwareProject, $uatSession, $validated) {
            $uatSession->signoffs()->create([
                'id' => Str::uuid(),
                'signatory_id' => $request->user()->id,
                'signatory_name' => $request->user()->name,
                'signatory_role' => $request->user()->role,
                'decision' => $validated['decision'],
                'remarks' => $validated['remarks'] ?? null,
                'signed_at' => now(),
            ]);

            $uatSession->update([
                'status' => 'COMPLETED',
                'actual_end_date' => now()->toDateString(),
            ]);

            if ($validated['decision'] === 'ACCEPTED') {
                $this->statusService->transition(
                    $softwareProject,
                    ProjectStatus::Deployment,
                    $request->user(),
                    $validated['remarks'] ?? null,
                );
            }
        });

        $message = $validated['decision'] === 'ACCEPTED'
            ? "{$softwareProject->project_id} accepted — moved to Deployment."
            : "{$softwareProject->project_id} rejected — schedule another UAT round.";

        return redirect()
            ->route('workflow.uat.show', $softwareProject)
            ->with('success', $message);
    }
}
