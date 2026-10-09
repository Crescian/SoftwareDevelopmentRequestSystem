<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * The single place that moves a software_projects.status forward per
 * MST-SDR-BFC-001 / MST-PMO-SDR-BW-001, and records every change to
 * project_status_histories for the required audit trail.
 */
class ProjectStatusService
{
    public function transition(
        SoftwareProject $project,
        ProjectStatus $to,
        User $actor,
        ?string $remarks = null,
    ): SoftwareProject {
        $from = ProjectStatus::from($project->status);

        if (! $from->canTransitionTo($to)) {
            throw new InvalidArgumentException(
                "Cannot move a project from {$from->value} to {$to->value}.",
            );
        }

        return DB::transaction(function () use ($project, $from, $to, $actor, $remarks) {
            $project->status = $to->value;

            if ($to === ProjectStatus::Submitted && ! $project->submitted_at) {
                $project->submitted_at = now();
            }

            $project->save();

            $project->statusHistories()->create([
                'id' => Str::uuid(),
                'from_status' => $from->value,
                'to_status' => $to->value,
                'changed_by' => $actor->id,
                'remarks' => $remarks,
                'changed_at' => now(),
            ]);

            return $project;
        });
    }
}
