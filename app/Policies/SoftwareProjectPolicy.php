<?php

namespace App\Policies;

use App\Models\SoftwareProject;
use App\Models\User;

class SoftwareProjectPolicy
{
    /**
     * The Initial Review queue (and future MST-only review queues) is
     * restricted to Management Systems and Technology.
     */
    public function viewAny(User $user): bool
    {
        return $this->isMst($user);
    }

    public function view(User $user, SoftwareProject $project): bool
    {
        return $user->id === $project->requestor_id
            || $user->id === $project->project_sponsor_id
            || $user->id === $project->project_manager_id
            || $this->isMst($user);
    }

    /**
     * Only the requestor may submit their own draft, and only while it's
     * in a state that's actually theirs to submit (DRAFT or sent back for
     * revision).
     */
    public function submit(User $user, SoftwareProject $project): bool
    {
        return $user->id === $project->requestor_id
            && in_array($project->status, ['DRAFT', 'RETURNED_FOR_REVISION'], true);
    }

    /**
     * Initial Review (step 04) is Management Systems and Technology's
     * responsibility per MST-PMO-SDR-BW-001 section 1.
     */
    public function conductInitialReview(User $user, SoftwareProject $project): bool
    {
        return $this->isMst($user) && $project->status === 'SUBMITTED';
    }

    /**
     * Feasibility Assessment (step 05) is also Management Systems and
     * Technology's responsibility per MST-PMO-SDR-BW-001 section 1.
     */
    public function conductFeasibilityAssessment(User $user, SoftwareProject $project): bool
    {
        return $this->isMst($user) && $project->status === 'FEASIBILITY_ASSESSMENT';
    }

    /**
     * Management Approval (step 06) is decided by the project's own
     * sponsor, not by role — confirmed: "named per-request approver",
     * not a general role-based approver list.
     */
    public function decideManagementApproval(User $user, SoftwareProject $project): bool
    {
        return $user->id === $project->project_sponsor_id
            && $project->status === 'PENDING_APPROVAL';
    }

    /**
     * Step 07 drafting (Requirements Document / System Design) is done by
     * Management Systems and Technology as the implementing team.
     */
    public function manageRequirementsDesign(User $user, SoftwareProject $project): bool
    {
        return $this->isMst($user) && $project->status === 'REQUIREMENTS_DESIGN';
    }

    /**
     * Review/approval of Requirements & Design, and unlocking Development,
     * belongs to the project's own Project Manager (confirmed) — matching
     * their stated responsibility to coordinate progress.
     */
    public function decideRequirementsDesign(User $user, SoftwareProject $project): bool
    {
        return $project->project_manager_id !== null
            && $user->id === $project->project_manager_id
            && $project->status === 'REQUIREMENTS_DESIGN';
    }

    /**
     * Step 08 (Development) is Management Systems and Technology's
     * responsibility per MST-PMO-SDR-BW-001 section 1. The flowchart has
     * no decision diamond here, so the same ability covers managing
     * repositories/tasks and advancing the project to Testing & QA.
     */
    public function manageDevelopment(User $user, SoftwareProject $project): bool
    {
        return $this->isMst($user) && $project->status === 'DEVELOPMENT';
    }

    /**
     * Step 09 (Testing & QA) is Management Systems and Technology / QA's
     * responsibility. Unlike Development, the flowchart does have a real
     * decision diamond here ("Passed testing?"), enforced in the
     * controller rather than the policy (it depends on open defects, not
     * just status/department).
     */
    public function manageTestingQa(User $user, SoftwareProject $project): bool
    {
        return $this->isMst($user) && $project->status === 'TESTING_QA';
    }

    /**
     * Step 10: Management Systems and Technology facilitates the UAT
     * session (schedules it, sets scope/environment) per uat_sessions'
     * facilitated_by column.
     */
    public function manageUat(User $user, SoftwareProject $project): bool
    {
        return $this->isMst($user) && $project->status === 'USER_ACCEPTANCE_TESTING';
    }

    /**
     * The formal accept/reject decision belongs to the requestor — the
     * flowchart's "Requestor / Users" responsible party, narrowed to the
     * one named-per-request role already on the project (same precedent
     * as Management Approval's sponsor and Requirements & Design's PM).
     */
    public function decideUat(User $user, SoftwareProject $project): bool
    {
        return $user->id === $project->requestor_id
            && $project->status === 'USER_ACCEPTANCE_TESTING';
    }

    /**
     * Step 11 (Deployment) is Management Systems and Technology's
     * responsibility throughout — preparing, deploying, and approving the
     * release are all technical/execution roles, not named per-request
     * business roles like the sponsor/PM/requestor in earlier gates.
     */
    public function manageDeployment(User $user, SoftwareProject $project): bool
    {
        return $this->isMst($user) && $project->status === 'DEPLOYMENT';
    }

    /**
     * Step 12 (Monitoring & Maintenance) is ongoing/terminal — no further
     * status transition. Recording monitoring data and KPIs is MST's job.
     */
    public function manageMonitoring(User $user, SoftwareProject $project): bool
    {
        return $this->isMst($user) && $project->status === 'MONITORING_MAINTENANCE';
    }

    /**
     * Reporting a maintenance need is open to anyone who can already see
     * the project (requestor, sponsor, PM, or MST) — matching the
     * flowchart's "Management Systems and Technology / Users" responsible
     * party for this step.
     */
    public function reportMaintenance(User $user, SoftwareProject $project): bool
    {
        return $this->view($user, $project) && $project->status === 'MONITORING_MAINTENANCE';
    }

    /**
     * Change Management: independent of the main lifecycle (confirmed —
     * it never moves software_projects.status), usable once a project is
     * in ongoing operation. Anyone who can already see the project may
     * propose a change; MST manages implementation/closure.
     */
    public function proposeChangeRequest(User $user, SoftwareProject $project): bool
    {
        return $this->view($user, $project) && $project->status === 'MONITORING_MAINTENANCE';
    }

    public function manageChangeRequest(User $user, SoftwareProject $project): bool
    {
        return $this->isMst($user) && $project->status === 'MONITORING_MAINTENANCE';
    }

    /**
     * Comments and attached documents are general-purpose and usable at
     * any stage of the request — anyone who can already see the project
     * may add one. Reviewing a document (marking it reviewed/approved)
     * is MST's call, but isn't a blocking gate for anything.
     */
    public function collaborate(User $user, SoftwareProject $project): bool
    {
        return $this->view($user, $project);
    }

    public function reviewDocument(User $user, SoftwareProject $project): bool
    {
        return $this->isMst($user);
    }

    private function isMst(User $user): bool
    {
        return $user->department === config('workflow.mst_department');
    }
}
