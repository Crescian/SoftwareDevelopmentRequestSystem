<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoftwareProject extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_id',
        'project_name',
        'requesting_department_id',
        'requestor_id',
        'project_sponsor_id',
        'project_manager_id',
        'date_submitted',
        'project_summary',
        'business_problem',
        'proposed_solution',
        'status',
        'priority',
        'target_start_date',
        'target_end_date',
        'submitted_at',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'date_submitted' => 'datetime',
        'target_start_date' => 'date',
        'target_end_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function requestingDepartment(): BelongsTo
    {
        return $this->belongsTo(
            Department::class,
            'requesting_department_id'
        );
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function requestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    public function projectSponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_sponsor_id');
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(ProjectObjective::class);
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(ProjectScope::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(ProjectRequirement::class);
    }

    public function requiredAttachments(): HasMany
    {
        return $this->hasMany(ProjectRequiredAttachment::class);
    }

    public function feasibilityAssessments(): HasMany
    {
        return $this->hasMany(FeasibilityAssessment::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ProjectApproval::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ProjectStatusHistory::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectComment::class);
    }
    public function requirementsDocuments(): HasMany
    {
        return $this->hasMany(RequirementsDocument::class);
    }

    public function systemDesigns(): HasMany
    {
        return $this->hasMany(SystemDesign::class);
    }

    public function repositories(): HasMany
    {
        return $this->hasMany(Repository::class);
    }

    public function developmentTasks(): HasMany
    {
        return $this->hasMany(DevelopmentTask::class);
    }

    public function testPlans(): HasMany
    {
        return $this->hasMany(TestPlan::class);
    }

    public function defects(): HasMany
    {
        return $this->hasMany(Defect::class);
    }

    public function uatSessions(): HasMany
    {
        return $this->hasMany(UatSession::class);
    }
    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }
    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function securityAssessments(): HasMany
    {
        return $this->hasMany(SecurityAssessment::class);
    }

    public function monitoringRecords(): HasMany
    {
        return $this->hasMany(ProjectMonitoringRecord::class);
    }

    public function kpis(): HasMany
    {
        return $this->hasMany(ProjectKpi::class);
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(ChangeRequest::class);
    }
}