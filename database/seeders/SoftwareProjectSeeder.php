<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Database\Seeder;

class SoftwareProjectSeeder extends Seeder
{
    public function run(): void
    {
        $ict = Department::where('code', 'ICT')->first();

        // Seeded by UserSeeder, which must run first. Looked up by email,
        // the same key UserSeeder matches on.
        $requestor = User::where('email', 'nmsanchez@leoniogroup.com')->firstOrFail();
        $manager = User::where('email', 'manager@gmail.com')->firstOrFail();

        // Create-only: a re-seed must not reset a project that has since
        // moved through the workflow back to DRAFT.
        SoftwareProject::firstOrCreate(['project_id' => 'PRJ-2026-0001'], [
            'project_name' => 'Digital Solutions Request System',

            'requesting_department_id' => $ict->id,

            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $manager->id,
            'project_manager_id' => $manager->id,

            'date_submitted' => now(),

            'project_summary' =>
                'An internal web-based system for managing digital solution requests and their delivery lifecycle.',

            'business_problem' =>
                'Digital solution requests currently require manual coordination and document tracking, making it difficult to monitor approvals, requirements, development, testing, deployment, and maintenance activities.',

            'proposed_solution' =>
                'Develop a centralized Laravel-based digital solutions request and governance system with workflow tracking, document management, approvals, testing, deployment, and maintenance records.',

            'status' => 'DRAFT',

            'priority' => 'HIGH',

            'target_start_date' => now()->toDateString(),

            'target_end_date' => now()
                ->addMonths(3)
                ->toDateString(),
        ]);
    }
}