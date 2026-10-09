@extends('layouts.app')

@section('title')
Feasibility Assessment — {{ $project->project_id }}
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
                <a href="{{ route('reviews.feasibility-assessment.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to Queue
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
                Feasibility Assessment
            </div>

            <div class="card-body">

                @if ($errors->any())

                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>

                @endif

                <form method="POST" action="{{ route('reviews.feasibility-assessment.store', $project) }}">
                    @csrf

                    <div class="mb-3">
                        <label for="technical_feasibility" class="form-label">
                            Technical Feasibility
                        </label>
                        <textarea name="technical_feasibility" id="technical_feasibility" class="form-control" rows="3" required>{{ old('technical_feasibility') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="operational_feasibility" class="form-label">
                            Operational Feasibility
                        </label>
                        <textarea name="operational_feasibility" id="operational_feasibility" class="form-control" rows="3" required>{{ old('operational_feasibility') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="cost_benefit_analysis" class="form-label">
                            Cost-Benefit Analysis
                        </label>
                        <textarea name="cost_benefit_analysis" id="cost_benefit_analysis" class="form-control" rows="3" required>{{ old('cost_benefit_analysis') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="overall_recommendation" class="form-label">
                            Overall Recommendation
                        </label>
                        <select name="overall_recommendation" id="overall_recommendation" class="form-select" required>
                            <option value="">Choose...</option>
                            <option value="FEASIBLE" {{ old('overall_recommendation') === 'FEASIBLE' ? 'selected' : '' }}>
                                Feasible &mdash; proceed to Management Approval
                            </option>
                            <option value="NOT_FEASIBLE_RETURN" {{ old('overall_recommendation') === 'NOT_FEASIBLE_RETURN' ? 'selected' : '' }}>
                                Not Feasible &mdash; return to requestor for revision
                            </option>
                            <option value="NOT_FEASIBLE_CLOSE" {{ old('overall_recommendation') === 'NOT_FEASIBLE_CLOSE' ? 'selected' : '' }}>
                                Not Feasible &mdash; close the request
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="assessment_notes" class="form-label">
                            Assessment Notes
                            <small class="text-muted">(required unless Feasible)</small>
                        </label>
                        <textarea name="assessment_notes" id="assessment_notes" class="form-control" rows="3">{{ old('assessment_notes') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Save Assessment
                    </button>

                </form>

            </div>

        </div>

@endsection
