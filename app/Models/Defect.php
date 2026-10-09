<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Defect extends Model
{
    use HasUuids;

    protected $fillable = [
        'test_case_id',
        'software_project_id',
        'defect_number',
        'title',
        'description',
        'severity',
        'priority',
        'status',
        'reported_by',
        'reported_at',
        'assigned_to',
        'resolved_at',
        'resolution',
        'verified_by',
        'verified_at',
        'remarks',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function testCase(): BelongsTo
    {
        return $this->belongsTo(TestCase::class);
    }

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}