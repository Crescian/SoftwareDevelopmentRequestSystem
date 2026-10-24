<?php

namespace Tests\Feature;

use App\Enums\DashboardRole;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_all_users_and_is_safe_to_rerun(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(UserSeeder::class);

        $this->assertSame(7, User::count());
        $this->assertSame(
            DashboardRole::SystemAdministrator,
            User::where('email', 'cmlanoy@leoniogroup.com')->value('dashboard_role'),
        );
    }

    public function test_user_who_logged_in_first_gets_their_role_without_a_duplicate(): void
    {
        // Created by login under a different id, with no role yet.
        User::factory()->create([
            'email' => 'cmlanoy@leoniogroup.com',
            'dashboard_role' => null,
        ]);

        $this->seed(UserSeeder::class);

        $this->assertSame(1, User::where('email', 'cmlanoy@leoniogroup.com')->count());
        $this->assertSame(
            DashboardRole::SystemAdministrator,
            User::where('email', 'cmlanoy@leoniogroup.com')->value('dashboard_role'),
        );
    }

    public function test_role_changed_in_user_management_survives_a_reseed(): void
    {
        $this->seed(UserSeeder::class);
        User::where('email', 'jrgalindez@leoniogroup.com')->update(['dashboard_role' => DashboardRole::Qa]);

        $this->seed(UserSeeder::class);

        $this->assertSame(
            DashboardRole::Qa,
            User::where('email', 'jrgalindez@leoniogroup.com')->value('dashboard_role'),
        );
    }
}
