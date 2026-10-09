<?php

namespace App\Http\Controllers;

use App\Models\ProjectDocument;
use App\Models\SoftwareProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProjectCollaborationController extends Controller
{
    public function storeComment(Request $request, SoftwareProject $softwareProject): RedirectResponse
    {
        $this->authorize('collaborate', $softwareProject);

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:4000'],
        ]);

        $softwareProject->comments()->create([
            'id' => Str::uuid(),
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        return redirect()
            ->route('software-projects.show', $softwareProject)
            ->with('success', 'Comment added.');
    }

    public function storeDocument(Request $request, SoftwareProject $softwareProject): RedirectResponse
    {
        $this->authorize('collaborate', $softwareProject);

        $validated = $request->validate([
            'document_number' => ['required', 'string', 'max:50'],
            'document_type' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file_name' => ['nullable', 'string', 'max:255'],
            'file_path' => ['nullable', 'string', 'max:2000'],
        ]);

        $softwareProject->documents()->create([
            'id' => Str::uuid(),
            'document_number' => $validated['document_number'],
            'document_type' => $validated['document_type'],
            'title' => $validated['title'],
            'status' => 'DRAFT',
            'description' => $validated['description'] ?? null,
            'file_name' => $validated['file_name'] ?? null,
            'file_path' => $validated['file_path'] ?? null,
            'prepared_by' => $request->user()->id,
            'prepared_at' => now(),
        ]);

        return redirect()
            ->route('software-projects.show', $softwareProject)
            ->with('success', 'Document attached.');
    }

    public function reviewDocument(
        Request $request,
        SoftwareProject $softwareProject,
        ProjectDocument $document
    ): RedirectResponse {
        $this->authorize('reviewDocument', $softwareProject);

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['APPROVED', 'RETURNED'])],
            'remarks' => ['required_if:decision,RETURNED', 'nullable', 'string', 'max:2000'],
        ]);

        $document->update([
            'status' => $validated['decision'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'approved_by' => $validated['decision'] === 'APPROVED' ? $request->user()->id : null,
            'approved_at' => $validated['decision'] === 'APPROVED' ? now() : null,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()
            ->route('software-projects.show', $softwareProject)
            ->with('success', "Document {$document->document_number} " . strtolower($validated['decision']) . '.');
    }
}
