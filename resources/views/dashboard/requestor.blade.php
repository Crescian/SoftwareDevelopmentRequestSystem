@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
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

    .section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 22px 12px;
    }

    .section-head h2 {
        font-size: 16px;
        font-weight: 800;
        margin: 0;
    }

    .section-head a {
        font-size: 12px;
        font-weight: 700;
        color: var(--nl);
        text-decoration: none;
    }

    .table-requests {
        margin: 0;
        font-size: 13px;
    }

    .table-requests thead th {
        font-size: 11px;
        font-weight: 700;
        color: var(--tm);
        text-transform: uppercase;
        letter-spacing: .4px;
        border-bottom: 1px solid var(--bd);
        padding: 10px 22px;
        background: #fafbfd;
        white-space: nowrap;
    }

    .table-requests tbody td {
        padding: 13px 22px;
        vertical-align: middle;
        border-bottom: 1px solid #eef1f5;
    }

    .table-requests tbody tr:last-child td {
        border-bottom: none;
    }

    .table-requests .ref {
        font-weight: 700;
        color: var(--tm);
        white-space: nowrap;
    }

    .table-requests .title {
        font-weight: 700;
        color: var(--nd);
    }

    .table-requests .view-link {
        font-size: 12px;
        font-weight: 700;
        color: var(--nl);
        text-decoration: none;
    }

    .empty-state {
        padding: 40px 22px;
        text-align: center;
        color: var(--tm);
        font-size: 13px;
    }

    .empty-state i {
        font-size: 32px;
        color: var(--bd);
        display: block;
        margin-bottom: 8px;
    }

    .cta-card {
        background: var(--acl);
        border: 1px solid #cfe1ff;
        border-radius: 16px;
        padding: 24px;
        display: flex;
        gap: 18px;
        align-items: center;
        height: 100%;
    }

    .cta-card .icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: #fff;
        color: var(--nl);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        flex-shrink: 0;
    }

    .cta-card h3 {
        font-size: 15px;
        font-weight: 800;
        margin: 0 0 2px;
    }

    .cta-card p {
        font-size: 12px;
        color: var(--tm);
        margin: 0 0 12px;
    }

    .links-card {
        padding: 20px 22px;
        height: 100%;
    }

    .links-card h3 {
        font-size: 15px;
        font-weight: 800;
        margin: 0 0 12px;
    }

    .links-card a {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        font-weight: 600;
        color: var(--nd);
        text-decoration: none;
        padding: 7px 0;
    }

    .links-card a .li-icon {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        background: var(--nd);
        color: #fff;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .links-card a .ext {
        margin-left: auto;
        color: var(--tm);
        font-size: 12px;
    }

    .links-card a:hover {
        color: var(--nl);
    }
@endsection

@section('content')

    @php
        $user = auth()->user();
        $firstName = collect(explode(' ', trim($user->name ?? '')))->first() ?: 'there';
        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        $pillClass = function (string $status): array {
            $s = strtoupper($status);
            return match (true) {
                in_array($s, \App\Http\Controllers\DashboardController::STATUS_COMPLETED)         => ['completed', 'Completed'],
                in_array($s, \App\Http\Controllers\DashboardController::STATUS_FOR_CLARIFICATION) => ['clarify', 'For Clarification'],
                in_array($s, \App\Http\Controllers\DashboardController::STATUS_FOR_REVIEW)        => ['review', 'For Review'],
                $s === 'DRAFT'                                                                   => ['draft', 'Draft'],
                default                                                                          => ['progress', ucwords(strtolower(str_replace('_', ' ', $s)))],
            };
        };
    @endphp

    {{-- Greeting --}}
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

    {{-- Stat cards --}}
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

    {{-- Recent requests --}}
    <div class="card-soft mb-4" id="recent">
        <div class="section-head">
            <h2>My Recent Requests</h2>
            <a href="{{ route('software-projects.index') }}">View All</a>
        </div>

        @if ($recent->isEmpty())
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                You haven't submitted any requests yet.
                <a href="{{ route('software-projects.welcome') }}" class="fw-bold ms-1">Create your first request</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-requests">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Project / Request Title</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recent as $project)
                            @php [$cls, $label] = $pillClass($project->status ?? 'DRAFT'); @endphp
                            <tr>
                                <td class="ref">{{ $project->project_id }}</td>
                                <td class="title">{{ $project->project_name }}</td>
                                <td class="text-nowrap">{{ optional($project->submitted_at ?? $project->created_at)->format('M j, Y') }}</td>
                                <td><span class="status-pill {{ $cls }}">{{ $label }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('software-projects.show', $project) }}" class="view-link">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- CTA + useful links --}}
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="cta-card">
                <div class="icon"><i class="bi bi-file-earmark-text"></i></div>
                <div>
                    <h3>Need a new system?</h3>
                    <p>Submit a software development request.</p>
                    <a href="{{ route('software-projects.welcome') }}" class="btn-navy" style="padding:8px 16px;font-size:13px">
                        <i class="bi bi-plus-lg"></i> New Request
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card-soft links-card">
                <h3>Useful Links</h3>
                <a href="#"><span class="li-icon"><i class="bi bi-book"></i></span> Request Guidelines <i class="bi bi-box-arrow-up-right ext"></i></a>
                <a href="#"><span class="li-icon"><i class="bi bi-file-earmark"></i></span> Templates &amp; Forms <i class="bi bi-box-arrow-up-right ext"></i></a>
                <a href="#"><span class="li-icon"><i class="bi bi-question-lg"></i></span> FAQs <i class="bi bi-box-arrow-up-right ext"></i></a>
                <a href="#"><span class="li-icon"><i class="bi bi-envelope"></i></span> Contact MST <i class="bi bi-box-arrow-up-right ext"></i></a>
            </div>
        </div>
    </div>

@endsection
