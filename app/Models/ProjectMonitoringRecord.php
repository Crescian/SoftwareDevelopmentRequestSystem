<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMonitoringRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_project_id',
        'record_number',
        'monitoring_date',
        'monitoring_type',
        'uptime_percentage',
        'average_response_time_ms',
        'active_users',
        'total_users',
        'incidents_count',
        'user_feedback_count',
        'performance_observations',
        'user_feedback_summary',
        'improvement_actions',
        'recorded_by',
        'recorded_at',
        'remarks',
    ];

    protected $casts = [
        'monitoring_date' => 'date',
        'uptime_percentage' => 'decimal:2',
        'average_response_time_ms' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }
}