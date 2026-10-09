<?php

namespace App\Enums;

/**
 * Mirrors the 12-step process in MST-SDR-BFC-001 / MST-PMO-SDR-BW-001.
 * Values match software_projects.status exactly (varchar, no DB enum).
 */
enum ProjectStatus: string
{
    case Draft = 'DRAFT';
    case Submitted = 'SUBMITTED';
    case ReturnedForRevision = 'RETURNED_FOR_REVISION';
    case FeasibilityAssessment = 'FEASIBILITY_ASSESSMENT';
    case PendingApproval = 'PENDING_APPROVAL';
    case Rejected = 'REJECTED';
    case Closed = 'CLOSED';
    case RequirementsDesign = 'REQUIREMENTS_DESIGN';
    case Development = 'DEVELOPMENT';
    case TestingQa = 'TESTING_QA';
    case UserAcceptanceTesting = 'USER_ACCEPTANCE_TESTING';
    case Deployment = 'DEPLOYMENT';
    case MonitoringMaintenance = 'MONITORING_MAINTENANCE';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::ReturnedForRevision => 'Returned for Revision',
            self::FeasibilityAssessment => 'Feasibility Assessment',
            self::PendingApproval => 'Pending Management Approval',
            self::Rejected => 'Rejected',
            self::Closed => 'Closed',
            self::RequirementsDesign => 'Requirements & Design',
            self::Development => 'Development',
            self::TestingQa => 'Testing & QA',
            self::UserAcceptanceTesting => 'User Acceptance Testing',
            self::Deployment => 'Deployment',
            self::MonitoringMaintenance => 'Monitoring & Maintenance',
        };
    }

    /**
     * Legal next statuses from this one. Phase 1 only wires Draft through
     * Feasibility Assessment; later stages are named here so the audit
     * trail vocabulary is fixed up front, but have no controller yet.
     *
     * @return list<self>
     */
    public function allowedNextStatuses(): array
    {
        return match ($this) {
            self::Draft => [self::Submitted],
            self::Submitted => [self::FeasibilityAssessment, self::ReturnedForRevision],
            self::ReturnedForRevision => [self::Submitted],
            self::FeasibilityAssessment => [self::PendingApproval, self::ReturnedForRevision, self::Closed],
            self::PendingApproval => [self::RequirementsDesign, self::ReturnedForRevision, self::Rejected],
            self::RequirementsDesign => [self::Development],
            self::Development => [self::TestingQa],
            self::TestingQa => [self::UserAcceptanceTesting],
            self::UserAcceptanceTesting => [self::Deployment],
            self::Deployment => [self::MonitoringMaintenance],
            self::MonitoringMaintenance, self::Rejected, self::Closed => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedNextStatuses(), true);
    }

    /**
     * Route name of the stage-specific action page for this status (where
     * the actual work — drafting, deciding, executing — happens), or null
     * for statuses with no stage page (Draft, ReturnedForRevision,
     * Rejected, Closed), which fall back to software-projects.show.
     */
    public function actionRoute(): ?string
    {
        return match ($this) {
            self::Submitted => 'reviews.initial-review.show',
            self::FeasibilityAssessment => 'reviews.feasibility-assessment.show',
            self::PendingApproval => 'approvals.management.show',
            self::RequirementsDesign => 'workflow.requirements-design.show',
            self::Development => 'workflow.development.show',
            self::TestingQa => 'workflow.testing-qa.show',
            self::UserAcceptanceTesting => 'workflow.uat.show',
            self::Deployment => 'workflow.deployment.show',
            self::MonitoringMaintenance => 'workflow.monitoring.show',
            self::Draft, self::ReturnedForRevision, self::Rejected, self::Closed => null,
        };
    }
}
