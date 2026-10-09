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
            <p class="page-sub">System-wide overview across every project and department.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn-navy">
            <i class="bi bi-people"></i> Manage Users
        </a>
    </div>

    @include('dashboard._needs-your-decision')

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card-soft stat-card">
                <div>
                    <div class="num">{{ $totalProjects }}</div>
                    <div class="label">Total Projects</div>
                </div>
                <div class="stat-icon blue"><i class="bi bi-journal-text"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-soft stat-card">
                <div>
                    <div class="num">{{ $stats['in_progress'] }}</div>
                    <div class="label">In Progress</div>
                </div>
                <div class="stat-icon amber"><i class="bi bi-hourglass-split"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-soft stat-card">
                <div>
                    <div class="num">{{ $stats['completed'] }}</div>
                    <div class="label">Completed</div>
                </div>
                <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-soft stat-card">
                <div>
                    <div class="num">{{ $totalUsers }}</div>
                    <div class="label">Active Users</div>
                </div>
                <div class="stat-icon purple"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card-soft mb-4">
                <div class="section-head">
                    <h2>Projects by Department</h2>
                </div>

                @if ($departmentBreakdown->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        No projects yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-requests">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th class="text-end">Projects</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($departmentBreakdown as $row)
                                    <tr>
                                        <td class="title">{{ $row->department }}</td>
                                        <td class="text-end">{{ $row->total }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-7">
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
                                        <td>{{ ucwords(strtolower(str_replace('_', ' ', $entry->to_status))) }}</td>
                                        <td>{{ $entry->changedBy?->name ?? '—' }}</td>
                                        <td class="text-nowrap">{{ optional($entry->changed_at)->format('M j, Y g:ia') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
