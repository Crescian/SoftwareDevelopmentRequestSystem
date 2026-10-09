@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
@include('dashboard._stat-styles')
@endsection

@section('content')

    @php
        $user = auth()->user();
        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    @endphp

    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <div class="page-eyebrow">{{ $greeting }},</div>
            <h1 class="page-title">{{ $user->name }}!</h1>
            <p class="page-sub">{{ $dashboardRole->label() }} dashboard — here's what needs your attention.</p>
        </div>
    </div>

    @include('dashboard._needs-your-decision')

    @if ($dashboardRole === \App\Enums\DashboardRole::ProjectManager)

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card-soft stat-card">
                    <div>
                        <div class="num">{{ $managedProjectCount }}</div>
                        <div class="label">Projects Managed</div>
                    </div>
                    <div class="stat-icon blue"><i class="bi bi-diagram-3"></i></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card-soft stat-card">
                    <div>
                        <div class="num">{{ $pendingDecisions->count() }}</div>
                        <div class="label">Awaiting Your Decision</div>
                    </div>
                    <div class="stat-icon amber"><i class="bi bi-hourglass-split"></i></div>
                </div>
            </div>
        </div>

        <div class="card-soft mb-4">
            <div class="section-head">
                <h2>Awaiting Your Decision</h2>
            </div>

            @if ($pendingDecisions->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-check2-circle"></i>
                    Nothing is waiting on you right now.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-requests">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Type</th>
                                <th>Title / Ref</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingDecisions as $item)
                                <tr>
                                    <td class="ref">{{ $item['project']?->project_id }}</td>
                                    <td>{{ $item['type'] }}</td>
                                    <td class="title">{{ $item['title'] }}</td>
                                    <td class="text-end">
                                        @if ($item['project'])
                                            <a href="{{ route('workflow.requirements-design.show', $item['project']) }}" class="view-link">Review</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    @endif

    @if ($dashboardRole === \App\Enums\DashboardRole::Developer)

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card-soft stat-card">
                    <div>
                        <div class="num">{{ $taskStats['todo'] }}</div>
                        <div class="label">To Do</div>
                    </div>
                    <div class="stat-icon blue"><i class="bi bi-list-task"></i></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card-soft stat-card">
                    <div>
                        <div class="num">{{ $taskStats['in_progress'] }}</div>
                        <div class="label">In Progress</div>
                    </div>
                    <div class="stat-icon amber"><i class="bi bi-arrow-repeat"></i></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card-soft stat-card">
                    <div>
                        <div class="num">{{ $taskStats['blocked'] }}</div>
                        <div class="label">Blocked</div>
                    </div>
                    <div class="stat-icon red"><i class="bi bi-exclamation-circle"></i></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card-soft stat-card">
                    <div>
                        <div class="num">{{ $taskStats['done'] }}</div>
                        <div class="label">Done</div>
                    </div>
                    <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
                </div>
            </div>
        </div>

        <div class="card-soft mb-4">
            <div class="section-head">
                <h2>My Open Tasks</h2>
            </div>

            @if ($openTasks->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    No open tasks assigned to you.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-requests">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Due</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($openTasks as $task)
                                <tr>
                                    <td class="title">{{ $task->task_code }} — {{ $task->title }}</td>
                                    <td class="ref">{{ $task->softwareProject?->project_id }}</td>
                                    <td>{{ ucwords(strtolower(str_replace('_', ' ', $task->status))) }}</td>
                                    <td class="text-nowrap">{{ optional($task->planned_end_date)->format('M j, Y') ?? '—' }}</td>
                                    <td class="text-end">
                                        @if ($task->softwareProject)
                                            <a href="{{ route('workflow.development.show', $task->softwareProject) }}" class="view-link">Open</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    @endif

    @if ($dashboardRole === \App\Enums\DashboardRole::Qa)

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card-soft stat-card">
                    <div>
                        <div class="num">{{ $defectStats['open'] }}</div>
                        <div class="label">Open Defects</div>
                    </div>
                    <div class="stat-icon red"><i class="bi bi-bug"></i></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card-soft stat-card">
                    <div>
                        <div class="num">{{ $defectStats['in_progress'] }}</div>
                        <div class="label">In Progress</div>
                    </div>
                    <div class="stat-icon amber"><i class="bi bi-arrow-repeat"></i></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card-soft stat-card">
                    <div>
                        <div class="num">{{ $defectStats['reopened'] }}</div>
                        <div class="label">Reopened</div>
                    </div>
                    <div class="stat-icon purple"><i class="bi bi-arrow-counterclockwise"></i></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card-soft stat-card">
                    <div>
                        <div class="num">{{ $pendingTestCases }}</div>
                        <div class="label">Test Cases Pending</div>
                        <div class="hint">Across projects in Testing &amp; QA</div>
                    </div>
                    <div class="stat-icon blue"><i class="bi bi-clipboard-check"></i></div>
                </div>
            </div>
        </div>

        <div class="card-soft mb-4">
            <div class="section-head">
                <h2>My Open Defects</h2>
            </div>

            @if ($openDefects->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-check2-circle"></i>
                    No open defects assigned to you.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-requests">
                        <thead>
                            <tr>
                                <th>Defect</th>
                                <th>Project</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($openDefects as $defect)
                                <tr>
                                    <td class="title">{{ $defect->defect_number }} — {{ $defect->title }}</td>
                                    <td class="ref">{{ $defect->softwareProject?->project_id }}</td>
                                    <td>{{ ucwords(strtolower($defect->severity)) }}</td>
                                    <td>{{ ucwords(strtolower(str_replace('_', ' ', $defect->status))) }}</td>
                                    <td class="text-end">
                                        @if ($defect->softwareProject)
                                            <a href="{{ route('workflow.testing-qa.show', $defect->softwareProject) }}" class="view-link">Open</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    @endif

@endsection
