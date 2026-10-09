@extends('layouts.app')

@section('title', 'Workflow Tracker')

@section('styles')
@include('software-projects._tracker-styles')

.filter-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.filter-pill {
    display: inline-flex;
    align-items: center;
    line-height: 1.4;
    font-size: 13.5px;
    font-weight: 700;
    padding: 8px 18px;
    border-radius: 50px;
    border: 1.5px solid var(--bd);
    color: var(--tm);
    background: #fff;
    text-decoration: none;
    transition: all .15s;
}

.filter-pill:hover {
    border-color: var(--nl);
    color: var(--nl);
}

.filter-pill.active {
    background: var(--nd);
    border-color: var(--nd);
    color: #fff;
}
@endsection

@section('content')

        @if (session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif

        <a href="{{ route('dashboard') }}" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>

        <h1 class="fw-bold mb-3">
            Workflow Tracker
        </h1>

        <div class="filter-pills mb-4">

            <a href="{{ route('workflow.tracker.index') }}" class="filter-pill {{ $statusFilter === '' ? 'active' : '' }}">
                All
            </a>

            @foreach ($statuses as $status)

                <a href="{{ route('workflow.tracker.index', ['status' => $status->value]) }}" class="filter-pill {{ $statusFilter === $status->value ? 'active' : '' }}">
                    {{ $status->label() }}
                </a>

            @endforeach

        </div>

        @if ($projects->isEmpty())

            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    No projects match this view.
                </div>
            </div>

        @else

            @php
                $pillClass = fn (string $status) => match ($status) {
                    'DRAFT' => 'draft',
                    'RETURNED_FOR_REVISION' => 'clarify',
                    'PENDING_APPROVAL' => 'review',
                    'MONITORING_MAINTENANCE' => 'completed',
                    'REJECTED', 'CLOSED' => 'terminal',
                    default => 'progress',
                };
                $initials = fn (?string $name) => $name
                    ? collect(explode(' ', trim($name)))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('')
                    : '—';
            @endphp

            <div class="ticket-list mb-3">

                @foreach ($projects as $project)

                    @php
                        $status = \App\Enums\ProjectStatus::from($project->status);
                        $owner = $ownerResolver->resolve($project);
                        $openRoute = $status->actionRoute() ?? 'software-projects.show';
                        $tone = match (true) {
                            $owner->isTerminal => 'muted',
                            in_array($project->status, ['PENDING_APPROVAL', 'RETURNED_FOR_REVISION'], true) => 'warning',
                            $project->status === 'MONITORING_MAINTENANCE' => 'success',
                            default => 'forward',
                        };
                    @endphp

                    <a href="{{ route($openRoute, $project) }}" class="ticket-card tone-{{ $tone }}">

                        <div class="ticket-main">
                            <div class="ticket-ref">{{ $project->project_id }}</div>
                            <div class="ticket-title">{{ $project->project_name }}</div>
                            <div class="ticket-meta">
                                <span class="status-pill {{ $pillClass($project->status) }}">
                                    {{ $status->label() }}
                                </span>
                                <span class="owner-badge {{ $owner->isDepartmentWide ? 'team' : '' }}" style="padding:5px 12px 5px 5px;font-size:13px;">
                                    <span class="avatar-sm" style="width:23px;height:23px;font-size:10.5px;">
                                        {{ $owner->isDepartmentWide ? 'MST' : $initials($owner->user?->name) }}
                                    </span>
                                    {{ $owner->label }}
                                </span>
                                <span>{{ $project->requestingDepartment->name ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="ticket-side">
                            <span class="ticket-date">Updated {{ $project->updated_at->diffForHumans() }}</span>
                            <span class="ticket-open">Open <i class="fa-solid fa-chevron-right"></i></span>
                        </div>

                    </a>

                @endforeach

            </div>

            {{ $projects->links() }}

        @endif

@endsection
