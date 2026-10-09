<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReleaseNote extends Model
{
    use HasUuids;

    protected $fillable = [
        'deployment_id',
        'document_number',
        'title',
        'version',
        'release_summary',
        'new_features',
        'bug_fixes',
        'known_issues',
        'breaking_changes',
        'file_name',
        'file_path',
        'prepared_by',
        'prepared_at',
        'approved_by',
        'approved_at',
        'remarks',
    ];

    protected $casts = [
        'prepared_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }
}