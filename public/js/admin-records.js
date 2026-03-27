// API endpoints
const API_URL          = '../../routes/api.php';
const MEDICINE_API_URL = '../../routes/medicine_api.php';
const SYMPTOMS_API_URL = '../../routes/symptoms_api.php';

// ─── Init ───────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadPatients();
    loadMedicines();
    loadSymptoms();

    // Close modal with Escape key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeModal();
    });
});

// ─── Modal helpers ──────────────────────────────────────────
function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').textContent = 'Add Patient Record';
    document.getElementById('submitBtn').textContent  = 'Add Record';
    document.getElementById('date').valueAsDate = new Date();
    document.getElementById('recordModal').classList.add('open');
}

function openEditModal() {
    document.getElementById('modalTitle').textContent = 'Edit Patient Record';
    document.getElementById('submitBtn').textContent  = 'Update Record';
    document.getElementById('recordModal').classList.add('open');
}

function closeModal() {
    document.getElementById('recordModal').classList.remove('open');
    resetForm();
}

// Close when clicking outside the modal box
function handleOverlayClick(e) {
    if (e.target === document.getElementById('recordModal')) closeModal();
}

// ─── Load symptoms dropdown ──────────────────────────────────
async function loadSymptoms() {
    try {
        const res    = await fetch(`${SYMPTOMS_API_URL}?action=read`);
        const result = await res.json();
        const select = document.getElementById('symptom_select');

        if (!result.success || !result.data.length) {
            select.innerHTML = '<option value="">No symptoms defined</option>';
            return;
        }

        select.innerHTML = '<option value="">— Select a symptom —</option>';

        // Render grouped <optgroup> elements
        const grouped = result.grouped || {};
        Object.keys(grouped).sort().forEach(cat => {
            const group = document.createElement('optgroup');
            group.label = cat;
            grouped[cat].forEach(s => {
                const opt = document.createElement('option');
                opt.value       = s.symptom_name;
                opt.textContent = s.symptom_name;
                group.appendChild(opt);
            });
            select.appendChild(group);
        });

        // "Other" option at the bottom
        const other = document.createElement('option');
        other.value       = '__other__';
        other.textContent = '✏️  Other (type manually)';
        select.appendChild(other);

    } catch (err) {
        console.error('Error loading symptoms:', err);
        document.getElementById('symptom_select').innerHTML = '<option value="">Error loading symptoms</option>';
    }
}

// Show/hide the free-text field based on dropdown selection
function handleSymptomChange(sel) {
    const otherGroup  = document.getElementById('otherReasonGroup');
    const reasonField = document.getElementById('reason');
    const hiddenField = document.getElementById('reason_hidden');

    if (sel.value === '__other__') {
        otherGroup.style.display  = '';
        reasonField.required      = true;
        reasonField.value         = '';
        hiddenField.value         = '';
    } else {
        otherGroup.style.display  = 'none';
        reasonField.required      = false;
        hiddenField.value         = sel.value;  // store selected symptom name
    }
}

// ─── Load medicines for dropdown ────────────────────────────
async function loadMedicines() {
    try {
        const res    = await fetch(`${MEDICINE_API_URL}?action=read`);
        const result = await res.json();
        const select = document.getElementById('medicine');

        if (result.success && result.data && result.data.length > 0) {
            select.innerHTML = '<option value="">Select medicine…</option>';
            result.data.forEach(med => {
                const opt   = document.createElement('option');
                opt.value   = med.medicine_name;
                opt.dataset.stock = med.current_stock;

                if (parseInt(med.current_stock) === 0) {
                    opt.disabled     = true;
                    opt.textContent  = `${med.medicine_name} (Out of stock)`;
                    opt.style.color  = '#999';
                } else {
                    opt.textContent = med.medicine_name;
                    if (parseInt(med.current_stock) < 10) {
                        opt.style.color      = '#dc2626';
                        opt.style.fontWeight = 'bold';
                    }
                }
                select.appendChild(opt);
            });
        } else {
            select.innerHTML = '<option value="">No medicines available</option>';
        }
    } catch (err) {
        console.error('Error loading medicines:', err);
        document.getElementById('medicine').innerHTML = '<option value="">Error loading medicines</option>';
    }
}

// ─── Load all patient records ───────────────────────────────
async function loadPatients() {
    const tbody = document.getElementById('patientTableBody');
    tbody.innerHTML = '<tr><td colspan="9" class="loading">Loading records…</td></tr>';

    try {
        const res    = await fetch(`${API_URL}?action=read`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const result = await res.json();

        if (result.success && result.data && result.data.length > 0) {
            displayPatients(result.data);
            const cnt = document.getElementById('recordCount');
            if (cnt) cnt.textContent = `${result.data.length} record${result.data.length !== 1 ? 's' : ''}`;

        } else if (result.success) {
            tbody.innerHTML = '<tr><td colspan="9"><div class="empty-state"><i class=\'bx bx-folder-open\'></i><p>No records found. Click "Add Record" to get started.</p></div></td></tr>';
        } else {
            showMessage(result.error || 'Failed to load records', 'error');
        }
    } catch (err) {
        console.error('Error loading patients:', err);
        tbody.innerHTML = '<tr><td colspan="9" class="empty-state">Error loading records. Check console for details.</td></tr>';
    }
}

// ─── Display records in table ───────────────────────────────
function displayPatients(patients) {
    const tbody = document.getElementById('patientTableBody');
    tbody.innerHTML = '';

        patients.forEach(p => {
            const row = document.createElement('tr');
            row.setAttribute('data-search',
                [p.patient_name, p.patient_type, p.medicine_name, p.reason, p.contact_no]
                    .join(' ').toLowerCase());

            const type    = p.patient_type || 'N/A';
            const badgeCls = ['Student','Faculty','Staff','Visitor'].includes(type) ? `badge-${type}` : 'badge-gray';

            row.innerHTML = `
                <td style="color:var(--txt-3);font-size:0.8em;">${escapeHtml(p.record_id || 'N/A')}</td>
                <td><strong>${escapeHtml(p.patient_name || 'N/A')}</strong></td>
                <td><span class="badge ${badgeCls}">${escapeHtml(type)}</span></td>
                <td>${escapeHtml(p.contact_no || 'N/A')}</td>
                <td>${escapeHtml(p.medicine_name || 'N/A')}</td>
                <td><strong>${escapeHtml(p.quantity ?? 0)}</strong></td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${escapeHtml(p.reason || '')}">${escapeHtml(p.reason || 'N/A')}</td>
                <td style="white-space:nowrap;">${formatDate(p.date_given)}</td>
                <td>
                    <div class="action-btns">
                        <button class="btn-tbl btn-tbl-edit"   title="Edit"   onclick="editPatient(${p.record_id})"><i class='bx bx-edit'></i></button>
                        <button class="btn-tbl btn-tbl-delete" title="Delete" onclick="deletePatient(${p.record_id})"><i class='bx bx-trash'></i></button>
                    </div>
                </td>`;
            tbody.appendChild(row);
        });
}

// ─── Live search / filter ───────────────────────────────────
function filterTable() {
    const q     = document.getElementById('searchInput').value.toLowerCase();
    const rows  = document.querySelectorAll('#patientTableBody tr[data-search]');
    rows.forEach(row => {
        row.style.display = row.dataset.search.includes(q) ? '' : 'none';
    });
}

// ─── Form submission (create + update) ─────────────────────
document.getElementById('patientForm').addEventListener('submit', async e => {
    e.preventDefault();

    // If "Other" was chosen, copy the typed text into the hidden field
    const symSel = document.getElementById('symptom_select');
    if (symSel.value === '__other__') {
        document.getElementById('reason_hidden').value = document.getElementById('reason').value.trim();
    }

    const data    = Object.fromEntries(new FormData(e.target).entries());
    const isEdit  = data.id !== '';
    const action  = isEdit ? 'update' : 'create';

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled    = true;
    submitBtn.textContent = isEdit ? 'Saving…' : 'Adding…';

    try {
        const res    = await fetch(`${API_URL}?action=${action}`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(data),
        });
        const result = await res.json();

        if (result.success) {
            showMessage(result.message, 'success');
            closeModal();
            loadPatients();
            loadMedicines();
        } else {
            showMessage(result.error || 'Operation failed', 'error');
        }
    } catch (err) {
        console.error('Error:', err);
        showMessage('An error occurred. Please try again.', 'error');
    } finally {
        submitBtn.disabled    = false;
        submitBtn.textContent = isEdit ? 'Update Record' : 'Add Record';
    }
});

// ─── Edit ───────────────────────────────────────────────────
async function editPatient(id) {
    try {
        const res    = await fetch(`${API_URL}?action=read_one&id=${id}`);
        const result = await res.json();

        if (!result.success) { showMessage('Failed to load record', 'error'); return; }

        const p = result.data;

        document.getElementById('editId').value      = p.record_id;
        document.getElementById('patient_id').value  = p.patient_id;
        document.getElementById('name').value        = p.patient_name;
        document.getElementById('patient_type').value= p.patient_type;
        document.getElementById('contact_no').value  = p.contact_no;
        document.getElementById('quantity').value    = p.quantity;
        document.getElementById('reason').value      = p.reason;
        document.getElementById('date').value        = (p.date_given || '').split(' ')[0];

        // Select medicine option
        const medSelect = document.getElementById('medicine');
        const medMatch  = Array.from(medSelect.options).find(o => o.value === p.medicine_name);
        if (medMatch) medSelect.value = p.medicine_name;

        // Select symptom — try to find in dropdown; fall back to "Other"
        const symSelect    = document.getElementById('symptom_select');
        const reasonHidden = document.getElementById('reason_hidden');
        const reasonField  = document.getElementById('reason');
        const otherGroup   = document.getElementById('otherReasonGroup');
        const symMatch     = Array.from(symSelect.options).find(o => o.value === p.reason);

        if (symMatch) {
            symSelect.value        = p.reason;
            reasonHidden.value     = p.reason;
            otherGroup.style.display = 'none';
            reasonField.required   = false;
        } else {
            symSelect.value        = '__other__';
            otherGroup.style.display = '';
            reasonField.required   = true;
            reasonField.value      = p.reason;
            reasonHidden.value     = p.reason;
        }

        openEditModal();
    } catch (err) {
        console.error('Error:', err);
        showMessage('Failed to load record', 'error');
    }
}

// ─── Delete ─────────────────────────────────────────────────
async function deletePatient(id) {
    if (!confirm('Delete this patient record? The medicine stock will be restored.')) return;

    try {
        const res    = await fetch(`${API_URL}?action=delete`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id }),
        });
        const result = await res.json();

        if (result.success) {
            showMessage(result.message, 'success');
            loadPatients();
            loadMedicines();
        } else {
            showMessage(result.error || 'Delete failed', 'error');
        }
    } catch (err) {
        console.error('Error:', err);
        showMessage('Failed to delete record', 'error');
    }
}

// ─── Helpers ────────────────────────────────────────────────
function resetForm() {
    document.getElementById('patientForm').reset();
    document.getElementById('editId').value          = '';
    document.getElementById('patient_id').value      = '';
    document.getElementById('reason_hidden').value   = '';
    document.getElementById('otherReasonGroup').style.display = 'none';
    document.getElementById('reason').required       = false;
}

function escapeHtml(text) {
    const el = document.createElement('div');
    el.appendChild(document.createTextNode(String(text ?? '')));
    return el.innerHTML;
}

function formatDate(str) {
    if (!str) return 'N/A';
    try {
        const d = new Date(str);
        if (isNaN(d)) return 'N/A';
        return d.toLocaleDateString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' });
    } catch { return 'N/A'; }
}
