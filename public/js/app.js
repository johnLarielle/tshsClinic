/* ── API endpoints ─────────────────────────────── */
const API_URL          = '../routes/api.php';
const MEDICINE_API_URL = '../routes/medicine_api.php';
const SYMPTOMS_API_URL = '../routes/symptoms_api.php';

/* ── DOM references ────────────────────────────── */
const form           = document.getElementById('patientForm');
const successBanner  = document.getElementById('successBanner');
const errorAlert     = document.getElementById('errorAlert');
const errorMsg       = document.getElementById('errorMsg');
const submitBtn      = document.getElementById('submitBtn');
const medicineSelect = document.getElementById('medicine');

/* ── Init ──────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    // Auto-set today's date (hidden field)
    document.getElementById('date').value = new Date().toISOString().split('T')[0];
    loadMedicines();
    loadSymptoms();
    setupValidation();

    // Contact number — numbers only, max 11 digits
    const contactInput = document.getElementById('contact_no');
    contactInput.addEventListener('keypress', e => {
        if (!/[0-9]/.test(e.key)) e.preventDefault();
    });
    contactInput.addEventListener('input', () => {
        contactInput.value = contactInput.value.replace(/\D/g, '').slice(0, 11);
    });
    contactInput.addEventListener('paste', e => {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text');
        contactInput.value = pasted.replace(/\D/g, '').slice(0, 11);
    });
});

/* ═══════════════════════════════════════════════
   LOAD: Medicines
   ═══════════════════════════════════════════════ */
async function loadMedicines() {
    try {
        const res    = await fetch(`${MEDICINE_API_URL}?action=read`);
        const result = await res.json();

        if (result.success && result.data && result.data.length > 0) {
            medicineSelect.innerHTML = '<option value="" disabled selected>Select medicine…</option>';
            result.data.forEach(med => {
                const stock  = parseInt(med.current_stock);
                const opt    = document.createElement('option');
                opt.value    = med.medicine_name;
                opt.dataset.stock = stock;

                const mgLabel = med.milligrams ? ` ${med.milligrams}mg` : '';

                if (stock === 0) {
                    opt.textContent = `${med.medicine_name}${mgLabel} — Out of stock`;
                    opt.disabled = true;
                } else if (stock < 10) {
                    opt.textContent = `${med.medicine_name}${mgLabel} (Low: ${stock} left)`;
                } else {
                    opt.textContent = `${med.medicine_name}${mgLabel}`;
                }
                medicineSelect.appendChild(opt);
            });
        } else {
            medicineSelect.innerHTML = '<option value="" disabled selected>No medicines available</option>';
        }
    } catch {
        medicineSelect.innerHTML = '<option value="" disabled selected>Error loading medicines</option>';
    }
}

/* Medicine stock hint on selection */
medicineSelect.addEventListener('change', () => {
    const hint  = document.getElementById('stockHint');
    const opt   = medicineSelect.options[medicineSelect.selectedIndex];
    const stock = parseInt(opt?.dataset?.stock ?? 999);
    hint.className = 'stock-hint';
    if (stock === 0) {
        hint.textContent = '⛔ Out of stock';
        hint.classList.add('out');
    } else if (stock < 10) {
        hint.textContent = `⚠️ Low stock — only ${stock} unit${stock !== 1 ? 's' : ''} remaining`;
        hint.classList.add('low');
    }
    validateField('medicine');
});

/* ═══════════════════════════════════════════════
   LOAD: Symptoms dropdown
   ═══════════════════════════════════════════════ */
async function loadSymptoms() {
    const sel = document.getElementById('symptom_select');
    try {
        const res    = await fetch(`${SYMPTOMS_API_URL}?action=read`);
        const result = await res.json();

        sel.innerHTML = '<option value="" disabled selected>Select symptom…</option>';

        if (result.success && result.data && result.data.length > 0) {
            // Group by category
            const grouped = {};
            result.data.forEach(s => {
                const cat = s.category || 'General';
                if (!grouped[cat]) grouped[cat] = [];
                grouped[cat].push(s);
            });

            Object.keys(grouped).sort().forEach(cat => {
                const grp   = document.createElement('optgroup');
                grp.label   = cat;
                grouped[cat].forEach(s => {
                    const opt       = document.createElement('option');
                    opt.value       = s.symptom_name;
                    opt.textContent = s.symptom_name;
                    grp.appendChild(opt);
                });
                sel.appendChild(grp);
            });
        }

        // Always add "Other" option at the bottom
        const otherOpt       = document.createElement('option');
        otherOpt.value       = '__other__';
        otherOpt.textContent = '— Other (describe below) —';
        sel.appendChild(otherOpt);

    } catch {
        sel.innerHTML = '<option value="" disabled selected>Error loading symptoms</option>';
        const other       = document.createElement('option');
        other.value       = '__other__';
        other.textContent = 'Other / Custom';
        sel.appendChild(other);
    }
}

function handleSymptomChange(sel) {
    const group   = document.getElementById('otherReasonGroup');
    const reason  = document.getElementById('reason');
    const hidden  = document.getElementById('reason_hidden');

    if (sel.value === '__other__') {
        group.style.display = '';
        reason.required     = true;
        hidden.value        = reason.value.trim();
    } else {
        group.style.display = 'none';
        reason.required     = false;
        reason.value        = '';
        hidden.value        = sel.value;
        clearError('symptom_select');
    }
}

document.getElementById('reason').addEventListener('input', function () {
    document.getElementById('reason_hidden').value = this.value.trim();
});

/* ═══════════════════════════════════════════════
   FORM SUBMISSION
   ═══════════════════════════════════════════════ */
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!validateAll()) return;

    // Ensure reason_hidden is set
    const symSel = document.getElementById('symptom_select');
    const hidden = document.getElementById('reason_hidden');
    if (symSel.value === '__other__') {
        hidden.value = document.getElementById('reason').value.trim();
    } else {
        hidden.value = symSel.value;
    }

    if (!hidden.value) {
        setError('symptom_select', 'err-symptom', 'Please select or describe a symptom.');
        return;
    }

    setLoading(true);
    hideAlerts();

    const data = {
        name:         document.getElementById('name').value.trim(),
        patient_type: document.getElementById('patient_type').value,
        contact_no:   document.getElementById('contact_no').value.trim(),
        medicine:     document.getElementById('medicine').value,
        quantity:     document.getElementById('quantity').value,
        date:         document.getElementById('date').value,
        reason:       hidden.value,
    };

    try {
        const res    = await fetch(`${API_URL}?action=create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
        const result = await res.json();

        if (result.success) {
            showSuccess();
            resetForm(false);
        } else {
            showError(result.error || result.message || 'Submission failed. Please try again.');
        }
    } catch {
        showError('Network error. Please check your connection and try again.');
    } finally {
        setLoading(false);
    }
});

/* ═══════════════════════════════════════════════
   INLINE VALIDATION
   ═══════════════════════════════════════════════ */
function setupValidation() {
    const fields = ['name','patient_type','contact_no','medicine','quantity'];
    fields.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('blur', () => validateField(id));
        el.addEventListener('input', () => { if (document.getElementById(`grp-${id}`)?.classList.contains('is-error')) validateField(id); });
    });

    document.getElementById('reason').addEventListener('blur', () => {
        const grp = document.getElementById('otherReasonGroup');
        if (grp.style.display !== 'none') validateReason();
    });
}

function validateField(id) {
    const el = document.getElementById(id);
    if (!el) return true;
    const val = el.value.trim();

    const rules = {
        name:         { msg: 'Full name is required.', test: () => val.length > 0 },
        patient_type: { msg: 'Please select your type.', test: () => val !== '' },
        contact_no:   { msg: 'Enter an 11-digit number (e.g. 09XXXXXXXXX).', test: () => /^[0-9]{11}$/.test(val) },
        medicine:     { msg: 'Please select a medicine.', test: () => val !== '' },
        quantity:     { msg: 'Enter a valid quantity (min 1).', test: () => parseInt(val) >= 1 },
    };

    const rule = rules[id];
    if (!rule) return true;

    if (!rule.test()) {
        setError(id, `err-${id}`, rule.msg);
        return false;
    } else {
        clearError(id);
        return true;
    }
}

function validateReason() {
    const val = document.getElementById('reason').value.trim();
    if (!val) {
        setError('reason', 'err-reason', 'Please describe your symptom.');
        return false;
    }
    clearError('reason');
    return true;
}

function validateAll() {
    const fieldResults = ['name','patient_type','contact_no','medicine','quantity']
        .map(id => validateField(id));

    const symSel = document.getElementById('symptom_select');
    let symOk = true;
    if (!symSel.value) {
        document.getElementById('err-symptom').classList.add('visible');
        document.getElementById('grp-symptom_select').classList.add('is-error');
        symOk = false;
    } else {
        document.getElementById('err-symptom').classList.remove('visible');
        document.getElementById('grp-symptom_select').classList.remove('is-error');
    }

    let reasonOk = true;
    const otherGroup = document.getElementById('otherReasonGroup');
    if (otherGroup.style.display !== 'none') {
        reasonOk = validateReason();
    }

    const allOk = fieldResults.every(r => r) && symOk && reasonOk;
    if (!allOk) {
        // Scroll to first error
        const firstErr = document.querySelector('.is-error');
        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return allOk;
}

function setError(fieldId, errId, msg) {
    const grp = document.getElementById(`grp-${fieldId}`);
    const err = document.getElementById(errId);
    if (grp) { grp.classList.add('is-error'); grp.classList.remove('is-valid'); }
    if (err) {
        // Update text node (last child after the icon)
        const nodes = err.childNodes;
        for (let n of nodes) { if (n.nodeType === 3) { n.textContent = ' ' + msg; break; } }
        err.classList.add('visible');
    }
}

function clearError(fieldId) {
    const grp = document.getElementById(`grp-${fieldId}`);
    const err = document.getElementById(`err-${fieldId}`);
    if (grp) { grp.classList.remove('is-error'); grp.classList.add('is-valid'); }
    if (err) err.classList.remove('visible');
}

/* ═══════════════════════════════════════════════
   UI HELPERS
   ═══════════════════════════════════════════════ */
function setLoading(on) {
    submitBtn.classList.toggle('loading', on);
    submitBtn.disabled = on;
}

function showSuccess() {
    successBanner.classList.add('show');
    successBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => successBanner.classList.remove('show'), 6000);
}

function showError(msg) {
    errorMsg.textContent = msg;
    errorAlert.classList.add('show');
    errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => errorAlert.classList.remove('show'), 7000);
}

function hideAlerts() {
    successBanner.classList.remove('show');
    errorAlert.classList.remove('show');
}

function resetForm(scrollToTop = true) {
    form.reset();
    document.getElementById('date').value = new Date().toISOString().split('T')[0];
    document.getElementById('reason_hidden').value = '';
    document.getElementById('otherReasonGroup').style.display = 'none';
    document.getElementById('reason').required = false;
    document.getElementById('stockHint').className = 'stock-hint';
    // Clear all validation states
    document.querySelectorAll('.form-group').forEach(g => {
        g.classList.remove('is-valid', 'is-error');
    });
    document.querySelectorAll('.error-msg').forEach(e => e.classList.remove('visible'));
    if (scrollToTop) form.scrollIntoView({ behavior: 'smooth' });
}

function esc(t) {
    const el = document.createElement('div');
    el.appendChild(document.createTextNode(String(t ?? '')));
    return el.innerHTML;
}
