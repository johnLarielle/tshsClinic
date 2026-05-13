<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — TSHS Clinic</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:      #1a2a6e;
            --navy-dark: #111c52;
            --navy-mid:  #243080;
            --navy-light:#e8ecf8;
            --accent:    #2541b2;
            --txt-1:     #111827;
            --txt-2:     #4b5563;
            --txt-3:     #9ca3af;
            --border:    #e5e7eb;
            --input-bg:  #f8f9fc;
            --error:     #dc2626;
            --success:   #16a34a;
            --radius:    12px;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f0f2f8;
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        /* ── Two-panel wrapper ── */
        .split-wrap {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ═══════════════════════════════
           LEFT PANEL — login form
        ═══════════════════════════════ */
        .left-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #fff;
            padding: 48px 40px;
            position: relative;
        }

        .form-shell {
            width: 100%;
            max-width: 400px;
        }

        /* Brand strip */
        .brand-strip {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 36px;
        }
        .brand-icon-box {
            width: 38px;
            height: 38px;
            background: var(--navy);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .brand-icon-box i { font-size: 1.2rem; color: #fff; }
        .brand-name {
            font-family: 'DM Sans', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--navy);
            letter-spacing: -0.2px;
        }

        /* Heading */
        .form-heading {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--txt-1);
            letter-spacing: -0.5px;
            line-height: 1.15;
            margin-bottom: 8px;
        }
        .form-sub {
            font-size: 0.875rem;
            color: var(--txt-2);
            margin-bottom: 28px;
            line-height: 1.5;
        }

        /* Role pills */
        .role-row {
            display: flex;
            gap: 8px;
            margin-bottom: 26px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 4px;
        }
        .role-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 7px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--txt-2);
            background: transparent;
            cursor: pointer;
            transition: all 0.18s ease;
            white-space: nowrap;
        }
        .role-btn:hover:not(.active) { color: var(--navy); background: rgba(26,42,110,0.06); }
        .role-btn.active {
            background: var(--navy);
            color: #fff;
            box-shadow: 0 2px 8px rgba(26,42,110,0.28);
        }

        /* Form groups */
        .f-group {
            margin-bottom: 18px;
        }
        .f-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--txt-2);
            margin-bottom: 6px;
            letter-spacing: 0.2px;
        }
        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon {
            position: absolute;
            left: 13px;
            font-size: 1.1rem;
            color: var(--txt-3);
            pointer-events: none;
            transition: color 0.15s;
        }
        .input-wrap input {
            width: 100%;
            padding: 11px 14px 11px 38px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.92rem;
            color: var(--txt-1);
            outline: none;
            transition: border-color 0.18s, box-shadow 0.18s;
        }
        .input-wrap input::placeholder { color: var(--txt-3); }
        .input-wrap input:focus {
            border-color: var(--navy);
            box-shadow: 0 0 0 3px rgba(26,42,110,0.1);
            background: #fff;
        }
        .input-wrap input:focus ~ .input-icon,
        .input-wrap:focus-within .input-icon { color: var(--navy); }

        /* Password eye toggle */
        .pw-toggle {
            position: absolute;
            right: 13px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            color: var(--txt-3);
            padding: 2px;
            display: flex;
            align-items: center;
            transition: color 0.15s;
        }
        .pw-toggle:hover { color: var(--navy); }

        /* Error message */
        .msg-box {
            display: none;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 0.83rem;
            font-weight: 500;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .msg-box.error   { background: #fef2f2; color: var(--error);   border: 1px solid #fecaca; }
        .msg-box.success { background: #f0fdf4; color: var(--success); border: 1px solid #bbf7d0; }
        .msg-box { display: none; }

        /* Sign in button */
        .btn-signin {
            width: 100%;
            padding: 12px 20px;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: 0.2px;
            transition: background 0.18s, transform 0.14s, box-shadow 0.18s;
            margin-top: 6px;
        }
        .btn-signin:hover {
            background: var(--navy-mid);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(26,42,110,0.28);
        }
        .btn-signin:active { transform: translateY(0); box-shadow: none; }
        .btn-signin:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }
        .btn-signin i { font-size: 1.1rem; }

        /* Footer note */
        .form-footer {
            margin-top: 28px;
            text-align: center;
            font-size: 0.78rem;
            color: var(--txt-3);
        }
        .form-footer a {
            color: var(--navy);
            font-weight: 600;
            text-decoration: none;
        }
        .form-footer a:hover { text-decoration: underline; }

        /* Left panel bottom watermark */
        .left-watermark {
            position: absolute;
            bottom: 20px;
            left: 0; right: 0;
            text-align: center;
            font-size: 0.7rem;
            color: var(--txt-3);
        }

        /* ═══════════════════════════════
           RIGHT PANEL — school branding
        ═══════════════════════════════ */
        .right-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            background: radial-gradient(ellipse at 40% 40%, #223082 0%, #1a2a6e 40%, #0f1a4a 75%, #08102e 100%);
        }

        /* Decorative blobs */
        .right-panel::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
            top: -120px; right: -120px;
        }
        .right-panel::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,0.025);
            bottom: -80px; left: -80px;
        }

        .right-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 28px;
            z-index: 1;
            padding: 40px 24px;
        }

        /* Seal frame */
        .seal-frame {
            width: 260px;
            height: 260px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.18);
            box-shadow:
                0 0 0 8px rgba(255,255,255,0.05),
                0 0 40px rgba(100,130,255,0.25),
                0 0 80px rgba(100,130,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(4px);
            position: relative;
            animation: sealGlow 4s ease-in-out infinite alternate;
        }
        @keyframes sealGlow {
            from { box-shadow: 0 0 0 8px rgba(255,255,255,0.05), 0 0 40px rgba(100,130,255,0.2), 0 0 80px rgba(100,130,255,0.08); }
            to   { box-shadow: 0 0 0 8px rgba(255,255,255,0.08), 0 0 60px rgba(120,150,255,0.35), 0 0 100px rgba(120,150,255,0.15); }
        }

        /* Seal image */
        .seal-img {
            width: 220px;
            height: 220px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            background: #fff;
            padding: 10px;
        }

        /* Caption */
        .right-caption {
            text-align: center;
        }
        .right-caption .school-name {
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            color: rgba(255,255,255,0.8);
            letter-spacing: 0.5px;
        }
        .right-caption .school-est {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.4);
            margin-top: 4px;
            letter-spacing: 1px;
        }

        /* Dot separator */
        .dot-sep { display: inline-block; margin: 0 6px; opacity: 0.5; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .right-panel { display: none; }
            .left-panel { padding: 36px 24px; }
        }
    </style>
</head>
<body>

<div class="split-wrap">

    <!-- ══ LEFT — Login Form ══ -->
    <div class="left-panel">
        <div class="form-shell">

            <!-- Brand -->
            <div class="brand-strip">
                <div class="brand-icon-box">
                    <i class='bx bx-plus-medical'></i>
                </div>
                <span class="brand-name">TSHS Clinic</span>
            </div>

            <!-- Heading -->
            <h1 class="form-heading">Welcome back</h1>
            <p class="form-sub">Sign in to your clinic account to continue.</p>

            <!-- Role selector — Admin only -->
            <div style="margin-bottom:26px;">
                <div class="role-row" id="roleRow" style="display:inline-flex;width:auto;">
                    <button type="button" class="role-btn active" data-role="admin" onclick="selectRole(this)">
                        <i class='bx bxs-shield-alt-2' style="margin-right:4px;font-size:0.9em;"></i>Admin
                    </button>
                </div>
            </div>

            <!-- Alert box -->
            <div id="msgBox" class="msg-box">
                <i class='bx bx-error-circle'></i>
                <span id="msgText"></span>
            </div>

            <form id="loginForm" autocomplete="off">
                <input type="hidden" id="userTypeField" name="user_type" value="admin">

                <!-- Username -->
                <div class="f-group">
                    <label class="f-label" for="username">Username</label>
                    <div class="input-wrap">
                        <i class='bx bx-user input-icon'></i>
                        <input type="text" id="username" name="username"
                               placeholder="Enter your username" required autofocus autocomplete="username">
                    </div>
                </div>

                <!-- Password -->
                <div class="f-group">
                    <label class="f-label" for="password">Password</label>
                    <div class="input-wrap">
                        <i class='bx bx-lock-alt input-icon'></i>
                        <input type="password" id="password" name="password"
                               placeholder="Enter your password" required autocomplete="current-password">
                        <button type="button" class="pw-toggle" id="pwToggle" onclick="togglePassword()" title="Show / hide password">
                            <i class='bx bx-hide' id="pwEye"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-signin" id="signInBtn">
                    <i class='bx bx-log-in-circle'></i>
                    Sign In
                </button>
            </form>

            <p class="form-footer">
                Having trouble? <a href="mailto:admin@tshs.edu.ph">Contact administrator</a>
            </p>

        </div><!-- /form-shell -->

        <div class="left-watermark">&copy; <?php echo date('Y'); ?> Talavera Senior High School &mdash; Clinic Management System</div>
    </div><!-- /left-panel -->

    <!-- ══ RIGHT — School Branding ══ -->
    <div class="right-panel">
        <div class="right-inner">

            <!-- School seal -->
            <div class="seal-frame">
                <img src="assets/tshs-logo.png" alt="Talavera Senior High School Seal" class="seal-img">
            </div><!-- /seal-frame -->

            <!-- Caption -->
            <div class="right-caption">
                <div class="school-name">Talavera Senior High School</div>
                <div class="school-est">CLINIC MANAGEMENT SYSTEM<span class="dot-sep">·</span>EST. 2017</div>
            </div>

        </div><!-- /right-inner -->
    </div><!-- /right-panel -->

</div><!-- /split-wrap -->

<script>
    let selectedRole = 'admin';

    function selectRole(btn) {
        document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedRole = btn.dataset.role;
        document.getElementById('userTypeField').value = selectedRole;
        clearMsg();
    }

    // Always admin — set on load
    document.getElementById('userTypeField').value = 'admin';

    function togglePassword() {
        const pw  = document.getElementById('password');
        const eye = document.getElementById('pwEye');
        if (pw.type === 'password') {
            pw.type  = 'text';
            eye.className = 'bx bx-show';
        } else {
            pw.type  = 'password';
            eye.className = 'bx bx-hide';
        }
    }

    function showMsg(text, type) {
        const box  = document.getElementById('msgBox');
        const span = document.getElementById('msgText');
        const icon = box.querySelector('i');
        span.textContent = text;
        box.className    = `msg-box ${type}`;
        icon.className   = type === 'error' ? 'bx bx-error-circle' : 'bx bx-check-circle';
        box.style.display = 'flex';
    }
    function clearMsg() {
        document.getElementById('msgBox').style.display = 'none';
    }

    document.getElementById('loginForm').addEventListener('submit', async e => {
        e.preventDefault();
        clearMsg();

        const btn      = document.getElementById('signInBtn');
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;

        if (!username || !password) {
            showMsg('Please fill in all fields.', 'error');
            return;
        }

        btn.disabled     = true;
        btn.innerHTML    = '<i class=\'bx bx-loader-alt bx-spin\'></i> Signing in…';

        try {
            const res    = await fetch('../routes/auth_api.php?action=login', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ username, password, user_type: selectedRole }),
            });
            const result = await res.json();

            if (result.success) {
                showMsg('Login successful! Redirecting…', 'success');
                setTimeout(() => { window.location.href = 'admin/dashboard.php'; }, 900);
            } else {
                showMsg(result.error || 'Invalid username or password.', 'error');
                btn.disabled  = false;
                btn.innerHTML = '<i class=\'bx bx-log-in-circle\'></i> Sign In';
            }
        } catch (err) {
            console.error(err);
            showMsg('Connection error. Please try again.', 'error');
            btn.disabled  = false;
            btn.innerHTML = '<i class=\'bx bx-log-in-circle\'></i> Sign In';
        }
    });
</script>
</body>
</html>
