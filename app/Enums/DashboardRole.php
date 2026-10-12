<?php

namespace App\Enums;

/**
 * Which dashboard a user lands on. Local override (users.dashboard_role) —
 * independent of the `role`/`department` strings mirrored from the
 * ticketing system, since those have no fixed value set.
 */
enum DashboardRole: string
{
    case Requestor = 'requestor';
    case MstReviewer = 'mst_reviewer';
    case Developer = 'developer';
    case ProjectManager = 'project_manager';
    case Qa = 'qa';
    case SystemAdministrator = 'system_administrator';

    public function label(): string
    {
        return match ($this) {
            self::Requestor => 'Requestor',
            self::MstReviewer => 'MST Reviewer',
            self::Developer => 'Developer',
            self::ProjectManager => 'Project Manager',
            self::Qa => 'QA',
            self::SystemAdministrator => 'System Administrator',
        };
    }

    /**
     * The view for each role's dashboard. Requestor has no dashboard —
     * DashboardController redirects that role to "My Requests" before this
     * is ever reached.
     */
    public function dashboardView(): string
    {
        return match ($this) {
            self::MstReviewer => 'dashboard.mst-reviewer',
            self::Developer, self::ProjectManager, self::Qa => 'dashboard.team',
            self::SystemAdministrator => 'dashboard.admin',
            self::Requestor => throw new \LogicException('Requestor has no dashboard view; DashboardController redirects before calling this.'),
        };
    }
}
