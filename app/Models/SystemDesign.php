<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemDesign extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_project_id',
        'document_number',
        'title',
        'version',
        'status',
        'document_date',
        'description',
        'architecture_overview',
        'database_design',
        'integration_design',
        'security_design',
        'access_control_design',
        'file_name',
        'file_path',
        'prepared_by',
        'prepared_at',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'remarks',
    ];

    protected $casts = [
        'document_date' => 'date',
        'prepared_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }
}