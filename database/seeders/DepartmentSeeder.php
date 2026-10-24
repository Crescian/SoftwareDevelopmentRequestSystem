<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'code' => 'ICT',
                'name' => 'Information and Communications Technology',
            ],
            [
                'code' => 'HR',
                'name' => 'Human Resources',
            ],
            [
                'code' => 'FIN',
                'name' => 'Finance',
            ],
            [
                'code' => 'OPS',
                'name' => 'Operations',
            ],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']],
                ['name' => $department['name'], 'is_active' => true],
            );
        }
    }
}