@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
@include('dashboard._stat-styles')
@endsection

@section('content')

    @php
        $user = auth()->user();
        $firstName = collect(explode(' ', trim($user->name ?? '')))->first() ?: 'there';
        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        $icons = [
            'Initial Review' => ['bi-clipboard-check', 'blue'],
            'Feasibility Assessment' => ['bi-graph-up', 'amber'],
            'Requirements & Design' => ['bi-diagram-3', 'purple'],
            'Development' => ['bi-code-slash', 'blue'],
            'Testing & QA' => ['bi-bug', 'red'],
            'User Acceptance Testing' => ['bi-person-check', 'amber'],
            'Deployment' => ['bi-cloud-upload', 'purple'],
            'Monitoring & Maintenance' => ['bi-activity', 'green'],
        ];
    @endphp

    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <div class="page-eyebrow">{{ $greeting }},</div>
            <h1 class="page-title">{{ $user->name }}!</h1>
            <p class="page-sub">Here's what's waiting across the workflow queues.</p>
        </div>
    </div>

    @include('dashboard._needs-your-decision')

    <div class="row g-3 mb-4">
        @foreach ($queues as $label => $queue)
            <div class="col-6 col-lg-3">
                <a href="{{ route($queue['route']) }}" class="text-decoration-none">
                    <div class="card-soft stat-card">
                        <div>
                            <div class="num" style="color: var(--nd)">{{ $queue['count'] }}</div>
                            <div class="label" style="color: var(--nd)">{{ $label }}</div>
                            <div class="hint">Awaiting action</div>
                        </div>
                        <div class="stat-icon {{ $icons[$label][1] }}"><i class="bi {{ $icons[$label][0] }}"></i></div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <a href="{{ route('workflow.development.index') }}" class="text-decoration-none">
                <div class="card-soft stat-card">
                    <div>
                        <div class="num" style="color: var(--nd)">{{ $unassigned['development_tasks'] }}</div>
                        <div class="label" style="color: var(--nd)">Unassigned Tasks</div>
                        <div class="hint">Need a developer</div>
                    </div>
                    <div class="stat-icon red"><i class="bi bi-person-dash"></i></div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('workflow.testing-qa.index') }}" class="text-decoration-none">
                <div class="card-soft stat-card">
                    <div>
                        <div class="num" style="color: var(--nd)">{{ $unassigned['defects'] }}</div>
                        <div class="label" style="color: var(--nd)">Unassigned Defects</div>
                        <div class="hint">Need a QA owner</div>
                    </div>
                    <div class="stat-icon red"><i class="bi bi-person-dash"></i></div>
                </div>
            </a>
        </div>
    </div>

    <div class="card-soft mb-4">
        <div class="section-head">
            <h2>Recent Activity</h2>
        </div>

        @if ($recentActivity->isEmpty())
            <div class="empty-state">
                <i class="bi bi-clock-history"></i>
                No status changes recorded yet.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-requests">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Project</th>
                            <th>Change</th>
                            <th>By</th>
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentActivity as $entry)
                            <tr>
                                <td class="ref">{{ $entry->softwareProject?->project_id }}</td>
                                <td class="title">{{ $entry->softwareProject?->project_name }}</td>
                                <td>
                                    {{ ucwords(strtolower(str_replace('_', ' ', $entry->from_status ?? '—'))) }}
                                    <i class="bi bi-arrow-right mx-1"></i>
                                    {{ ucwords(strtolower(str_replace('_', ' ', $entry->to_status))) }}
                                </td>
                                <td>{{ $entry->changedBy?->name ?? '—' }}</td>
                                <td class="text-nowrap">{{ optional($entry->changed_at)->format('M j, Y g:ia') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection
