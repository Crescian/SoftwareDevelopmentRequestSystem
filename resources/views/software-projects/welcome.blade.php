@extends('layouts.app')

@section('title', 'New Request')

@section('styles')
@include('software-projects._tracker-styles')

    /* ── Page wrapper + ambient background effect ── */
    .welcome-page {
        position: relative;
        overflow: hidden;
        padding: 4px 4px 0;
    }

    .welcome-blob {
        position: absolute;
        border-radius: 50%;
        filter: blur(70px);
        opacity: .35;
        z-index: 0;
        pointer-events: none;
        animation: blobFloat 16s ease-in-out infinite alternate;
    }

    .welcome-blob.b1 {
        width: 340px;
        height: 340px;
        top: -140px;
        right: 6%;
        background: var(--ac);
    }

    .welcome-blob.b2 {
        width: 280px;
        height: 280px;
        top: 220px;
        left: -100px;
        background: var(--acl);
        opacity: .5;
        animation-delay: -5s;
    }

    .welcome-blob.b3 {
        width: 240px;
        height: 240px;
        bottom: -40px;
        right: 18%;
        background: var(--nm);
        opacity: .18;
        animation-delay: -9s;
    }

    @keyframes blobFloat {
        0%   { transform: translate(0, 0) scale(1); }
        100% { transform: translate(-24px, 26px) scale(1.08); }
    }

    @media (prefers-reduced-motion: reduce) {
        .welcome-blob { animation: none; }
    }

    .welcome-inner {
        position: relative;
        z-index: 1;
    }

    /* ── Hero band ── */
    .welcome-hero {
        position: relative;
        overflow: hidden;
        background: var(--nd);
        background-image:
            radial-gradient(circle at 18px 18px, rgba(255, 255, 255, .06) 1.5px, transparent 1.5px),
            linear-gradient(135deg, var(--nd) 0%, var(--nm) 100%);
        background-size: 36px 36px, 100% 100%;
        border-radius: 20px;
        padding: 44px 40px;
        color: #fff;
        margin-bottom: 26px;
        box-shadow: 0 18px 40px -16px rgba(11, 35, 64, .45);
    }

    .welcome-hero::after {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .06);
    }

    .welcome-hero .eyebrow {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: rgba(255, 255, 255, .78);
        margin-bottom: 14px;
    }

    .welcome-hero h1 {
        position: relative;
        z-index: 1;
        font-size: 30px;
        font-weight: 800;
        margin-bottom: 10px;
        max-width: 560px;
    }

    .welcome-hero p {
        position: relative;
        z-index: 1;
        font-size: 15px;
        color: rgba(255, 255, 255, .82);
        max-width: 520px;
        margin: 0 0 22px;
        line-height: 1.5;
    }

    .welcome-chips {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .welcome-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, .1);
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 50px;
        padding: 7px 14px;
        font-size: 12.5px;
        font-weight: 600;
        color: #fff;
        backdrop-filter: blur(2px);
    }

    .welcome-chip i {
        color: var(--ac);
        font-size: 12px;
    }

    /* ── How it works strip ── */
    .welcome-steps {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 30px;
    }

    .welcome-step {
        position: relative;
        background: #fff;
        border: 1px solid var(--bd);
        border-radius: 14px;
        padding: 18px 16px;
    }

    .welcome-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 50%;
        right: -14px;
        width: 14px;
        height: 2px;
        background: var(--bd);
        display: none;
    }

    @media (min-width: 992px) {
        .welcome-step:not(:last-child)::after { display: block; }
    }

    .welcome-step .step-no {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--acl);
        color: var(--acd);
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .welcome-step i.step-icon {
        float: right;
        color: var(--bd);
        font-size: 15px;
    }

    .welcome-step h4 {
        font-size: 13.5px;
        font-weight: 800;
        color: var(--nd);
        margin-bottom: 4px;
    }

    .welcome-step p {
        font-size: 12px;
        color: var(--tm);
        line-height: 1.45;
        margin: 0;
    }

    /* ── Section label ── */
    .welcome-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--tm);
        margin-bottom: 14px;
    }

    .info-trigger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: none;
        background: none;
        color: var(--tm);
        font-size: 14px;
        padding: 0;
        line-height: 1;
        transition: color .15s;
    }

    .info-trigger:hover,
    .info-trigger:focus {
        color: var(--acd);
    }

    #howItWorksModal .modal-content {
        border: none;
        border-radius: 18px;
    }

    #howItWorksModal .modal-header {
        border-bottom: 1px solid var(--bd);
        padding: 20px 24px;
    }

    #howItWorksModal .modal-header .modal-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--nd);
    }

    #howItWorksModal .modal-body {
        padding: 22px 24px 24px;
    }

    #howItWorksModal .welcome-steps {
        grid-template-columns: repeat(2, 1fr);
        margin-bottom: 0;
    }

    #howItWorksModal .welcome-step:not(:last-child)::after {
        display: none;
    }

    /* ── Type cards ── */
    .type-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .type-card {
        position: relative;
        background: #fff;
        border: 2px solid var(--bd);
        border-radius: 16px;
        padding: 26px 24px;
        cursor: pointer;
        transition: border-color .2s, box-shadow .2s, transform .15s;
    }

    .type-card:not(.disabled):hover {
        border-color: var(--ac);
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(11, 35, 64, .08);
    }

    .type-card.active {
        border-color: var(--acd);
        box-shadow: 0 10px 24px rgba(47, 126, 230, .16);
    }

    .type-card.disabled {
        cursor: not-allowed;
        pointer-events: none;
        background: var(--cr);
        opacity: .72;
    }

    .type-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .type-visual {
        width: 72px;
        height: 72px;
        flex-shrink: 0;
    }

    .type-visual svg {
        width: 100%;
        height: 100%;
        display: block;
    }

    .radio-dot {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid var(--bd);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .type-card.active .radio-dot {
        border-color: var(--acd);
    }

    .type-card.active .radio-dot::after {
        content: '';
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--acd);
    }

    .type-card h3 {
        font-size: 16.5px;
        font-weight: 800;
        color: var(--nd);
        margin-bottom: 6px;
    }

    .type-card .type-desc {
        font-size: 13.5px;
        color: var(--tm);
        line-height: 1.5;
        margin: 0 0 16px;
    }

    .type-features {
        list-style: none;
        margin: 0;
        padding: 14px 0 0;
        border-top: 1px dashed var(--bd);
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .type-features li {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        font-size: 12.5px;
        color: var(--nd);
        font-weight: 600;
    }

    .type-features li i {
        color: var(--acd);
        font-size: 11px;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .type-card.disabled .type-features li {
        color: var(--tm);
    }

    .type-card.disabled .type-features li i {
        color: #9aa4b2;
    }

    .unavailable-tag {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        background: #eef1f5;
        color: var(--tm);
        padding: 5px 12px;
        border-radius: 50px;
    }

    /* ── Proceed ── */
    .welcome-proceed {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 16px;
        flex-wrap: wrap;
    }

    .welcome-proceed .btn-navy,
    .welcome-proceed .btn-outline-navy {
        padding: 13px 30px;
        font-size: 14.5px;
        border-radius: 12px;
    }

    .welcome-footnote {
        font-size: 12px;
        color: var(--tm);
        margin-top: 12px;
    }

    @media (max-width: 991.98px) {
        .welcome-steps { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 575.98px) {
        .welcome-steps { grid-template-columns: 1fr; }
    }
@endsection

@section('content')

    <div class="welcome-page">
        <span class="welcome-blob b1"></span>
        <span class="welcome-blob b2"></span>
        <span class="welcome-blob b3"></span>

        <div class="welcome-inner">

            <div class="welcome-hero">
                <div class="eyebrow"><i class="fa-solid fa-circle-info"></i> {{ config('app.name') }}</div>
                <h1>Let's get your request started</h1>
                <p>Tell us what you need — choose a request type below and we'll guide you through the rest, step by step, from first review to go-live.</p>

                <div class="welcome-chips">
                    <span class="welcome-chip"><i class="fa-solid fa-bolt"></i> Fast initial review</span>
                    <span class="welcome-chip"><i class="fa-solid fa-route"></i> 8-stage tracked workflow</span>
                    <span class="welcome-chip"><i class="fa-solid fa-people-group"></i> Direct MST collaboration</span>
                </div>
            </div>

            <div class="welcome-label">
                Choose a request type
                <button type="button" class="info-trigger" data-bs-toggle="modal" data-bs-target="#howItWorksModal" title="How it works" aria-label="How it works">
                    <i class="fa-solid fa-circle-info"></i>
                </button>
            </div>

            <div class="type-grid">
                <div class="type-card active" data-route="{{ route('software-projects.create') }}" tabindex="0" role="button" aria-pressed="true">
                    <div class="type-card-top">
                        <div class="type-visual">
                            <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="32" cy="34" r="26" fill="var(--acl)"/>
                                <path d="M32 10c6 6 8 14 8 22 0 4-1 8-3 11h-10c-2-3-3-7-3-11 0-8 2-16 8-22z" fill="var(--acd)"/>
                                <circle cx="32" cy="26" r="3.5" fill="#fff"/>
                                <path d="M21 36c0-4 2-7 5-9v9z" fill="var(--nm)"/>
                                <path d="M43 36c0-4-2-7-5-9v9z" fill="var(--nm)"/>
                                <path d="M28 43c0 4 1.5 7 4 9 2.5-2 4-5 4-9z" fill="#ffb020"/>
                                <circle cx="14" cy="18" r="1.6" fill="var(--acd)"/>
                                <circle cx="50" cy="22" r="1.2" fill="var(--acd)"/>
                                <circle cx="46" cy="12" r="1.8" fill="var(--acd)"/>
                            </svg>
                        </div>
                        <span class="radio-dot"></span>
                    </div>
                    <h3>New Request</h3>
                    <p class="type-desc">Submit a new idea or system for the MST team to review, assess, and build from the ground up.</p>
                    <ul class="type-features">
                        <li><i class="fa-solid fa-check"></i> Full workflow from review to deployment</li>
                        <li><i class="fa-solid fa-check"></i> Real-time status &amp; timeline tracking</li>
                        <li><i class="fa-solid fa-check"></i> Direct collaboration with the MST team</li>
                    </ul>
                </div>

                <div class="type-card disabled" aria-disabled="true">
                    <span class="unavailable-tag">Currently Unavailable</span>
                    <div class="type-card-top">
                        <div class="type-visual">
                            <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="32" cy="34" r="26" fill="#eef1f5"/>
                                <rect x="24" y="20" width="16" height="22" rx="2" fill="#fff" stroke="var(--tm)" stroke-width="2"/>
                                <line x1="27" y1="26" x2="37" y2="26" stroke="var(--tm)" stroke-width="1.6"/>
                                <line x1="27" y1="31" x2="37" y2="31" stroke="var(--tm)" stroke-width="1.6"/>
                                <line x1="27" y1="36" x2="33" y2="36" stroke="var(--tm)" stroke-width="1.6"/>
                                <path d="M16 24a18 18 0 0 1 30-6" stroke="var(--nm)" stroke-width="3" fill="none" stroke-linecap="round"/>
                                <path d="M44 15l3 7-7 1" stroke="var(--nm)" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M48 44a18 18 0 0 1-30 6" stroke="var(--nm)" stroke-width="3" fill="none" stroke-linecap="round"/>
                                <path d="M20 53l-3-7 7-1" stroke="var(--nm)" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="radio-dot"></span>
                    </div>
                    <h3>Change Request</h3>
                    <p class="type-desc">Request a modification or enhancement to an existing, already-delivered system.</p>
                    <ul class="type-features">
                        <li><i class="fa-solid fa-check"></i> Modify or enhance a live system</li>
                        <li><i class="fa-solid fa-check"></i> Lighter-weight approval path</li>
                        <li><i class="fa-solid fa-check"></i> Launching in a future update</li>
                    </ul>
                </div>
            </div>

            <div class="welcome-proceed">
                <a href="{{ route('software-projects.create') }}" id="welcomeProceedBtn" class="btn-navy">
                    Proceed <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <p class="welcome-footnote">You can always start a new request later from the sidebar.</p>

        </div>
    </div>

    <div class="modal fade" id="howItWorksModal" tabindex="-1" aria-labelledby="howItWorksModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="howItWorksModalLabel">How it works</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="welcome-steps">
                        <div class="welcome-step">
                            <i class="fa-solid fa-paper-plane step-icon"></i>
                            <div class="step-no">1</div>
                            <h4>Submit</h4>
                            <p>Describe the project, its objectives, and scope.</p>
                        </div>
                        <div class="welcome-step">
                            <i class="fa-solid fa-magnifying-glass step-icon"></i>
                            <div class="step-no">2</div>
                            <h4>MST Review</h4>
                            <p>MST checks completeness and feasibility.</p>
                        </div>
                        <div class="welcome-step">
                            <i class="fa-solid fa-code step-icon"></i>
                            <div class="step-no">3</div>
                            <h4>Build &amp; Test</h4>
                            <p>Design, development, and QA run through to UAT.</p>
                        </div>
                        <div class="welcome-step">
                            <i class="fa-solid fa-flag-checkered step-icon"></i>
                            <div class="step-no">4</div>
                            <h4>Go Live</h4>
                            <p>Deployment, monitoring, and handover.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.type-card:not(.disabled)').forEach(function (card) {
            card.addEventListener('click', function () {
                document.querySelectorAll('.type-card').forEach(function (c) {
                    c.classList.remove('active');
                    c.setAttribute('aria-pressed', 'false');
                });
                card.classList.add('active');
                card.setAttribute('aria-pressed', 'true');
                document.getElementById('welcomeProceedBtn').setAttribute('href', card.dataset.route);
            });
        });
    </script>
@endsection
