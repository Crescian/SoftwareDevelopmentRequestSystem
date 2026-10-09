@extends('layouts.app')

@section('title', 'My Approvals')

@section('content')

        @if (session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif

        <h1 class="fw-bold mb-4">
            My Approvals
        </h1>

        <div class="card">

            <div class="card-body">

                @if ($projects->isEmpty())

                    <p class="text-muted mb-0">
                        No requests are awaiting your approval.
                    </p>

                @else

                    <table class="table">

                        <thead>

                            <tr>
                                <th>SDR #</th>
                                <th>Project</th>
                                <th>Department</th>
                                <th></th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($projects as $project)

                                <tr>

                                    <td>{{ $project->project_id }}</td>
                                    <td>{{ $project->project_name }}</td>
                                    <td>{{ $project->requestingDepartment->name }}</td>

                                    <td>
                                        <a href="{{ route('approvals.management.show', $project) }}" class="btn btn-sm btn-primary">
                                            Review
                                        </a>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                @endif

            </div>

        </div>

@endsection
