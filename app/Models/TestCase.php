<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestCase extends Model
{
    use HasUuids;

    protected $fillable = [
        'test_plan_id',
        'test_case_number',
        'title',
        'description',
        'test_type',
        'preconditions',
        'test_steps',
        'expected_result',
        'actual_result',
        'status',
        'executed_by',
        'executed_at',
        'remarks',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    public function testPlan(): BelongsTo
    {
        return $this->belongsTo(TestPlan::class);
    }

    public function defects(): HasMany
    {
        return $this->hasMany(Defect::class);
    }
}