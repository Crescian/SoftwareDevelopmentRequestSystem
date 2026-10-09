<?php

namespace Database\Factories;

use App\Enums\DashboardRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Users here are a mirror of the ticketing system's identity (see
 * TicketingIdentityService::syncLocalUser) — the factory fabricates what
 * that sync would have written, for tests that don't hit the real API.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role_id' => fake()->uuid(),
            'role' => fake()->randomElement(['Requestor', 'IT Admin', 'Project Sponsor']),
            'department_id' => fake()->uuid(),
            'department' => fake()->randomElement(['Finance', 'Human Resources', 'Operations']),
            'dashboard_role' => DashboardRole::Requestor->value,
            'active' => true,
            'last_synced_at' => now(),
        ];
    }

    public function mst(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'IT Admin',
            'department' => config('workflow.mst_department'),
            'dashboard_role' => DashboardRole::MstReviewer->value,
        ]);
    }

    public function developer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Developer',
            'department' => config('workflow.mst_department'),
            'dashboard_role' => DashboardRole::Developer->value,
        ]);
    }

    public function projectManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Project Manager',
            'department' => config('workflow.mst_department'),
            'dashboard_role' => DashboardRole::ProjectManager->value,
        ]);
    }

    public function qa(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'QA',
            'department' => config('workflow.mst_department'),
            'dashboard_role' => DashboardRole::Qa->value,
        ]);
    }

    public function systemAdministrator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'System Administrator',
            'department' => config('workflow.mst_department'),
            'dashboard_role' => DashboardRole::SystemAdministrator->value,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
