@extends('layouts.app')

@section('title')
Monitoring & Maintenance — {{ $project->project_id }}
@endsection

@section('content')

        @if (session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif

        @if ($errors->any())

            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>

        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">
                    {{ $project->project_name }}
                </h1>

                <div class="text-muted">
                    {{ $project->project_id }} &middot; live in operation
                </div>

            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('workflow.change-requests.show', $project) }}" class="btn btn-outline-primary">
                    <i class="fa-solid fa-pen-to-square"></i> Change Requests
                </a>
                <a href="{{ route('software-projects.show', $project) }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-file-lines"></i> View Request
                </a>
                <a href="{{ route('workflow.monitoring.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to Queue
                </a>
            </div>

        </div>

        {{-- MAINTENANCE --}}

        <div class="card mb-4">

            <div class="card-header fw-bold">
                Maintenance Requests
            </div>

            <div class="card-body">

                @forelse ($project->maintenanceRecords as $record)

                    <div class="border rounded p-2 mb-2">

                        <div class="d-flex justify-content-between">
                            <strong>{{ $record->maintenance_number }} &middot; {{ $record->title }}</strong>
                            <span class="badge {{ match($record->status) { 'RESOLVED', 'CLOSED' => 'bg-success', 'IN_PROGRESS' => 'bg-warning', default => 'bg-secondary' } }}">
                                {{ $record->status }}
                            </span>
                        </div>

                        <p class="text-muted mb-1"><small>{{ $record->maintenance_type }} &middot; {{ $record->priority }}</small></p>
                        <p class="mb-1">{{ $record->description }}</p>

                        @if ($record->resolution)
                            <p class="mb-1"><em>Resolution: {{ $record->resolution }}</em></p>
                        @endif

                        @can('manageMonitoring', $project)

                            @if ($record->status !== 'CLOSED')

                                <form method="POST" action="{{ route('workflow.monitoring.maintenance-records.status', [$project, $record]) }}" class="d-flex gap-2 mt-2">
                                    @csrf
                                    <select name="status" class="form-select form-select-sm" style="width:auto;">
                                        <option value="IN_PROGRESS">In Progress</option>
                                        <option value="RESOLVED">Resolved</option>
                                        <option value="CLOSED">Closed</option>
                                    </select>
                                    <input type="text" name="resolution" class="form-control form-control-sm" placeholder="Resolution (required if resolved)">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                                </form>

                            @endif

                        @endcan

                    </div>

                @empty

                    <p class="text-muted">No maintenance requests yet.</p>

                @endforelse

                @can('reportMaintenance', $project)

                    <hr>

                    <form method="POST" action="{{ route('workflow.monitoring.maintenance-records.store', $project) }}" class="row g-2">
                        @csrf
                        <div class="col-md-2">
                            <select name="maintenance_type" class="form-select" required>
                                <option value="CORRECTIVE">Corrective</option>
                                <option value="PREVENTIVE">Preventive</option>
                                <option value="ENHANCEMENT">Enhancement</option>
                                <option value="ADAPTIVE">Adaptive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="title" class="form-control" placeholder="Title" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="description" class="form-control" placeholder="Description" required>
                        </div>
                        <div class="col-md-1">
                            <select name="priority" class="form-select">
                                <option value="LOW">Low</option>
                                <option value="MEDIUM" selected>Med</option>
                                <option value="HIGH">High</option>
                                <option value="CRITICAL">Crit</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-outline-primary w-100">Report</button>
                        </div>
                    </form>

                @endcan

            </div>

        </div>

        {{-- MONITORING RECORDS --}}

        <div class="card mb-4">

            <div class="card-header fw-bold">
                Monitoring Records
            </div>

            <div class="card-body">

                @forelse ($project->monitoringRecords as $record)

                    <div class="border rounded p-2 mb-2">
                        <strong>{{ $record->record_number }}</strong> &middot; {{ $record->monitoring_date->format('Y-m-d') }} &middot; {{ $record->monitoring_type }}
                        <div class="text-muted">
                            <small>
                                Uptime: {{ $record->uptime_percentage ?? 'n/a' }}% &middot;
                                Avg response: {{ $record->average_response_time_ms ?? 'n/a' }}ms &middot;
                                Incidents: {{ $record->incidents_count }}
                            </small>
                        </div>
                    </div>

                @empty

                    <p class="text-muted">No monitoring records yet.</p>

                @endforelse

                @can('manageMonitoring', $project)

                    <hr>

                    <form method="POST" action="{{ route('workflow.monitoring.monitoring-records.store', $project) }}" class="row g-2">
                        @csrf
                        <div class="col-md-2">
                            <input type="text" name="record_number" class="form-control" placeholder="MON-001" required>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="monitoring_date" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="monitoring_type" class="form-control" placeholder="Type" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" name="uptime_percentage" class="form-control" placeholder="Uptime %">
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="incidents_count" class="form-control" placeholder="Incidents">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary w-100">Add</button>
                        </div>
                    </form>

                @endcan

            </div>

        </div>

        {{-- KPIS --}}

        <div class="card">

            <div class="card-header fw-bold">
                KPIs
            </div>

            <div class="card-body">

                @forelse ($project->kpis as $kpi)

                    <div class="d-flex justify-content-between border rounded p-2 mb-2">
                        <span><strong>{{ $kpi->kpi_code }}</strong> &middot; {{ $kpi->kpi_name }} ({{ $kpi->measurement_period }})</span>
                        <span>
                            {{ $kpi->actual_value ?? '?' }} / {{ $kpi->target_value ?? '?' }} {{ $kpi->unit }}
                            @if ($kpi->status)
                                <span class="badge {{ $kpi->status === 'MET' ? 'bg-success' : 'bg-secondary' }}">{{ $kpi->status }}</span>
                            @endif
                        </span>
                    </div>

                @empty

                    <p class="text-muted">No KPIs recorded yet.</p>

                @endforelse

                @can('manageMonitoring', $project)

                    <hr>

                    <form method="POST" action="{{ route('workflow.monitoring.kpis.store', $project) }}" class="row g-2">
                        @csrf
                        <div class="col-md-2">
                            <input type="text" name="kpi_code" class="form-control" placeholder="KPI-01" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="kpi_name" class="form-control" placeholder="Name" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="measurement_period" class="form-control" placeholder="Q1 2027">
                        </div>
                        <div class="col-md-1">
                            <input type="number" step="0.01" name="target_value" class="form-control" placeholder="Target">
                        </div>
                        <div class="col-md-1">
                            <input type="number" step="0.01" name="actual_value" class="form-control" placeholder="Actual">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">Status</option>
                                <option value="MET">Met</option>
                                <option value="NOT_MET">Not Met</option>
                                <option value="IN_PROGRESS">In Progress</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-outline-primary w-100">Add</button>
                        </div>
                    </form>

                @endcan

            </div>

        </div>

@endsection
