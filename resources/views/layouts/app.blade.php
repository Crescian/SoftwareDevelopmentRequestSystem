<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Software Development Request System</title>

    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --nd: #0b2340;
            --nm: #143a66;
            --nl: #2563b8;
            --ac: #4f9cff;
            --acd: #2f7ee6;
            --acl: #e3efff;
            --cr: #f5f7fb;
            --bd: #dfe5ee;
            --tm: #5b6b82;
            --sidebar-w: 240px;
            --topbar-h: 64px;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito Sans', sans-serif;
            background: var(--cr);
            color: var(--nd);
            margin: 0;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-w);
            background: var(--nd);
            background-image:
                radial-gradient(circle at 20% 10%, rgba(37, 99, 184, .45) 0%, transparent 45%),
                radial-gradient(circle at 80% 100%, rgba(79, 156, 255, .12) 0%, transparent 40%);
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 22px 16px;
            z-index: 1040;
            transition: transform .25s ease;
            overflow-y: auto;
        }

        .sidebar-brand {
            font-weight: 900;
            font-size: 15px;
            line-height: 1.25;
            color: #fff;
            text-decoration: none;
            padding: 0 8px;
            margin-bottom: 30px;
            display: block;
        }

        .sidebar-brand span {
            color: var(--ac);
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar-section-label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: rgba(255, 255, 255, .4);
            padding: 16px 14px 6px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 10px;
            color: rgba(255, 255, 255, .7);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background .2s, color .2s;
        }

        .sidebar-nav a i {
            font-size: 16px;
            width: 18px;
            text-align: center;
        }

        .sidebar-nav a:hover {
            background: rgba(255, 255, 255, .07);
            color: #fff;
        }

        .sidebar-nav a.active {
            background: var(--acd);
            color: #fff;
            box-shadow: 0 6px 16px rgba(47, 126, 230, .35);
        }

        .sidebar-nav .badge-count {
            margin-left: auto;
            background: var(--ac);
            color: var(--nd);
            font-size: 11px;
            font-weight: 800;
            border-radius: 50px;
            padding: 2px 8px;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 8px;
            border-top: 1px solid rgba(255, 255, 255, .1);
            margin-top: 12px;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--acd);
            color: #fff;
            font-weight: 800;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-user .name {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }

        .sidebar-user .dept {
            font-size: 11px;
            color: rgba(255, 255, 255, .5);
        }

        .sidebar-logout {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: rgba(255, 255, 255, .7);
            font-size: 14px;
            font-weight: 600;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            border-radius: 10px;
            cursor: pointer;
            font-family: inherit;
        }

        .sidebar-logout:hover {
            background: rgba(255, 255, 255, .07);
            color: #fff;
        }

        /* ── Topbar ── */
        .topbar {
            position: sticky;
            top: 0;
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid var(--bd);
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 0 28px;
            z-index: 1030;
        }

        .topbar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            color: var(--nd);
            padding: 4px;
        }

        .topbar-search {
            flex: 1;
            max-width: 420px;
            position: relative;
        }

        .topbar-search input {
            width: 100%;
            border: 1.5px solid var(--bd);
            border-radius: 50px;
            padding: 9px 14px 9px 38px;
            font-size: 13px;
            font-family: inherit;
            background: var(--cr);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .topbar-search input:focus {
            border-color: var(--nl);
            box-shadow: 0 0 0 3px rgba(37, 99, 184, .12);
            background: #fff;
        }

        .topbar-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--tm);
            font-size: 14px;
        }

        .topbar-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .topbar-bell {
            position: relative;
            color: var(--nd);
            font-size: 19px;
            text-decoration: none;
        }

        .topbar-bell .dot {
            position: absolute;
            top: -2px;
            right: -3px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #e5484d;
            border: 2px solid #fff;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-user .name {
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
        }

        .topbar-user .role {
            font-size: 11px;
            color: var(--tm);
        }

        /* ── Main ── */
        .main {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .content {
            padding: 28px;
            flex: 1;
            animation: fadeUp .4s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px) }
            to   { opacity: 1; transform: translateY(0) }
        }

        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(11, 35, 64, .5);
            z-index: 1035;
        }

        /* ── Shared components ── */
        .page-eyebrow {
            font-size: 14px;
            color: var(--tm);
            margin-bottom: 2px;
        }

        .page-title {
            font-weight: 900;
            font-size: 28px;
            letter-spacing: -.4px;
            line-height: 1.15;
            margin: 0;
        }

        .page-sub {
            font-size: 13px;
            color: var(--tm);
            margin: 6px 0 0;
        }

        .card-soft {
            background: #fff;
            border: 1px solid var(--bd);
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(11, 35, 64, .04);
        }

        .btn-navy {
            background: var(--nd);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: background .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 14px rgba(11, 35, 64, .18);
        }

        .btn-navy:hover {
            background: var(--nm);
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-outline-navy {
            background: #fff;
            color: var(--nd);
            border: 1.5px solid var(--bd);
            border-radius: 10px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: border-color .2s, color .2s;
        }

        .btn-outline-navy:hover {
            border-color: var(--nl);
            color: var(--nl);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            line-height: 1.4;
            font-size: 13px;
            font-weight: 700;
            border-radius: 50px;
            padding: 6px 14px;
            white-space: nowrap;
            vertical-align: middle;
        }

        .status-pill.draft        { background: #eef1f5; color: #5b6b82; }
        .status-pill.progress     { background: var(--acl); color: var(--nl); }
        .status-pill.review       { background: #fff4d6; color: #a1650d; }
        .status-pill.clarify      { background: #fde8e8; color: #b42318; }
        .status-pill.completed    { background: #e3f7ea; color: #1b7a3e; }

        /* ── Responsive ── */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            body.sidebar-open .sidebar {
                transform: translateX(0);
            }

            body.sidebar-open .sidebar-backdrop {
                display: block;
            }

            .main {
                margin-left: 0;
            }

            .topbar {
                padding: 0 16px;
            }

            .topbar-toggle {
                display: inline-flex;
            }

            .topbar-user .name,
            .topbar-user .role {
                display: none;
            }

            .content {
                padding: 20px 16px;
            }

            .page-title {
                font-size: 24px;
            }
        }

        @media (max-width: 575.98px) {
            .topbar-search {
                display: none;
            }
        }

        @yield('styles')
    </style>
</head>

<body>

    @php
        $authUser = auth()->user();
        $initials = collect(explode(' ', trim($authUser?->name ?? 'U')))
            ->filter()
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
            ->take(2)
            ->implode('');
    @endphp

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            Software Development<br>Request <span>System</span>
        </a>

        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('software-projects.index') }}" class="{{ request()->routeIs('software-projects.index') || (request()->routeIs('software-projects.show') && isset($project) && $project->requestor_id === $authUser?->id) ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> My Requests
                </a>
            </li>
            <li>
                <a href="{{ route('software-projects.welcome') }}" class="{{ request()->routeIs('software-projects.welcome') || request()->routeIs('software-projects.create') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-plus"></i> New Request
                </a>
            </li>
        </ul>

        @if ($authUser?->department === config('workflow.mst_department'))

            <div class="sidebar-section-label">Workflow</div>

            <ul class="sidebar-nav">
                <li>
                    <a href="{{ route('workflow.tracker.index') }}" class="{{ request()->routeIs('workflow.tracker.*') ? 'active' : '' }}">
                        <i class="bi bi-kanban"></i> Workflow Tracker
                    </a>
                </li>
            </ul>

        @endif

        @if ($authUser?->dashboard_role === \App\Enums\DashboardRole::SystemAdministrator)

            <div class="sidebar-section-label">Administration</div>

            <ul class="sidebar-nav">
                <li>
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Manage Users
                    </a>
                </li>
            </ul>

        @endif

        <div class="sidebar-user">
            <div class="avatar">{{ $initials }}</div>
            <div>
                <div class="name">{{ $authUser?->name }}</div>
                <div class="dept">{{ $authUser?->department ?? 'Department' }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-logout">
                <i class="bi bi-box-arrow-left"></i> Log out
            </button>
        </form>
    </aside>

    <div class="main">
        <header class="topbar">
            <button class="topbar-toggle" id="sidebarToggle" type="button" aria-label="Toggle navigation">
                <i class="bi bi-list"></i>
            </button>

            <div class="topbar-search">
                <i class="bi bi-search"></i>
                <input type="search" placeholder="Search requests, reference, or help..." aria-label="Search">
            </div>

            <div class="topbar-right">
                <a href="#" class="topbar-bell" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="dot"></span>
                </a>
                <div class="topbar-user">
                    <div class="avatar">{{ $initials }}</div>
                    <div>
                        <div class="name">{{ $authUser?->name }}</div>
                        <div class="role">{{ $authUser?->role ?? 'Requestor' }} · {{ $authUser?->department ?? '' }}</div>
                    </div>
                </div>
            </div>
        </header>

        <main class="content">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const toggle = document.getElementById('sidebarToggle');
            const backdrop = document.getElementById('sidebarBackdrop');
            const close = () => document.body.classList.remove('sidebar-open');

            toggle.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
            backdrop.addEventListener('click', close);
            document.querySelectorAll('.sidebar-nav a').forEach(a => a.addEventListener('click', close));
        })();
    </script>
    @yield('scripts')
</body>

</html>
