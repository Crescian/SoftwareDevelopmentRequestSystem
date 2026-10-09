@extends('layouts.app')

@section('title')
Requirements & Design — {{ $project->project_id }}
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
                    &middot; <span class="badge bg-secondary">{{ $project->status }}</span>
                </div>

            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('software-projects.show', $project) }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-file-lines"></i> View Request
                </a>
                <a href="{{ route('workflow.requirements-design.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to Queue
                </a>
            </div>

        </div>

        {{-- REQUIREMENTS DOCUMENTS --}}

        <div class="card mb-4">

            <div class="card-header fw-bold">
                Requirements Documents
            </div>

            <div class="card-body">

                @forelse ($project->requirementsDocuments as $document)

                    <div class="border rounded p-3 mb-3">

                        <div class="d-flex justify-content-between">
                            <strong>{{ $document->document_number }} &middot; {{ $document->title }} (v{{ $document->version }})</strong>
                            <span class="badge {{ match($document->status) { 'APPROVED' => 'bg-success', 'RETURNED' => 'bg-danger', default => 'bg-secondary' } }}">
                                {{ $document->status }}
                            </span>
                        </div>

                        <p class="text-muted mb-2">{{ $document->description }}</p>

                        @if ($document->remarks)
                            <p class="mb-2"><em>Remarks: {{ $document->remarks }}</em></p>
                        @endif

                        @if ($document->status === 'DRAFT' && $canDecide)

                            <form method="POST" action="{{ route('workflow.requirements-design.requirements-documents.decide', [$project, $document]) }}" class="d-flex gap-2 align-items-start">
                                @csrf
                                <select name="decision" class="form-select form-select-sm" style="width: auto;" required>
                                    <option value="APPROVED">Approve</option>
                                    <option value="RETURNED">Return</option>
                                </select>
                                <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Remarks (required if returning)">
                                <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                            </form>

                        @endif

                    </div>

                @empty

                    <p class="text-muted mb-0">No requirements document drafted yet.</p>

                @endforelse

                @if ($canManage)

                    <hr>

                    <h6>Draft a Requirements Document</h6>

                    <form method="POST" action="{{ route('workflow.requirements-design.requirements-documents.store', $project) }}">
                        @csrf

                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <input type="text" name="document_number" class="form-control" placeholder="Document # (e.g. RD-001)" required>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="title" class="form-control" placeholder="Title" required>
                            </div>
                        </div>

                        <div class="mb-2">
                            <textarea name="description" class="form-control" rows="2" placeholder="Description"></textarea>
                        </div>

                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            Add Requirements Document
                        </button>

                    </form>

                @endif

            </div>

        </div>

        {{-- SECURITY ASSESSMENT --}}

        <div class="card mb-4">

            <div class="card-header fw-bold">
                Security Assessment (Design Review)
            </div>

            <div class="card-body">

                @forelse ($project->securityAssessments as $assessment)

                    <div class="border rounded p-3 mb-3">

                        <div class="d-flex justify-content-between">
                            <strong>{{ $assessment->assessment_number }}</strong>
                            <div>
                                <span class="badge bg-light text-dark">{{ $assessment->overall_result }}</span>
                                <span class="badge {{ match($assessment->status) { 'APPROVED' => 'bg-success', 'RETURNED' => 'bg-danger', default => 'bg-secondary' } }}">
                                    {{ $assessment->status }}
                                </span>
                            </div>
                        </div>

                        @if ($assessment->security_risks)
                            <p class="text-muted mb-1"><small>Risks: {{ $assessment->security_risks }}</small></p>
                        @endif

                        @if ($assessment->remarks)
                            <p class="mb-1"><em>Remarks: {{ $assessment->remarks }}</em></p>
                        @endif

                        @if ($assessment->status === 'COMPLETED' && $canDecide)

                            <form method="POST" action="{{ route('workflow.requirements-design.security-assessments.decide', [$project, $assessment]) }}" class="d-flex gap-2 align-items-start">
                                @csrf
                                <select name="decision" class="form-select form-select-sm" style="width: auto;" required>
                                    <option value="APPROVED">Approve</option>
                                    <option value="RETURNED">Return</option>
                                </select>
                                <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Remarks (required if returning)">
                                <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                            </form>

                        @endif

                    </div>

                @empty

                    <p class="text-muted mb-0">No security assessment yet.</p>

                @endforelse

                @if ($canManage)

                    <hr>

                    <h6>Record Security Assessment</h6>

                    <form method="POST" action="{{ route('workflow.requirements-design.security-assessments.store', $project) }}">
                        @csrf

                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <input type="text" name="assessment_number" class="form-control" placeholder="SEC-001" required>
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
                            <textarea name="authentication_review" class="form-control" rows="2" placeholder="Authentication review"></textarea>
                        </div>

                        <div class="mb-2">
                            <textarea name="authorization_review" class="form-control" rows="2" placeholder="Authorization review"></textarea>
                        </div>

                        <div class="mb-2">
                            <textarea name="data_privacy_review" class="form-control" rows="2" placeholder="Data privacy review"></textarea>
                        </div>

                        <div class="mb-2">
                            <textarea name="security_risks" class="form-control" rows="2" placeholder="Security risks"></textarea>
                        </div>

                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            Record Assessment
                        </button>

                    </form>

                @endif

            </div>

        </div>

        {{-- SYSTEM DESIGNS --}}

        <div class="card">

            <div class="card-header fw-bold">
                System Designs
            </div>

            <div class="card-body">

                @forelse ($project->systemDesigns as $design)

                    <div class="border rounded p-3 mb-3">

                        <div class="d-flex justify-content-between">
                            <strong>{{ $design->document_number }} &middot; {{ $design->title }} (v{{ $design->version }})</strong>
                            <span class="badge {{ match($design->status) { 'APPROVED' => 'bg-success', 'RETURNED' => 'bg-danger', default => 'bg-secondary' } }}">
                                {{ $design->status }}
                            </span>
                        </div>

                        <p class="text-muted mb-2">{{ $design->description }}</p>

                        @if ($design->remarks)
                            <p class="mb-2"><em>Remarks: {{ $design->remarks }}</em></p>
                        @endif

                        @if ($design->status === 'DRAFT' && $canDecide)

                            <form method="POST" action="{{ route('workflow.requirements-design.system-designs.decide', [$project, $design]) }}" class="d-flex gap-2 align-items-start">
                                @csrf
                                <select name="decision" class="form-select form-select-sm" style="width: auto;" required>
                                    <option value="APPROVED">Approve</option>
                                    <option value="RETURNED">Return</option>
                                </select>
                                <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Remarks (required if returning)">
                                <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                            </form>

                        @endif

                    </div>

                @empty

                    <p class="text-muted mb-0">No system design drafted yet.</p>

                @endforelse

                @if ($canManage)

                    <hr>

                    <h6>Draft a System Design</h6>

                    <form method="POST" action="{{ route('workflow.requirements-design.system-designs.store', $project) }}">
                        @csrf

                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <input type="text" name="document_number" class="form-control" placeholder="Document # (e.g. SD-001)" required>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="title" class="form-control" placeholder="Title" required>
                            </div>
                        </div>

                        <div class="mb-2">
                            <textarea name="description" class="form-control" rows="2" placeholder="Description"></textarea>
                        </div>

                        <div class="mb-2">
                            <textarea name="architecture_overview" class="form-control" rows="2" placeholder="Architecture overview"></textarea>
                        </div>

                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            Add System Design
                        </button>

                    </form>

                @endif

            </div>

        </div>

@endsection
