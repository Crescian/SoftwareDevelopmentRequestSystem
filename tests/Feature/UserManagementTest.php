<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_view_the_user_management_page(): void
    {
        $requestor = User::factory()->create();

        $this->actingAs($requestor)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_mst_reviewer_is_not_an_administrator_and_cannot_view_the_page(): void
    {
        $mst = User::factory()->mst()->create();

        $this->actingAs($mst)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_administrator_can_view_the_user_management_page(): void
    {
        $admin = User::factory()->systemAdministrator()->create();
        $someone = User::factory()->create(['name' => 'Jane Requestor']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Jane Requestor')
            ->assertSee('Manage Users');
    }

    public function test_administrator_can_change_a_users_dashboard_role(): void
    {
        $admin = User::factory()->systemAdministrator()->create();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.role.update', $target), [
                'dashboard_role' => 'developer',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame('developer', $target->fresh()->dashboard_role->value);
    }

    public function test_non_admin_cannot_change_a_users_dashboard_role(): void
    {
        $requestor = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($requestor)
            ->post(route('admin.users.role.update', $target), [
                'dashboard_role' => 'system_administrator',
            ])
            ->assertForbidden();

        $this->assertNotSame('system_administrator', $target->fresh()->dashboard_role->value);
    }

    public function test_an_invalid_role_value_is_rejected(): void
    {
        $admin = User::factory()->systemAdministrator()->create();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.role.update', $target), [
                'dashboard_role' => 'super_admin_hacker',
            ])
            ->assertSessionHasErrors('dashboard_role');
    }
}
