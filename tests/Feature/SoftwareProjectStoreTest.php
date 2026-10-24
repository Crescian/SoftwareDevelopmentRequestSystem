<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SoftwareProjectStoreTest extends TestCase
{
    use RefreshDatabase;

    private function payload(User $requestor, array $overrides = []): array
    {
        $department = Department::create([
            'id' => (string) Str::uuid(),
            'code' => 'FIN',
            'name' => 'Finance',
            'is_active' => true,
        ]);

        return array_merge([
            'project_id' => 'SDR-2026-001',
            'project_name' => 'Expense Tracker',
            'requesting_department_id' => $department->id,
            'project_sponsor_id' => $requestor->id,
            'project_summary' => 'Summary',
            'business_problem' => 'Problem',
            'proposed_solution' => 'Solution',
            'objectives' => ['Reduce manual work'],
            'in_scope' => ['Expense submission'],
            'requirements' => [[
                'requirement_type' => 'FUNCTIONAL',
                'requirement_code' => 'FR-001',
                'title' => 'Submit expense',
                'description' => 'Users can submit an expense.',
            ]],
            'required_attachments' => [[
                'attachment_type' => 'BUSINESS_WORKFLOW',
                'attachment_status' => 'NOT_ATTACHED',
            ]],
        ], $overrides);
    }

    public function test_valid_request_creates_a_draft_project_with_its_details(): void
    {
        $requestor = User::factory()->create();

        $response = $this->actingAs($requestor)
            ->post(route('software-projects.store'), $this->payload($requestor));

        $project = SoftwareProject::where('project_id', 'SDR-2026-001')->firstOrFail();

        $response->assertRedirect(route('software-projects.show', $project));
        $this->assertSame('DRAFT', $project->status);
        $this->assertCount(1, $project->objectives);
        $this->assertCount(1, $project->scopes);
        $this->assertCount(1, $project->requirements);
        $this->assertCount(1, $project->requiredAttachments);
    }

    public function test_missing_objectives_is_rejected_and_nothing_is_created(): void
    {
        $requestor = User::factory()->create();

        $this->actingAs($requestor)
            ->post(route('software-projects.store'), $this->payload($requestor, ['objectives' => []]))
            ->assertSessionHasErrors('objectives');

        $this->assertSame(0, SoftwareProject::count());
    }

    public function test_duplicate_project_id_is_rejected(): void
    {
        $requestor = User::factory()->create();
        $payload = $this->payload($requestor);

        $this->actingAs($requestor)->post(route('software-projects.store'), $payload);

        $this->actingAs($requestor)
            ->post(route('software-projects.store'), $payload)
            ->assertSessionHasErrors('project_id');

        $this->assertSame(1, SoftwareProject::count());
    }

    public function test_requestor_is_always_the_signed_in_user(): void
    {
        $requestor = User::factory()->create();
        $someoneElse = User::factory()->create();

        $this->actingAs($requestor)->post(
            route('software-projects.store'),
            $this->payload($requestor, ['requestor_id' => $someoneElse->id]),
        );

        $this->assertSame($requestor->id, SoftwareProject::sole()->requestor_id);
    }

    public function test_unknown_sponsor_or_manager_is_rejected(): void
    {
        $requestor = User::factory()->create();

        $this->actingAs($requestor)
            ->post(route('software-projects.store'), $this->payload($requestor, [
                'project_sponsor_id' => (string) Str::uuid(),
                'project_manager_id' => (string) Str::uuid(),
            ]))
            ->assertSessionHasErrors(['project_sponsor_id', 'project_manager_id']);

        $this->assertSame(0, SoftwareProject::count());
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->post(route('software-projects.store'), [])
            ->assertRedirect(route('login'));
    }
}
