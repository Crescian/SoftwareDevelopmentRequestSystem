@extends('layouts.app')

@section('title', 'My Requests')

@section('styles')
@include('software-projects._tracker-styles')

    .stat-card {
        padding: 18px 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        height: 100%;
    }

    .stat-card .num {
        font-weight: 900;
        font-size: 30px;
        line-height: 1;
        letter-spacing: -.5px;
    }

    .stat-card .label {
        font-size: 13px;
        font-weight: 700;
        margin-top: 8px;
    }

    .stat-card .hint {
        font-size: 11px;
        color: var(--tm);
        margin-top: 2px;
    }

    .stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    .stat-icon.blue   { background: var(--acl); color: var(--nl); }
    .stat-icon.amber  { background: #fff4d6; color: #d9880c; }
    .stat-icon.red    { background: #fde8e8; color: #e5484d; }
    .stat-icon.green  { background: #e3f7ea; color: #1b9a4b; }
@endsection

@section('content')

        @if (session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif

        @if ($isHome)

            @php
                $user = auth()->user();
                $firstName = collect(explode(' ', trim($user->name ?? '')))->first() ?: 'there';
                $hour = (int) now()->format('G');
                $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
            @endphp

            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
                <div>
                    <div class="page-eyebrow">{{ $greeting }},</div>
                    <h1 class="page-title">{{ $user->name }}!</h1>
                    <p class="page-sub">Have an idea or a system you need? Submit a request and let's build it together.</p>
                </div>
                <a href="{{ route('software-projects.welcome') }}" class="btn-navy">
                    <i class="bi bi-plus-lg"></i> New Request
                </a>
            </div>

            @include('dashboard._needs-your-decision')

            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="card-soft stat-card">
                        <div>
                            <div class="num">{{ $stats['in_progress'] }}</div>
                            <div class="label">My Requests</div>
                            <div class="hint">In Progress</div>
                        </div>
                        <div class="stat-icon blue"><i class="bi bi-journal-text"></i></div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card-soft stat-card">
                        <div>
                            <div class="num">{{ $stats['for_review'] }}</div>
                            <div class="label">For Review</div>
                            <div class="hint">Waiting for approval</div>
                        </div>
                        <div class="stat-icon amber"><i class="bi bi-hourglass-split"></i></div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card-soft stat-card">
                        <div>
                            <div class="num">{{ $stats['for_clarification'] }}</div>
                            <div class="label">For Clarification</div>
                            <div class="hint">Action needed</div>
                        </div>
                        <div class="stat-icon red"><i class="bi bi-exclamation-circle"></i></div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card-soft stat-card">
                        <div>
                            <div class="num">{{ $stats['completed'] }}</div>
                            <div class="label">Completed</div>
                            <div class="hint">Successfully delivered</div>
                        </div>
                        <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
                    </div>
                </div>
            </div>

        @else

            <a href="{{ route('dashboard') }}" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h1 class="fw-bold mb-0">
                    My Requests
                </h1>

                <a href="{{ route('software-projects.welcome') }}" class="btn btn-primary">
                    New Request
                </a>

            </div>

        @endif

        @if ($projects->isEmpty())

            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    You haven't submitted any requests yet.
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
                        $owner = $ownerResolver->resolve($project);
                        $tone = match (true) {
                            $owner->isTerminal => 'muted',
                            in_array($project->status, ['PENDING_APPROVAL', 'RETURNED_FOR_REVISION'], true) => 'warning',
                            $project->status === 'MONITORING_MAINTENANCE' => 'success',
                            default => 'forward',
                        };
                    @endphp

                    <a href="{{ route('software-projects.show', $project) }}" class="ticket-card tone-{{ $tone }}">

                        <div class="ticket-main">
                            <div class="ticket-ref">{{ $project->project_id }}</div>
                            <div class="ticket-title">{{ $project->project_name }}</div>
                            <div class="ticket-meta">
                                <span class="status-pill {{ $pillClass($project->status) }}">
                                    {{ \App\Enums\ProjectStatus::from($project->status)->label() }}
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
                            <span class="ticket-date">{{ $project->created_at->format('M j, Y') }}</span>
                            <span class="ticket-open">Open <i class="fa-solid fa-chevron-right"></i></span>
                        </div>

                    </a>

                @endforeach

            </div>

            {{ $projects->links() }}

        @endif

@endsection
