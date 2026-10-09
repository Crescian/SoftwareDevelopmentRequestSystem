<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_credentials_log_the_user_in_and_mirror_the_ticketing_user(): void
    {
        Http::fake([
            '*/login' => Http::response([
                'access_token' => 'fake-token-123',
                'token_type' => 'Bearer',
                'user' => [
                    'id' => '11111111-1111-1111-1111-111111111111',
                    'name' => 'Jane Doe',
                    'email' => 'jane@example.com',
                    'role_id' => '22222222-2222-2222-2222-222222222222',
                    'role' => 'Requester',
                    'department_id' => '33333333-3333-3333-3333-333333333333',
                    'department' => 'IT',
                    'active' => true,
                ],
            ], 200),
        ]);

        $response = $this->post('/login', [
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $response->assertRedirect(route('software-projects.welcome'));
        $this->assertAuthenticated();

        $user = User::find('11111111-1111-1111-1111-111111111111');
        $this->assertNotNull($user);
        $this->assertSame('jane@example.com', $user->email);
        $this->assertSame('Requester', $user->role);
        $this->assertSame('fake-token-123', session('ticketing_token'));
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        Http::fake([
            '*/login' => Http::response(['message' => 'Invalid credentials'], 401),
        ]);

        $response = $this->post('/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_account_is_rejected(): void
    {
        Http::fake([
            '*/login' => Http::response(['message' => 'Account is deactivated'], 403),
        ]);

        $response = $this->post('/login', [
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_protected_routes_require_authentication(): void
    {
        $this->get('/projects/create')->assertRedirect('/login');
    }

    public function test_logout_revokes_the_ticketing_token_and_clears_the_session(): void
    {
        Http::fake([
            '*/login' => Http::response([
                'access_token' => 'fake-token-123',
                'token_type' => 'Bearer',
                'user' => [
                    'id' => '11111111-1111-1111-1111-111111111111',
                    'name' => 'Jane Doe',
                    'email' => 'jane@example.com',
                    'active' => true,
                ],
            ], 200),
            '*/logout' => Http::response([], 204),
        ]);

        $this->post('/login', ['email' => 'jane@example.com', 'password' => 'secret']);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/logout')
                && $request->hasHeader('Authorization', 'Bearer fake-token-123');
        });
    }
}
