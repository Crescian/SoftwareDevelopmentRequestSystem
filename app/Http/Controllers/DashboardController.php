<?php

namespace App\Http\Controllers;

use App\Enums\DashboardRole;
use App\Models\Defect;
use App\Models\DevelopmentTask;
use App\Models\ProjectStatusHistory;
use App\Models\SoftwareProject;
use App\Models\TestCase;
use App\Models\User;
use App\Services\NeedsYourDecisionResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Status buckets shown as stat cards on the admin dashboard and the
     * Requestor's "My Requests" home. Values match App\Enums\ProjectStatus
     * (MST-SDR-BFC-001).
     */
    public const STATUS_IN_PROGRESS = [
        'SUBMITTED', 'FEASIBILITY_ASSESSMENT', 'PENDING_APPROVAL',
        'REQUIREMENTS_DESIGN', 'DEVELOPMENT', 'TESTING_QA',
        'USER_ACCEPTANCE_TESTING', 'DEPLOYMENT',
    ];
    public const STATUS_FOR_REVIEW = ['SUBMITTED', 'PENDING_APPROVAL'];
    public const STATUS_FOR_CLARIFICATION = ['RETURNED_FOR_REVISION'];
    public const STATUS_COMPLETED = ['MONITORING_MAINTENANCE'];

    public function __construct(
        private readonly NeedsYourDecisionResolver $needsYourDecisionResolver,
    ) {
    }

    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $role = $user->effectiveDashboardRole();

        if ($role === DashboardRole::Requestor) {
            return redirect()->route('software-projects.index');
        }

        $needsYourDecision = $this->needsYourDecisionResolver->resolve($user);

        return match ($role) {
            DashboardRole::MstReviewer => view($role->dashboardView(), $this->mstReviewerData() + ['needsYourDecision' => $needsYourDecision]),
            DashboardRole::Developer, DashboardRole::ProjectManager, DashboardRole::Qa
                => view($role->dashboardView(), $this->teamData($user, $role) + ['needsYourDecision' => $needsYourDecision]),
            DashboardRole::SystemAdministrator => view($role->dashboardView(), $this->adminData() + ['needsYourDecision' => $needsYourDecision]),
        };
    }

    /**
     * MST's own workflow-queue counts, mirroring each queue controller's
     * `where('status', ...)` filter exactly (InitialReviewController,
     * FeasibilityAssessmentController, RequirementsDesignController, etc.).
     *
     * @return array{queues: array<string, array{count: int, route: string}>, recentActivity: \Illuminate\Support\Collection}
     */
    private function mstReviewerData(): array
    {
        $queues = [
            'Initial Review' => ['status' => 'SUBMITTED', 'route' => 'reviews.initial-review.index'],
            'Feasibility Assessment' => ['status' => 'FEASIBILITY_ASSESSMENT', 'route' => 'reviews.feasibility-assessment.index'],
            'Requirements & Design' => ['status' => 'REQUIREMENTS_DESIGN', 'route' => 'workflow.requirements-design.index'],
            'Development' => ['status' => 'DEVELOPMENT', 'route' => 'workflow.development.index'],
            'Testing & QA' => ['status' => 'TESTING_QA', 'route' => 'workflow.testing-qa.index'],
            'User Acceptance Testing' => ['status' => 'USER_ACCEPTANCE_TESTING', 'route' => 'workflow.uat.index'],
            'Deployment' => ['status' => 'DEPLOYMENT', 'route' => 'workflow.deployment.index'],
            'Monitoring & Maintenance' => ['status' => 'MONITORING_MAINTENANCE', 'route' => 'workflow.monitoring.index'],
        ];

        $queues = collect($queues)->map(function (array $queue) {
            $queue['count'] = SoftwareProject::where('status', $queue['status'])->count();

            return $queue;
        })->all();

        $unassigned = [
            'development_tasks' => DevelopmentTask::whereNull('assigned_to')
                ->whereIn('status', ['TODO', 'IN_PROGRESS', 'BLOCKED'])
                ->count(),
            'defects' => Defect::whereNull('assigned_to')
                ->whereIn('status', ['OPEN', 'IN_PROGRESS', 'REOPENED'])
                ->count(),
        ];

        $recentActivity = ProjectStatusHistory::with(['softwareProject', 'changedBy'])
            ->latest('changed_at')
            ->limit(8)
            ->get();

        return ['queues' => $queues, 'unassigned' => $unassigned, 'recentActivity' => $recentActivity];
    }

    /**
     * Shared Developer / Project Manager / QA dashboard — the widgets shown
     * depend on which of the three the user's dashboard_role is.
     *
     * @return array<string, mixed>
     */
    private function teamData(User $user, DashboardRole $role): array
    {
        $data = ['dashboardRole' => $role];

        if ($role === DashboardRole::ProjectManager) {
            $projectIds = $user->managedProjects()->pluck('id');

            $data['pendingDecisions'] = $this->needsYourDecisionResolver->projectManagerPendingDecisions($projectIds)
                ->sortBy('since')
                ->values();

            $data['managedProjectCount'] = $projectIds->count();
        }

        if ($role === DashboardRole::Developer) {
            $tasks = $user->developmentTasks()->with('softwareProject');

            $data['taskStats'] = [
                'todo' => (clone $tasks)->where('status', 'TODO')->count(),
                'in_progress' => (clone $tasks)->where('status', 'IN_PROGRESS')->count(),
                'blocked' => (clone $tasks)->where('status', 'BLOCKED')->count(),
                'done' => (clone $tasks)->where('status', 'DONE')->count(),
            ];

            $data['openTasks'] = (clone $tasks)
                ->whereIn('status', ['TODO', 'IN_PROGRESS', 'BLOCKED'])
                ->orderBy('planned_end_date')
                ->limit(8)
                ->get();
        }

        if ($role === DashboardRole::Qa) {
            $defects = $user->defects()->with('softwareProject');

            $data['defectStats'] = [
                'open' => (clone $defects)->where('status', 'OPEN')->count(),
                'in_progress' => (clone $defects)->where('status', 'IN_PROGRESS')->count(),
                'reopened' => (clone $defects)->where('status', 'REOPENED')->count(),
                'resolved' => (clone $defects)->where('status', 'RESOLVED')->count(),
            ];

            $data['openDefects'] = (clone $defects)
                ->whereIn('status', ['OPEN', 'IN_PROGRESS', 'REOPENED'])
                ->orderBy('severity')
                ->limit(8)
                ->get();

            $data['pendingTestCases'] = TestCase::where('status', 'NOT_EXECUTED')
                ->whereHas('testPlan.softwareProject', fn ($query) => $query->where('status', 'TESTING_QA'))
                ->count();
        }

        return $data;
    }

    /**
     * System-wide, read-only overview: no user/department management here
     * yet — just visibility across every project and department.
     *
     * @return array<string, mixed>
     */
    private function adminData(): array
    {
        $stats = [
            'in_progress' => SoftwareProject::whereIn('status', self::STATUS_IN_PROGRESS)->count(),
            'for_review' => SoftwareProject::whereIn('status', self::STATUS_FOR_REVIEW)->count(),
            'for_clarification' => SoftwareProject::whereIn('status', self::STATUS_FOR_CLARIFICATION)->count(),
            'completed' => SoftwareProject::whereIn('status', self::STATUS_COMPLETED)->count(),
        ];

        $departmentBreakdown = SoftwareProject::query()
            ->join('departments', 'departments.id', '=', 'software_projects.requesting_department_id')
            ->selectRaw('departments.name as department, count(*) as total')
            ->groupBy('departments.name')
            ->orderByDesc('total')
            ->get();

        $recentActivity = ProjectStatusHistory::with(['softwareProject', 'changedBy'])
            ->latest('changed_at')
            ->limit(10)
            ->get();

        return [
            'stats' => $stats,
            'totalProjects' => SoftwareProject::count(),
            'totalUsers' => User::where('active', true)->count(),
            'departmentBreakdown' => $departmentBreakdown,
            'recentActivity' => $recentActivity,
        ];
    }
}
