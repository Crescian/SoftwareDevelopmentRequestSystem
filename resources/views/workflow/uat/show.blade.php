@extends('layouts.app')

@section('title')
UAT — {{ $project->project_id }}
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
                <a href="{{ route('workflow.uat.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to Queue
                </a>
            </div>

        </div>

        @forelse ($project->uatSessions as $session)

            <div class="card mb-4">

                <div class="card-header d-flex justify-content-between">
                    <strong>{{ $session->uat_number }} &middot; {{ $session->title }}</strong>
                    <span class="badge {{ $session->status === 'COMPLETED' ? 'bg-success' : 'bg-secondary' }}">
                        {{ $session->status }}
                    </span>
                </div>

                <div class="card-body">

                    <p class="text-muted mb-2">{{ $session->scope }}</p>

                    @forelse ($session->signoffs as $signoff)

                        <div class="border rounded p-2 mb-2">
                            <strong>{{ $signoff->signatory_name }}</strong>
                            ({{ $signoff->signatory_role }})
                            &mdash;
                            <span class="badge {{ $signoff->decision === 'ACCEPTED' ? 'bg-success' : 'bg-danger' }}">
                                {{ $signoff->decision }}
                            </span>
                            @if ($signoff->remarks)
                                <div class="text-muted"><small>{{ $signoff->remarks }}</small></div>
                            @endif
                        </div>

                    @empty

                        <p class="text-muted">No sign-off recorded yet.</p>

                    @endforelse

                    @can('decideUat', $project)

                        @if ($session->status !== 'COMPLETED')

                            <form method="POST" action="{{ route('workflow.uat.sessions.signoff', [$project, $session]) }}" class="mt-3">
                                @csrf

                                <div class="mb-2">
                                    <select name="decision" class="form-select" required>
                                        <option value="">Your decision...</option>
                                        <option value="ACCEPTED">Accept &mdash; proceed to Deployment</option>
                                        <option value="REJECTED">Reject &mdash; needs another round</option>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <textarea name="remarks" class="form-control" rows="2" placeholder="Remarks (required if rejecting)"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    Submit Decision
                                </button>

                            </form>

                        @endif

                    @endcan

                </div>

            </div>

        @empty

            <p class="text-muted">No UAT session scheduled yet.</p>

        @endforelse

        @can('manageUat', $project)

            <div class="card">

                <div class="card-header fw-bold">
                    Schedule a UAT Session
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('workflow.uat.sessions.store', $project) }}">
                        @csrf

                        <div class="row g-2 mb-2">
                            <div class="col-md-3">
                                <input type="text" name="uat_number" class="form-control" placeholder="UAT-001" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="title" class="form-control" placeholder="Title" required>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="planned_date" class="form-control">
                            </div>
                        </div>

                        <div class="mb-2">
                            <textarea name="scope" class="form-control" rows="2" placeholder="Scope"></textarea>
                        </div>

                        <button type="submit" class="btn btn-outline-primary">
                            Schedule Session
                        </button>

                    </form>

                </div>

            </div>

        @endcan

@endsection
