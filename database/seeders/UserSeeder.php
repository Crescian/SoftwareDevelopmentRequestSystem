<?php

namespace Database\Seeders;

use App\Enums\DashboardRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Mirrors of the ticketing system's identities (see
 * TicketingIdentityService::syncLocalUser), with their real ticketing IDs.
 * Only `dashboard_role` is local — login never overwrites it. Runs in
 * every environment, production included.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $mst = config('workflow.mst_department');

        $users = [
            [
                'id' => '019f83e2-a1cc-7001-b7a5-8156cdfb5592',
                'name' => 'Crescian Lloyd Lanoy',
                'email' => 'cmlanoy@leoniogroup.com',
                'role_id' => 'a7d3c2e1-5b4f-4e8a-9c6d-2f1e0b9a8d7c',
                'role' => 'Developer',
                'department_id' => '019f88f8-0725-709b-b13c-142da8fbab37',
                'department' => $mst,
                'dashboard_role' => DashboardRole::SystemAdministrator,
            ],
            [
                'id' => 'cde9af14-1b9a-4929-85c7-df6b0fe587f7',
                'name' => 'Jev Galindez',
                'email' => 'jrgalindez@leoniogroup.com',
                'role_id' => '539bad4b-2ad1-40fb-b1af-2bfc10cc8865',
                'role' => 'IT Admin',
                'department_id' => '019f88f8-0725-709b-b13c-142da8fbab37',
                'department' => $mst,
                'dashboard_role' => DashboardRole::MstReviewer,
            ],
            [
                'id' => '537cfdf4-7e8d-48f1-9d58-b7544b662450',
                'name' => 'Manager',
                'email' => 'manager@gmail.com',
                'role_id' => 'bc8d510f-171b-447f-a674-0f4e8d251a6f',
                'role' => 'Manager',
                'department_id' => '019f88f8-0725-709b-b13c-142da8fbab37',
                'department' => $mst,
                'dashboard_role' => DashboardRole::ProjectManager,
            ],
            [
                'id' => '0fc1e880-6cd1-424f-a363-b3470e3d17cf',
                'name' => 'Ruby Cabuhat',
                'email' => 'rscabuhat@leoniogroup.com',
                'role_id' => 'b4f15bf4-04aa-44ea-b003-f1ac3b8f736e',
                'role' => 'Employee',
                'department_id' => '019fa7b7-9407-7006-a223-87ad175cabd1',
                'department' => 'No Assigned Department Name',
                'dashboard_role' => DashboardRole::Developer,
            ],
            [
                'id' => 'f1bf2e50-8b28-42f6-9390-416b39649c18',
                'name' => 'Noe Cagomoc',
                'email' => 'npcagomoc@leoniogroup.com',
                'role_id' => '798a1b73-a2de-47dc-96a1-a8afb77fe589',
                'role' => 'Supervisor - IT Admin',
                'department_id' => '019f88f8-0725-709b-b13c-142da8fbab37',
                'department' => $mst,
                'dashboard_role' => DashboardRole::Qa,
            ],
            [
                'id' => '52aba908-1238-42da-97e0-5f19e6e0b39a',
                'name' => 'Nia Sanchez',
                'email' => 'nmsanchez@leoniogroup.com',
                'role_id' => '539bad4b-2ad1-40fb-b1af-2bfc10cc8865',
                'role' => 'IT Admin',
                'department_id' => '019f88f8-0725-709b-b13c-142da8fbab37',
                'department' => $mst,
                'dashboard_role' => DashboardRole::Requestor,
            ],
            [
                'id' => '61679213-3b57-4aaa-b544-1cff0c89ccb8',
                'name' => 'Helpdesk',
                'email' => 'icthelpdesk@leoniogroup.com',
                'role_id' => '793b5da5-508c-4082-835f-d55caea045a3',
                'role' => 'Helpdesk',
                'department_id' => '019f88f8-0725-709b-b13c-142da8fbab37',
                'department' => $mst,
                'dashboard_role' => DashboardRole::Requestor,
            ],
        ];

        // Matched by email (unique, and what login falls back to), not id —
        // a user who logged in first keeps their row. Existing rows only get
        // a dashboard_role if they have none yet, so a role changed later in
        // User Management survives a re-seed. last_synced_at stays null
        // until a real login syncs the row.
        foreach ($users as $user) {
            $existing = User::where('email', $user['email'])->first();

            if ($existing === null) {
                User::create([...$user, 'active' => true]);
            } elseif ($existing->dashboard_role === null) {
                $existing->update(['dashboard_role' => $user['dashboard_role']]);
            }
        }
    }
}
