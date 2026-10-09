@extends('layouts.app')

@section('title')
Change Requests — {{ $project->project_id }}
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
                    {{ $project->project_id }} &middot; Change Requests
                </div>

            </div>

            <a href="{{ route('workflow.monitoring.show', $project) }}" class="btn btn-outline-secondary">
                Back to Monitoring
            </a>

        </div>

        @forelse ($project->changeRequests as $changeRequest)

            <div class="card mb-4">

                <div class="card-header d-flex justify-content-between">
                    <strong>{{ $changeRequest->change_request_number }} &middot; {{ $changeRequest->title }}</strong>
                    <span class="badge {{ match($changeRequest->status) { 'APPROVED', 'IMPLEMENTED', 'CLOSED' => 'bg-success', 'REJECTED' => 'bg-danger', 'RETURNED_FOR_REVISION' => 'bg-warning', default => 'bg-secondary' } }}">
                        {{ $changeRequest->status }}
                    </span>
                </div>

                <div class="card-body">

                    <p class="mb-1">{{ $changeRequest->description }}</p>
                    <p class="text-muted mb-3"><small>Reason: {{ $changeRequest->reason }}</small></p>

                    <h6>Approval Chain</h6>

                    @foreach ($changeRequest->approvals->sortBy('approval_sequence') as $approval)

                        <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">

                            <div>
                                Step {{ $approval->approval_sequence }}: {{ $approval->approval_type }}
                            </div>

                            <div class="d-flex align-items-center gap-2">

                                <span class="badge {{ $approval->status === 'APPROVED' ? 'bg-success' : ($approval->status === 'REJECTED' ? 'bg-danger' : 'bg-secondary') }}">
                                    {{ $approval->status }}
                                </span>

                                @can('decide', $approval)

                                    <form method="POST" action="{{ route('workflow.change-requests.decide', [$project, $changeRequest, $approval]) }}" class="d-flex gap-1">
                                        @csrf
                                        <select name="decision" class="form-select form-select-sm" style="width:auto;">
                                            <option value="APPROVED">Approve</option>
                                            <option value="RETURNED">Return</option>
                                            <option value="REJECTED">Reject</option>
                                        </select>
                                        <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Remarks">
                                        <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                                    </form>

                                @endcan

                            </div>

                        </div>

                    @endforeach

                    @if ($changeRequest->status === 'RETURNED_FOR_REVISION')

                        <form method="POST" action="{{ route('workflow.change-requests.resubmit', [$project, $changeRequest]) }}" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                Resubmit
                            </button>
                        </form>

                    @endif

                    @can('manageChangeRequest', $project)

                        @if ($changeRequest->status === 'APPROVED')

                            <form method="POST" action="{{ route('workflow.change-requests.implement', [$project, $changeRequest]) }}" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    Mark Implemented
                                </button>
                            </form>

                        @elseif ($changeRequest->status === 'IMPLEMENTED')

                            <form method="POST" action="{{ route('workflow.change-requests.close', [$project, $changeRequest]) }}" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm">
                                    Close
                                </button>
                            </form>

                        @endif

                    @endcan

                </div>

            </div>

        @empty

            <p class="text-muted">No change requests yet.</p>

        @endforelse

        @can('proposeChangeRequest', $project)

            <div class="card">

                <div class="card-header fw-bold">
                    Propose a Change Request
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('workflow.change-requests.store', $project) }}">
                        @csrf

                        <div class="row g-2 mb-2">
                            <div class="col-md-3">
                                <input type="text" name="change_request_number" class="form-control" placeholder="CR-001" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="title" class="form-control" placeholder="Title" required>
                            </div>
                            <div class="col-md-3">
                                <select name="priority" class="form-select">
                                    <option value="LOW">Low</option>
                                    <option value="MEDIUM" selected>Medium</option>
                                    <option value="HIGH">High</option>
                                    <option value="CRITICAL">Critical</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-2">
                            <textarea name="description" class="form-control" rows="2" placeholder="Description" required></textarea>
                        </div>

                        <div class="mb-2">
                            <textarea name="reason" class="form-control" rows="2" placeholder="Reason" required></textarea>
                        </div>

                        <h6>Approval Chain</h6>
                        <p class="text-muted"><small>Enter each required approver, in the order they must approve.</small></p>

                        <div class="row g-2 mb-1">
                            <div class="col-md-5">
                                <input type="text" name="approvals[0][approval_type]" class="form-control" placeholder="Approval type (e.g. IT_ADMIN)" required>
                            </div>
                            <div class="col-md-7">
                                <input type="text" name="approvals[0][approver_id]" class="form-control" placeholder="Approver user ID (UUID)" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-5">
                                <input type="text" name="approvals[1][approval_type]" class="form-control" placeholder="Approval type (optional 2nd step)">
                            </div>
                            <div class="col-md-7">
                                <input type="text" name="approvals[1][approver_id]" class="form-control" placeholder="Approver user ID (optional)">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-outline-primary">
                            Submit Change Request
                        </button>

                    </form>

                </div>

            </div>

        @endcan

@endsection
