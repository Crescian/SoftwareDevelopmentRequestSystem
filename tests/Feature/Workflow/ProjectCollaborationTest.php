<?php

namespace Tests\Feature\Workflow;

use App\Models\Department;
use App\Models\SoftwareProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectCollaborationTest extends TestCase
{
    use RefreshDatabase;

    private function anyProject(User $requestor, string $status = 'DRAFT'): SoftwareProject
    {
        $department = Department::create([
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
            'status' => $status,
        ]);
    }

    public function test_someone_unrelated_to_the_project_cannot_comment_or_attach_documents(): void
    {
        $requestor = User::factory()->create();
        $unrelated = User::factory()->create();
        $project = $this->anyProject($requestor);

        $this->actingAs($unrelated)
            ->post(route('software-projects.comments.store', $project), ['comment' => 'Hello'])
            ->assertForbidden();

        $this->actingAs($unrelated)
            ->post(route('software-projects.documents.store', $project), [
                'document_number' => 'DOC-001',
                'document_type' => 'NOTE',
                'title' => 'Note',
            ])
            ->assertForbidden();
    }

    public function test_the_requestor_can_comment_and_attach_a_document_at_any_stage(): void
    {
        $requestor = User::factory()->create();
        $project = $this->anyProject($requestor, 'DEVELOPMENT');

        $this->actingAs($requestor)
            ->post(route('software-projects.comments.store', $project), ['comment' => 'Any update on this?'])
            ->assertRedirect(route('software-projects.show', $project));

        $this->actingAs($requestor)->post(route('software-projects.documents.store', $project), [
            'document_number' => 'DOC-001',
            'document_type' => 'REFERENCE',
            'title' => 'Vendor quote',
            'file_path' => 'https://example.com/quote.pdf',
        ])->assertRedirect(route('software-projects.show', $project));

        $fresh = $project->fresh();
        $this->assertCount(1, $fresh->comments);
        $this->assertSame('Any update on this?', $fresh->comments->first()->comment);
        $this->assertSame($requestor->id, $fresh->comments->first()->user_id);

        $this->assertCount(1, $fresh->documents);
        $this->assertSame('DRAFT', $fresh->documents->first()->status);
    }

    public function test_only_mst_can_review_a_document_and_project_status_is_unaffected(): void
    {
        $requestor = User::factory()->create();
        $mst = User::factory()->mst()->create();
        $project = $this->anyProject($requestor, 'TESTING_QA');

        $this->actingAs($requestor)->post(route('software-projects.documents.store', $project), [
            'document_number' => 'DOC-001',
            'document_type' => 'REFERENCE',
            'title' => 'Vendor quote',
        ]);

        $document = $project->fresh()->documents->first();

        $this->actingAs($requestor)
            ->post(route('software-projects.documents.review', [$project, $document]), ['decision' => 'APPROVED'])
            ->assertForbidden();

        $this->actingAs($mst)
            ->post(route('software-projects.documents.review', [$project, $document]), ['decision' => 'APPROVED'])
            ->assertRedirect();

        $fresh = $document->fresh();
        $this->assertSame('APPROVED', $fresh->status);
        $this->assertSame($mst->id, $fresh->approved_by);

        // Reviewing a document never touches the project's own workflow status.
        $this->assertSame('TESTING_QA', $project->fresh()->status);
    }
}
