<?php

namespace Tests\Feature\Workflow;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InitialReviewTest extends TestCase
{
    use RefreshDatabase;

    private function draftProject(User $requestor, ?Department $department = null): SoftwareProject
    {
        $department ??= Department::create([
            'id' => Str::uuid(),
            'code' => 'FIN',
            'name' => 'Finance',
            'is_active' => true,
        ]);

        return SoftwareProject::create([
            'id' => Str::uuid(),
            'project_id' => 'SDR-2026-' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'project_name' => 'Test Project',
            'requesting_department_id' => $department->id,
            'requestor_id' => $requestor->id,
            'project_sponsor_id' => $requestor->id,
            'project_summary' => 'Summary',
            'business_problem' => 'Problem',
            'proposed_solution' => 'Solution',
            'status' => 'DRAFT',
        ]);
    }

    public function test_requestor_can_submit_their_own_draft(): void
    {
        $requestor = User::factory()->create();
        $project = $this->draftProject($requestor);

        $response = $this->actingAs($requestor)
            ->post(route('software-projects.submit', $project));

        $response->assertRedirect(route('software-projects.show', $project));
        $this->assertSame('SUBMITTED', $project->fresh()->status);
        $this->assertNotNull($project->fresh()->submitted_at);

        $history = $project->fresh()->statusHistories->last();
        $this->assertSame('DRAFT', $history->from_status);
        $this->assertSame('SUBMITTED', $history->to_status);
        $this->assertSame($requestor->id, $history->changed_by);
    }

    public function test_a_different_user_cannot_submit_someone_elses_draft(): void
    {
        $requestor = User::factory()->create();
        $someoneElse = User::factory()->create();
        $project = $this->draftProject($requestor);

        $this->actingAs($someoneElse)
            ->post(route('software-projects.submit', $project))
            ->assertForbidden();

        $this->assertSame('DRAFT', $project->fresh()->status);
    }

    public function test_non_mst_user_cannot_view_the_initial_review_queue(): void
    {
        $requestor = User::factory()->create();

        $this->actingAs($requestor)
            ->get(route('reviews.initial-review.index'))
            ->assertForbidden();
    }

    public function test_mst_user_sees_only_submitted_projects_in_the_queue(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $department = Department::create([
            'id' => Str::uuid(),
            'code' => 'FIN',
            'name' => 'Finance',
            'is_active' => true,
        ]);

        $draft = $this->draftProject($requestor, $department);
        $submitted = $this->draftProject($requestor, $department);
        $submitted->update(['status' => 'SUBMITTED', 'submitted_at' => now()]);

        $response = $this->actingAs($mst)->get(route('reviews.initial-review.index'));

        $response->assertOk();
        $response->assertSee($submitted->project_id);
        $response->assertDontSee($draft->project_id);
    }

    public function test_mst_user_can_pass_initial_review(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->draftProject($requestor);
        $project->update(['status' => 'SUBMITTED', 'submitted_at' => now()]);

        $response = $this->actingAs($mst)
            ->post(route('reviews.initial-review.pass', $project));

        $response->assertRedirect(route('reviews.initial-review.index'));
        $this->assertSame('FEASIBILITY_ASSESSMENT', $project->fresh()->status);
    }

    public function test_mst_user_can_return_a_project_with_required_remarks(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->draftProject($requestor);
        $project->update(['status' => 'SUBMITTED', 'submitted_at' => now()]);

        $this->actingAs($mst)
            ->post(route('reviews.initial-review.return', $project), [])
            ->assertSessionHasErrors('remarks');

        $this->assertSame('SUBMITTED', $project->fresh()->status);

        $this->actingAs($mst)
            ->post(route('reviews.initial-review.return', $project), [
                'remarks' => 'Missing the cost-benefit attachment.',
            ])
            ->assertRedirect(route('reviews.initial-review.index'));

        $fresh = $project->fresh();
        $this->assertSame('RETURNED_FOR_REVISION', $fresh->status);
        $this->assertSame('Missing the cost-benefit attachment.', $fresh->statusHistories->last()->remarks);
    }

    public function test_requestor_can_resubmit_a_returned_project(): void
    {
        $requestor = User::factory()->create();
        $project = $this->draftProject($requestor);
        $project->update(['status' => 'RETURNED_FOR_REVISION']);

        $this->actingAs($requestor)
            ->post(route('software-projects.submit', $project))
            ->assertRedirect(route('software-projects.show', $project));

        $this->assertSame('SUBMITTED', $project->fresh()->status);
    }

    public function test_non_mst_user_cannot_conduct_initial_review_even_with_direct_url(): void
    {
        $requestor = User::factory()->create();
        $project = $this->draftProject($requestor);
        $project->update(['status' => 'SUBMITTED', 'submitted_at' => now()]);

        $this->actingAs($requestor)
            ->post(route('reviews.initial-review.pass', $project))
            ->assertForbidden();
    }
}
