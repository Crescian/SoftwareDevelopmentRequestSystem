<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users are the only data production needs seeded: dashboard roles
        // can only be assigned by a seeded System Administrator.
        $this->call(UserSeeder::class);

        // Sample data for local/dev only — in production, departments are
        // mirrored from the ticketing org API (OrgDirectoryService).
        if (! app()->isProduction()) {
            $this->call([
                DepartmentSeeder::class,
                SoftwareProjectSeeder::class,
            ]);
        }
    }
}