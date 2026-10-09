<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
            Department::create([
                'id' => Str::uuid(),
                'code' => $department['code'],
                'name' => $department['name'],
                'is_active' => true,
            ]);
        }
    }
}