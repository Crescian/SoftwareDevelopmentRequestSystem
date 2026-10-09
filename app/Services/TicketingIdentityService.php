<?php

namespace App\Services;

use App\Exceptions\TicketingIdentityException;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Consumes the ticketing system's centralized identity API
 * (POST /api/login, POST /api/logout) instead of maintaining local
 * passwords. This app's own `users` table is a read-through mirror,
 * keyed by the ticketing system's UUID, refreshed on every login.
 */
class TicketingIdentityService
{
    /**
     * Verify credentials against the ticketing system and return the raw
     * response payload ({ access_token, token_type, user }).
     *
     * @throws TicketingIdentityException
     */
    public function login(string $email, string $password): array
    {
        $response = $this->client()->post('/login', [
            'email' => $email,
            'password' => $password,
            'client' => config('services.ticketing.client'),
        ]);

        if ($response->status() === 401 || $response->status() === 422) {
            throw new TicketingIdentityException(
                'Those credentials were not recognized.',
                $response->status(),
            );
        }

        if ($response->status() === 403) {
            throw new TicketingIdentityException(
                'This account is deactivated. Contact your administrator.',
                403,
            );
        }

        if ($response->status() === 429) {
            throw new TicketingIdentityException(
                'Too many login attempts. Please wait a moment and try again.',
                429,
            );
        }

        if ($response->failed()) {
            Log::error('Ticketing identity login failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new TicketingIdentityException(
                'The identity service is unavailable right now. Please try again shortly.',
                $response->status(),
            );
        }

        return $response->json();
    }

    /**
     * Revoke this app's token on the ticketing system. Best-effort: a local
     * logout must never be blocked by the identity service being down.
     */
    public function logout(?string $token): void
    {
        if (! $token) {
            return;
        }

        try {
            $this->client()
                ->withToken($token)
                ->post('/logout');
        } catch (\Throwable $e) {
            Log::warning('Ticketing identity logout call failed', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Upsert the local mirror row from the ticketing system's user payload.
     */
    public function syncLocalUser(array $ticketingUser): User
    {
        return User::updateOrCreate(
            ['id' => $ticketingUser['id']],
            [
                'name' => $ticketingUser['name'],
                'email' => $ticketingUser['email'],
                'role_id' => $ticketingUser['role_id'] ?? null,
                'role' => $ticketingUser['role'] ?? null,
                'department_id' => $ticketingUser['department_id'] ?? null,
                'department' => $ticketingUser['department'] ?? null,
                'active' => $ticketingUser['active'] ?? true,
                'last_synced_at' => now(),
            ],
        );
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl(config('services.ticketing.base_url'))
            ->acceptJson()
            ->timeout((int) config('services.ticketing.timeout', 10));
    }
}
