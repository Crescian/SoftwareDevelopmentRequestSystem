<?php

namespace App\Services;

use App\Models\SoftwareProject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SoftwareProjectService
{
    public function createProject(array $data): SoftwareProject
    {
        return DB::transaction(function () use ($data) {

            $project = SoftwareProject::create([
                'id' => Str::uuid(),

                'project_id' => $data['project_id'],
                'project_name' => $data['project_name'],

                'requesting_department_id' =>
                    $data['requesting_department_id'],

                'requestor_id' =>
                    $data['requestor_id'],

                'project_sponsor_id' =>
                    $data['project_sponsor_id'],

                'project_manager_id' =>
                    $data['project_manager_id'] ?? null,

                'date_submitted' =>
                    $data['date_submitted'] ?? null,

                'project_summary' =>
                    $data['project_summary'],

                'business_problem' =>
                    $data['business_problem'],

                'proposed_solution' =>
                    $data['proposed_solution'],

                'status' => 'DRAFT',

                'priority' =>
                    $data['priority'] ?? 'MEDIUM',

                'target_start_date' =>
                    $data['target_start_date'] ?? null,

                'target_end_date' =>
                    $data['target_end_date'] ?? null,
            ]);

            /*
             * Objectives
             */
            foreach ($data['objectives'] ?? [] as $index => $objective) {

                $project->objectives()->create([
                    'id' => Str::uuid(),
                    'objective_no' => $index + 1,
                    'objective' => $objective,
                ]);
            }

            /*
             * Scope
             */
            foreach ($data['in_scope'] ?? [] as $index => $scope) {

                $project->scopes()->create([
                    'id' => Str::uuid(),
                    'scope_type' => 'IN_SCOPE',
                    'description' => $scope,
                    'sort_order' => $index + 1,
                ]);
            }

            foreach ($data['out_of_scope'] ?? [] as $index => $scope) {

                $project->scopes()->create([
                    'id' => Str::uuid(),
                    'scope_type' => 'OUT_OF_SCOPE',
                    'description' => $scope,
                    'sort_order' => $index + 1,
                ]);
            }

            /*
             * Requirements
             */
            foreach ($data['requirements'] ?? [] as $requirement) {

                $project->requirements()->create([
                    'id' => Str::uuid(),

                    'requirement_type' =>
                        $requirement['requirement_type'],

                    'requirement_code' =>
                        $requirement['requirement_code'],

                    'title' =>
                        $requirement['title'],

                    'description' =>
                        $requirement['description'],

                    'priority' =>
                        $requirement['priority'] ?? null,

                    'status' => 'DRAFT',

                    'created_by' =>
                        $data['requestor_id'],
                ]);
            }

            /*
             * Required Attachments
             */
            foreach ($data['required_attachments'] ?? [] as $attachment) {

                $project->requiredAttachments()->create([
                    'id' => Str::uuid(),

                    'attachment_type' =>
                        $attachment['attachment_type'],

                    'is_required' =>
                        $attachment['is_required'] ?? true,

                    'attachment_status' =>
                        $attachment['attachment_status'] ?? 'NOT_ATTACHED',

                    'file_name' =>
                        $attachment['file_name'] ?? null,

                    'file_path' =>
                        $attachment['file_path'] ?? null,

                    'document_title' =>
                        $attachment['document_title'] ?? null,

                    'document_version' =>
                        $attachment['document_version'] ?? null,

                    'document_date' =>
                        $attachment['document_date'] ?? null,

                    'uploaded_by' =>
                        $attachment['uploaded_by'] ?? null,

                    'uploaded_at' =>
                        $attachment['uploaded_at'] ?? null,

                    'requester_signed_off' =>
                        $attachment['requester_signed_off'] ?? false,

                    'signed_off_by' =>
                        $attachment['signed_off_by'] ?? null,

                    'signed_off_at' =>
                        $attachment['signed_off_at'] ?? null,

                    'remarks' =>
                        $attachment['remarks'] ?? null,
                ]);
            }

            return $project->load([
                'requestingDepartment',
                'objectives',
                'scopes',
                'requirements',
                'requiredAttachments',
            ]);
        });
    }
}