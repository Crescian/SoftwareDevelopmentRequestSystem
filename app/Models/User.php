<?php

namespace App\Models;

use App\Enums\DashboardRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    /**
     * The identity record itself is a mirror of the ticketing system's user
     * (see TicketingIdentityService::syncLocalUser) — never a source of truth,
     * and never holds a local password.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'role_id',
        'role',
        'department_id',
        'department',
        'dashboard_role',
        'active',
        'last_synced_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'last_synced_at' => 'datetime',
            'dashboard_role' => DashboardRole::class,
        ];
    }

    /**
     * Falls back to Requestor when no local override has been set yet.
     */
    public function effectiveDashboardRole(): DashboardRole
    {
        return $this->dashboard_role ?? DashboardRole::Requestor;
    }

    /**
     * The route name for this user's "home" — Requestors have no dashboard,
     * so My Requests is home for them; every other role keeps the Dashboard.
     */
    public function homeRoute(): string
    {
        return $this->effectiveDashboardRole() === DashboardRole::Requestor
            ? 'software-projects.index'
            : 'dashboard';
    }

    public function managedProjects(): HasMany
    {
        return $this->hasMany(SoftwareProject::class, 'project_manager_id');
    }

    public function developmentTasks(): HasMany
    {
        return $this->hasMany(DevelopmentTask::class, 'assigned_to');
    }

    public function defects(): HasMany
    {
        return $this->hasMany(Defect::class, 'assigned_to');
    }
}
