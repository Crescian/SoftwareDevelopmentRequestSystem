@extends('layouts.app')

@section('title')
Initial Review — {{ $project->project_id }}
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
                <a href="{{ route('reviews.initial-review.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to Queue
                </a>
            </div>

        </div>

        {{-- COMPLETENESS CHECKLIST --}}

        <div class="card mb-4">

            <div class="card-header fw-bold">
                Completeness Checklist
            </div>

            <div class="card-body">

                <ul class="list-group list-group-flush">

                    <li class="list-group-item d-flex justify-content-between">
                        Objectives
                        <span class="badge {{ $project->objectives->isNotEmpty() ? 'bg-success' : 'bg-danger' }}">
                            {{ $project->objectives->count() }}
                        </span>
                    </li>

                    <li class="list-group-item d-flex justify-content-between">
                        Scope (in / out)
                        <span class="badge {{ $project->scopes->isNotEmpty() ? 'bg-success' : 'bg-danger' }}">
                            {{ $project->scopes->count() }}
                        </span>
                    </li>

                    <li class="list-group-item d-flex justify-content-between">
                        Requirements
                        <span class="badge {{ $project->requirements->isNotEmpty() ? 'bg-success' : 'bg-danger' }}">
                            {{ $project->requirements->count() }}
                        </span>
                    </li>

                    <li class="list-group-item d-flex justify-content-between">
                        Required attachments signed off
                        @php
                            $required = $project->requiredAttachments->where('is_required', true);
                            $signedOff = $required->where('requester_signed_off', true)->count();
                        @endphp
                        <span class="badge {{ $signedOff === $required->count() ? 'bg-success' : 'bg-danger' }}">
                            {{ $signedOff }} / {{ $required->count() }}
                        </span>
                    </li>

                </ul>

            </div>

        </div>

        {{-- OVERVIEW --}}

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

        {{-- DECISION --}}

        <div class="card">

            <div class="card-header fw-bold">
                Decision
            </div>

            <div class="card-body">

                @if ($errors->any())

                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>

                @endif

                <div class="d-flex gap-3">

                    <form method="POST" action="{{ route('reviews.initial-review.pass', $project) }}">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            Mark Complete &rarr; Feasibility Assessment
                        </button>
                    </form>

                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#returnForm">
                        Return for Revision
                    </button>

                </div>

                <div class="collapse mt-3" id="returnForm">

                    <form method="POST" action="{{ route('reviews.initial-review.return', $project) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="remarks" class="form-label">
                                What's missing or needs correction?
                            </label>
                            <textarea name="remarks" id="remarks" class="form-control" rows="3" required>{{ old('remarks') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-danger">
                            Return to Requestor
                        </button>

                    </form>

                </div>

            </div>

        </div>

@endsection
