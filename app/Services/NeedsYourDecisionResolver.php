<?php

namespace App\Services;

use App\Models\RequirementsDocument;
use App\Models\SecurityAssessment;
use App\Models\SoftwareProject;
use App\Models\SystemDesign;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Cross-cutting "needs your decision" panel, shown wherever a user lands
 * regardless of dashboard_role. The policy layer gates these abilities by
 * per-project identity (project_sponsor_id / project_manager_id /
 * requestor_id), not by dashboard_role, so a person can hold one of these
 * identities on a project without their dashboard_role matching it — this
 * surfaces those items wherever they actually log in.
 */
class NeedsYourDecisionResolver
{
    public function resolve(User $user): Collection
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
     */
    public function projectManagerPendingDecisions(Collection $projectIds): Collection
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
}
