<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectKpi extends Model
{
    use HasUuids;

    protected $table = 'project_kpis';

    protected $fillable = [
        'software_project_id',
        'kpi_code',
        'kpi_name',
        'measurement_period',
        'target_value',
        'actual_value',
        'unit',
        'status',
        'calculation_method',
        'remarks',
        'recorded_by',
        'recorded_at',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function softwareProject(): BelongsTo
    {
        return $this->belongsTo(SoftwareProject::class);
    }
}