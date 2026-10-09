<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UatSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_project_id',
        'uat_number',
        'title',
        'version',
        'status',
        'planned_date',
        'actual_start_date',
        'actual_end_date',
        'scope',
        'environment',
        'summary',
        'facilitated_by',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
    ];

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }

    public function signoffs(): HasMany
    {
        return $this->hasMany(UatSignoff::class);
    }
}