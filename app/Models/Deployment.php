<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deployment extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_project_id',
        'deployment_number',
        'version',
        'environment',
        'deployment_type',
        'status',
        'release_summary',
        'changes_summary',
        'rollback_plan',
        'planned_date',
        'started_at',
        'completed_at',
        'prepared_by',
        'deployed_by',
        'approved_by',
        'approved_at',
        'remarks',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(DeploymentChecklist::class);
    }

    public function releaseNotes(): HasMany
    {
        return $this->hasMany(ReleaseNote::class);
    }
}