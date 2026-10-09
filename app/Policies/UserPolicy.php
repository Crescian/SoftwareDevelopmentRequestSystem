<?php

namespace App\Policies;

use App\Enums\DashboardRole;
use App\Models\User;

class UserPolicy
{
    /**
     * Managing dashboard roles is System Administrator's job — it's the
     * role whose whole purpose is system-wide oversight, matching how the
     * rest of the dashboards are already split by responsibility.
     */
    public function viewAny(User $user): bool
    {
        return $user->dashboard_role === DashboardRole::SystemAdministrator;
    }

    public function update(User $user, User $target): bool
    {
        return $user->dashboard_role === DashboardRole::SystemAdministrator;
    }
}
