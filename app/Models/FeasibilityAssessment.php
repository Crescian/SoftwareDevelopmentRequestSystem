<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeasibilityAssessment extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_project_id',
        'assessed_by',
        'technical_feasibility',
        'operational_feasibility',
        'cost_benefit_analysis',
        'overall_recommendation',
        'assessment_notes',
        'assessed_at',
    ];

    protected $casts = [
        'assessed_at' => 'datetime',
    ];

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }
}