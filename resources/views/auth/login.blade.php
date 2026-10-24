<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — {{ config('app.name') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --nd: #0b2340;   /* navy dark   */
            --nm: #143a66;   /* navy mid    */
            --nl: #2563b8;   /* navy light  */
            --ac: #4f9cff;   /* accent blue */
            --acd: #2f7ee6;  /* accent dark */
            --acl: #e3efff;  /* accent pale */
            --cr: #f5f7fb;   /* page cream  */
            --bd: #d9e0ea;   /* border      */
            --tm: #5b6b82;   /* text muted  */
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: 'Nunito Sans', sans-serif;
            background: var(--cr);
            color: var(--nd);
            overflow: hidden;
        }

        /* ── Layout ── */
        .login-wrap {
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        /* ── LEFT PANEL ── */
        .left-panel {
            width: 52%;
            background: var(--nd);
            background-image:
                radial-gradient(circle at 30% 20%, rgba(37, 99, 184, .55) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(79, 156, 255, .14) 0%, transparent 45%),
                radial-gradient(circle at 10% 90%, rgba(20, 58, 102, .9) 0%, transparent 40%);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 56px;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(79, 156, 255, .05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(79, 156, 255, .05) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        .deco-circle {
            position: absolute;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            border: 1px solid rgba(79, 156, 255, .10);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: pulseRing 4s ease-in-out infinite;
        }

        .deco-circle.c2 {
            width: 340px;
            height: 340px;
            border-color: rgba(79, 156, 255, .15);
            animation-delay: 1s;
        }

        .deco-circle.c3 {
            width: 200px;
            height: 200px;
            border-color: rgba(79, 156, 255, .22);
            animation-delay: 2s;
        }

        @keyframes pulseRing {
            0%, 100% { opacity: .6; transform: translate(-50%, -50%) scale(1); }
            50%      { opacity: 1;  transform: translate(-50%, -50%) scale(1.04); }
        }

        .left-logo {
            font-weight: 900;
            font-size: 26px;
            line-height: 1.15;
            color: #fff;
            letter-spacing: -.5px;
            position: relative;
            z-index: 1;
            text-decoration: none;
            display: block;
        }

        .left-logo span {
            color: var(--ac);
        }

        .left-main {
            position: relative;
            z-index: 1;
        }

        .left-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(79, 156, 255, .12);
            border: 1px solid rgba(79, 156, 255, .3);
            border-radius: 50px;
            padding: 6px 16px;
            font-size: 12px;
            font-weight: 700;
            color: var(--ac);
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 24px;
        }

        .left-eyebrow .dot {
            width: 6px;
            height: 6px;
            background: var(--ac);
            border-radius: 50%;
            animation: blink 1.8s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1 }
            50%      { opacity: .3 }
        }

        .left-headline {
            font-weight: 900;
            font-size: clamp(32px, 3.5vw, 48px);
            color: #fff;
            line-height: 1.05;
            letter-spacing: -.5px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .left-headline em {
            font-style: normal;
            color: var(--ac);
        }

        .left-desc {
            font-size: 15px;
            color: rgba(255, 255, 255, .6);
            line-height: 1.6;
            max-width: 380px;
            margin-bottom: 36px;
        }

        .role-cards {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .role-card {
            display: flex;
            align-items: center;
            gap: 14px;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 14px;
            padding: 13px 18px;
            transition: background .2s, border-color .2s, transform .2s;
            animation: slideIn .5s ease both;
        }

        .role-card:hover {
            background: rgba(255, 255, 255, .09);
            border-color: rgba(79, 156, 255, .4);
            transform: translateX(4px);
        }

        .role-card:nth-child(1) { animation-delay: .1s }
        .role-card:nth-child(2) { animation-delay: .2s }
        .role-card:nth-child(3) { animation-delay: .3s }
        .role-card:nth-child(4) { animation-delay: .4s }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-16px) }
            to   { opacity: 1; transform: translateX(0) }
        }

        .role-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            background: rgba(79, 156, 255, .15);
            color: var(--ac);
        }

        .role-title {
            font-weight: 800;
            font-size: 13px;
            color: #fff;
        }

        .role-desc {
            font-size: 11px;
            color: rgba(255, 255, 255, .5);
            margin-top: 1px;
        }

        .left-footer {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, .55);
            line-height: 1.4;
        }

        .left-footer i {
            color: var(--ac);
            font-size: 18px;
        }

        /* ── RIGHT PANEL ── */
        .right-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 48px 20px;
            background: var(--cr);
            position: relative;
            overflow-y: auto;
        }

        .right-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 80% 10%, rgba(79, 156, 255, .08) 0%, transparent 40%),
                radial-gradient(circle at 20% 90%, rgba(11, 35, 64, .05) 0%, transparent 40%);
            pointer-events: none;
        }

        .form-box {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            margin: auto 0;
            animation: fadeUp .5s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px) }
            to   { opacity: 1; transform: translateY(0) }
        }

        .brand-mark {
            display: flex;
            justify-content: center;
            margin-bottom: 28px;
        }

        .brand-mark svg {
            height: 34px;
            width: auto;
        }

        .form-eyebrow {
            font-size: 12px;
            font-weight: 700;
            color: var(--tm);
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-bottom: 8px;
        }

        .form-title {
            font-weight: 900;
            font-size: 30px;
            color: var(--nd);
            letter-spacing: -.5px;
            line-height: 1.1;
            margin-bottom: 6px;
        }

        .form-title em {
            font-style: normal;
            color: var(--nl);
        }

        .form-sub {
            font-size: 14px;
            color: var(--tm);
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .field-wrap {
            margin-bottom: 18px;
        }

        .field-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--nd);
            margin-bottom: 7px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--tm);
            font-size: 15px;
            pointer-events: none;
            transition: color .2s;
        }

        .form-input {
            width: 100%;
            border: 1.5px solid var(--bd);
            border-radius: 12px;
            padding: 13px 14px 13px 42px;
            font-size: 14px;
            font-family: 'Nunito Sans', sans-serif;
            background: #fff;
            color: var(--nd);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-input:focus {
            border-color: var(--nl);
            box-shadow: 0 0 0 3px rgba(37, 99, 184, .14);
        }

        .input-wrap:focus-within .input-icon {
            color: var(--nl);
        }

        .form-input.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, .12) !important;
        }

        .field-error {
            font-size: 12px;
            color: #dc3545;
            font-weight: 600;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .pw-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--tm);
            font-size: 15px;
            cursor: pointer;
            padding: 0;
            transition: color .2s;
        }

        .pw-toggle:hover {
            color: var(--nd);
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--tm);
            cursor: pointer;
            user-select: none;
        }

        .remember-label input[type="checkbox"] {
            accent-color: var(--nd);
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        .forgot-link {
            font-size: 13px;
            font-weight: 700;
            color: var(--nl);
            text-decoration: none;
        }

        .forgot-link:hover {
            color: var(--nd);
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            background: var(--nd);
            color: #fff;
            font-family: 'Nunito Sans', sans-serif;
            font-weight: 800;
            font-size: 15px;
            padding: 15px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            transition: background .2s, transform .15s, box-shadow .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: .3px;
            margin-bottom: 20px;
            box-shadow: 0 4px 16px rgba(11, 35, 64, .22);
            text-decoration: none;
        }

        .btn-login:hover {
            background: var(--nm);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(11, 35, 64, .28);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login.btn-ms {
            background: #fff;
            color: #4a5568;
            border: 1.5px solid var(--bd);
            box-shadow: none;
        }

        .btn-login.btn-ms:hover {
            background: #fff;
            color: var(--nd);
            border-color: var(--nl);
            box-shadow: 0 6px 18px rgba(11, 35, 64, .10);
        }

        .btn-login .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2.5px solid rgba(255, 255, 255, .3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        .btn-login.loading .spinner {
            display: inline-block;
        }

        .btn-login.loading .btn-text {
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
            color: var(--tm);
            font-size: 12px;
            font-weight: 600;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--bd);
        }

        .error-box {
            background: #fde8e8;
            border: 1.5px solid #f0c0c0;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 600;
            color: #8b1a1a;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 18px;
            animation: shakeX .4s ease;
        }

        .error-box ul {
            margin: 0;
            padding: 0 0 0 4px;
            list-style: none;
        }

        .error-box ul li + li {
            margin-top: 4px;
        }

        @keyframes shakeX {
            0%, 100% { transform: translateX(0) }
            20%      { transform: translateX(-6px) }
            40%      { transform: translateX(6px) }
            60%      { transform: translateX(-4px) }
            80%      { transform: translateX(4px) }
        }

        .help-text {
            text-align: center;
            font-size: 12px;
            color: var(--tm);
            line-height: 1.6;
        }

        .help-text a {
            color: var(--nl);
            font-weight: 700;
            text-decoration: none;
        }

        .help-text a:hover {
            text-decoration: underline;
        }

        .right-footer {
            position: relative;
            z-index: 1;
            width: 100%;
            text-align: center;
            font-size: 12px;
            color: #a9b4c4;
            padding-top: 28px;
        }

        /* ── Responsive ── */
        @media (min-width: 992px) and (max-height: 820px) {
            .left-panel { padding: 32px 44px; }
            .left-logo { font-size: 22px; }
            .left-eyebrow { margin-bottom: 14px; }
            .left-headline { font-size: clamp(28px, 3vw, 40px); margin-bottom: 12px; }
            .left-desc { margin-bottom: 20px; font-size: 14px; }
            .role-cards { gap: 6px; }
            .role-card { padding: 9px 14px; }
            .left-footer { margin-top: 16px; }
        }

        @media (max-width: 991.98px) {
            body {
                overflow: auto;
            }

            .login-wrap {
                flex-direction: column;
                height: auto;
                min-height: 100vh;
            }

            .left-panel {
                width: 100%;
                padding: 36px 28px 32px;
                min-height: auto;
            }

            .deco-circle {
                display: none;
            }

            .role-cards {
                display: none;
            }

            .left-desc {
                margin-bottom: 24px;
            }

            .left-footer {
                margin-top: 8px;
            }

            .right-panel {
                padding: 40px 28px 20px;
            }
        }

        @media (max-width: 575.98px) {
            .left-panel {
                padding: 28px 20px 24px;
            }

            .left-headline {
                font-size: 30px;
            }

            .right-panel {
                padding: 32px 20px 16px;
            }

            .form-title {
                font-size: 26px;
            }

            .form-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrap">

        {{-- LEFT PANEL --}}
        <div class="left-panel">
            <div class="deco-circle"></div>
            <div class="deco-circle c2"></div>
            <div class="deco-circle c3"></div>

            <a href="{{ url('/') }}" class="left-logo">Digital Solutions<br>Request<span> System</span></a>

            <div class="left-main">
                <div class="left-eyebrow">
                    <div class="dot"></div>Management Systems and Technology
                </div>

                <h1 class="left-headline">Ideas<br>Systems<br><em>Progress.</em></h1>

                <p class="left-desc">
                    Submit. Collaborate. Build.<br>
                    Turn your ideas into real solutions.
                </p>

                <div class="role-cards">
                    <div class="role-card">
                        <div class="role-icon"><i class="bi bi-file-earmark-plus"></i></div>
                        <div>
                            <div class="role-title">Submit Requests</div>
                            <div class="role-desc">Start a new digital solution request in minutes</div>
                        </div>
                    </div>
                    <div class="role-card">
                        <div class="role-icon"><i class="bi bi-activity"></i></div>
                        <div>
                            <div class="role-title">Track Progress</div>
                            <div class="role-desc">Stay updated in real time as your request moves forward</div>
                        </div>
                    </div>
                    <div class="role-card">
                        <div class="role-icon"><i class="bi bi-people"></i></div>
                        <div>
                            <div class="role-title">Collaborate</div>
                            <div class="role-desc">Work together with MST for better solutions</div>
                        </div>
                    </div>
                    <div class="role-card">
                        <div class="role-icon"><i class="bi bi-rocket-takeoff"></i></div>
                        <div>
                            <div class="role-title">Deliver Impact</div>
                            <div class="role-desc">Build a more efficient workplace</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="left-footer">
                <i class="bi bi-arrow-right"></i>
                <div>Smarter Systems.<br>A More Connected Organization.</div>
            </div>
        </div>

        {{-- RIGHT PANEL --}}
        <div class="right-panel">
            <div class="form-box">

                <div class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 132 40" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22 4 L6 20 L22 36" fill="none" stroke="#0b2340" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
                        <text x="30" y="30" font-family="Nunito Sans, sans-serif" font-weight="900" font-size="30" fill="#0b2340" letter-spacing="-1">ICT</text>
                    </svg>
                </div>

                <div class="form-eyebrow">Welcome back</div>
                <h2 class="form-title">Sign in to<br><em>{{ config('app.name') }}</em></h2>
                <p class="form-sub">
                    Use your company email and password. You'll be redirected to your role's dashboard automatically.
                </p>

                @if ($errors->any())
                    <div class="error-box" role="alert">
                        <i class="bi bi-exclamation-circle-fill" style="font-size:16px;margin-top:1px"></i>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" id="loginForm" novalidate>
                    @csrf

                    <div class="field-wrap">
                        <label class="field-label" for="email">Company Email</label>
                        <div class="input-wrap">
                            <input
                                type="email"
                                class="form-input @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="name@company.com"
                                autocomplete="email"
                                autofocus
                                required
                            >
                            <i class="bi bi-envelope input-icon"></i>
                        </div>
                    </div>

                    <div class="field-wrap">
                        <label class="field-label" for="password">Password</label>
                        <div class="input-wrap">
                            <input
                                type="password"
                                class="form-input @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                style="padding-right:44px"
                                required
                            >
                            <i class="bi bi-lock input-icon"></i>
                            <button class="pw-toggle" id="pwToggle" type="button" tabindex="-1" aria-label="Show password">
                                <i class="bi bi-eye" id="pwIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="remember-label">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            Remember me
                        </label>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-login" id="btnLogin">
                        <span class="spinner"></span>
                        <span class="btn-text"><i class="bi bi-arrow-right-circle me-1"></i>Sign In</span>
                    </button>
                </form>

                <div class="divider">OR</div>

                <a href="#" class="btn-login btn-ms">
                    <svg width="18" height="18" viewBox="0 0 21 21" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <rect x="1" y="1" width="9" height="9" fill="#f25022"></rect>
                        <rect x="11" y="1" width="9" height="9" fill="#7fba00"></rect>
                        <rect x="1" y="11" width="9" height="9" fill="#00a4ef"></rect>
                        <rect x="11" y="11" width="9" height="9" fill="#ffb900"></rect>
                    </svg>
                    Sign in with Microsoft
                </a>

                <p class="help-text">
                    Need access? Contact the <a href="#">Management Systems and Technology</a> team.
                </p>

            </div>

            <div class="right-footer">{{ config('app.name') }} &nbsp;·&nbsp; Secure internal access</div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const pwInput = document.getElementById('password');
            const pwToggle = document.getElementById('pwToggle');
            const pwIcon = document.getElementById('pwIcon');
            const form = document.getElementById('loginForm');
            const btn = document.getElementById('btnLogin');

            pwToggle.addEventListener('click', function () {
                const isText = pwInput.type === 'text';
                pwInput.type = isText ? 'password' : 'text';
                pwIcon.classList.toggle('bi-eye', isText);
                pwIcon.classList.toggle('bi-eye-slash', !isText);
                pwToggle.setAttribute('aria-label', isText ? 'Show password' : 'Hide password');
            });

            form.addEventListener('submit', function (e) {
                const email = document.getElementById('email');
                let ok = true;

                [email, pwInput].forEach(function (input) {
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        ok = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                if (!ok) {
                    e.preventDefault();
                    (email.value.trim() ? pwInput : email).focus();
                    return;
                }

                btn.classList.add('loading');
                btn.disabled = true;
            });

            [document.getElementById('email'), pwInput].forEach(function (input) {
                input.addEventListener('input', function () {
                    input.classList.remove('is-invalid');
                });
            });
        })();
    </script>
</body>

</html>
