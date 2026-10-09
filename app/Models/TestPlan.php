<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestPlan extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_project_id',
        'test_plan_number',
        'title',
        'version',
        'status',
        'objectives',
        'scope',
        'test_strategy',
        'test_environment',
        'planned_start_date',
        'planned_end_date',
        'prepared_by',
        'prepared_at',
        'approved_by',
        'approved_at',
        'remarks',
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'prepared_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }

    public function testCases(): HasMany
    {
        return $this->hasMany(TestCase::class);
    }
}