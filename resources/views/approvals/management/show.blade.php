@extends('layouts.app')

@section('title')
Management Approval — {{ $project->project_id }}
@endsection

@section('content')

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">
                    {{ $project->project_name }}
                </h1>

                <div class="text-muted">
                    {{ $project->project_id }} &middot; {{ $project->requestingDepartment->name }}
                </div>

            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('software-projects.show', $project) }}" class="btn btn-outline-primary">
                    <i class="fa-solid fa-clock-rotate-left"></i> View Project Timeline
                </a>
                <a href="{{ route('approvals.management.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to My Approvals
                </a>
            </div>

        </div>

        <div class="card mb-4">

            <div class="card-header fw-bold">
                Project Overview
            </div>

            <div class="card-body">

                <h6>Summary</h6>
                <p>{{ $project->project_summary }}</p>

                <h6>Business Problem</h6>
                <p>{{ $project->business_problem }}</p>

                <h6>Proposed Solution</h6>
                <p class="mb-0">{{ $project->proposed_solution }}</p>

            </div>

        </div>

        <div class="card">

            <div class="card-header fw-bold">
                Your Decision
            </div>

            <div class="card-body">

                @if ($errors->any())

                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>

                @endif

                <form method="POST" action="{{ route('approvals.management.store', $project) }}">
                    @csrf

                    <div class="mb-3">
                        <label for="decision" class="form-label">
                            Decision
                        </label>
                        <select name="decision" id="decision" class="form-select" required>
                            <option value="">Choose...</option>
                            <option value="APPROVED" {{ old('decision') === 'APPROVED' ? 'selected' : '' }}>
                                Approve &mdash; proceed to Requirements &amp; Design
                            </option>
                            <option value="RETURNED" {{ old('decision') === 'RETURNED' ? 'selected' : '' }}>
                                Return &mdash; send back to requestor for revision
                            </option>
                            <option value="REJECTED" {{ old('decision') === 'REJECTED' ? 'selected' : '' }}>
                                Reject &mdash; do not proceed
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label">
                            Remarks
                            <small class="text-muted">(required unless Approve)</small>
                        </label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="3">{{ old('remarks') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Submit Decision
                    </button>

                </form>

            </div>

        </div>

@endsection
