<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guests landing on the root URL are sent straight to the login page.
     */
    public function test_root_redirects_guests_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_root_sends_signed_in_users_to_their_home_page(): void
    {
        $this->actingAs(\App\Models\User::factory()->create())
            ->get('/')
            ->assertRedirect(route('software-projects.index'));

        $this->actingAs(\App\Models\User::factory()->mst()->create())
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }
}
