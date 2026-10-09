<?php

namespace App\Policies;

use App\Models\ChangeApproval;
use App\Models\User;

class ChangeApprovalPolicy
{
    /**
     * Only the named approver for this exact step can decide it, and only
     * once it's actually their turn — earlier steps in the sequence must
     * already be approved, and the change request itself must still be
     * open for decisions.
     */
    public function decide(User $user, ChangeApproval $approval): bool
    {
        if ($user->id !== $approval->approver_id || $approval->status !== 'PENDING') {
            return false;
        }

        if ($approval->changeRequest->status !== 'SUBMITTED') {
            return false;
        }

        $earliestPendingSequence = $approval->changeRequest->approvals()
            ->where('status', 'PENDING')
            ->min('approval_sequence');

        return (int) $approval->approval_sequence === (int) $earliestPendingSequence;
    }
}
