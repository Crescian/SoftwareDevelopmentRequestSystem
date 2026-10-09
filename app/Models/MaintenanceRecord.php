<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_project_id',
        'maintenance_number',
        'maintenance_type',
        'title',
        'description',
        'reason',
        'priority',
        'status',
        'reported_by',
        'assigned_to',
        'reported_at',
        'started_at',
        'completed_at',
        'resolution',
        'remarks',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }
}