<?php
require_once __DIR__ . '/../../app/includes/check_session.php';
checkSession('admin');
$user      = getSessionUser();
$pageTitle = 'Patient Records';
require_once __DIR__ . '/../../app/includes/admin_header.php';
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Patient Records</h2>
        <p class="page-subtitle">Manage all patient visit records</p>
    </div>
    <div class="page-header-actions">
        <button class="btn btn-success" onclick="openAddModal()">
            <i class='bx bx-plus'></i> Add Record
        </button>
    </div>
</div>

<div class="toolbar">
    <div class="search-box">
        <i class='bx bx-search'></i>
        <input type="text" id="searchInput" placeholder="Search name, medicine, reason…" oninput="filterTable()">
    </div>
    <div style="font-size:0.82em;color:var(--txt-3);" id="recordCount"></div>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient Name</th>
                <th>Type</th>
                <th>Contact</th>
                <th>Medicine</th>
                <th>Qty</th>
                <th>Symptom / Reason</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="patientTableBody">
            <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--txt-3);">Loading records…</td></tr>
        </tbody>
    </table>
</div>

<!-- ── Add / Edit Modal ── -->
<div class="modal-overlay" id="recordModal" onclick="handleOverlayClick(event)">
    <div class="modal-box" style="max-width:680px;">
        <div class="modal-header">
            <span class="modal-title" id="modalTitle">Add Patient Record</span>
            <button class="modal-close" onclick="closeModal()"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body">
            <form id="patientForm">
                <input type="hidden" id="editId"     name="id">
                <input type="hidden" id="patient_id" name="patient_id">
                <input type="hidden" id="reason_hidden" name="reason">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Patient Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Full name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Patient Type <span class="required">*</span></label>
                        <select id="patient_type" name="patient_type" class="form-control" required>
                            <option value="">Select type…</option>
                            <option value="Student">Student</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Staff">Staff</option>
                            <option value="Visitor">Visitor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number <span class="required">*</span></label>
                        <input type="tel" id="contact_no" name="contact_no" class="form-control" placeholder="0961-549-6134" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Medicine <span class="required">*</span></label>
                        <select id="medicine" name="medicine" class="form-control" required>
                            <option value="">Loading medicines…</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Quantity <span class="required">*</span></label>
                        <input type="number" id="quantity" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date <span class="required">*</span></label>
                        <input type="date" id="date" name="date" class="form-control" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Symptom / Reason <span class="required">*</span></label>
                        <select id="symptom_select" class="form-control" onchange="handleSymptomChange(this)">
                            <option value="">Loading symptoms…</option>
                        </select>
                    </div>
                    <div class="form-group full" id="otherReasonGroup" style="display:none;">
                        <label class="form-label">Describe symptom <span class="required">*</span></label>
                        <textarea id="reason" class="form-control" placeholder="Describe the symptom or reason…"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class='bx bx-save'></i> Add Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../js/admin-records.js"></script>
<?php require_once __DIR__ . '/../../app/includes/admin_footer.php'; ?>
