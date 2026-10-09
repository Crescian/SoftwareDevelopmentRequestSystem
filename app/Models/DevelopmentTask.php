<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevelopmentTask extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_project_id',
        'repository_id',
        'task_code',
        'title',
        'description',
        'task_type',
        'priority',
        'status',
        'assigned_to',
        'created_by',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'remarks',
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
    ];

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }

    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}