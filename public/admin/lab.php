<?php
require_once __DIR__ . '/../../app/Config/Config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php'); exit();
}
$user      = $_SESSION;
$pageTitle = 'Lab Results';

$pageHeadExtra = <<<HTML
<style>
/* ── Upload zone ───────────────────────────── */
.upload-zone {
    border: 2px dashed var(--border);
    border-radius: var(--r-lg);
    padding: 32px 20px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    background: var(--body-bg);
}
.upload-zone:hover,
.upload-zone.drag-over {
    border-color: var(--primary);
    background: var(--primary-light);
}
.upload-zone .uz-icon {
    font-size: 2.4rem;
    color: var(--primary);
    margin-bottom: 8px;
    display: block;
}
.upload-zone p     { margin: 0 0 3px; font-weight: 600; font-size:.9em; color: var(--txt-1); }
.upload-zone small { color: var(--txt-3); font-size: .77em; }
.upload-zone input[type="file"] { display: none; }

/* File preview row */
.file-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 14px;
    background: var(--primary-light);
    border: 1.5px solid rgba(59,130,246,0.25);
    border-radius: var(--r-md);
    margin-top: 10px;
}
.file-preview i        { font-size: 1.6rem; color: var(--primary); flex-shrink:0; }
.file-preview .fp-name { font-weight: 600; font-size:.86em; flex:1; word-break:break-all; color:var(--txt-1); }
.file-preview .fp-size { font-size:.74em; color:var(--txt-3); white-space:nowrap; }
.file-preview .fp-rm   {
    background: none; border: none; color: var(--txt-3);
    cursor: pointer; font-size: 1.2rem; padding: 2px;
    transition: color .15s;
}
.file-preview .fp-rm:hover { color: var(--danger); }

/* ── Lab card grid ─────────────────────────── */
.lab-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 18px;
    margin-top: 6px;
}
.lab-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    padding: 18px;
    display: flex; flex-direction: column; gap: 10px;
    transition: box-shadow .2s, transform .2s;
}
.lab-card:hover { box-shadow: var(--sh-md); transform: translateY(-2px); }
.lab-card-top   { display: flex; align-items: flex-start; gap: 12px; }
.lab-thumb {
    width: 56px; height: 56px;
    border-radius: var(--r-md);
    object-fit: cover;
    border: 1px solid var(--border);
    flex-shrink: 0;
    background: var(--body-bg);
}
.lab-thumb-icon {
    width: 56px; height: 56px;
    border-radius: var(--r-md);
    background: var(--primary-light);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; color: var(--primary); flex-shrink: 0;
}
.lab-card-info { flex: 1; min-width: 0; }
.lab-badge {
    display: inline-block;
    font-size: .7em; font-weight: 700; letter-spacing: .04em;
    padding: 2px 10px; border-radius: 20px;
    background: var(--primary-light); color: var(--primary);
    margin-bottom: 5px;
}
.lab-patient { font-weight: 700; font-size: .91em; color: var(--txt-1); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.lab-filename { font-size: .77em; color: var(--txt-3); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:1px; }
.lab-meta  { display:flex; gap:12px; font-size:.74em; color:var(--txt-3); flex-wrap:wrap; }
.lab-meta i { vertical-align: middle; }
.lab-notes { font-size:.8em; color:var(--txt-2); background:var(--body-bg); border-radius:var(--r-sm); padding:7px 10px; border:1px solid var(--border); }
.lab-actions { display:flex; gap:8px; margin-top:2px; }
.lab-actions a,
.lab-actions button {
    flex:1; display:flex; align-items:center; justify-content:center; gap:5px;
    padding:8px 0; border-radius:var(--r-md); font-size:.82em; font-weight:600;
    cursor:pointer; text-decoration:none; border:none; transition:background .18s;
    font-family: inherit;
}
.btn-view       { background:var(--primary-light); color:var(--primary); }
.btn-view:hover { background:rgba(59,130,246,.2); }
.btn-del        { background:rgba(239,68,68,.08);  color:var(--danger); }
.btn-del:hover  { background:rgba(239,68,68,.18); }

/* ── Filters bar ───────────────────────────── */
.filters-bar {
    display: flex; flex-wrap: wrap;
    gap: 10px; align-items: center;
    margin-bottom: 20px;
}
.filters-bar .ac-wrap { flex: 1; min-width: 200px; }

/* ── Autocomplete ──────────────────────────── */
.ac-wrap { position: relative; }
.ac-list {
    position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 200;
    background: var(--card-bg);
    border: 1.5px solid var(--border);
    border-radius: var(--r-md);
    box-shadow: var(--sh-md);
    max-height: 220px; overflow-y: auto; display: none;
}
.ac-item { padding: 10px 14px; cursor: pointer; font-size: .88em; }
.ac-item:hover { background: var(--body-bg); }
.ac-item small { color: var(--txt-3); margin-left: 6px; }

/* ── Empty / loading ──────────────────────── */
.lab-empty {
    grid-column: 1 / -1;
    text-align: center; padding: 64px 20px;
    color: var(--txt-3); font-size: .92em;
}
.lab-empty i { font-size: 3rem; display:block; margin-bottom:12px; opacity:.5; }

/* ── Upload modal form spacing ─────────────── */
#uploadForm .form-group { margin-bottom: 18px; }
#uploadForm .form-group:last-child { margin-bottom: 0; }

/* search input with icon */
.input-icon-wrap { position: relative; }
.input-icon-wrap .ii-icon {
    position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
    color: var(--txt-3); font-size: 1.05em; pointer-events: none;
}
.input-icon-wrap .form-control { padding-left: 34px; }
.input-icon-wrap:focus-within .ii-icon { color: var(--primary); }
</style>
HTML;

require_once __DIR__ . '/../../app/includes/admin_header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Lab Results</h1>
        <p class="page-subtitle">Upload and manage X-ray, ECG, blood test results and other lab documents</p>
    </div>
    <button class="btn btn-primary" onclick="openUploadModal()">
        <i class='bx bx-upload'></i> Upload Lab Result
    </button>
</div>

<!-- ── Filters ─────────────────────────────────────────────── -->
<div class="filters-bar">
    <div class="ac-wrap" id="filterPatientWrap">
        <div class="input-icon-wrap">
            <i class='bx bx-search ii-icon'></i>
            <input type="text" id="filterPatient" class="form-control"
                   placeholder="Search patient…" autocomplete="off"
                   style="padding-left:34px;">
        </div>
        <div class="ac-list" id="filterAcList"></div>
    </div>
    <input type="hidden" id="filterPatientId" value="">

    <select id="filterType" class="form-control" onchange="loadLabs()" style="width:auto;min-width:160px;">
        <option value="">All Types</option>
        <option>X-Ray</option>
        <option>ECG / EKG</option>
        <option>Blood Test</option>
        <option>Urinalysis</option>
        <option>Stool Analysis</option>
        <option>CBC</option>
        <option>Other</option>
    </select>

    <button class="btn btn-secondary btn-sm" onclick="clearFilters()">
        <i class='bx bx-x'></i> Clear
    </button>
</div>

<!-- ── Lab cards ───────────────────────────────────────────── -->
<div id="labGrid" class="lab-grid">
    <div class="lab-empty"><i class='bx bx-loader-circle bx-spin'></i>Loading…</div>
</div>

<!-- ════════════════════════════════════════════════════════
     Upload Modal
═════════════════════════════════════════════════════════ -->
<div id="uploadModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class='bx bx-upload' style="color:var(--primary);margin-right:6px;"></i>
                Upload Lab Result
            </h3>
            <button class="modal-close" onclick="closeUploadModal()"><i class='bx bx-x'></i></button>
        </div>

        <div class="modal-body">
            <form id="uploadForm" enctype="multipart/form-data">

                <!-- Patient search -->
                <div class="form-group">
                    <label class="form-label">Patient <span class="required">*</span></label>
                    <div class="ac-wrap" id="modalPatientWrap">
                        <div class="input-icon-wrap">
                            <i class='bx bx-search ii-icon'></i>
                            <input type="text"
                                   id="modalPatientSearch"
                                   class="form-control"
                                   placeholder="Type patient name to search…"
                                   autocomplete="off">
                        </div>
                        <div class="ac-list" id="modalAcList"></div>
                    </div>
                    <input type="hidden" id="modalPatientId" name="patient_id">
                </div>

                <!-- Lab type -->
                <div class="form-group">
                    <label class="form-label">Lab Type <span class="required">*</span></label>
                    <select name="lab_type" id="modalLabType" class="form-control">
                        <option value="" disabled selected>— Select type —</option>
                        <option>X-Ray</option>
                        <option>ECG / EKG</option>
                        <option>Blood Test</option>
                        <option>Urinalysis</option>
                        <option>Stool Analysis</option>
                        <option>CBC</option>
                        <option>Other</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="form-group">
                    <label class="form-label">
                        Notes / Remarks
                        <span style="font-weight:400;color:var(--txt-3);font-size:.9em;">(optional)</span>
                    </label>
                    <textarea name="notes" id="modalNotes"
                              class="form-control"
                              rows="2"
                              style="resize:vertical;min-height:72px;"
                              placeholder="Any remarks about this result…"></textarea>
                </div>

                <!-- File -->
                <div class="form-group">
                    <label class="form-label">
                        File <span class="required">*</span>
                        <span style="font-weight:400;color:var(--txt-3);font-size:.9em;">— JPG, PNG, PDF, WEBP · max 10 MB</span>
                    </label>

                    <div class="upload-zone" id="dropZone" onclick="document.getElementById('labFile').click()">
                        <i class='bx bx-cloud-upload uz-icon'></i>
                        <p>Click to browse or drag &amp; drop</p>
                        <small>Supported: JPG · PNG · PDF · GIF · WEBP</small>
                        <input type="file" id="labFile" name="lab_file"
                               accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
                    </div>

                    <div id="filePreview" style="display:none;" class="file-preview">
                        <i class='bx bx-file' id="previewIcon"></i>
                        <span class="fp-name" id="previewName"></span>
                        <span class="fp-size" id="previewSize"></span>
                        <button type="button" class="fp-rm" onclick="clearFile()" title="Remove file">
                            <i class='bx bx-x-circle'></i>
                        </button>
                    </div>
                </div>

                <!-- Error -->
                <div id="uploadError"
                     style="display:none;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;
                            border-radius:var(--r-md);font-size:.85em;color:#991b1b;margin-bottom:4px;">
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeUploadModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                        <i class='bx bx-upload'></i> Upload
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════
     View Modal (image/PDF preview)
═════════════════════════════════════════════════════════ -->
<div id="viewModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width:700px;">
        <div class="modal-header">
            <h3 class="modal-title" id="viewModalTitle">View File</h3>
            <button class="modal-close" onclick="closeViewModal()"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body" style="padding:0 0 16px;">
            <div id="viewContent" style="text-align:center;min-height:200px;"></div>
            <div style="padding:16px 20px 0; text-align:right;">
                <a id="downloadBtn" class="btn btn-primary btn-sm" href="#" download target="_blank">
                    <i class='bx bx-download'></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

<script src="../js/admin-lab.js"></script>

<?php require_once __DIR__ . '/../../app/includes/admin_footer.php'; ?>
