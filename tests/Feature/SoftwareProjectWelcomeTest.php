<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftwareProjectWelcomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_sees_both_request_types(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('software-projects.welcome'));

        $response->assertOk();
        $response->assertSee('New Request');
        $response->assertSee('Change Request');
        $response->assertSee('Currently Unavailable');
        $response->assertSee(route('software-projects.create'), false);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('software-projects.welcome'));

        $response->assertRedirect(route('login'));
    }

    public function test_new_request_links_point_to_welcome_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('software-projects.index'))
            ->assertSee(route('software-projects.welcome'), false);
    }
}
