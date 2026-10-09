<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Http\Requests\StoreSoftwareProjectRequest;
use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use App\Services\ProjectOwnerResolver;
use App\Services\ProjectStatusService;
use App\Services\SoftwareProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SoftwareProjectController extends Controller
{
    public function __construct(
        protected SoftwareProjectService $projectService,
        protected ProjectStatusService $statusService,
        protected ProjectOwnerResolver $ownerResolver,
    ) {
    }

    public function create(): View
    {
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get();

        $people = User::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'department']);

        return view('software-projects.create', [
            'departments' => $departments,
            'people' => $people,
            'nextProjectId' => $this->nextProjectId(),
        ]);
    }

    /**
     * Suggest the next SDR reference (SDR-YYYY-NNN) for the current year.
     */
    protected function nextProjectId(): string
    {
        $prefix = 'SDR-' . now()->format('Y') . '-';

        $last = SoftwareProject::where('project_id', 'like', $prefix . '%')
            ->orderByDesc('project_id')
            ->value('project_id');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    public function store(
        StoreSoftwareProjectRequest $request
    ): RedirectResponse {
        $project = $this->projectService->createProject(
            $request->validated()
        );

        return redirect()
            ->route('software-projects.show', $project)
            ->with(
                'success',
                'Software Development Request created successfully.'
            );
    }

    public function index(Request $request): View
    {
        $projects = SoftwareProject::with(['requestor', 'projectSponsor', 'projectManager', 'requestingDepartment'])
            ->where('requestor_id', $request->user()->id)
            ->latest('created_at')
            ->paginate(15);

        return view('software-projects.index', [
            'projects' => $projects,
            'ownerResolver' => $this->ownerResolver,
        ]);
    }

    public function show(string $softwareProject): View
    {
        $project = SoftwareProject::with([
            'requestingDepartment',
            'objectives',
            'scopes',
            'requirements',
            'requiredAttachments',
            'statusHistories' => fn ($query) => $query->with('changedBy')->orderBy('changed_at'),
            'comments' => fn ($query) => $query->with('user')->latest(),
            'documents' => fn ($query) => $query->latest(),
        ])->findOrFail($softwareProject);

        $this->authorize('view', $project);

        return view('software-projects.show', [
            'project' => $project,
            'currentOwner' => $this->ownerResolver->resolve($project),
        ]);
    }

    public function submit(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('submit', $softwareProject);

        $this->statusService->transition(
            $softwareProject,
            ProjectStatus::Submitted,
            $request->user(),
        );

        return redirect()
            ->route('software-projects.show', $softwareProject)
            ->with('success', 'Request submitted for Initial Review.');
    }
}