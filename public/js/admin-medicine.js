// API endpoint
const API_URL = '../../routes/medicine_api.php';

// ─── Init ───────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadMedicines();

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeMedModal();
            closeStockModal();
        }
    });
});

// ─── Add/Edit Medicine Modal ─────────────────────────────────
function openAddModal() {
    resetMedForm();
    document.getElementById('medModalTitle').textContent = 'Add Medicine';
    document.getElementById('submitBtn').textContent     = 'Add Medicine';
    document.getElementById('current_stock').closest('.form-group').style.display = '';
    document.getElementById('medicineModal').classList.add('open');
    document.getElementById('medicine_name').focus();
}

function openEditMedModal() {
    document.getElementById('medModalTitle').textContent = 'Edit Medicine';
    document.getElementById('submitBtn').textContent     = 'Update Medicine';
    // Hide stock field on edit — use Stock button to adjust
    const sg = document.getElementById('stockFieldGroup');
    if (sg) sg.style.display = 'none';
    document.getElementById('medicineModal').classList.add('open');
    document.getElementById('medicine_name').focus();
}

function closeMedModal() {
    document.getElementById('medicineModal').classList.remove('open');
    resetMedForm();
}

function handleMedOverlayClick(e) {
    if (e.target === document.getElementById('medicineModal')) closeMedModal();
}

// ─── Stock Modal ─────────────────────────────────────────────
function openStockModal(medicineId, medicineName, currentStock) {
    document.getElementById('stock_medicine_id').value         = medicineId;
    document.getElementById('stock_medicine_name').textContent = medicineName;
    document.getElementById('stock_current').textContent       = currentStock;
    document.getElementById('stock_quantity').value            = '';
    document.getElementById('stock_action').value              = 'add';
    document.getElementById('stockModal').classList.add('open');
    document.getElementById('stock_quantity').focus();
}

function closeStockModal() {
    document.getElementById('stockModal').classList.remove('open');
    document.getElementById('stockForm').reset();
}

function handleStockOverlayClick(e) {
    if (e.target === document.getElementById('stockModal')) closeStockModal();
}

// ─── Load medicines ──────────────────────────────────────────
async function loadMedicines() {
    const tbody = document.getElementById('medicineTableBody');
    tbody.innerHTML = '<tr><td colspan="6" class="loading">Loading medicines…</td></tr>';

    try {
        const res    = await fetch(`${API_URL}?action=read`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const result = await res.json();

        if (result.success && result.data && result.data.length > 0) {
            displayMedicines(result.data);
        } else if (result.success) {
            tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No medicines found. Click "Add Medicine" to get started.</td></tr>';
        } else {
            showMessage(result.error || 'Failed to load medicines', 'error');
        }
    } catch (err) {
        console.error('Error loading medicines:', err);
        tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Error loading medicines. Check console for details.</td></tr>';
    }
}

// ─── Display medicines ───────────────────────────────────────
function displayMedicines(medicines) {
    const tbody = document.getElementById('medicineTableBody');
    tbody.innerHTML = '';

        medicines.forEach(med => {
            const stock = parseInt(med.current_stock);
            let stockBadge = '';
            if      (stock === 0)  stockBadge = `<span class="stock-badge stock-low">Out of Stock</span>`;
            else if (stock < 10)   stockBadge = `<span class="stock-badge stock-low">${stock} — Low</span>`;
            else if (stock < 50)   stockBadge = `<span class="stock-badge stock-medium">${stock} — Medium</span>`;
            else                   stockBadge = `<span class="stock-badge stock-good">${stock} — Good</span>`;

            const mgLabel = med.milligrams
                ? `<span style="font-size:0.75em;background:#eff6ff;color:#1d4ed8;padding:1px 6px;border-radius:4px;margin-left:5px;font-weight:600;">${escapeHtml(med.milligrams)}mg</span>`
                : '';

            const nameSafe = (med.medicine_name || '').replace(/'/g, "\\'");
            const row = document.createElement('tr');
            row.setAttribute('data-search', (med.medicine_name || '').toLowerCase());

            row.innerHTML = `
                <td style="color:var(--txt-3);font-size:0.8em;">${escapeHtml(med.medicine_id || 'N/A')}</td>
                <td><strong>${escapeHtml(med.medicine_name || 'N/A')}</strong>${mgLabel}</td>
                <td style="color:var(--txt-2);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${escapeHtml(med.description || '')}">${escapeHtml(med.description || '—')}</td>
                <td>${stockBadge}</td>
                <td style="white-space:nowrap;font-size:0.85em;color:var(--txt-2);">${formatDate(med.manufactured_date)}</td>
                <td>${expiryBadge(med.expiry_date)}</td>
                <td>
                    <div class="action-btns">
                        <button class="btn-tbl btn-tbl-stock"  title="Update Stock" onclick="openStockModal(${med.medicine_id}, '${nameSafe}', ${med.current_stock})"><i class='bx bx-package'></i></button>
                        <button class="btn-tbl btn-tbl-edit"   title="Edit"         onclick="editMedicine(${med.medicine_id})"><i class='bx bx-edit'></i></button>
                        <button class="btn-tbl btn-tbl-delete" title="Soft Delete"  onclick="deleteMedicine(${med.medicine_id})"><i class='bx bx-trash'></i></button>
                    </div>
                </td>`;
            tbody.appendChild(row);
        });

        const cnt = document.getElementById('medicineCount');
        if (cnt) cnt.textContent = `${medicines.length} medicine${medicines.length !== 1 ? 's' : ''}`;
}

// ─── Live search ─────────────────────────────────────────────
function filterTable() {
    const q    = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#medicineTableBody tr[data-search]');
    rows.forEach(row => {
        row.style.display = row.dataset.search.includes(q) ? '' : 'none';
    });
}

// ─── Form submit (create + update) ──────────────────────────
document.getElementById('medicineForm').addEventListener('submit', async e => {
    e.preventDefault();

    const data   = Object.fromEntries(new FormData(e.target).entries());
    const isEdit = data.medicine_id !== '';
    const action = isEdit ? 'update' : 'create';

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
            closeMedModal();
            loadMedicines();
        } else {
            showMessage(result.error || 'Operation failed', 'error');
        }
    } catch (err) {
        console.error('Error:', err);
        showMessage('An error occurred. Please try again.', 'error');
    } finally {
        submitBtn.disabled    = false;
        submitBtn.textContent = isEdit ? 'Update Medicine' : 'Add Medicine';
    }
});

// ─── Edit ────────────────────────────────────────────────────
async function editMedicine(id) {
    try {
        const res    = await fetch(`${API_URL}?action=read_one&id=${id}`);
        const result = await res.json();

        if (!result.success) { showMessage('Failed to load medicine', 'error'); return; }

        const med = result.data;
        document.getElementById('editId').value            = med.medicine_id;
        document.getElementById('medicine_name').value     = med.medicine_name;
        document.getElementById('milligrams').value        = med.milligrams        || '';
        document.getElementById('description').value       = med.description       || '';
        document.getElementById('manufactured_date').value = med.manufactured_date || '';
        document.getElementById('expiry_date').value       = med.expiry_date       || '';
        document.getElementById('current_stock').value     = med.current_stock;

        openEditMedModal();
    } catch (err) {
        console.error('Error:', err);
        showMessage('Failed to load medicine', 'error');
    }
}

// ─── Delete ──────────────────────────────────────────────────
async function deleteMedicine(id) {
    if (!confirm('Soft-delete this medicine? It will be hidden from the system but remains in the database.')) return;

    try {
        const res    = await fetch(`${API_URL}?action=delete`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ medicine_id: id }),
        });
        const result = await res.json();

        if (result.success) {
            showMessage(result.message, 'success');
            loadMedicines();
        } else {
            showMessage(result.error || 'Delete failed', 'error');
        }
    } catch (err) {
        console.error('Error:', err);
        showMessage('Failed to delete medicine', 'error');
    }
}

// ─── Stock form submit ────────────────────────────────────────
document.getElementById('stockForm').addEventListener('submit', async e => {
    e.preventDefault();

    const data = {
        medicine_id: document.getElementById('stock_medicine_id').value,
        type:        document.getElementById('stock_action').value,
        quantity:    document.getElementById('stock_quantity').value,
    };

    try {
        const res    = await fetch(`${API_URL}?action=update_stock`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(data),
        });
        const result = await res.json();

        if (result.success) {
            showMessage(result.message, 'success');
            closeStockModal();
            loadMedicines();
        } else {
            showMessage(result.error || 'Stock update failed', 'error');
        }
    } catch (err) {
        console.error('Error:', err);
        showMessage('Failed to update stock', 'error');
    }
});

// ─── Helpers ─────────────────────────────────────────────────
function resetMedForm() {
    document.getElementById('medicineForm').reset();
    document.getElementById('editId').value            = '';
    document.getElementById('milligrams').value        = '';
    document.getElementById('manufactured_date').value = '';
    document.getElementById('expiry_date').value       = '';
    const sg = document.getElementById('stockFieldGroup');
    if (sg) sg.style.display = '';
}

// ─── Expiry badge ─────────────────────────────────────────────
function expiryBadge(dateStr) {
    if (!dateStr) return '<span style="color:var(--txt-3);font-size:0.82em;">—</span>';
    try {
        const today   = new Date(); today.setHours(0,0,0,0);
        const expDate = new Date(dateStr + 'T00:00:00');
        const diffMs  = expDate - today;
        const diffDays = Math.ceil(diffMs / 86400000);

        if (diffDays < 0) {
            return `<span style="background:#fee2e2;color:#991b1b;font-size:0.75em;font-weight:700;padding:3px 8px;border-radius:12px;">Expired ${formatDate(dateStr)}</span>`;
        } else if (diffDays <= 30) {
            return `<span style="background:#fef3c7;color:#92400e;font-size:0.75em;font-weight:700;padding:3px 8px;border-radius:12px;">Exp. soon ${formatDate(dateStr)}</span>`;
        } else {
            return `<span style="font-size:0.85em;color:var(--txt-2);">${formatDate(dateStr)}</span>`;
        }
    } catch { return '<span style="color:var(--txt-3);font-size:0.82em;">—</span>'; }
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
