<?php
require_once __DIR__ . '/../../app/includes/check_session.php';
checkSession('admin');
$user      = getSessionUser();
$pageTitle = 'Symptoms';
require_once __DIR__ . '/../../app/includes/admin_header.php';
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Symptoms</h2>
        <p class="page-subtitle">Manage predefined symptom options used in patient records</p>
    </div>
    <div class="page-header-actions">
        <button class="btn btn-success" onclick="openAddModal()">
            <i class='bx bx-plus'></i> Add Symptom
        </button>
    </div>
</div>

<!-- Stats -->
<div id="statsRow" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 18px;font-size:0.85em;font-weight:600;color:var(--txt-2);">Loading…</div>
</div>

<div id="symptomsContainer">
    <div style="text-align:center;padding:60px;color:var(--txt-3);">Loading symptoms…</div>
</div>

<!-- Add / Edit Symptom Modal -->
<div class="modal-overlay" id="symptomModal" onclick="handleOverlayClick(event)">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-header">
            <span class="modal-title" id="modalTitle">Add Symptom</span>
            <button class="modal-close" onclick="closeModal()"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body">
            <form id="symptomForm" onsubmit="submitSymptom(event)">
                <input type="hidden" id="editId">
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">Symptom Name <span class="required">*</span></label>
                    <input type="text" id="symptomName" class="form-control" placeholder="e.g. Headache" required>
                </div>
                <div class="form-group" style="margin-bottom:4px;">
                    <label class="form-label">Category <span class="required">*</span></label>
                    <input type="text" id="symptomCategory" class="form-control" list="categoryList" placeholder="e.g. Pain, Respiratory…" required>
                    <datalist id="categoryList"></datalist>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class='bx bx-save'></i> Add Symptom
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const API = '../../routes/symptoms_api.php';
let allSymptoms = [];

async function loadSymptoms() {
    try {
        const d = await (await fetch(`${API}?action=read_all`)).json();
        if (!d.success) { showToast('Failed to load symptoms.', 'error'); return; }
        allSymptoms = d.data || [];
        updateStats();
        renderSymptoms();
        populateCategoryList();
    } catch(e) { showToast('Error loading symptoms.', 'error'); }
}

function updateStats() {
    const total   = allSymptoms.length;
    const active  = allSymptoms.filter(s => parseInt(s.is_active) === 1).length;
    const cats    = [...new Set(allSymptoms.map(s => s.category))].length;
    document.getElementById('statsRow').innerHTML = `
        <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 18px;font-size:0.85em;font-weight:600;color:var(--txt-2);">📋 Total: <strong>${total}</strong></div>
        <div style="background:var(--card-bg);border:1px solid #a7f3d0;border-radius:var(--r-md);padding:10px 18px;font-size:0.85em;font-weight:600;color:#065f46;">✅ Active: <strong>${active}</strong></div>
        <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 18px;font-size:0.85em;font-weight:600;color:var(--txt-2);">🏷️ Categories: <strong>${cats}</strong></div>
    `;
}

function renderSymptoms() {
    const grouped = {};
    allSymptoms.forEach(s => {
        const cat = s.category || 'General';
        if (!grouped[cat]) grouped[cat] = [];
        grouped[cat].push(s);
    });
    const container = document.getElementById('symptomsContainer');
    if (!allSymptoms.length) {
        container.innerHTML = `<div class="empty-state"><i class='bx bx-heart'></i><p>No symptoms yet. Click "Add Symptom" to get started.</p></div>`;
        return;
    }
    container.innerHTML = Object.keys(grouped).sort().map(cat => {
        const items = grouped[cat];
        return `<div class="category-section">
            <div class="category-header">
                <span class="category-name">${esc(cat)}</span>
                <span class="category-count">${items.length} symptom${items.length!==1?'s':''}</span>
            </div>
            <div class="symptoms-grid">
                ${items.map(s => `
                    <div class="symptom-card ${parseInt(s.is_active)===0?'inactive':''}">
                        <div>
                            <div class="symptom-name">${esc(s.symptom_name)}</div>
                            ${parseInt(s.is_active)===0?`<div class="inactive-label">Hidden</div>`:''}
                        </div>
                        <div style="display:flex;gap:5px;flex-shrink:0;">
                            <button class="btn-tbl btn-tbl-edit"   title="Edit"   onclick="openEditModal(${s.symptom_id})"><i class='bx bx-edit'></i></button>
                            <button class="btn-tbl btn-tbl-toggle" title="${parseInt(s.is_active)?'Hide':'Show'}" onclick="toggleSymptom(${s.symptom_id})" style="background:${parseInt(s.is_active)?'#fef3c7':'#f0fdf4'};color:${parseInt(s.is_active)?'#92400e':'#15803d'};"><i class='bx ${parseInt(s.is_active)?'bx-hide':'bx-show'}'></i></button>
                            <button class="btn-tbl btn-tbl-delete" title="Delete" onclick="deleteSymptom(${s.symptom_id})"><i class='bx bx-trash'></i></button>
                        </div>
                    </div>`).join('')}
            </div>
        </div>`;
    }).join('');
}

function populateCategoryList() {
    const cats = [...new Set(allSymptoms.map(s => s.category))].sort();
    const dl   = document.getElementById('categoryList');
    dl.innerHTML = cats.map(c => `<option value="${esc(c)}">`).join('');
}

// ── Modal ──────────────────────────────────────
function openAddModal() {
    document.getElementById('editId').value = '';
    document.getElementById('symptomName').value = '';
    document.getElementById('symptomCategory').value = '';
    document.getElementById('modalTitle').textContent = 'Add Symptom';
    document.getElementById('submitBtn').innerHTML = "<i class='bx bx-save'></i> Add Symptom";
    document.getElementById('symptomModal').classList.add('open');
    document.getElementById('symptomName').focus();
}

function openEditModal(id) {
    const s = allSymptoms.find(x => x.symptom_id == id);
    if (!s) return;
    document.getElementById('editId').value = s.symptom_id;
    document.getElementById('symptomName').value = s.symptom_name;
    document.getElementById('symptomCategory').value = s.category;
    document.getElementById('modalTitle').textContent = 'Edit Symptom';
    document.getElementById('submitBtn').innerHTML = "<i class='bx bx-save'></i> Update Symptom";
    document.getElementById('symptomModal').classList.add('open');
    document.getElementById('symptomName').focus();
}

function closeModal() { document.getElementById('symptomModal').classList.remove('open'); }
function handleOverlayClick(e) { if (e.target === e.currentTarget) closeModal(); }

// ── Submit ────────────────────────────────────
async function submitSymptom(e) {
    e.preventDefault();
    const id   = document.getElementById('editId').value;
    const name = document.getElementById('symptomName').value.trim();
    const cat  = document.getElementById('symptomCategory').value.trim();
    if (!name || !cat) { showToast('Please fill in all fields.', 'error'); return; }

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    try {
        const action = id ? 'update' : 'create';
        const body   = { symptom_name: name, category: cat };
        if (id) body.symptom_id = id;

        const res = await fetch(`${API}?action=${action}`, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body) });
        const d   = await res.json();
        if (d.success) {
            showToast(d.message || 'Saved successfully.', 'success');
            closeModal();
            loadSymptoms();
        } else {
            showToast(d.message || 'Failed to save.', 'error');
        }
    } catch(err) { showToast('Network error.', 'error'); }
    finally { btn.disabled = false; }
}

async function toggleSymptom(id) {
    try {
        const d = await (await fetch(`${API}?action=toggle`, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({symptom_id:id}) })).json();
        if (d.success) { showToast(d.message||'Updated.','success'); loadSymptoms(); }
        else showToast(d.message||'Failed.','error');
    } catch { showToast('Network error.','error'); }
}

async function deleteSymptom(id) {
    const s = allSymptoms.find(x => x.symptom_id == id);
    if (!confirm(`Delete "${s?.symptom_name}"? This cannot be undone.`)) return;
    try {
        const d = await (await fetch(`${API}?action=delete`, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({symptom_id:id}) })).json();
        if (d.success) { showToast(d.message||'Deleted.','success'); loadSymptoms(); }
        else showToast(d.message||'Failed to delete.','error');
    } catch { showToast('Network error.','error'); }
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
function esc(t){const el=document.createElement('div');el.appendChild(document.createTextNode(String(t??'')));return el.innerHTML;}

loadSymptoms();
</script>

<?php require_once __DIR__ . '/../../app/includes/admin_footer.php'; ?>
