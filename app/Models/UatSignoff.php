<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UatSignoff extends Model
{
    use HasUuids;

    protected $fillable = [
        'uat_session_id',
        'signatory_id',
        'signatory_name',
        'signatory_role',
        'decision',
        'remarks',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function uatSession(): BelongsTo
    {
        return $this->belongsTo(UatSession::class);
    }
}