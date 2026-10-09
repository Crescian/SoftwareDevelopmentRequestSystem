@extends('layouts.app')

@section('title', 'My Requests')

@section('styles')
@include('software-projects._tracker-styles')
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

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h1 class="fw-bold mb-0">
                My Requests
            </h1>

            <a href="{{ route('software-projects.welcome') }}" class="btn btn-primary">
                New Request
            </a>

        </div>

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
