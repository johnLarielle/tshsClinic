<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Information Form — MedRecord</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ── Variables ─────────────────────────────── */
        :root {
            --primary:       #1d4ed8;
            --primary-dark:  #1e3a8a;
            --primary-mid:   #2563eb;
            --primary-light: #eff6ff;
            --primary-ring:  rgba(37,99,235,0.18);
            --accent:        #06b6d4;
            --success:       #10b981;
            --danger:        #ef4444;
            --warning:       #f59e0b;

            --body-bg:  #f0f7ff;
            --card-bg:  #ffffff;
            --nav-bg:   #ffffff;
            --txt-1:    #1e293b;
            --txt-2:    #64748b;
            --txt-3:    #94a3b8;
            --border:   #e2e8f0;
            --hover-bg: #f8fafc;

            --r-sm: 6px;
            --r-md: 10px;
            --r-lg: 14px;
            --r-xl: 20px;
            --r-2xl:28px;

            --sh-sm: 0 1px 3px rgba(0,0,0,0.07);
            --sh-md: 0 4px 24px rgba(0,0,0,0.09);
            --sh-lg: 0 16px 56px rgba(0,0,0,0.14);

            --ease: all 0.22s ease;
        }

        /* ── Reset ──────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 15px; scroll-behavior: smooth; }
        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: var(--body-bg);
            color: var(--txt-1);
            line-height: 1.6;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Navbar ─────────────────────────────────── */
        .navbar {
            background: var(--nav-bg);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--sh-sm);
            position: sticky; top: 0; z-index: 100;
        }
        .navbar-inner {
            max-width: 860px;
            margin: 0 auto;
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .navbar-brand {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none;
        }
        .brand-pill {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: var(--r-md);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.15em;
            box-shadow: 0 4px 10px rgba(29,78,216,0.35);
        }
        .brand-text {
            font-size: 1.05em; font-weight: 700;
            color: var(--txt-1); letter-spacing: -0.2px;
        }
        .brand-text span { color: var(--primary-mid); }

        /* ── Main wrapper ────────────────────────────── */
        .main-wrapper {
            max-width: 780px;
            margin: 0 auto;
            padding: 40px 20px 60px;
        }

        /* ── Hero ───────────────────────────────────── */
        .hero {
            text-align: center;
            margin-bottom: 32px;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--primary-light);
            color: var(--primary-mid);
            border: 1.5px solid #bfdbfe;
            border-radius: 99px;
            padding: 5px 14px;
            font-size: 0.78em; font-weight: 700;
            letter-spacing: 0.3px;
            margin-bottom: 14px;
            text-transform: uppercase;
        }
        .hero h1 {
            font-size: 2em; font-weight: 800;
            color: var(--txt-1); letter-spacing: -0.5px;
            line-height: 1.2; margin-bottom: 10px;
        }
        .hero h1 span { color: var(--primary-mid); }
        .hero p {
            font-size: 0.95em; color: var(--txt-2);
            max-width: 440px; margin: 0 auto;
        }

        /* ── Card ───────────────────────────────────── */
        .form-card {
            background: var(--card-bg);
            border-radius: var(--r-2xl);
            box-shadow: var(--sh-lg);
            border: 1px solid #dbeafe;
            overflow: hidden;
        }

        /* Section header */
        .section-header {
            display: flex; align-items: center; gap: 10px;
            padding: 18px 28px 0;
            margin-bottom: 18px;
        }
        .section-icon {
            width: 34px; height: 34px;
            border-radius: var(--r-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1em; flex-shrink: 0;
        }
        .section-icon.blue   { background: #eff6ff; color: #2563eb; }
        .section-icon.teal   { background: #ecfeff; color: #0891b2; }

        .section-label {
            font-size: 0.9em; font-weight: 700;
            color: var(--txt-1); letter-spacing: -0.1px;
        }
        .section-sublabel { font-size: 0.78em; color: var(--txt-3); }

        /* Divider */
        .section-divider {
            height: 1px; background: var(--border);
            margin: 0 28px 22px;
        }

        /* Form body */
        .form-body { padding: 0 28px 8px; }

        /* Form grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }
        .form-grid.cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        /* Form group */
        .form-group { display: flex; flex-direction: column; gap: 5px; position: relative; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group.col-2 { grid-column: span 2; }

        .form-label {
            font-size: 0.83em; font-weight: 600;
            color: var(--txt-2);
            display: flex; align-items: center; gap: 5px;
        }
        .form-label i { font-size: 1em; color: var(--txt-3); }
        .req { color: var(--danger); }

        /* Input wrapper */
        .input-wrap { position: relative; }
        .input-wrap .icon {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: var(--txt-3); font-size: 1.1em; pointer-events: none;
            transition: color 0.2s;
        }
        .input-wrap input,
        .input-wrap select,
        .input-wrap textarea {
            padding-left: 38px;
        }

        /* Controls */
        input, select, textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: var(--r-md);
            font-size: 0.9em;
            color: var(--txt-1);
            font-family: inherit;
            background: var(--card-bg);
            transition: var(--ease);
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-mid);
            box-shadow: 0 0 0 3px var(--primary-ring);
        }
        .input-wrap:focus-within .icon { color: var(--primary-mid); }
        input::placeholder, textarea::placeholder { color: var(--txt-3); }
        select option:disabled { color: var(--txt-3); font-style: italic; }
        textarea { resize: vertical; min-height: 92px; padding-left: 14px !important; }

        /* Validation states */
        .form-group.is-valid input,
        .form-group.is-valid select,
        .form-group.is-valid textarea { border-color: var(--success); }
        .form-group.is-valid .icon { color: var(--success); }

        .form-group.is-error input,
        .form-group.is-error select,
        .form-group.is-error textarea { border-color: var(--danger); box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }
        .form-group.is-error .icon { color: var(--danger); }

        .error-msg {
            font-size: 0.76em; color: var(--danger);
            display: none; margin-top: 2px;
            font-weight: 500;
            display: flex; align-items: center; gap: 4px;
        }
        .error-msg.visible { display: flex; }

        /* Out-of-stock hint */
        .stock-hint {
            font-size: 0.76em; color: var(--txt-3);
            margin-top: 3px; display: none;
        }
        .stock-hint.low  { color: var(--warning); display: block; }
        .stock-hint.out  { color: var(--danger);  display: block; }

        /* ── Form actions ────────────────────────────── */
        .form-footer {
            padding: 22px 28px 28px;
            background: var(--hover-bg);
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 11px 22px;
            border: none; border-radius: var(--r-md);
            font-size: 0.9em; font-weight: 600;
            cursor: pointer; transition: var(--ease); font-family: inherit;
        }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn i { font-size: 1.1em; }

        .btn-primary {
            background: var(--primary-mid); color: white;
            flex: 1; justify-content: center;
        }
        .btn-primary:hover:not(:disabled) {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(37,99,235,0.4);
        }
        .btn-primary.loading { pointer-events: none; }
        .btn-primary.loading .btn-text { display: none; }
        .btn-primary .btn-spinner { display: none; }
        .btn-primary.loading .btn-spinner { display: inline-block; }

        .btn-ghost {
            background: white; color: var(--txt-2);
            border: 1.5px solid var(--border);
        }
        .btn-ghost:hover { background: var(--border); color: var(--txt-1); }

        /* Spin animation */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.35); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; }

        /* ── Success banner ──────────────────────────── */
        .success-banner {
            display: none;
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 1.5px solid #6ee7b7;
            border-radius: var(--r-xl);
            padding: 28px 32px;
            text-align: center;
            margin-bottom: 24px;
            animation: fadeSlideIn 0.4s ease;
        }
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .success-banner.show { display: block; }
        .success-icon {
            width: 60px; height: 60px;
            background: var(--success);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.8em;
            margin: 0 auto 14px;
            box-shadow: 0 6px 20px rgba(16,185,129,0.35);
        }
        .success-title { font-size: 1.15em; font-weight: 800; color: #065f46; margin-bottom: 5px; }
        .success-sub   { font-size: 0.88em; color: #047857; }

        /* ── Alert (error) ───────────────────────────── */
        .alert {
            padding: 13px 16px;
            border-radius: var(--r-md);
            margin-bottom: 18px;
            font-size: 0.88em; font-weight: 600;
            display: none; align-items: center; gap: 8px;
        }
        .alert.show { display: flex; }
        .alert.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        /* ── Footer ─────────────────────────────────── */
        .page-footer {
            text-align: center; padding: 32px 20px;
            font-size: 0.8em; color: var(--txt-3);
        }

        /* ── Responsive ─────────────────────────────── */
        @media (max-width: 600px) {
            .main-wrapper { padding: 24px 14px 48px; }
            .hero h1 { font-size: 1.55em; }
            .form-body { padding: 0 18px 8px; }
            .section-header { padding: 16px 18px 0; }
            .section-divider { margin: 0 18px 18px; }
            .form-footer { padding: 18px; flex-direction: column; }
            .btn-primary { width: 100%; }
            .form-grid.cols-3 { grid-template-columns: 1fr; }
            .navbar-inner { padding: 0 16px; }
            .records-header { flex-direction: column; align-items: flex-start; gap: 8px; }
        }
        @media (max-width: 480px) {
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- ── Navbar ── -->
<nav class="navbar">
    <div class="navbar-inner">
        <a href="#" class="navbar-brand">
            <div class="brand-pill"><i class='bx bx-plus-medical'></i></div>
            <span class="brand-text">TSHS <span>Clinic</span></span>
        </a>
    </div>
</nav>

<!-- ── Main ── -->
<div class="main-wrapper">

    <!-- Hero -->
    <div class="hero">
        <div class="hero-badge"><i class='bx bx-heart-circle'></i> Clinic Health Records</div>
        <h1>Patient <span>Information</span> Form</h1>
        <p>Please fill out all fields accurately. Your information will be kept secure and confidential.</p>
    </div>

    <!-- Success banner (shown after submit) -->
    <div class="success-banner" id="successBanner">
        <div class="success-icon"><i class='bx bx-check'></i></div>
        <div class="success-title">Record Submitted Successfully!</div>
        <div class="success-sub">Your patient record has been saved. Thank you!</div>
    </div>

    <!-- Error alert -->
    <div class="alert error" id="errorAlert">
        <i class='bx bx-error-circle'></i>
        <span id="errorMsg">An error occurred. Please try again.</span>
    </div>

    <!-- Form Card -->
    <div class="form-card">
        <form id="patientForm" novalidate>

            <!-- ── Section 1: Personal Information ── -->
            <div class="section-header">
                <div class="section-icon blue"><i class='bx bxs-user'></i></div>
                <div>
                    <div class="section-label">Personal Information</div>
                    <div class="section-sublabel">Tell us a little about yourself</div>
                </div>
            </div>
            <div class="section-divider"></div>

            <div class="form-body">
                <div class="form-grid">
                    <!-- Full Name -->
                    <div class="form-group full" id="grp-name">
                        <label class="form-label" for="name">
                            <i class='bx bx-user'></i> Full Name <span class="req">*</span>
                        </label>
                        <div class="input-wrap">
                            <i class='bx bx-user icon'></i>
                            <input type="text" id="name" name="name" placeholder="e.g. Maria Santos" required autocomplete="name">
                        </div>
                        <span class="error-msg" id="err-name"><i class='bx bx-error-circle'></i> Full name is required.</span>
                    </div>

                    <!-- Patient Type -->
                    <div class="form-group" id="grp-patient_type">
                        <label class="form-label" for="patient_type">
                            <i class='bx bx-id-card'></i> Patient Type <span class="req">*</span>
                        </label>
                        <div class="input-wrap">
                            <i class='bx bx-id-card icon'></i>
                            <select id="patient_type" name="patient_type" required>
                                <option value="" disabled selected>Select type…</option>
                                <option value="Student">Student</option>
                                <option value="Faculty">Faculty</option>
                                <option value="Staff">Staff</option>
                                <option value="Visitor">Visitor</option>
                            </select>
                        </div>
                        <span class="error-msg" id="err-patient_type"><i class='bx bx-error-circle'></i> Please select your type.</span>
                    </div>

                    <!-- Contact Number -->
                    <div class="form-group" id="grp-contact_no">
                        <label class="form-label" for="contact_no">
                            <i class='bx bx-phone'></i> Contact Number <span class="req">*</span>
                        </label>
                        <div class="input-wrap">
                            <i class='bx bx-phone icon'></i>
                            <input type="tel" id="contact_no" name="contact_no" placeholder="09XX-XXX-XXXX" required autocomplete="tel">
                        </div>
                        <span class="error-msg" id="err-contact_no"><i class='bx bx-error-circle'></i> Contact number is required.</span>
                    </div>
                </div>
            </div>

            <!-- ── Section 2: Medical Information ── -->
            <div class="section-header" style="margin-top:10px;">
                <div class="section-icon teal"><i class='bx bx-capsule'></i></div>
                <div>
                    <div class="section-label">Medical Information</div>
                    <div class="section-sublabel">Details about medicine and symptoms</div>
                </div>
            </div>
            <div class="section-divider"></div>

            <div class="form-body">
                <div class="form-grid">
                    <!-- Medicine -->
                    <div class="form-group col-2" id="grp-medicine">
                        <label class="form-label" for="medicine">
                            <i class='bx bx-capsule'></i> Medicine <span class="req">*</span>
                        </label>
                        <div class="input-wrap">
                            <i class='bx bx-capsule icon'></i>
                            <select id="medicine" name="medicine" required>
                                <option value="" disabled selected>Loading medicines…</option>
                            </select>
                        </div>
                        <span class="stock-hint" id="stockHint"></span>
                        <span class="error-msg" id="err-medicine"><i class='bx bx-error-circle'></i> Please select a medicine.</span>
                    </div>

                    <!-- Quantity -->
                    <div class="form-group" id="grp-quantity">
                        <label class="form-label" for="quantity">
                            <i class='bx bx-hash'></i> Quantity <span class="req">*</span>
                        </label>
                        <div class="input-wrap">
                            <i class='bx bx-hash icon'></i>
                            <input type="number" id="quantity" name="quantity" min="1" max="999" placeholder="1" required>
                        </div>
                        <span class="error-msg" id="err-quantity"><i class='bx bx-error-circle'></i> Enter a valid quantity (min 1).</span>
                    </div>

                    <!-- Date (hidden, auto-set to today) -->
                    <input type="hidden" id="date" name="date">

                    <!-- Symptom dropdown -->
                    <div class="form-group full" id="grp-symptom_select">
                        <label class="form-label" for="symptom_select">
                            <i class='bx bx-heart'></i> Symptom / Reason <span class="req">*</span>
                        </label>
                        <div class="input-wrap">
                            <i class='bx bx-heart icon'></i>
                            <select id="symptom_select" onchange="handleSymptomChange(this)">
                                <option value="" disabled selected>Loading symptoms…</option>
                            </select>
                        </div>
                        <span class="error-msg" id="err-symptom"><i class='bx bx-error-circle'></i> Please select or describe a symptom.</span>
                    </div>

                    <!-- Other / custom reason (shown when "Other" selected) -->
                    <div class="form-group full" id="otherReasonGroup" style="display:none;">
                        <label class="form-label" for="reason">
                            <i class='bx bx-edit'></i> Describe your symptom <span class="req">*</span>
                        </label>
                        <textarea id="reason" placeholder="Describe the symptom or reason for your visit…"></textarea>
                        <span class="error-msg" id="err-reason"><i class='bx bx-error-circle'></i> Please describe your symptom.</span>
                    </div>

                    <!-- Hidden field carries the final reason value -->
                    <input type="hidden" id="reason_hidden" name="reason">
                </div>
            </div>

            <!-- ── Form Footer ── -->
            <div class="form-footer">
                <button type="button" class="btn btn-ghost" onclick="resetForm()">
                    <i class='bx bx-reset'></i> Clear Form
                </button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span class="btn-text"><i class='bx bx-send'></i> Submit Record</span>
                    <span class="btn-spinner"><div class="spinner"></div>&nbsp; Submitting…</span>
                </button>
            </div>

        </form>
    </div><!-- /form-card -->

</div><!-- /main-wrapper -->

<div class="page-footer">
    &copy; <?php echo date('Y'); ?> TSHS Clinic — Health Information System
</div>

<script src="js/app.js"></script>
</body>
</html>
