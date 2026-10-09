/* ── Info grid (Project Information card) ── */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px 28px;
}

.info-item .info-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--tm);
    text-transform: uppercase;
    letter-spacing: .4px;
    margin-bottom: 5px;
}

.info-item .info-value {
    font-size: 14px;
    font-weight: 700;
    color: var(--nd);
}

/* ── Section card header (Project Information tab) ── */
.card-header-clean {
    background: #fff;
    border-bottom: 2px solid var(--acd);
    padding: 14px 20px;
    font-weight: 800;
    font-size: 14px;
    letter-spacing: .2px;
    color: var(--nd);
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-header-clean i {
    color: var(--acd);
    font-size: 13px;
}

/* ── Clean table (Requirements / Required Attachments) ──
   Wrapper owns the frame (border + radius + clipping) since border-radius
   on <table>/<th> itself is unreliable across browsers with collapsed
   borders — the table stays a plain, borderless grid inside it. */
.table-clean-wrap {
    border: 1px solid var(--bd);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(11, 35, 64, .04);
}

.table-clean {
    margin: 0;
}

.table-clean thead th {
    background: var(--nd);
    background-image: linear-gradient(135deg, var(--nd) 0%, var(--nm) 100%);
    color: rgba(255, 255, 255, .92);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    padding: 14px 18px;
    border: none;
    white-space: nowrap;
}

.table-clean tbody td {
    padding: 14px 18px;
    vertical-align: middle;
    border-bottom: 1px solid #eef1f5;
    background: #fff;
}

.table-clean tbody tr:last-child td {
    border-bottom: none;
}

.table-clean tbody tr {
    transition: background-color .15s ease;
}

.table-clean tbody tr:hover td {
    background: #f6fafe;
}

.table-clean .ref {
    font-weight: 700;
    color: var(--tm);
    white-space: nowrap;
}

.table-clean .title {
    font-weight: 700;
    color: var(--nd);
}

/* ── Tabs (Activity Timeline / Project Information) ── */
.nav-tabs .nav-link {
    color: var(--tm);
    font-weight: 700;
    border: none;
    border-bottom: 3px solid transparent;
}

.nav-tabs .nav-link:hover {
    border-color: transparent;
    color: var(--nl);
}

.nav-tabs .nav-link.active {
    color: var(--nd);
    border-bottom-color: var(--acd);
    background: none;
}

/* ── Back link (shared by every page with a parent to return to) ── */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 700;
    color: var(--tm);
    text-decoration: none;
    margin-bottom: 10px;
}

.back-link:hover { color: var(--nl); }

/* ── Extra status-pill variants (base set lives in layouts/app.blade.php) ── */
.status-pill.terminal     { background: #eef1f5; color: var(--tm); }
.status-pill.danger       { background: #fde8e8; color: #b42318; }
.status-pill.start        { background: #eef1f5; color: var(--tm); }

/* ── Owner badge ── */
.owner-badge {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 7px 16px 7px 7px;
    border-radius: 50px;
    background: #fff;
    border: 1.5px solid var(--bd);
    font-size: 14.5px;
    font-weight: 700;
    color: var(--nd);
    line-height: 1.4;
    vertical-align: middle;
}

.owner-badge .avatar-sm {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--acd);
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.owner-badge.team .avatar-sm { background: var(--nm); }
.owner-badge.terminal .avatar-sm { background: #9aa4b2; }
.owner-badge.terminal { color: var(--tm); }

/* ── Stage stepper ── */
.stage-stepper {
    display: flex;
    align-items: flex-start;
    overflow-x: auto;
    padding: 22px 4px 8px;
    -webkit-overflow-scrolling: touch;
}

.stage-step {
    position: relative;
    flex: 1 0 96px;
    min-width: 96px;
    text-align: center;
}

.stage-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 16px;
    left: 50%;
    width: 100%;
    height: 3px;
    background: var(--bd);
    z-index: 0;
}

.stage-step.completed:not(:last-child)::after {
    background: var(--acd);
}

.stage-dot {
    position: relative;
    z-index: 1;
    width: 32px;
    height: 32px;
    margin: 0 auto 8px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    font-weight: 800;
    background: #fff;
    border: 3px solid var(--bd);
    color: var(--tm);
}

.stage-step.completed .stage-dot {
    background: var(--acd);
    border-color: var(--acd);
    color: #fff;
}

.stage-step.current .stage-dot {
    width: 38px;
    height: 38px;
    margin-bottom: 5px;
    border-color: var(--ac);
    color: var(--nl);
    box-shadow: 0 0 0 5px var(--acl);
    animation: stepPulse 2s ease-in-out infinite;
}

.stage-step.terminal .stage-dot {
    background: #e5484d;
    border-color: #e5484d;
    color: #fff;
}

.stage-step.returned .stage-dot {
    background: #d9880c;
    border-color: #d9880c;
    color: #fff;
}

@keyframes stepPulse {
    0%, 100% { box-shadow: 0 0 0 5px var(--acl); }
    50%      { box-shadow: 0 0 0 8px var(--acl); }
}

.stage-label {
    font-size: 13px;
    font-weight: 700;
    color: var(--tm);
    line-height: 1.25;
    padding: 0 4px;
}

.stage-step.completed .stage-label { color: var(--nd); }
.stage-step.current .stage-label   { color: var(--nl); font-weight: 800; }
.stage-step.terminal .stage-label  { color: #e5484d; }
.stage-step.returned .stage-label  { color: #d9880c; }

/* ── Activity timeline ── */
.activity-timeline {
    position: relative;
    padding-left: 8px;
}

.activity-item {
    position: relative;
    display: flex;
    gap: 16px;
    padding-bottom: 26px;
}

.activity-item:last-child {
    padding-bottom: 2px;
}

.activity-item:not(:last-child)::before {
    content: '';
    position: absolute;
    top: 34px;
    left: 17px;
    width: 2px;
    bottom: -2px;
    background: var(--bd);
}

.activity-marker {
    flex-shrink: 0;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    color: #fff;
    z-index: 1;
    box-shadow: 0 0 0 4px #fff;
}

.activity-marker.tone-origin   { background: var(--nd); }
.activity-marker.tone-start    { background: #9aa4b2; }
.activity-marker.tone-forward  { background: var(--acd); }
.activity-marker.tone-warning  { background: #d9880c; }
.activity-marker.tone-danger   { background: #e5484d; }
.activity-marker.tone-success  { background: #1b9a4b; }
.activity-marker.tone-muted    { background: #9aa4b2; }

.activity-content {
    flex: 1;
    min-width: 0;
    padding-top: 2px;
}

.activity-head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 6px;
}

.activity-transition {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    font-size: 14px;
}

.activity-transition i {
    color: var(--tm);
    font-size: 13px;
}

.activity-time {
    font-size: 13px;
    color: var(--tm);
    white-space: nowrap;
    text-align: right;
}

.activity-time .activity-time-abs {
    display: block;
    font-weight: 700;
    color: var(--nd);
}

.activity-time .activity-time-rel {
    display: block;
    font-size: 11.5px;
    color: var(--tm);
}

.activity-duration {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--tm);
    font-style: italic;
    margin-bottom: 6px;
}

.activity-actor {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: var(--nd);
    margin-bottom: 6px;
}

.activity-actor .actor-dept {
    font-size: 12px;
    font-weight: 600;
    color: var(--tm);
}

.activity-actor .avatar-sm {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--nm);
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.activity-remarks {
    position: relative;
    background: var(--cr);
    border: 1px solid var(--bd);
    border-radius: 10px;
    padding: 11px 15px;
    font-size: 14px;
    color: var(--nd);
    margin: 0;
}

/* ── Ticket cards (My Requests / Workflow Tracker lists) ── */
.ticket-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ticket-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #fff;
    border: 1px solid var(--bd);
    border-left: 4px solid var(--bd);
    border-radius: 14px;
    padding: 16px 20px;
    text-decoration: none;
    color: inherit;
    transition: box-shadow .2s, transform .15s, border-color .2s;
}

a.ticket-card:hover {
    box-shadow: 0 6px 18px rgba(11, 35, 64, .08);
    transform: translateY(-1px);
    border-left-color: var(--acd);
}

.ticket-card.tone-forward  { border-left-color: var(--acd); }
.ticket-card.tone-warning  { border-left-color: #d9880c; }
.ticket-card.tone-danger   { border-left-color: #e5484d; }
.ticket-card.tone-success  { border-left-color: #1b9a4b; }
.ticket-card.tone-muted    { border-left-color: #9aa4b2; }

.ticket-ref {
    font-size: 12.5px;
    font-weight: 800;
    color: var(--tm);
    letter-spacing: .03em;
}

.ticket-title {
    font-size: 17px;
    font-weight: 800;
    color: var(--nd);
    margin: 2px 0 10px;
    line-height: 1.3;
}

.ticket-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px 10px;
    font-size: 13px;
    color: var(--tm);
}

.ticket-main {
    flex: 1;
    min-width: 0;
}

.ticket-side {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
    flex-shrink: 0;
    text-align: right;
}

.ticket-date {
    font-size: 12.5px;
    color: var(--tm);
    white-space: nowrap;
}

.ticket-open {
    font-size: 13px;
    font-weight: 700;
    color: var(--nl);
    white-space: nowrap;
}

@media (max-width: 575.98px) {
    .ticket-card {
        flex-wrap: wrap;
    }

    .ticket-side {
        align-items: flex-start;
        text-align: left;
        flex-direction: row;
        gap: 12px;
    }
}
