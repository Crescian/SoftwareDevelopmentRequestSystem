<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRequirement extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_project_id',
        'requirement_type',
        'requirement_code',
        'title',
        'description',
        'priority',
        'status',
        'created_by',
    ];

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }
}