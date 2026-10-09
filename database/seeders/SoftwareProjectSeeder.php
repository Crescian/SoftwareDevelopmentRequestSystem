<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\SoftwareProject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SoftwareProjectSeeder extends Seeder
{
    public function run(): void
    {
        $ict = Department::where('code', 'ICT')->first();

        SoftwareProject::create([
            'id' => Str::uuid(),

            'project_id' => 'PRJ-2026-0001',

            'project_name' => 'Software Development Request System',

            'requesting_department_id' => $ict->id,

            // Temporary UUIDs until LGICT Core integration.
            'requestor_id' => Str::uuid(),
            'project_sponsor_id' => Str::uuid(),
            'project_manager_id' => Str::uuid(),

            'date_submitted' => now(),

            'project_summary' =>
                'An internal web-based system for managing software development requests and the software development lifecycle.',

            'business_problem' =>
                'Software development requests currently require manual coordination and document tracking, making it difficult to monitor approvals, requirements, development, testing, deployment, and maintenance activities.',

            'proposed_solution' =>
                'Develop a centralized Laravel-based software development request and governance system with workflow tracking, document management, approvals, testing, deployment, and maintenance records.',

            'status' => 'DRAFT',

            'priority' => 'HIGH',

            'target_start_date' => now()->toDateString(),

            'target_end_date' => now()
                ->addMonths(3)
                ->toDateString(),
        ]);
    }
}