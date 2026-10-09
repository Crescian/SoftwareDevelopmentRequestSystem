@extends('layouts.app')

@section('title', 'New Request')

@section('styles')
@include('software-projects._tracker-styles')

    .wizard-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 22px;
    }

    /* ── Stepper ── */
    .stepper {
        display: flex;
        align-items: flex-start;
        margin-bottom: 26px;
        padding: 0 4px;
    }

    .step {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        min-width: 0;
    }

    .step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 15px;
        left: calc(50% + 18px);
        right: calc(-50% + 18px);
        height: 2px;
        background: var(--bd);
        z-index: 0;
    }

    .step.done:not(:last-child)::after {
        background: var(--nd);
    }

    .step .circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 2px solid var(--bd);
        background: #fff;
        color: var(--tm);
        font-size: 13px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1;
        transition: all .2s;
    }

    .step .label {
        font-size: 11px;
        font-weight: 700;
        color: var(--tm);
        margin-top: 8px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .step.active .circle {
        background: var(--nd);
        border-color: var(--nd);
        color: #fff;
        box-shadow: 0 0 0 4px rgba(11, 35, 64, .12);
    }

    .step.active .label { color: var(--nd); }

    .step.done .circle {
        background: var(--nd);
        border-color: var(--nd);
        color: #fff;
    }

    /* ── Panels ── */
    .wizard-card {
        padding: 26px 28px 22px;
    }

    .wizard-panel { display: none; }
    .wizard-panel.active { display: block; animation: fadeUp .3s ease both; }

    .panel-title {
        font-size: 16px;
        font-weight: 800;
        margin: 0 0 4px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--bd);
        margin-bottom: 20px;
    }

    .panel-title small {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--tm);
        margin-top: 4px;
    }

    .f-label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: var(--nd);
        margin-bottom: 7px;
    }

    .f-label .req { color: #e5484d; }
    .f-label .opt { color: var(--tm); font-weight: 600; }

    .f-input,
    .f-select,
    .f-textarea {
        width: 100%;
        border: 1.5px solid var(--bd);
        border-radius: 10px;
        padding: 11px 13px;
        font-size: 14px;
        font-family: inherit;
        background: #fff;
        color: var(--nd);
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    .f-input:focus,
    .f-select:focus,
    .f-textarea:focus {
        border-color: var(--nl);
        box-shadow: 0 0 0 3px rgba(37, 99, 184, .14);
    }

    .f-input[readonly] {
        background: var(--cr);
        color: var(--tm);
        font-weight: 700;
    }

    .f-input.is-invalid,
    .f-select.is-invalid,
    .f-textarea.is-invalid {
        border-color: #dc3545;
    }

    .f-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%235b6b82' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 14px;
        padding-right: 36px;
    }

    .icon-input { position: relative; }
    .icon-input .f-input { padding-left: 40px; }
    .icon-input > i {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--tm);
        font-size: 15px;
        pointer-events: none;
    }

    .f-hint {
        font-size: 12px;
        color: var(--tm);
        margin-top: 5px;
    }

    /* ── Dynamic rows ── */
    .dyn-row {
        display: flex;
        gap: 8px;
        margin-bottom: 8px;
    }

    .dyn-row .f-input { flex: 1; }

    .btn-remove {
        border: 1.5px solid var(--bd);
        background: #fff;
        color: var(--tm);
        border-radius: 10px;
        width: 44px;
        flex-shrink: 0;
        cursor: pointer;
        transition: all .2s;
    }

    .btn-remove:hover { border-color: #e5484d; color: #e5484d; }

    .btn-add {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--acl);
        color: var(--nl);
        border: none;
        border-radius: 8px;
        padding: 7px 12px;
        font-size: 12.5px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        transition: background .2s;
    }

    .btn-add:hover { background: #cfe1ff; }

    .req-item {
        border: 1.5px solid var(--bd);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
        background: #fafbfd;
        position: relative;
    }

    .req-item .req-remove {
        position: absolute;
        top: 10px;
        right: 10px;
        border: none;
        background: none;
        color: var(--tm);
        font-size: 16px;
        cursor: pointer;
    }

    .req-item .req-remove:hover { color: #e5484d; }

    /* ── Attachments ── */
    .attach-item {
        display: flex;
        align-items: center;
        gap: 14px;
        border: 1.5px dashed var(--bd);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 10px;
    }

    .attach-item .a-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--acl);
        color: var(--nl);
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .attach-item .a-title { font-size: 14px; font-weight: 700; }
    .attach-item .a-desc { font-size: 12px; color: var(--tm); }

    .attach-item .a-status {
        margin-left: auto;
        font-size: 11px;
        font-weight: 700;
        color: var(--tm);
        background: #eef1f5;
        border-radius: 50px;
        padding: 4px 10px;
        white-space: nowrap;
    }

    /* ── Review ── */
    .review-block {
        border: 1.5px solid var(--bd);
        border-radius: 12px;
        padding: 16px 18px;
        margin-bottom: 12px;
    }

    .review-block h4 {
        font-size: 13px;
        font-weight: 800;
        margin: 0 0 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .review-block h4 a {
        font-size: 12px;
        font-weight: 700;
        color: var(--nl);
        text-decoration: none;
    }

    .review-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 10px 20px;
        font-size: 13px;
    }

    .review-grid dt { font-size: 11px; font-weight: 700; color: var(--tm); text-transform: uppercase; letter-spacing: .3px; }
    .review-grid dd { margin: 2px 0 0; font-weight: 600; word-break: break-word; }

    .review-text { font-size: 13px; white-space: pre-wrap; margin: 0; }
    .review-list { font-size: 13px; margin: 0; padding-left: 18px; }
    .review-list li + li { margin-top: 3px; }
    .review-empty { color: var(--tm); font-style: italic; font-size: 13px; }

    .wizard-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid var(--bd);
    }

    .server-errors {
        background: #fde8e8;
        border: 1.5px solid #f0c0c0;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 13px;
        color: #8b1a1a;
        margin-bottom: 18px;
    }

    .server-errors ul { margin: 6px 0 0; padding-left: 18px; }

    @media (max-width: 767.98px) {
        .wizard-card { padding: 20px 16px 18px; }
        .step .label { font-size: 10px; }
        .step:not(:last-child)::after { left: calc(50% + 15px); right: calc(-50% + 15px); }
        .wizard-footer { flex-wrap: wrap; }
        .wizard-footer .right { margin-left: auto; }
    }

    @media (max-width: 575.98px) {
        .step .label { display: none; }
        .step.active .label { display: block; position: absolute; top: 36px; }
        .stepper { margin-bottom: 44px; }
    }
@endsection

@section('content')

    @php
        $oldObjectives = old('objectives', ['']);
        $oldInScope = old('in_scope', ['']);
        $oldOutScope = old('out_of_scope', ['']);
        $oldRequirements = old('requirements', [[
            'requirement_type' => 'FUNCTIONAL',
            'requirement_code' => 'FR-001',
            'title' => '',
            'description' => '',
            'priority' => 'MEDIUM',
        ]]);

        $attachmentTypes = [
            'BUSINESS_WORKFLOW' => ['Business Workflow', 'Step-by-step description of the current and proposed process', 'bi-diagram-3'],
            'BUSINESS_FLOWCHART' => ['Business Flowchart', 'Visual flow of the business process the system will support', 'bi-bezier2'],
            'DESIGN_DOCUMENT' => ['Design Document', 'Screens, forms, reports or any reference design material', 'bi-file-earmark-richtext'],
        ];

        $steps = ['Project Details', 'Business Case', 'Scope & Requirements', 'Attachments', 'Review & Submit'];
    @endphp

    <div class="wizard-head">
        <div>
            <a href="{{ route('dashboard') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back</a>
            <h1 class="page-title">New Software Project Request</h1>
            <p class="page-sub">Please fill out the details below. All fields are required unless indicated.</p>
        </div>
    </div>

    <div class="stepper" id="stepper">
        @foreach ($steps as $i => $label)
            <div class="step {{ $i === 0 ? 'active' : '' }}" data-step="{{ $i + 1 }}">
                <div class="circle">{{ $i + 1 }}</div>
                <div class="label">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    @if ($errors->any())
        <div class="server-errors" role="alert">
            <strong><i class="bi bi-exclamation-circle-fill me-1"></i>Please correct the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('software-projects.store') }}" id="sdrForm" novalidate>
        @csrf

        <input type="hidden" name="requestor_id" value="{{ auth()->id() }}">

        <div class="card-soft wizard-card">

            {{-- ───────── STEP 1: PROJECT DETAILS ───────── --}}
            <section class="wizard-panel active" data-panel="1">
                <h2 class="panel-title">1. Project Identification</h2>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="f-label" for="project_id">Reference No.</label>
                        <div class="icon-input">
                            <i class="bi bi-hash"></i>
                            <input type="text" id="project_id" name="project_id" class="f-input"
                                value="{{ old('project_id', $nextProjectId) }}" readonly required>
                        </div>
                        <div class="f-hint">Auto-generated reference for this request.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="f-label" for="priority">Priority</label>
                        <select id="priority" name="priority" class="f-select">
                            <option value="LOW" @selected(old('priority') === 'LOW')>Low</option>
                            <option value="MEDIUM" @selected(old('priority', 'MEDIUM') === 'MEDIUM')>Medium</option>
                            <option value="HIGH" @selected(old('priority') === 'HIGH')>High</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="f-label" for="project_name">Project Name <span class="req">*</span></label>
                        <input type="text" id="project_name" name="project_name"
                            class="f-input @error('project_name') is-invalid @enderror"
                            placeholder="e.g. Attendance System Enhancement"
                            value="{{ old('project_name') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="f-label" for="target_start_date">Target Start Date <span class="opt">(optional)</span></label>
                        <div class="icon-input">
                            <i class="bi bi-calendar3"></i>
                            <input type="date" id="target_start_date" name="target_start_date" class="f-input"
                                value="{{ old('target_start_date') }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="f-label" for="requesting_department_id">Requesting Department <span class="req">*</span></label>
                        <select id="requesting_department_id" name="requesting_department_id"
                            class="f-select @error('requesting_department_id') is-invalid @enderror" required>
                            <option value="">Select Department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('requesting_department_id') == $department->id)>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="f-label" for="target_end_date">Target Implementation Date <span class="opt">(optional)</span></label>
                        <div class="icon-input">
                            <i class="bi bi-calendar-check"></i>
                            <input type="date" id="target_end_date" name="target_end_date" class="f-input"
                                value="{{ old('target_end_date') }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="f-label" for="project_sponsor_id">Project Sponsor <span class="req">*</span></label>
                        <select id="project_sponsor_id" name="project_sponsor_id"
                            class="f-select @error('project_sponsor_id') is-invalid @enderror" required>
                            <option value="">Select Sponsor</option>
                            @foreach ($people as $person)
                                <option value="{{ $person->id }}" @selected(old('project_sponsor_id') == $person->id)>
                                    {{ $person->name }}{{ $person->department ? ' — ' . $person->department : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="f-hint">The person accountable for this project.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="f-label" for="project_manager_id">Project Manager <span class="opt">(if known)</span></label>
                        <select id="project_manager_id" name="project_manager_id" class="f-select">
                            <option value="">Not yet assigned</option>
                            @foreach ($people as $person)
                                <option value="{{ $person->id }}" @selected(old('project_manager_id') == $person->id)>
                                    {{ $person->name }}{{ $person->department ? ' — ' . $person->department : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            {{-- ───────── STEP 2: BUSINESS CASE ───────── --}}
            <section class="wizard-panel" data-panel="2">
                <h2 class="panel-title">2. Business Case
                    <small>Help us understand what you need and why it matters.</small>
                </h2>

                <div class="mb-3">
                    <label class="f-label" for="project_summary">Project Summary <span class="req">*</span></label>
                    <textarea id="project_summary" name="project_summary" rows="3" class="f-textarea"
                        placeholder="Briefly describe the system or enhancement you are requesting." required>{{ old('project_summary') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="f-label" for="business_problem">Business Problem / Opportunity <span class="req">*</span></label>
                    <textarea id="business_problem" name="business_problem" rows="4" class="f-textarea"
                        placeholder="What problem are you trying to solve? What is the impact today?" required>{{ old('business_problem') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="f-label" for="proposed_solution">Proposed Solution <span class="req">*</span></label>
                    <textarea id="proposed_solution" name="proposed_solution" rows="4" class="f-textarea"
                        placeholder="Describe how you envision the solution working." required>{{ old('proposed_solution') }}</textarea>
                </div>

                <label class="f-label">Project Objectives <span class="req">*</span></label>
                <div class="f-hint mb-2" style="margin-top:-4px">List measurable outcomes the project should achieve.</div>
                <div id="objectives-container">
                    @foreach ($oldObjectives as $objective)
                        <div class="dyn-row">
                            <input type="text" name="objectives[]" class="f-input" placeholder="e.g. Reduce manual encoding by 50%" value="{{ $objective }}" required>
                            <button type="button" class="btn-remove" aria-label="Remove"><i class="bi bi-x-lg"></i></button>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn-add" data-add="objective"><i class="bi bi-plus-lg"></i> Add Objective</button>
            </section>

            {{-- ───────── STEP 3: SCOPE & REQUIREMENTS ───────── --}}
            <section class="wizard-panel" data-panel="3">
                <h2 class="panel-title">3. Scope &amp; Requirements
                    <small>Define what is included, what is not, and the key requirements.</small>
                </h2>

                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <label class="f-label">In Scope <span class="req">*</span></label>
                        <div id="in-scope-container">
                            @foreach ($oldInScope as $item)
                                <div class="dyn-row">
                                    <input type="text" name="in_scope[]" class="f-input" placeholder="Item included in scope" value="{{ $item }}" required>
                                    <button type="button" class="btn-remove" aria-label="Remove"><i class="bi bi-x-lg"></i></button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn-add" data-add="in_scope"><i class="bi bi-plus-lg"></i> Add In Scope</button>
                    </div>

                    <div class="col-lg-6">
                        <label class="f-label">Out of Scope <span class="opt">(optional)</span></label>
                        <div id="out-scope-container">
                            @foreach ($oldOutScope as $item)
                                <div class="dyn-row">
                                    <input type="text" name="out_of_scope[]" class="f-input" placeholder="Item excluded from scope" value="{{ $item }}">
                                    <button type="button" class="btn-remove" aria-label="Remove"><i class="bi bi-x-lg"></i></button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn-add" data-add="out_scope"><i class="bi bi-plus-lg"></i> Add Out of Scope</button>
                    </div>
                </div>

                <label class="f-label">Key Requirements <span class="req">*</span></label>
                <div id="requirements-container">
                    @foreach ($oldRequirements as $index => $r)
                        <div class="req-item">
                            <button type="button" class="req-remove" aria-label="Remove requirement"><i class="bi bi-x-lg"></i></button>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="f-label">Type</label>
                                    <select name="requirements[{{ $index }}][requirement_type]" class="f-select" required>
                                        @foreach (['FUNCTIONAL' => 'Functional', 'NON_FUNCTIONAL' => 'Non-Functional', 'INTEGRATION' => 'Integration', 'COMPLIANCE' => 'Compliance'] as $val => $lbl)
                                            <option value="{{ $val }}" @selected(($r['requirement_type'] ?? '') === $val)>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="f-label">Code</label>
                                    <input type="text" name="requirements[{{ $index }}][requirement_code]" class="f-input" placeholder="FR-001" value="{{ $r['requirement_code'] ?? '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="f-label">Title</label>
                                    <input type="text" name="requirements[{{ $index }}][title]" class="f-input" placeholder="Short requirement title" value="{{ $r['title'] ?? '' }}" required>
                                </div>
                                <div class="col-md-9">
                                    <label class="f-label">Description</label>
                                    <textarea name="requirements[{{ $index }}][description]" class="f-textarea" rows="2" placeholder="Describe the requirement" required>{{ $r['description'] ?? '' }}</textarea>
                                </div>
                                <div class="col-md-3">
                                    <label class="f-label">Priority</label>
                                    <select name="requirements[{{ $index }}][priority]" class="f-select">
                                        <option value="LOW" @selected(($r['priority'] ?? '') === 'LOW')>Low</option>
                                        <option value="MEDIUM" @selected(($r['priority'] ?? 'MEDIUM') === 'MEDIUM')>Medium</option>
                                        <option value="HIGH" @selected(($r['priority'] ?? '') === 'HIGH')>High</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn-add" data-add="requirement"><i class="bi bi-plus-lg"></i> Add Requirement</button>
            </section>

            {{-- ───────── STEP 4: ATTACHMENTS ───────── --}}
            <section class="wizard-panel" data-panel="4">
                <h2 class="panel-title">4. Required Attachments
                    <small>These documents are required for the SDR. Files are uploaded during the document management stage.</small>
                </h2>

                @foreach ($attachmentTypes as $type => [$label, $desc, $icon])
                    <input type="hidden" name="required_attachments[{{ $loop->index }}][attachment_type]" value="{{ $type }}">
                    <input type="hidden" name="required_attachments[{{ $loop->index }}][is_required]" value="1">
                    <input type="hidden" name="required_attachments[{{ $loop->index }}][attachment_status]" value="NOT_ATTACHED">

                    <div class="attach-item">
                        <div class="a-icon"><i class="bi {{ $icon }}"></i></div>
                        <div>
                            <div class="a-title">{{ $label }}</div>
                            <div class="a-desc">{{ $desc }}</div>
                        </div>
                        <span class="a-status"><i class="bi bi-clock me-1"></i>To be uploaded</span>
                    </div>
                @endforeach

                <div class="f-hint mt-3">
                    <i class="bi bi-info-circle me-1"></i>
                    You can proceed without uploading. MST will request the files once the request is accepted for review.
                </div>
            </section>

            {{-- ───────── STEP 5: REVIEW & SUBMIT ───────── --}}
            <section class="wizard-panel" data-panel="5">
                <h2 class="panel-title">5. Review &amp; Submit
                    <small>Double-check everything before submitting your request.</small>
                </h2>

                <div class="review-block">
                    <h4>Project Details <a href="#" data-goto="1">Edit</a></h4>
                    <dl class="review-grid" id="rv-details"></dl>
                </div>

                <div class="review-block">
                    <h4>Business Case <a href="#" data-goto="2">Edit</a></h4>
                    <dt class="f-label mb-1">Project Summary</dt>
                    <p class="review-text mb-3" id="rv-summary"></p>
                    <dt class="f-label mb-1">Business Problem / Opportunity</dt>
                    <p class="review-text mb-3" id="rv-problem"></p>
                    <dt class="f-label mb-1">Proposed Solution</dt>
                    <p class="review-text mb-3" id="rv-solution"></p>
                    <dt class="f-label mb-1">Objectives</dt>
                    <ol class="review-list" id="rv-objectives"></ol>
                </div>

                <div class="review-block">
                    <h4>Scope &amp; Requirements <a href="#" data-goto="3">Edit</a></h4>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <dt class="f-label mb-1">In Scope</dt>
                            <ul class="review-list" id="rv-inscope"></ul>
                        </div>
                        <div class="col-md-6">
                            <dt class="f-label mb-1">Out of Scope</dt>
                            <ul class="review-list" id="rv-outscope"></ul>
                        </div>
                    </div>
                    <dt class="f-label mb-1">Key Requirements</dt>
                    <ul class="review-list" id="rv-requirements"></ul>
                </div>

                <div class="review-block">
                    <h4>Attachments <a href="#" data-goto="4">Edit</a></h4>
                    <ul class="review-list">
                        @foreach ($attachmentTypes as [$label])
                            <li>{{ $label }} — <span class="text-muted">to be uploaded</span></li>
                        @endforeach
                    </ul>
                </div>

                <label class="d-flex align-items-start gap-2 mt-3" style="font-size:13px;font-weight:600;color:var(--tm);cursor:pointer">
                    <input type="checkbox" id="confirmCheck" style="accent-color:var(--nd);width:16px;height:16px;margin-top:2px" required>
                    I confirm that the information above is accurate and complete to the best of my knowledge.
                </label>
            </section>

            {{-- ───────── FOOTER ───────── --}}
            <div class="wizard-footer">
                <a href="{{ route('dashboard') }}" class="btn-outline-navy" id="btnCancel">Cancel</a>
                <div class="d-flex gap-2 right">
                    <button type="button" class="btn-outline-navy" id="btnPrev" style="display:none">
                        <i class="bi bi-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="btn-navy" id="btnNext">
                        Next <i class="bi bi-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn-navy" id="btnSubmit" style="display:none">
                        <i class="bi bi-send"></i> Submit Request
                    </button>
                </div>
            </div>

        </div>
    </form>

@endsection

@section('scripts')
<script>
(function () {
    const TOTAL = 5;
    let current = 1;

    const form = document.getElementById('sdrForm');
    const panels = [...document.querySelectorAll('.wizard-panel')];
    const steps = [...document.querySelectorAll('#stepper .step')];
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const btnSubmit = document.getElementById('btnSubmit');

    /* ── Navigation ── */
    function show(n) {
        current = Math.min(Math.max(n, 1), TOTAL);

        panels.forEach(p => p.classList.toggle('active', +p.dataset.panel === current));
        steps.forEach(s => {
            const k = +s.dataset.step;
            s.classList.toggle('active', k === current);
            s.classList.toggle('done', k < current);
        });

        btnPrev.style.display = current === 1 ? 'none' : '';
        btnNext.style.display = current === TOTAL ? 'none' : '';
        btnSubmit.style.display = current === TOTAL ? '' : 'none';

        if (current === TOTAL) buildReview();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateStep(n) {
        const panel = panels.find(p => +p.dataset.panel === n);
        const fields = [...panel.querySelectorAll('input, select, textarea')].filter(f => !f.disabled);
        let firstInvalid = null;

        fields.forEach(f => {
            const ok = f.checkValidity();
            f.classList.toggle('is-invalid', !ok);
            if (!ok && !firstInvalid) firstInvalid = f;
        });

        if (firstInvalid) {
            firstInvalid.focus();
            firstInvalid.reportValidity();
            return false;
        }

        // Cross-field: implementation date must not precede start date
        if (n === 1) {
            const s = document.getElementById('target_start_date').value;
            const e = document.getElementById('target_end_date').value;
            if (s && e && e < s) {
                const el = document.getElementById('target_end_date');
                el.classList.add('is-invalid');
                el.setCustomValidity('Target Implementation Date must be on or after the Target Start Date.');
                el.reportValidity();
                el.setCustomValidity('');
                return false;
            }
        }
        return true;
    }

    btnNext.addEventListener('click', () => { if (validateStep(current)) show(current + 1); });
    btnPrev.addEventListener('click', () => show(current - 1));

    document.querySelectorAll('[data-goto]').forEach(a => {
        a.addEventListener('click', e => { e.preventDefault(); show(+a.dataset.goto); });
    });

    steps.forEach(s => {
        s.style.cursor = 'pointer';
        s.addEventListener('click', () => {
            const target = +s.dataset.step;
            if (target < current) show(target);
        });
    });

    form.addEventListener('submit', e => {
        for (let i = 1; i < TOTAL; i++) {
            if (!validateStep(i)) { e.preventDefault(); show(i); return; }
        }
        if (!document.getElementById('confirmCheck').checked) {
            e.preventDefault();
            document.getElementById('confirmCheck').reportValidity();
            return;
        }
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting…';
    });

    form.addEventListener('input', e => e.target.classList?.remove('is-invalid'));

    /* ── Dynamic rows ── */
    const rowTpl = (name, placeholder, required) => `
        <div class="dyn-row">
            <input type="text" name="${name}" class="f-input" placeholder="${placeholder}" ${required ? 'required' : ''}>
            <button type="button" class="btn-remove" aria-label="Remove"><i class="bi bi-x-lg"></i></button>
        </div>`;

    let reqIndex = document.querySelectorAll('#requirements-container .req-item').length;

    const reqTpl = (i) => `
        <div class="req-item">
            <button type="button" class="req-remove" aria-label="Remove requirement"><i class="bi bi-x-lg"></i></button>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="f-label">Type</label>
                    <select name="requirements[${i}][requirement_type]" class="f-select" required>
                        <option value="FUNCTIONAL">Functional</option>
                        <option value="NON_FUNCTIONAL">Non-Functional</option>
                        <option value="INTEGRATION">Integration</option>
                        <option value="COMPLIANCE">Compliance</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="f-label">Code</label>
                    <input type="text" name="requirements[${i}][requirement_code]" class="f-input" placeholder="FR-${String(i + 1).padStart(3, '0')}" value="FR-${String(i + 1).padStart(3, '0')}" required>
                </div>
                <div class="col-md-6">
                    <label class="f-label">Title</label>
                    <input type="text" name="requirements[${i}][title]" class="f-input" placeholder="Short requirement title" required>
                </div>
                <div class="col-md-9">
                    <label class="f-label">Description</label>
                    <textarea name="requirements[${i}][description]" class="f-textarea" rows="2" placeholder="Describe the requirement" required></textarea>
                </div>
                <div class="col-md-3">
                    <label class="f-label">Priority</label>
                    <select name="requirements[${i}][priority]" class="f-select">
                        <option value="LOW">Low</option>
                        <option value="MEDIUM" selected>Medium</option>
                        <option value="HIGH">High</option>
                    </select>
                </div>
            </div>
        </div>`;

    document.querySelectorAll('[data-add]').forEach(btn => {
        btn.addEventListener('click', () => {
            let html, container;
            switch (btn.dataset.add) {
                case 'objective':
                    container = document.getElementById('objectives-container');
                    html = rowTpl('objectives[]', 'e.g. Reduce manual encoding by 50%', true); break;
                case 'in_scope':
                    container = document.getElementById('in-scope-container');
                    html = rowTpl('in_scope[]', 'Item included in scope', true); break;
                case 'out_scope':
                    container = document.getElementById('out-scope-container');
                    html = rowTpl('out_of_scope[]', 'Item excluded from scope', false); break;
                case 'requirement':
                    container = document.getElementById('requirements-container');
                    html = reqTpl(reqIndex++); break;
            }
            container.insertAdjacentHTML('beforeend', html);
            container.lastElementChild.querySelector('input, select, textarea')?.focus();
        });
    });

    // Remove row (keep at least one for required lists)
    document.addEventListener('click', e => {
        const btn = e.target.closest('.btn-remove, .req-remove');
        if (!btn) return;
        const row = btn.closest('.dyn-row, .req-item');
        const container = row.parentElement;
        const min = container.id === 'out-scope-container' ? 0 : 1;
        if (container.children.length > min) row.remove();
        else row.querySelectorAll('input, textarea').forEach(i => i.value = '');
    });

    /* ── Review ── */
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const val = id => document.getElementById(id).value.trim();
    const selText = id => { const s = document.getElementById(id); return s.options[s.selectedIndex]?.text.trim() || ''; };
    const fmtDate = v => v ? new Date(v + 'T00:00:00').toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }) : '';
    const listHtml = (items) => items.length ? items.map(i => `<li>${esc(i)}</li>`).join('') : '<li class="review-empty" style="list-style:none;margin-left:-18px">None</li>';
    const vals = sel => [...document.querySelectorAll(sel)].map(i => i.value.trim()).filter(Boolean);

    function buildReview() {
        const details = [
            ['Reference No.', val('project_id')],
            ['Project Name', val('project_name')],
            ['Requesting Department', selText('requesting_department_id')],
            ['Priority', selText('priority')],
            ['Project Sponsor', selText('project_sponsor_id')],
            ['Project Manager', val('project_manager_id') ? selText('project_manager_id') : 'Not yet assigned'],
            ['Target Start Date', fmtDate(val('target_start_date')) || '—'],
            ['Target Implementation Date', fmtDate(val('target_end_date')) || '—'],
        ];
        document.getElementById('rv-details').innerHTML =
            details.map(([k, v]) => `<div><dt>${esc(k)}</dt><dd>${esc(v) || '<span class="review-empty">—</span>'}</dd></div>`).join('');

        document.getElementById('rv-summary').textContent = val('project_summary');
        document.getElementById('rv-problem').textContent = val('business_problem');
        document.getElementById('rv-solution').textContent = val('proposed_solution');
        document.getElementById('rv-objectives').innerHTML = listHtml(vals('input[name="objectives[]"]'));
        document.getElementById('rv-inscope').innerHTML = listHtml(vals('input[name="in_scope[]"]'));
        document.getElementById('rv-outscope').innerHTML = listHtml(vals('input[name="out_of_scope[]"]'));

        const reqs = [...document.querySelectorAll('#requirements-container .req-item')].map(item => {
            const g = n => item.querySelector(`[name$="[${n}]"]`);
            const type = g('requirement_type'); const pr = g('priority');
            return `<strong>${esc(g('requirement_code').value)}</strong> · ${esc(g('title').value)} <span class="text-muted">(${esc(type.options[type.selectedIndex].text)}, ${esc(pr.options[pr.selectedIndex].text)})</span>`;
        });
        document.getElementById('rv-requirements').innerHTML = reqs.length ? reqs.map(r => `<li>${r}</li>`).join('') : '<li class="review-empty">None</li>';
    }

    show(1);
})();
</script>
@endsection
