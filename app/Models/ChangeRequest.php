<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChangeRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_project_id',
        'change_request_number',
        'title',
        'description',
        'reason',
        'business_justification',
        'technical_impact',
        'operational_impact',
        'security_impact',
        'data_impact',
        'cost_impact',
        'schedule_impact',
        'risk_assessment',
        'implementation_plan',
        'rollback_plan',
        'priority',
        'status',
        'requested_by',
        'requested_at',
        'planned_implementation_date',
        'implemented_at',
        'closed_at',
        'remarks',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'planned_implementation_date' => 'date',
        'implemented_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ChangeApproval::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ChangeRequestHistory::class);
    }
}