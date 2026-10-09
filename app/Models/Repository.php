<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Repository extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_project_id',
        'name',
        'provider',
        'repository_url',
        'default_branch',
        'visibility',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }

    public function developmentTasks(): HasMany
    {
        return $this->hasMany(DevelopmentTask::class);
    }
}