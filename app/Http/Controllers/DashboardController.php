<?php

namespace App\Http\Controllers;

use App\Enums\DashboardRole;
use App\Models\Defect;
use App\Models\DevelopmentTask;
use App\Models\ProjectStatusHistory;
use App\Models\RequirementsDocument;
use App\Models\SecurityAssessment;
use App\Models\SoftwareProject;
use App\Models\SystemDesign;
use App\Models\TestCase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Status buckets shown as stat cards on the requestor dashboard.
     * Values match App\Enums\ProjectStatus (MST-SDR-BFC-001).
     */
    public const STATUS_IN_PROGRESS = [
        'SUBMITTED', 'FEASIBILITY_ASSESSMENT', 'PENDING_APPROVAL',
        'REQUIREMENTS_DESIGN', 'DEVELOPMENT', 'TESTING_QA',
        'USER_ACCEPTANCE_TESTING', 'DEPLOYMENT',
    ];
    public const STATUS_FOR_REVIEW = ['SUBMITTED', 'PENDING_APPROVAL'];
    public const STATUS_FOR_CLARIFICATION = ['RETURNED_FOR_REVISION'];
    public const STATUS_COMPLETED = ['MONITORING_MAINTENANCE'];

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $role = $user->effectiveDashboardRole();
        $needsYourDecision = $this->needsYourDecisionData($user);

        return match ($role) {
            DashboardRole::MstReviewer => view($role->dashboardView(), $this->mstReviewerData() + ['needsYourDecision' => $needsYourDecision]),
            DashboardRole::Developer, DashboardRole::ProjectManager, DashboardRole::Qa
                => view($role->dashboardView(), $this->teamData($user, $role) + ['needsYourDecision' => $needsYourDecision]),
            DashboardRole::SystemAdministrator => view($role->dashboardView(), $this->adminData() + ['needsYourDecision' => $needsYourDecision]),
            DashboardRole::Requestor => view($role->dashboardView(), $this->requestorData($user) + ['needsYourDecision' => $needsYourDecision]),
        };
    }

    /**
     * Cross-cutting "needs your decision" panel, shown on every dashboard
     * regardless of dashboard_role. The policy layer gates these abilities
     * by per-project identity (project_sponsor_id / project_manager_id /
     * requestor_id), not by dashboard_role, so a person can hold one of
     * these identities on a project without their dashboard_role matching
     * it — this surfaces those items wherever they actually log in.
     *
     * @return \Illuminate\Support\Collection
     */
    private function needsYourDecisionData(User $user): \Illuminate\Support\Collection
    {
        $sponsorDecisions = SoftwareProject::where('project_sponsor_id', $user->id)
            ->where('status', 'PENDING_APPROVAL')
            ->get()
            ->map(fn (SoftwareProject $project) => [
                'type' => 'Management Approval',
                'project' => $project,
                'title' => $project->project_name,
                'since' => $project->submitted_at,
            ]);

        $uatDecisions = SoftwareProject::where('requestor_id', $user->id)
            ->where('status', 'USER_ACCEPTANCE_TESTING')
            ->get()
            ->map(fn (SoftwareProject $project) => [
                'type' => 'UAT Sign-off',
                'project' => $project,
                'title' => $project->project_name,
                'since' => $project->submitted_at,
            ]);

        return $sponsorDecisions
            ->concat($this->projectManagerPendingDecisions($user->managedProjects()->pluck('id')))
            ->concat($uatDecisions)
            ->sortBy('since')
            ->values();
    }

    /**
     * Requirements & Design items awaiting a PM decision on the given
     * projects — shared by the identity-based cross-cutting panel (any
     * dashboard_role) and the Project Manager's own dashboard section.
     *
     * @param \Illuminate\Support\Collection<int, string> $projectIds
     * @return \Illuminate\Support\Collection
     */
    private function projectManagerPendingDecisions($projectIds): \Illuminate\Support\Collection
    {
        if ($projectIds->isEmpty()) {
            return collect();
        }

        $pendingRequirements = RequirementsDocument::with('softwareProject')
            ->whereIn('software_project_id', $projectIds)
            ->where('status', 'DRAFT')
            ->get()
            ->map(fn ($doc) => ['type' => 'Requirements Document', 'project' => $doc->softwareProject, 'title' => $doc->title, 'since' => $doc->prepared_at]);

        $pendingDesigns = SystemDesign::with('softwareProject')
            ->whereIn('software_project_id', $projectIds)
            ->where('status', 'DRAFT')
            ->get()
            ->map(fn ($doc) => ['type' => 'System Design', 'project' => $doc->softwareProject, 'title' => $doc->title, 'since' => $doc->prepared_at]);

        $pendingSecurity = SecurityAssessment::with('softwareProject')
            ->whereIn('software_project_id', $projectIds)
            ->where('assessment_type', 'DESIGN_REVIEW')
            ->where('status', 'COMPLETED')
            ->get()
            ->map(fn ($doc) => ['type' => 'Security Assessment', 'project' => $doc->softwareProject, 'title' => $doc->assessment_number, 'since' => $doc->assessed_at]);

        return $pendingRequirements->concat($pendingDesigns)->concat($pendingSecurity);
    }

    /**
     * @return array{stats: array<string, int>, recent: \Illuminate\Support\Collection}
     */
    private function requestorData(User $user): array
    {
        $mine = SoftwareProject::where('requestor_id', $user->id);

        $stats = [
            'in_progress' => (clone $mine)->whereIn('status', self::STATUS_IN_PROGRESS)->count(),
            'for_review' => (clone $mine)->whereIn('status', self::STATUS_FOR_REVIEW)->count(),
            'for_clarification' => (clone $mine)->whereIn('status', self::STATUS_FOR_CLARIFICATION)->count(),
            'completed' => (clone $mine)->whereIn('status', self::STATUS_COMPLETED)->count(),
        ];

        $recent = (clone $mine)->latest('created_at')->limit(5)->get();

        return ['stats' => $stats, 'recent' => $recent];
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

            $data['pendingDecisions'] = $this->projectManagerPendingDecisions($projectIds)
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
