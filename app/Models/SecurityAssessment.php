<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAssessment extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_project_id',
        'assessment_number',
        'assessment_type',
        'status',
        'authentication_review',
        'authorization_review',
        'data_privacy_review',
        'vulnerability_assessment',
        'security_risks',
        'mitigation_actions',
        'overall_result',
        'assessed_by',
        'assessed_at',
        'approved_by',
        'approved_at',
        'remarks',
    ];

    protected $casts = [
        'assessed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }
}