@extends('layouts.app')

@section('title')
Development — {{ $project->project_id }}
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
                    {{ $project->project_id }} &middot; {{ $project->requestingDepartment->name }}
                </div>

            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('software-projects.show', $project) }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-file-lines"></i> View Request
                </a>
                <a href="{{ route('workflow.development.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to Queue
                </a>
            </div>

        </div>

        {{-- REPOSITORIES --}}

        <div class="card mb-4">

            <div class="card-header fw-bold">
                Repositories
            </div>

            <div class="card-body">

                @forelse ($project->repositories as $repository)

                    <div class="mb-2">
                        <strong>{{ $repository->name }}</strong>
                        <span class="text-muted">({{ $repository->provider ?? 'n/a' }}, {{ $repository->default_branch }})</span>
                        &mdash;
                        <a href="{{ $repository->repository_url }}" target="_blank">{{ $repository->repository_url }}</a>
                    </div>

                @empty

                    <p class="text-muted">No repository linked yet.</p>

                @endforelse

                @can('manageDevelopment', $project)

                    <form method="POST" action="{{ route('workflow.development.repositories.store', $project) }}" class="row g-2 mt-3">
                        @csrf
                        <div class="col-md-3">
                            <input type="text" name="name" class="form-control" placeholder="Repository name" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="provider" class="form-control" placeholder="Provider (e.g. GitHub)">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="repository_url" class="form-control" placeholder="Repository URL" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="default_branch" class="form-control" placeholder="main">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-outline-primary w-100">Add</button>
                        </div>
                    </form>

                @endcan

            </div>

        </div>

        {{-- TASKS --}}

        <div class="card mb-4">

            <div class="card-header fw-bold d-flex justify-content-between">
                <span>Development Tasks</span>
                <span class="text-muted">{{ $openTaskCount }} open</span>
            </div>

            <div class="card-body">

                @forelse ($project->developmentTasks as $task)

                    <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">

                        <div>
                            <strong>{{ $task->task_code }}</strong> &middot; {{ $task->title }}
                            <span class="badge bg-light text-dark">{{ $task->priority }}</span>
                        </div>

                        @can('manageDevelopment', $project)

                            <form method="POST" action="{{ route('workflow.development.tasks.status', [$project, $task]) }}" class="d-flex gap-2">
                                @csrf
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach (['TODO', 'IN_PROGRESS', 'DONE', 'BLOCKED'] as $status)
                                        <option value="{{ $status }}" {{ $task->status === $status ? 'selected' : '' }}>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>

                        @else

                            <span class="badge bg-secondary">{{ $task->status }}</span>

                        @endcan

                    </div>

                @empty

                    <p class="text-muted">No tasks recorded yet.</p>

                @endforelse

                @can('manageDevelopment', $project)

                    <form method="POST" action="{{ route('workflow.development.tasks.store', $project) }}" class="row g-2 mt-3">
                        @csrf
                        <div class="col-md-2">
                            <input type="text" name="task_code" class="form-control" placeholder="Task # (e.g. DEV-001)" required>
                        </div>
                        <div class="col-md-7">
                            <input type="text" name="title" class="form-control" placeholder="Title" required>
                        </div>
                        <div class="col-md-2">
                            <select name="priority" class="form-select">
                                <option value="LOW">Low</option>
                                <option value="MEDIUM" selected>Medium</option>
                                <option value="HIGH">High</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-outline-primary w-100">Add</button>
                        </div>
                    </form>

                @endcan

            </div>

        </div>

        @can('manageDevelopment', $project)

            <div class="card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div class="text-muted">
                        @if ($openTaskCount > 0)
                            {{ $openTaskCount }} task(s) still open.
                        @else
                            All tasks are done.
                        @endif
                    </div>

                    <form method="POST" action="{{ route('workflow.development.advance', $project) }}">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            Move to Testing &amp; QA
                        </button>
                    </form>

                </div>

            </div>

        @endcan

@endsection
