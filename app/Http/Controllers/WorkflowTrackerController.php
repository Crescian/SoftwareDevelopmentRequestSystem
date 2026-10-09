<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\SoftwareProject;
use App\Services\ProjectOwnerResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkflowTrackerController extends Controller
{
    public function __construct(protected ProjectOwnerResolver $ownerResolver)
    {
    }

    /**
     * Consolidated view of every active project across all 8 workflow
     * queues, replacing the sidebar's 8 separate queue links. Each row
     * still opens the correct existing stage-specific action page
     * (ProjectStatus::actionRoute()) — this page is a list, not a
     * replacement for where the actual work happens.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SoftwareProject::class);

        $query = SoftwareProject::with(['requestor', 'projectSponsor', 'projectManager', 'requestingDepartment'])
            ->where('status', '!=', ProjectStatus::Draft->value)
            ->latest('updated_at');

        $statusFilter = $request->string('status')->toString();

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        $projects = $query->paginate(20)->withQueryString();

        return view('workflow.tracker.index', [
            'projects' => $projects,
            'ownerResolver' => $this->ownerResolver,
            'statusFilter' => $statusFilter,
            'statuses' => collect(ProjectStatus::cases())->reject(fn (ProjectStatus $status) => $status === ProjectStatus::Draft),
        ]);
    }
}
