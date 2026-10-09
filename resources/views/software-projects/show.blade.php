@extends('layouts.app')

@section('title')
{{ $project->project_name }}
@endsection

@section('styles')
@include('software-projects._tracker-styles')
@endsection

@section('content')

        @if (session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif

        @php
            $latestHistory = $project->statusHistories->last();
            $bannerClass = match ($project->status) {
                'RETURNED_FOR_REVISION' => 'alert-warning',
                'CLOSED' => 'alert-secondary',
                'REJECTED' => 'alert-danger',
                default => null,
            };
            $bannerLabel = match ($project->status) {
                'RETURNED_FOR_REVISION' => 'Returned for revision:',
                'CLOSED' => 'Request closed:',
                'REJECTED' => 'Request rejected:',
                default => null,
            };

            $initials = fn (?string $name) => $name
                ? collect(explode(' ', trim($name)))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('')
                : '—';

            $pillClass = fn (string $status) => match ($status) {
                'DRAFT' => 'draft',
                'RETURNED_FOR_REVISION' => 'clarify',
                'PENDING_APPROVAL' => 'review',
                'MONITORING_MAINTENANCE' => 'completed',
                'REJECTED', 'CLOSED' => 'terminal',
                default => 'progress',
            };

            // ── Stage stepper ──
            $stageOrder = [
                \App\Enums\ProjectStatus::Submitted, \App\Enums\ProjectStatus::FeasibilityAssessment, \App\Enums\ProjectStatus::PendingApproval,
                \App\Enums\ProjectStatus::RequirementsDesign, \App\Enums\ProjectStatus::Development, \App\Enums\ProjectStatus::TestingQa,
                \App\Enums\ProjectStatus::UserAcceptanceTesting, \App\Enums\ProjectStatus::Deployment, \App\Enums\ProjectStatus::MonitoringMaintenance,
            ];
            $stageLabels = ['Initial Review', 'Feasibility', 'Approval', 'Req & Design', 'Development', 'Testing & QA', 'UAT', 'Deployment', 'Monitoring'];

            $currentStatus = \App\Enums\ProjectStatus::from($project->status);
            $isRejectedOrClosed = in_array($currentStatus, [\App\Enums\ProjectStatus::Rejected, \App\Enums\ProjectStatus::Closed], true);
            $isReturned = $currentStatus === \App\Enums\ProjectStatus::ReturnedForRevision;

            $effectiveStatus = match (true) {
                ($isRejectedOrClosed || $isReturned) && $latestHistory
                    => \App\Enums\ProjectStatus::tryFrom($latestHistory->from_status ?? ''),
                $currentStatus === \App\Enums\ProjectStatus::Draft => null,
                default => $currentStatus,
            };

            $currentIndex = $effectiveStatus ? array_search($effectiveStatus, $stageOrder, true) : null;
            $currentIndex = $currentIndex === false ? null : $currentIndex;

            $stageStates = [];
            foreach ($stageOrder as $i => $stage) {
                $state = match (true) {
                    $currentIndex === null => 'upcoming',
                    $isRejectedOrClosed && $i === $currentIndex => 'terminal',
                    $isReturned && $i === $currentIndex => 'returned',
                    $i < $currentIndex => 'completed',
                    $i === $currentIndex => 'current',
                    default => 'upcoming',
                };
                $stageStates[] = ['label' => $stageLabels[$i], 'state' => $state];
            }
        @endphp

        @if ($bannerClass && $latestHistory && $latestHistory->remarks)

            <div class="alert {{ $bannerClass }}">
                <strong>{{ $bannerLabel }}</strong>
                {{ $latestHistory->remarks }}
            </div>

        @endif

        <a href="{{ url()->previous(route('software-projects.index')) }}" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">

            <div>

                <h1 class="fw-bold mb-1">
                    {{ $project->project_name }}
                </h1>

                <div class="text-muted">
                    {{ $project->project_id }}
                </div>

            </div>

            <div class="d-flex align-items-center gap-2">

                <span class="status-pill {{ $pillClass($project->status) }}" style="font-size:15px;padding:8px 18px;">
                    {{ \App\Enums\ProjectStatus::from($project->status)->label() }}
                </span>

                <span class="owner-badge {{ match (true) {
                    $currentOwner->isTerminal => 'terminal',
                    $currentOwner->isDepartmentWide => 'team',
                    default => '',
                } }}">
                    <span class="avatar-sm">
                        {{ $currentOwner->isDepartmentWide ? 'MST' : $initials($currentOwner->user?->name) }}
                    </span>
                    {{ $currentOwner->label }}
                </span>

                @can('submit', $project)

                    <form method="POST" action="{{ route('software-projects.submit', $project) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            Submit Request
                        </button>
                    </form>

                @endcan

            </div>

        </div>


        {{-- STAGE TRACKER --}}

        <div class="card mb-4">

            <div class="card-body pb-2">

                <div class="stage-stepper">

                    @foreach ($stageStates as $stage)

                        <div class="stage-step {{ $stage['state'] }}">
                            <div class="stage-dot">
                                @if ($stage['state'] === 'completed')
                                    <i class="fa-solid fa-check"></i>
                                @elseif ($stage['state'] === 'terminal')
                                    <i class="fa-solid fa-xmark"></i>
                                @elseif ($stage['state'] === 'returned')
                                    <i class="fa-solid fa-rotate-left"></i>
                                @endif
                            </div>
                            <div class="stage-label">{{ $stage['label'] }}</div>
                        </div>

                    @endforeach

                </div>

            </div>

        </div>


        {{-- TABS --}}

        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#timeline-pane" type="button" role="tab">
                    <i class="fa-solid fa-clock-rotate-left"></i> Activity Timeline
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#info-pane" type="button" role="tab">
                    <i class="fa-solid fa-circle-info"></i> Project Information
                </button>
            </li>
        </ul>

        <div class="tab-content">

        <div class="tab-pane fade show active" id="timeline-pane" role="tabpanel">

        <div class="card mb-4">

            <div class="card-body">

                <div class="activity-item">

                    <div class="activity-marker tone-origin">
                        <i class="fa-solid fa-flag"></i>
                    </div>

                    <div class="activity-content">

                        <div class="activity-head">
                            <span class="activity-transition" style="font-weight:800;color:var(--nd);">Request Created</span>
                            <span class="activity-time">
                                <span class="activity-time-abs">{{ $project->created_at->format('M j, Y g:ia') }}</span>
                                <span class="activity-time-rel">{{ $project->created_at->diffForHumans() }}</span>
                            </span>
                        </div>

                        <div class="activity-actor">
                            <span class="avatar-sm">{{ $initials($project->requestor->name ?? null) }}</span>
                            {{ $project->requestor->name ?? 'Unknown' }}
                            @if ($project->requestor?->department)
                                <span class="actor-dept">&middot; {{ $project->requestor->department }}</span>
                            @endif
                        </div>

                    </div>

                </div>

                @forelse ($project->statusHistories as $history)

                    @php
                        $toStatus = $history->to_status;
                        $tone = match (true) {
                            is_null($history->from_status) => 'start',
                            $toStatus === 'REJECTED' => 'danger',
                            $toStatus === 'RETURNED_FOR_REVISION' => 'warning',
                            $toStatus === 'CLOSED' => 'muted',
                            $toStatus === 'MONITORING_MAINTENANCE' => 'success',
                            default => 'forward',
                        };
                        $icon = match ($tone) {
                            'start' => 'fa-solid fa-flag',
                            'danger' => 'fa-solid fa-xmark',
                            'warning' => 'fa-solid fa-rotate-left',
                            'muted' => 'fa-solid fa-ban',
                            'success' => 'fa-solid fa-rocket',
                            default => 'fa-solid fa-arrow-right',
                        };

                        $previousAt = $loop->first ? $project->created_at : $project->statusHistories[$loop->index - 1]->changed_at;
                        $fromLabel = $history->from_status ? \App\Enums\ProjectStatus::from($history->from_status)->label() : 'Draft';
                        $duration = ($previousAt && $history->changed_at)
                            ? $previousAt->diffForHumans($history->changed_at, ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE])
                            : null;
                    @endphp

                    <div class="activity-item">

                        <div class="activity-marker tone-{{ $tone }}">
                            <i class="{{ $icon }}"></i>
                        </div>

                        <div class="activity-content">

                            <div class="activity-head">

                                <span class="activity-transition">
                                    <span class="status-pill {{ $history->from_status ? $pillClass($history->from_status) : 'start' }}">
                                        {{ $history->from_status ? \App\Enums\ProjectStatus::from($history->from_status)->label() : 'Created' }}
                                    </span>
                                    <i class="fa-solid fa-arrow-right"></i>
                                    <span class="status-pill {{ $pillClass($history->to_status) }}">
                                        {{ \App\Enums\ProjectStatus::from($history->to_status)->label() }}
                                    </span>
                                </span>

                                <span class="activity-time">
                                    <span class="activity-time-abs">{{ $history->changed_at?->format('M j, Y g:ia') }}</span>
                                    <span class="activity-time-rel">{{ $history->changed_at?->diffForHumans() }}</span>
                                </span>

                            </div>

                            @if ($duration)
                                <div class="activity-duration">
                                    <i class="fa-solid fa-hourglass-half"></i> Spent {{ $duration }} in {{ $fromLabel }}
                                </div>
                            @endif

                            <div class="activity-actor">
                                <span class="avatar-sm">{{ $initials($history->changedBy->name ?? null) }}</span>
                                {{ $history->changedBy->name ?? 'Unknown' }}
                                @if ($history->changedBy?->department)
                                    <span class="actor-dept">&middot; {{ $history->changedBy->department }}</span>
                                @endif
                            </div>

                            @if ($history->remarks)
                                <p class="activity-remarks">{{ $history->remarks }}</p>
                            @endif

                        </div>

                    </div>

                @empty

                    <p class="text-muted mb-0">No status changes recorded yet.</p>

                @endforelse

            </div>

        </div>

        </div>

        <div class="tab-pane fade" id="info-pane" role="tabpanel">

        {{-- PROJECT INFORMATION --}}

        <div class="card mb-4">

            <div class="card-header-clean">
                <i class="fa-solid fa-circle-info"></i> Project Information
            </div>

            <div class="card-body">

                @php
                    $priorityPillClass = fn (?string $priority) => match (strtoupper($priority ?? '')) {
                        'LOW' => 'draft',
                        'HIGH' => 'review',
                        'CRITICAL' => 'clarify',
                        default => 'progress',
                    };
                @endphp

                <div class="info-grid">

                    <div class="info-item">
                        <div class="info-label">Requesting Department</div>
                        <div class="info-value">{{ $project->requestingDepartment->name }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Priority</div>
                        <div class="info-value">
                            <span class="status-pill {{ $priorityPillClass($project->priority) }}">
                                {{ $project->priority ?? 'Not set' }}
                            </span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Date Submitted</div>
                        <div class="info-value">{{ $project->date_submitted?->format('M j, Y g:ia') ?? '—' }}</div>
                    </div>

                </div>

            </div>

        </div>


        {{-- OVERVIEW --}}

        <div class="card mb-4">

            <div class="card-header-clean">
                <i class="fa-solid fa-file-lines"></i> Project Overview
            </div>

            <div class="card-body">

                <h6>
                    Summary
                </h6>

                <p>
                    {{ $project->project_summary }}
                </p>

                <h6>
                    Business Problem
                </h6>

                <p>
                    {{ $project->business_problem }}
                </p>

                <h6>
                    Proposed Solution
                </h6>

                <p class="mb-0">
                    {{ $project->proposed_solution }}
                </p>

            </div>

        </div>


        {{-- OBJECTIVES --}}

        <div class="card mb-4">

            <div class="card-header-clean">
                <i class="fa-solid fa-bullseye"></i> Objectives
            </div>

            <div class="card-body">

                <ol>

                    @foreach ($project->objectives as $objective)

                        <li>
                            {{ $objective->objective }}
                        </li>

                    @endforeach

                </ol>

            </div>

        </div>


        {{-- SCOPE --}}

        <div class="card mb-4">

            <div class="card-header-clean">
                <i class="fa-solid fa-layer-group"></i> Scope
            </div>

            <div class="card-body">

                <h6>
                    In Scope
                </h6>

                <ul>

                    @foreach (
                            $project->scopes->where('scope_type', 'IN_SCOPE')
                            as $scope
                        )

                        <li>
                            {{ $scope->description }}
                        </li>

                    @endforeach

                </ul>


                <h6>
                    Out of Scope
                </h6>

                <ul>

                    @foreach (
                            $project->scopes->where('scope_type', 'OUT_OF_SCOPE')
                            as $scope
                        )

                        <li>
                            {{ $scope->description }}
                        </li>

                    @endforeach

                </ul>

            </div>

        </div>


        {{-- REQUIREMENTS --}}

        <div class="card mb-4">

            <div class="card-header-clean">
                <i class="fa-solid fa-list-check"></i> Requirements
            </div>

            <div class="card-body">

                @php
                    $freeTextPillClass = fn (?string $status) => match (true) {
                        $status === null => 'draft',
                        str_contains(strtoupper($status), 'DRAFT') => 'draft',
                        str_contains(strtoupper($status), 'APPROVED') || str_contains(strtoupper($status), 'COMPLETED') => 'completed',
                        default => 'progress',
                    };
                @endphp

                <div class="table-responsive table-clean-wrap">

                    <table class="table table-clean mb-0">

                        <thead>

                            <tr>

                                <th>Code</th>
                                <th>Requirement</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Status</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse ($project->requirements as $requirement)

                                <tr>

                                    <td class="ref">
                                        {{ $requirement->requirement_code }}
                                    </td>

                                    <td>
                                        <div class="title">{{ $requirement->title }}</div>
                                        @if ($requirement->description)
                                            <div class="text-muted" style="font-size:12px;">{{ $requirement->description }}</div>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $requirement->requirement_type }}
                                    </td>

                                    <td>
                                        {{ $requirement->priority }}
                                    </td>

                                    <td>
                                        <span class="status-pill {{ $freeTextPillClass($requirement->status) }}">
                                            {{ $requirement->status }}
                                        </span>
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No requirements listed yet.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- ATTACHMENTS --}}

        <div class="card mb-4">

            <div class="card-header-clean">
                <i class="fa-solid fa-paperclip"></i> Required Attachments
            </div>

            <div class="card-body">

                <div class="table-responsive table-clean-wrap">

                <table class="table table-clean mb-0">

                    <thead>

                        <tr>

                            <th>Document</th>
                            <th>Required</th>
                            <th>Status</th>
                            <th>Signed Off</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($project->requiredAttachments as $attachment)

                            <tr>

                                <td class="title">
                                    {{ $attachment->attachment_type }}
                                </td>

                                <td>
                                    @if ($attachment->is_required)
                                        <i class="fa-solid fa-check" style="color:#1b9a4b;"></i>
                                    @else
                                        <i class="fa-solid fa-minus text-muted"></i>
                                    @endif
                                </td>

                                <td>
                                    <span class="status-pill {{ $freeTextPillClass($attachment->attachment_status) }}">
                                        {{ $attachment->attachment_status }}
                                    </span>
                                </td>

                                <td>
                                    @if ($attachment->requester_signed_off)
                                        <i class="fa-solid fa-check" style="color:#1b9a4b;"></i>
                                    @else
                                        <i class="fa-solid fa-minus text-muted"></i>
                                    @endif
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No required attachments listed.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

                </div>

            </div>

        </div>


        {{-- DOCUMENTS --}}

        <div class="card mb-4">

            <div class="card-header-clean">
                <i class="fa-solid fa-folder-open"></i> Documents
            </div>

            <div class="card-body">

                @forelse ($project->documents as $document)

                    <div class="border rounded p-2 mb-2">

                        <div class="d-flex justify-content-between">
                            <strong>{{ $document->document_number }} &middot; {{ $document->title }} ({{ $document->document_type }})</strong>
                            <span class="badge {{ match($document->status) { 'APPROVED' => 'bg-success', 'RETURNED' => 'bg-danger', default => 'bg-secondary' } }}">
                                {{ $document->status }}
                            </span>
                        </div>

                        <p class="text-muted mb-1">{{ $document->description }}</p>

                        @if ($document->file_path)
                            <small class="text-muted">{{ $document->file_name ?? $document->file_path }}</small>
                        @endif

                        @can('reviewDocument', $project)

                            @if ($document->status === 'DRAFT')

                                <form method="POST" action="{{ route('software-projects.documents.review', [$project, $document]) }}" class="d-flex gap-2 mt-2">
                                    @csrf
                                    <select name="decision" class="form-select form-select-sm" style="width:auto;">
                                        <option value="APPROVED">Approve</option>
                                        <option value="RETURNED">Return</option>
                                    </select>
                                    <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Remarks (required if returning)">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Submit</button>
                                </form>

                            @endif

                        @endcan

                    </div>

                @empty

                    <p class="text-muted">No documents attached yet.</p>

                @endforelse

                @can('collaborate', $project)

                    <form method="POST" action="{{ route('software-projects.documents.store', $project) }}" class="row g-2 mt-3">
                        @csrf
                        <div class="col-md-2">
                            <input type="text" name="document_number" class="form-control" placeholder="DOC-001" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="document_type" class="form-control" placeholder="Type" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="title" class="form-control" placeholder="Title" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="file_path" class="form-control" placeholder="Link / path to file">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary w-100">Attach</button>
                        </div>
                    </form>

                @endcan

            </div>

        </div>


        {{-- COMMENTS --}}

        <div class="card mb-4">

            <div class="card-header-clean">
                <i class="fa-solid fa-comments"></i> Comments
            </div>

            <div class="card-body">

                @forelse ($project->comments as $comment)

                    <div class="border-bottom pb-2 mb-2">
                        <strong>{{ $comment->user->name ?? 'Unknown' }}</strong>
                        <span class="text-muted"><small>{{ $comment->created_at->format('Y-m-d H:i') }}</small></span>
                        <p class="mb-0">{{ $comment->comment }}</p>
                    </div>

                @empty

                    <p class="text-muted">No comments yet.</p>

                @endforelse

                @can('collaborate', $project)

                    <form method="POST" action="{{ route('software-projects.comments.store', $project) }}" class="mt-3">
                        @csrf
                        <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Add a comment" required></textarea>
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            Post Comment
                        </button>
                    </form>

                @endcan

            </div>

        </div>

        </div>

        </div>

@endsection
