/* ── Admin Lab Results JS ──────────────────────────────────── */

const LAB_API = '../../routes/lab_api.php';

// ══ Load & render lab cards ═══════════════════════════════════
async function loadLabs() {
    const grid      = document.getElementById('labGrid');
    const patientId = document.getElementById('filterPatientId').value;
    const labType   = document.getElementById('filterType').value;

    grid.innerHTML = '<div class="lab-empty"><i class=\'bx bx-loader-circle bx-spin\'></i>Loading…</div>';

    const params = new URLSearchParams({ action: 'read' });
    if (patientId) params.set('patient_id', patientId);
    if (labType)   params.set('lab_type',   labType);

    try {
        const res  = await fetch(`${LAB_API}?${params}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.error);
        renderLabs(json.data);
    } catch (e) {
        grid.innerHTML = `<div class="lab-empty"><i class='bx bx-error'></i>Error loading data: ${esc(e.message)}</div>`;
    }
}

function renderLabs(rows) {
    const grid = document.getElementById('labGrid');
    if (!rows.length) {
        grid.innerHTML = '<div class="lab-empty"><i class=\'bx bx-folder-open\'></i>No lab results found.</div>';
        return;
    }

    const imgMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    grid.innerHTML = rows.map(r => {
        const isImg    = imgMimes.includes(r.file_mime);
        const isPdf    = r.file_mime === 'application/pdf';
        const fileUrl  = `../uploads/lab_results/${esc(r.file_path)}`;
        const sizeLabel = formatBytes(r.file_size);
        const dateLabel = formatDate(r.dateCreated);

        const thumb = isImg
            ? `<img src="${fileUrl}" class="lab-thumb" alt="Preview" onerror="this.style.display='none'">`
            : `<div class="lab-thumb-icon"><i class='bx ${isPdf ? 'bxs-file-pdf' : 'bx-file'}'></i></div>`;

        const notes = r.notes
            ? `<div class="lab-notes">${esc(r.notes)}</div>`
            : '';

        return `
        <div class="lab-card">
            <div class="lab-card-top">
                ${thumb}
                <div class="lab-card-info">
                    <span class="lab-badge">${esc(r.lab_type)}</span>
                    <div class="lab-patient">${esc(r.patient_name)}</div>
                    <div class="lab-filename" title="${esc(r.file_name)}">${esc(r.file_name)}</div>
                </div>
            </div>
            <div class="lab-meta">
                <span><i class='bx bx-calendar'></i> ${dateLabel}</span>
                <span><i class='bx bx-data'></i> ${sizeLabel}</span>
                <span><i class='bx bx-user'></i> ${esc(r.patient_type ?? '')}</span>
            </div>
            ${notes}
            <div class="lab-actions">
                <a class="btn-view" href="javascript:void(0)" onclick="viewFile('${esc(r.file_path)}','${esc(r.file_name)}','${esc(r.file_mime)}','${esc(r.lab_type)} — ${esc(r.patient_name)}')">
                    <i class='bx bx-show'></i> View
                </a>
                <button class="btn-del" onclick="deleteResult(${r.lab_id}, '${esc(r.file_name)}')">
                    <i class='bx bx-trash'></i> Delete
                </button>
            </div>
        </div>`;
    }).join('');
}

// ══ View / preview modal ══════════════════════════════════════
function viewFile(path, name, mime, title) {
    const modal   = document.getElementById('viewModal');
    const content = document.getElementById('viewContent');
    const dlBtn   = document.getElementById('downloadBtn');
    const ttl     = document.getElementById('viewModalTitle');

    const fileUrl = `../uploads/lab_results/${encodeURIComponent(path)}`;

    ttl.textContent  = title || name;
    dlBtn.href       = fileUrl;
    dlBtn.download   = name;

    const imgMimes = ['image/jpeg','image/png','image/gif','image/webp'];

    if (imgMimes.includes(mime)) {
        content.innerHTML = `<img src="${fileUrl}" style="max-width:100%;max-height:70vh;border-radius:8px;object-fit:contain;padding:16px;" alt="${esc(name)}">`;
    } else if (mime === 'application/pdf') {
        content.innerHTML = `<iframe src="${fileUrl}" style="width:100%;height:70vh;border:none;"></iframe>`;
    } else {
        content.innerHTML = `<div style="padding:40px;color:var(--txt-3)"><i class='bx bx-file' style="font-size:3rem"></i><p>Preview not available — click Download.</p></div>`;
    }

    modal.style.display = 'flex';
}

function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
    document.getElementById('viewContent').innerHTML   = '';
}

// ══ Delete ════════════════════════════════════════════════════
async function deleteResult(id, name) {
    if (!confirm(`Delete "${name}"?\nThis action cannot be undone.`)) return;
    try {
        const res  = await fetch(`${LAB_API}?action=delete`, {
            method:  'POST',
            headers: {'Content-Type':'application/json'},
            body:    JSON.stringify({ lab_id: id })
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.error);
        showToast('Lab result deleted', 'success');
        loadLabs();
    } catch (e) {
        showToast('Delete failed: ' + e.message, 'error');
    }
}

// ══ Upload modal ══════════════════════════════════════════════
function openUploadModal() {
    document.getElementById('uploadModal').style.display = 'flex';
    document.getElementById('uploadForm').reset();
    document.getElementById('modalPatientId').value = '';
    document.getElementById('modalPatientSearch').value = '';
    clearFile();
    document.getElementById('uploadError').style.display = 'none';
}

function closeUploadModal() {
    document.getElementById('uploadModal').style.display = 'none';
}

// File input / drag-drop
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('labFile');

['dragenter','dragover'].forEach(ev => {
    dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
});
['dragleave','drop'].forEach(ev => {
    dropZone.addEventListener(ev, () => dropZone.classList.remove('drag-over'));
});
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    if (e.dataTransfer.files.length) setFile(e.dataTransfer.files[0]);
});
fileInput.addEventListener('change', () => {
    if (fileInput.files.length) setFile(fileInput.files[0]);
});

function setFile(file) {
    const iconMap = {
        'image/jpeg':'bx-image','image/png':'bx-image','image/gif':'bx-image','image/webp':'bx-image',
        'application/pdf':'bxs-file-pdf'
    };
    const icon = iconMap[file.type] || 'bx-file';

    document.getElementById('previewIcon').className = `bx ${icon}`;
    document.getElementById('previewName').textContent = file.name;
    document.getElementById('previewSize').textContent = formatBytes(file.size);
    document.getElementById('filePreview').style.display = 'flex';
    document.getElementById('dropZone').style.display    = 'none';

    // Update file input if dropped
    if (!fileInput.files.length) {
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
    }
}

function clearFile() {
    fileInput.value = '';
    document.getElementById('filePreview').style.display = 'none';
    document.getElementById('dropZone').style.display    = 'block';
}

// Upload submit
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const errDiv = document.getElementById('uploadError');
    errDiv.style.display = 'none';

    const patientId = document.getElementById('modalPatientId').value;
    const labType   = document.getElementById('modalLabType').value;
    const file      = document.getElementById('labFile').files[0];

    if (!patientId) { showErr('Please select a patient.'); return; }
    if (!labType)   { showErr('Please select a lab type.'); return; }
    if (!file)      { showErr('Please choose a file to upload.'); return; }

    const btn  = document.getElementById('uploadBtn');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = "<i class='bx bx-loader-circle bx-spin'></i> Uploading…";

    const formData = new FormData(this);
    formData.set('patient_id', patientId);

    try {
        const res  = await fetch(`${LAB_API}?action=upload`, { method: 'POST', body: formData });
        const json = await res.json();
        if (!json.success) throw new Error(json.error);
        closeUploadModal();
        showToast('Lab result uploaded successfully', 'success');
        loadLabs();
    } catch (e) {
        showErr(e.message);
    } finally {
        btn.disabled  = false;
        btn.innerHTML = orig;
    }

    function showErr(msg) {
        errDiv.textContent    = msg;
        errDiv.style.display  = 'block';
    }
});

// ══ Patient autocomplete — shared helper ═══════════════════
function initAutocomplete(inputId, listId, hiddenId, onSelect) {
    const input  = document.getElementById(inputId);
    const list   = document.getElementById(listId);
    let debounce;

    input.addEventListener('input', () => {
        clearTimeout(debounce);
        const q = input.value.trim();
        if (!q) { list.style.display = 'none'; if (hiddenId) document.getElementById(hiddenId).value = ''; return; }
        debounce = setTimeout(async () => {
            const res  = await fetch(`${LAB_API}?action=search_patients&q=${encodeURIComponent(q)}`);
            const json = await res.json();
            if (!json.data.length) { list.style.display = 'none'; return; }
            list.innerHTML = json.data.map(p =>
                `<div class="ac-item" data-id="${p.patient_id}" data-name="${esc(p.fullname)}">
                    ${esc(p.fullname)} <small style="color:var(--txt-3)">${esc(p.patient_type)}</small>
                 </div>`
            ).join('');
            list.style.display = 'block';
            list.querySelectorAll('.ac-item').forEach(item => {
                item.addEventListener('click', () => {
                    input.value = item.dataset.name;
                    if (hiddenId) document.getElementById(hiddenId).value = item.dataset.id;
                    list.style.display = 'none';
                    if (onSelect) onSelect(item.dataset.id, item.dataset.name);
                });
            });
        }, 280);
    });

    document.addEventListener('click', e => { if (!input.contains(e.target) && !list.contains(e.target)) list.style.display = 'none'; });
}

// Filter autocomplete
initAutocomplete('filterPatient', 'filterAcList', 'filterPatientId', () => loadLabs());

// Modal autocomplete
initAutocomplete('modalPatientSearch', 'modalAcList', 'modalPatientId', null);

// ══ Filter helpers ════════════════════════════════════════════
function clearFilters() {
    document.getElementById('filterPatient').value   = '';
    document.getElementById('filterPatientId').value = '';
    document.getElementById('filterType').value      = '';
    loadLabs();
}

// ══ Close modals on backdrop click ════════════════════════════
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.style.display = 'none'; });
});

// ══ Utility ══════════════════════════════════════════════════
function esc(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function formatBytes(b) { if (!b) return '—'; if (b < 1024) return b+'B'; if (b < 1048576) return (b/1024).toFixed(1)+'KB'; return (b/1048576).toFixed(1)+'MB'; }
function formatDate(d)  { if (!d) return '—'; const dt = new Date(d); return dt.toLocaleDateString('en-PH', {year:'numeric',month:'short',day:'numeric'}); }

function showToast(msg, type='success') {
    const t = document.createElement('div');
    t.className = `toast toast-${type}`;
    t.innerHTML = `<i class='bx ${type==='success'?'bx-check-circle':'bx-error-circle'}'></i> ${esc(msg)}`;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 400); }, 3400);
}

// ══ Init ══════════════════════════════════════════════════════
loadLabs();
