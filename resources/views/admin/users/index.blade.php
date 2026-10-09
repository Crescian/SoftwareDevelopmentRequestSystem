@extends('layouts.app')

@section('title', 'Manage Users')

@section('styles')
@include('dashboard._stat-styles')
@include('software-projects._tracker-styles')
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('dashboard') }}" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <div class="page-eyebrow">System Administration</div>
            <h1 class="page-title">Manage Users</h1>
            <p class="page-sub">Assign which dashboard each user lands on.</p>
        </div>
    </div>

    <div class="card-soft">
        <div class="table-responsive">
            <table class="table table-requests">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Dashboard</th>
                        <th class="text-end">Change To</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="title">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->department ?? '—' }}</td>
                            <td><span class="status-pill progress">{{ $user->effectiveDashboardRole()->label() }}</span></td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="d-flex gap-2 justify-content-end">
                                    @csrf
                                    <select name="dashboard_role" class="form-select form-select-sm" style="width:auto">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->value }}" @selected($user->dashboard_role === $role)>
                                                {{ $role->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-navy" style="padding:6px 14px">Save</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
