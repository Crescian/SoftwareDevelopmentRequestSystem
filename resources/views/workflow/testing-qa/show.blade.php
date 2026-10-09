@extends('layouts.app')

@section('title')
Testing & QA — {{ $project->project_id }}
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
                <a href="{{ route('workflow.testing-qa.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to Queue
                </a>
            </div>

        </div>

        @foreach ($project->testPlans as $testPlan)

            <div class="card mb-4">

                <div class="card-header fw-bold">
                    {{ $testPlan->test_plan_number }} &middot; {{ $testPlan->title }}
                </div>

                <div class="card-body">

                    @forelse ($testPlan->testCases as $testCase)

                        <div class="border rounded p-3 mb-3">

                            <div class="d-flex justify-content-between">
                                <strong>{{ $testCase->test_case_number }} &middot; {{ $testCase->title }}</strong>
                                <span class="badge {{ match($testCase->status) { 'PASSED' => 'bg-success', 'FAILED' => 'bg-danger', 'BLOCKED' => 'bg-warning', default => 'bg-secondary' } }}">
                                    {{ $testCase->status }}
                                </span>
                            </div>

                            <p class="text-muted mb-1"><small>Expected: {{ $testCase->expected_result }}</small></p>

                            @if ($testCase->actual_result)
                                <p class="mb-2"><small>Actual: {{ $testCase->actual_result }}</small></p>
                            @endif

                            @can('manageTestingQa', $project)

                                <form method="POST" action="{{ route('workflow.testing-qa.test-cases.execute', [$project, $testCase]) }}" class="row g-2 mb-2">
                                    @csrf
                                    <div class="col-md-3">
                                        <select name="status" class="form-select form-select-sm" required>
                                            <option value="PASSED">Passed</option>
                                            <option value="FAILED">Failed</option>
                                            <option value="BLOCKED">Blocked</option>
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <input type="text" name="actual_result" class="form-control form-control-sm" placeholder="Actual result" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Record</button>
                                    </div>
                                </form>

                            @endcan

                            {{-- DEFECTS FOR THIS TEST CASE --}}

                            @foreach ($testCase->defects as $defect)

                                <div class="ms-3 border-start ps-3 mb-2">

                                    <div class="d-flex justify-content-between">
                                        <span><strong>{{ $defect->defect_number }}</strong> &middot; {{ $defect->title }} <span class="badge bg-light text-dark">{{ $defect->severity }}</span></span>
                                        <span class="badge {{ $defect->status === 'VERIFIED' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $defect->status }}
                                        </span>
                                    </div>

                                    @if ($defect->resolution)
                                        <small class="text-muted">Resolution: {{ $defect->resolution }}</small><br>
                                    @endif

                                    @can('manageTestingQa', $project)

                                        <form method="POST" action="{{ route('workflow.testing-qa.defects.status', [$project, $defect]) }}" class="d-flex gap-2 mt-1">
                                            @csrf
                                            <select name="status" class="form-select form-select-sm" style="width:auto;">
                                                <option value="IN_PROGRESS">In Progress</option>
                                                <option value="RESOLVED">Resolved</option>
                                                <option value="VERIFIED">Verified</option>
                                                <option value="REOPENED">Reopened</option>
                                            </select>
                                            <input type="text" name="resolution" class="form-control form-control-sm" placeholder="Resolution (required if Resolved)">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                                        </form>

                                    @endcan

                                </div>

                            @endforeach

                            @can('manageTestingQa', $project)

                                @if ($testCase->status === 'FAILED')

                                    <form method="POST" action="{{ route('workflow.testing-qa.defects.store', [$project, $testCase]) }}" class="row g-2 mt-2">
                                        @csrf
                                        <div class="col-md-4">
                                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Defect title" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" name="description" class="form-control form-control-sm" placeholder="Description" required>
                                        </div>
                                        <div class="col-md-2">
                                            <select name="severity" class="form-select form-select-sm">
                                                <option value="LOW">Low</option>
                                                <option value="MEDIUM" selected>Medium</option>
                                                <option value="HIGH">High</option>
                                                <option value="CRITICAL">Critical</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">Log Defect</button>
                                        </div>
                                    </form>

                                @endif

                            @endcan

                        </div>

                    @empty

                        <p class="text-muted">No test cases yet.</p>

                    @endforelse

                    @can('manageTestingQa', $project)

                        <hr>

                        <form method="POST" action="{{ route('workflow.testing-qa.test-cases.store', [$project, $testPlan]) }}" class="row g-2">
                            @csrf
                            <div class="col-md-2">
                                <input type="text" name="test_case_number" class="form-control" placeholder="TC-001" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="title" class="form-control" placeholder="Title" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="test_type" class="form-control" placeholder="Type (e.g. FUNCTIONAL)" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="test_steps" class="form-control" placeholder="Steps" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="expected_result" class="form-control" placeholder="Expected result" required>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-outline-primary w-100">Add</button>
                            </div>
                        </form>

                    @endcan

                </div>

            </div>

        @endforeach

        @can('manageTestingQa', $project)

            <div class="card mb-4">

                <div class="card-header fw-bold">
                    New Test Plan
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('workflow.testing-qa.test-plans.store', $project) }}" class="row g-2">
                        @csrf
                        <div class="col-md-3">
                            <input type="text" name="test_plan_number" class="form-control" placeholder="TP-001" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="title" class="form-control" placeholder="Title" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-outline-primary w-100">Add Test Plan</button>
                        </div>
                    </form>

                </div>

            </div>

            <div class="card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div class="text-muted">
                        @if ($openDefectCount > 0)
                            {{ $openDefectCount }} defect(s) not yet verified.
                        @else
                            All defects verified.
                        @endif
                    </div>

                    <form method="POST" action="{{ route('workflow.testing-qa.advance', $project) }}">
                        @csrf
                        <button type="submit" class="btn btn-success" {{ $openDefectCount > 0 ? 'disabled' : '' }}>
                            Mark Testing Passed &rarr; Proceed to UAT
                        </button>
                    </form>

                </div>

            </div>

        @endcan

@endsection
