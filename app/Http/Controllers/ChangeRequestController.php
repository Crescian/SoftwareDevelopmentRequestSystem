<?php

namespace App\Http\Controllers;

use App\Models\ChangeApproval;
use App\Models\ChangeRequest;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChangeRequestController extends Controller
{
    public function show(SoftwareProject $softwareProject): View
    {
        $this->authorize('view', $softwareProject);

        $softwareProject->load(['changeRequests.approvals', 'changeRequests.histories']);

        return view('workflow.change-requests.show', [
            'project' => $softwareProject,
        ]);
    }

    public function store(Request $request, SoftwareProject $softwareProject): RedirectResponse
    {
        $this->authorize('proposeChangeRequest', $softwareProject);

        $request->merge([
            'approvals' => array_values(array_filter(
                $request->input('approvals', []),
                fn ($approval) => filled($approval['approval_type'] ?? null) && filled($approval['approver_id'] ?? null),
            )),
        ]);

        $validated = $request->validate([
            'change_request_number' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:4000'],
            'reason' => ['required', 'string', 'max:2000'],
            'priority' => ['nullable', Rule::in(['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])],
            'planned_implementation_date' => ['nullable', 'date'],
            'approvals' => ['required', 'array', 'min:1'],
            'approvals.*.approval_type' => ['required', 'string', 'max:50'],
            'approvals.*.approver_id' => ['required', 'uuid', 'exists:users,id'],
        ]);

        DB::transaction(function () use ($request, $softwareProject, $validated) {
            $changeRequest = $softwareProject->changeRequests()->create([
                'id' => Str::uuid(),
                'change_request_number' => $validated['change_request_number'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'reason' => $validated['reason'],
                'priority' => $validated['priority'] ?? 'MEDIUM',
                'status' => 'SUBMITTED',
                'requested_by' => $request->user()->id,
                'requested_at' => now(),
                'planned_implementation_date' => $validated['planned_implementation_date'] ?? null,
            ]);

            foreach ($validated['approvals'] as $index => $approval) {
                $changeRequest->approvals()->create([
                    'id' => Str::uuid(),
                    'approval_sequence' => $index + 1,
                    'approval_type' => $approval['approval_type'],
                    'approver_id' => $approval['approver_id'],
                    'status' => 'PENDING',
                ]);
            }

            $this->logHistory($changeRequest, 'SUBMITTED', null, 'SUBMITTED', $request->user());
        });

        return redirect()
            ->route('workflow.change-requests.show', $softwareProject)
            ->with('success', 'Change request submitted.');
    }

    public function decide(
        Request $request,
        SoftwareProject $softwareProject,
        ChangeRequest $changeRequest,
        ChangeApproval $changeApproval
    ): RedirectResponse {
        $this->authorize('decide', $changeApproval);

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['APPROVED', 'REJECTED', 'RETURNED'])],
            'remarks' => ['required_unless:decision,APPROVED', 'nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $changeRequest, $changeApproval, $validated) {
            $changeApproval->update([
                'status' => $validated['decision'],
                'remarks' => $validated['remarks'] ?? null,
                'approved_at' => now(),
            ]);

            $fromStatus = $changeRequest->status;

            if ($validated['decision'] === 'REJECTED') {
                $changeRequest->update(['status' => 'REJECTED']);
            } elseif ($validated['decision'] === 'RETURNED') {
                $changeRequest->update(['status' => 'RETURNED_FOR_REVISION']);
            } else {
                $remainingPending = $changeRequest->approvals()->where('status', 'PENDING')->count();

                if ($remainingPending === 0) {
                    $changeRequest->update(['status' => 'APPROVED']);
                }
            }

            $this->logHistory(
                $changeRequest,
                'APPROVAL_' . $validated['decision'],
                $fromStatus,
                $changeRequest->fresh()->status,
                $request->user(),
                $validated['remarks'] ?? null,
            );
        });

        return redirect()
            ->route('workflow.change-requests.show', $softwareProject)
            ->with('success', "Decision recorded for {$changeRequest->change_request_number}.");
    }

    /**
     * After a RETURNED decision, the requestor can resubmit — every
     * approval step resets to PENDING so the chain runs again from the
     * start, since a revised change may need every approver to re-check it.
     */
    public function resubmit(
        Request $request,
        SoftwareProject $softwareProject,
        ChangeRequest $changeRequest
    ): RedirectResponse {
        abort_unless(
            $request->user()->id === $changeRequest->requested_by
                && $changeRequest->status === 'RETURNED_FOR_REVISION',
            403,
        );

        DB::transaction(function () use ($request, $changeRequest) {
            $changeRequest->approvals()->update([
                'status' => 'PENDING',
                'remarks' => null,
                'approved_at' => null,
            ]);

            $changeRequest->update(['status' => 'SUBMITTED']);

            $this->logHistory($changeRequest, 'RESUBMITTED', 'RETURNED_FOR_REVISION', 'SUBMITTED', $request->user());
        });

        return redirect()
            ->route('workflow.change-requests.show', $softwareProject)
            ->with('success', "{$changeRequest->change_request_number} resubmitted.");
    }

    public function markImplemented(
        Request $request,
        SoftwareProject $softwareProject,
        ChangeRequest $changeRequest
    ): RedirectResponse {
        $this->authorize('manageChangeRequest', $softwareProject);

        abort_unless($changeRequest->status === 'APPROVED', 422, 'Change request is not approved yet.');

        $changeRequest->update([
            'status' => 'IMPLEMENTED',
            'implemented_at' => now(),
        ]);

        $this->logHistory($changeRequest, 'IMPLEMENTED', 'APPROVED', 'IMPLEMENTED', $request->user());

        return redirect()
            ->route('workflow.change-requests.show', $softwareProject)
            ->with('success', "{$changeRequest->change_request_number} marked implemented.");
    }

    public function markClosed(
        Request $request,
        SoftwareProject $softwareProject,
        ChangeRequest $changeRequest
    ): RedirectResponse {
        $this->authorize('manageChangeRequest', $softwareProject);

        abort_unless($changeRequest->status === 'IMPLEMENTED', 422, 'Change request has not been implemented yet.');

        $changeRequest->update([
            'status' => 'CLOSED',
            'closed_at' => now(),
        ]);

        $this->logHistory($changeRequest, 'CLOSED', 'IMPLEMENTED', 'CLOSED', $request->user());

        return redirect()
            ->route('workflow.change-requests.show', $softwareProject)
            ->with('success', "{$changeRequest->change_request_number} closed.");
    }

    private function logHistory(
        ChangeRequest $changeRequest,
        string $action,
        ?string $fromStatus,
        ?string $toStatus,
        User $actor,
        ?string $summary = null,
    ): void {
        $changeRequest->histories()->create([
            'id' => Str::uuid(),
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'change_summary' => $summary,
            'changed_by' => $actor->id,
            'changed_at' => now(),
        ]);
    }
}
