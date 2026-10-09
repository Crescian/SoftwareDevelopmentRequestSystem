<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\SoftwareProject;
use App\Models\User;

/**
 * Resolves "who should act on this project right now" per
 * MST-SDR-BFC-001 / MST-PMO-SDR-BW-001 — department-wide stages resolve
 * to a generic MST Team label, identity-gated stages resolve to the
 * named person who actually holds the decision at that status.
 */
class ProjectOwnerResolver
{
    public function resolve(SoftwareProject $project): ProjectOwner
    {
        $status = ProjectStatus::from($project->status);

        return match ($status) {
            ProjectStatus::Draft,
            ProjectStatus::ReturnedForRevision => $this->person('Requestor', $project->requestor),

            ProjectStatus::PendingApproval => $this->person('Sponsor', $project->projectSponsor),

            // Drafting within this status is MST department-wide, but the
            // gate that actually advances the project is the named PM's
            // decision (decideRequirementsDesign) — resolver reflects the
            // gate-holder, not every sub-task actor.
            ProjectStatus::RequirementsDesign => $this->person('Project Manager', $project->projectManager),

            // MST facilitates, but the real accept/reject gate is the
            // named requestor (decideUat).
            ProjectStatus::UserAcceptanceTesting => $this->person('Requestor', $project->requestor),

            ProjectStatus::Submitted,
            ProjectStatus::FeasibilityAssessment,
            ProjectStatus::Development,
            ProjectStatus::TestingQa,
            ProjectStatus::Deployment,
            ProjectStatus::MonitoringMaintenance => new ProjectOwner(
                label: 'MST Team — ' . $status->label(),
                isDepartmentWide: true,
            ),

            ProjectStatus::Rejected,
            ProjectStatus::Closed => new ProjectOwner(
                label: 'No action required — ' . $status->label(),
                isTerminal: true,
            ),
        };
    }

    private function person(string $roleLabel, ?User $user): ProjectOwner
    {
        return new ProjectOwner(
            label: $user ? "{$roleLabel}: {$user->name}" : "{$roleLabel}: Not yet assigned",
            user: $user,
        );
    }
}
