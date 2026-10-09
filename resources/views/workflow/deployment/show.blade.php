@extends('layouts.app')

@section('title')
Deployment — {{ $project->project_id }}
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
                    {{ $project->project_id }}
                </div>

            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('software-projects.show', $project) }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-file-lines"></i> View Request
                </a>
                <a href="{{ route('workflow.deployment.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to Queue
                </a>
            </div>

        </div>

        <div class="card mb-4">

            <div class="card-header fw-bold">
                Pre-Deployment Security Assessment
            </div>

            <div class="card-body">

                @forelse ($project->securityAssessments as $assessment)

                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $assessment->assessment_number }}</strong>
                            <div>
                                <span class="badge {{ $assessment->overall_result === 'FAIL' ? 'bg-danger' : 'bg-success' }}">
                                    {{ $assessment->overall_result }}
                                </span>
                                <span class="badge {{ $assessment->status === 'APPROVED' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $assessment->status }}
                                </span>
                            </div>
                        </div>
                        @if ($assessment->security_risks)
                            <p class="text-muted mb-0"><small>{{ $assessment->security_risks }}</small></p>
                        @endif
                    </div>

                @empty

                    <p class="text-muted">No pre-deployment security assessment yet.</p>

                @endforelse

                @can('manageDeployment', $project)

                    <hr>

                    <form method="POST" action="{{ route('workflow.deployment.security-assessments.store', $project) }}">
                        @csrf

                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <input type="text" name="assessment_number" class="form-control" placeholder="SEC-002" required>
                            </div>
                            <div class="col-md-8">
                                <select name="overall_result" class="form-select" required>
                                    <option value="PASS">Pass</option>
                                    <option value="PASS_WITH_CONDITIONS">Pass with Conditions</option>
                                    <option value="FAIL">Fail</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-2">
                            <textarea name="vulnerability_assessment" class="form-control" rows="2" placeholder="Vulnerability assessment"></textarea>
                        </div>

                        <div class="mb-2">
                            <textarea name="security_risks" class="form-control" rows="2" placeholder="Security risks"></textarea>
                        </div>

                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            Record Assessment
                        </button>

                    </form>

                @endcan

            </div>

        </div>

        @foreach ($project->deployments as $deployment)

            <div class="card mb-4">

                <div class="card-header d-flex justify-content-between">
                    <strong>{{ $deployment->deployment_number }} &middot; v{{ $deployment->version }} ({{ $deployment->environment }})</strong>
                    <span class="badge {{ match($deployment->status) { 'COMPLETED' => 'bg-success', 'FAILED' => 'bg-danger', default => 'bg-secondary' } }}">
                        {{ $deployment->status }}
                    </span>
                </div>

                <div class="card-body">

                    <p class="text-muted">{{ $deployment->release_summary }}</p>

                    <h6>Checklist</h6>

                    @forelse ($deployment->checklistItems as $item)

                        <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">

                            <div>
                                <strong>#{{ $item->item_no }}</strong> [{{ $item->category }}] {{ $item->item }}
                                @if ($item->is_required)
                                    <span class="badge bg-light text-dark">required</span>
                                @endif
                            </div>

                            @can('manageDeployment', $project)

                                <form method="POST" action="{{ route('workflow.deployment.checklist-items.check', [$project, $item]) }}" class="d-flex gap-1">
                                    @csrf
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="PENDING" {{ $item->status === 'PENDING' ? 'selected' : '' }}>Pending</option>
                                        <option value="DONE" {{ $item->status === 'DONE' ? 'selected' : '' }}>Done</option>
                                        <option value="FAILED" {{ $item->status === 'FAILED' ? 'selected' : '' }}>Failed</option>
                                        <option value="N_A" {{ $item->status === 'N_A' ? 'selected' : '' }}>N/A</option>
                                    </select>
                                </form>

                            @else

                                <span class="badge bg-secondary">{{ $item->status }}</span>

                            @endcan

                        </div>

                    @empty

                        <p class="text-muted">No checklist items yet.</p>

                    @endforelse

                    @can('manageDeployment', $project)

                        <form method="POST" action="{{ route('workflow.deployment.checklist-items.store', [$project, $deployment]) }}" class="row g-2 mb-4">
                            @csrf
                            <div class="col-md-3">
                                <input type="text" name="category" class="form-control" placeholder="Category" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="item" class="form-control" placeholder="Checklist item" required>
                            </div>
                            <div class="col-md-2">
                                <select name="is_required" class="form-select">
                                    <option value="1" selected>Required</option>
                                    <option value="0">Optional</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-outline-primary w-100">Add</button>
                            </div>
                        </form>

                    @endcan

                    <h6>Release Notes</h6>

                    @forelse ($deployment->releaseNotes as $note)

                        <div class="border rounded p-2 mb-2">
                            <strong>{{ $note->document_number }} &middot; {{ $note->title }} (v{{ $note->version }})</strong>
                            <p class="text-muted mb-0">{{ $note->release_summary }}</p>
                        </div>

                    @empty

                        <p class="text-muted">No release note yet.</p>

                    @endforelse

                    @can('manageDeployment', $project)

                        <form method="POST" action="{{ route('workflow.deployment.release-notes.store', [$project, $deployment]) }}" class="row g-2 mb-4">
                            @csrf
                            <div class="col-md-2">
                                <input type="text" name="document_number" class="form-control" placeholder="RN-001" required>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="title" class="form-control" placeholder="Title" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="version" class="form-control" placeholder="Version" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="release_summary" class="form-control" placeholder="Summary">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-outline-primary w-100">Add</button>
                            </div>
                        </form>

                        @if ($deployment->status !== 'COMPLETED')

                            <hr>

                            <h6>Record Outcome</h6>

                            <form method="POST" action="{{ route('workflow.deployment.outcome', [$project, $deployment]) }}">
                                @csrf
                                <div class="row g-2 align-items-start">
                                    <div class="col-md-3">
                                        <select name="outcome" class="form-select" required>
                                            <option value="SUCCESSFUL">Successful</option>
                                            <option value="FAILED">Failed / Rollback</option>
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <input type="text" name="remarks" class="form-control" placeholder="Remarks (required if failed)">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-success w-100">Submit</button>
                                    </div>
                                </div>
                            </form>

                        @endif

                    @endcan

                </div>

            </div>

        @endforeach

        @can('manageDeployment', $project)

            <div class="card">

                <div class="card-header fw-bold">
                    Plan a Deployment
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('workflow.deployment.deployments.store', $project) }}">
                        @csrf

                        <div class="row g-2 mb-2">
                            <div class="col-md-3">
                                <input type="text" name="deployment_number" class="form-control" placeholder="DEP-001" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="version" class="form-control" placeholder="Version" required>
                            </div>
                            <div class="col-md-3">
                                <select name="environment" class="form-select" required>
                                    <option value="STAGING">Staging</option>
                                    <option value="PRODUCTION">Production</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="date" name="planned_date" class="form-control">
                            </div>
                        </div>

                        <div class="mb-2">
                            <textarea name="release_summary" class="form-control" rows="2" placeholder="Release summary"></textarea>
                        </div>

                        <div class="mb-2">
                            <textarea name="rollback_plan" class="form-control" rows="2" placeholder="Rollback plan"></textarea>
                        </div>

                        <button type="submit" class="btn btn-outline-primary">
                            Plan Deployment
                        </button>

                    </form>

                </div>

            </div>

        @endcan

@endsection
