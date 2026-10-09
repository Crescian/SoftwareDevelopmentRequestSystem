<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\DevelopmentTask;
use App\Models\SoftwareProject;
use App\Services\ProjectStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DevelopmentController extends Controller
{
    public function __construct(protected ProjectStatusService $statusService)
    {
    }

    /**
     * Step 08: Management Systems and Technology's queue of requests
     * currently in Development.
     */
    public function index(): View
    {
        $this->authorize('viewAny', SoftwareProject::class);

        $projects = SoftwareProject::with('requestingDepartment')
            ->where('status', ProjectStatus::Development->value)
            ->orderBy('updated_at')
            ->get();

        return view('workflow.development.index', [
            'projects' => $projects,
        ]);
    }

    public function show(SoftwareProject $softwareProject): View
    {
        $this->authorize('view', $softwareProject);

        $softwareProject->load(['requestingDepartment', 'repositories', 'developmentTasks']);

        return view('workflow.development.show', [
            'project' => $softwareProject,
            'openTaskCount' => $softwareProject->developmentTasks
                ->whereNotIn('status', ['DONE'])
                ->count(),
        ]);
    }

    public function storeRepository(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('manageDevelopment', $softwareProject);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:30'],
            'repository_url' => ['required', 'string', 'max:2000'],
            'default_branch' => ['nullable', 'string', 'max:100'],
            'visibility' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $softwareProject->repositories()->create([
            'id' => Str::uuid(),
            'name' => $validated['name'],
            'provider' => $validated['provider'] ?? null,
            'repository_url' => $validated['repository_url'],
            'default_branch' => $validated['default_branch'] ?? 'main',
            'visibility' => $validated['visibility'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('workflow.development.show', $softwareProject)
            ->with('success', 'Repository added.');
    }

    public function storeTask(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('manageDevelopment', $softwareProject);

        $validated = $request->validate([
            'repository_id' => ['nullable', 'uuid', 'exists:repositories,id'],
            'task_code' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'task_type' => ['nullable', 'string', 'max:30'],
            'priority' => ['nullable', 'string', 'max:30'],
            'assigned_to' => ['nullable', 'uuid', 'exists:users,id'],
            'planned_start_date' => ['nullable', 'date'],
            'planned_end_date' => ['nullable', 'date', 'after_or_equal:planned_start_date'],
        ]);

        $softwareProject->developmentTasks()->create([
            'id' => Str::uuid(),
            'repository_id' => $validated['repository_id'] ?? null,
            'task_code' => $validated['task_code'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'task_type' => $validated['task_type'] ?? 'DEVELOPMENT',
            'priority' => $validated['priority'] ?? 'MEDIUM',
            'status' => 'TODO',
            'assigned_to' => $validated['assigned_to'] ?? null,
            'created_by' => $request->user()->id,
            'planned_start_date' => $validated['planned_start_date'] ?? null,
            'planned_end_date' => $validated['planned_end_date'] ?? null,
        ]);

        return redirect()
            ->route('workflow.development.show', $softwareProject)
            ->with('success', 'Task added.');
    }

    public function updateTaskStatus(
        Request $request,
        SoftwareProject $softwareProject,
        DevelopmentTask $developmentTask
    ): RedirectResponse {
        $this->authorize('manageDevelopment', $softwareProject);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['TODO', 'IN_PROGRESS', 'DONE', 'BLOCKED'])],
        ]);

        $attributes = ['status' => $validated['status']];

        if ($validated['status'] === 'IN_PROGRESS' && ! $developmentTask->actual_start_date) {
            $attributes['actual_start_date'] = now()->toDateString();
        }

        if ($validated['status'] === 'DONE' && ! $developmentTask->actual_end_date) {
            $attributes['actual_end_date'] = now()->toDateString();
        }

        $developmentTask->update($attributes);

        return redirect()
            ->route('workflow.development.show', $softwareProject)
            ->with('success', "Task {$developmentTask->task_code} updated.");
    }

    /**
     * The flowchart has no decision diamond for Development — it's an MST
     * judgment call that the working solution is ready for Testing & QA,
     * not an objective completeness check like Requirements & Design's.
     */
    public function advance(
        Request $request,
        SoftwareProject $softwareProject
    ): RedirectResponse {
        $this->authorize('manageDevelopment', $softwareProject);

        $this->statusService->transition(
            $softwareProject,
            ProjectStatus::TestingQa,
            $request->user(),
            $request->input('remarks'),
        );

        return redirect()
            ->route('workflow.development.index')
            ->with('success', "{$softwareProject->project_id} moved to Testing & QA.");
    }
}
