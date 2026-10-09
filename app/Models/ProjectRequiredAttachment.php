<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRequiredAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_project_id',
        'attachment_type',
        'is_required',
        'attachment_status',
        'file_name',
        'file_path',
        'document_title',
        'document_version',
        'document_date',
        'uploaded_by',
        'uploaded_at',
        'checked_by',
        'checked_at',
        'requester_signed_off',
        'signed_off_by',
        'signed_off_at',
        'remarks',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'document_date' => 'date',
        'uploaded_at' => 'datetime',
        'checked_at' => 'datetime',
        'requester_signed_off' => 'boolean',
        'signed_off_at' => 'datetime',
    ];

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }
}