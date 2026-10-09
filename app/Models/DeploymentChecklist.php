<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentChecklist extends Model
{
    use HasUuids;

    protected $fillable = [
        'deployment_id',
        'item_no',
        'category',
        'item',
        'is_required',
        'status',
        'remarks',
        'checked_by',
        'checked_at',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }
}