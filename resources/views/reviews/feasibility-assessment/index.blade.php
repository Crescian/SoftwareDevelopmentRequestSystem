@extends('layouts.app')

@section('title', 'Feasibility Assessment Queue')

@section('styles')
@include('software-projects._tracker-styles')
@endsection

@section('content')

        @if (session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif

        <a href="{{ route('dashboard') }}" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>

        <h1 class="fw-bold mb-4">
            Feasibility Assessment Queue
        </h1>

        <div class="card">

            <div class="card-body">

                @if ($projects->isEmpty())

                    <p class="text-muted mb-0">
                        No requests are awaiting Feasibility Assessment.
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
                                        <a href="{{ route('reviews.feasibility-assessment.show', $project) }}" class="btn btn-sm btn-primary">
                                            Assess
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
