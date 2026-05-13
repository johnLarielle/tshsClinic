<?php
require_once __DIR__ . '/../../app/includes/check_session.php';
checkSession('admin');
$user      = getSessionUser();
$pageTitle = 'Medicine';
require_once __DIR__ . '/../../app/includes/admin_header.php';
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Medicine Management</h2>
        <p class="page-subtitle">Track medicine inventory and stock levels</p>
    </div>
    <div class="page-header-actions">
        <button class="btn btn-success" onclick="openAddModal()">
            <i class='bx bx-plus'></i> Add Medicine
        </button>
    </div>
</div>

<div class="toolbar">
    <div class="search-box">
        <i class='bx bx-search'></i>
        <input type="text" id="searchInput" placeholder="Search medicine name…" oninput="filterTable()">
    </div>
    <div style="font-size:0.82em;color:var(--txt-3);" id="medicineCount"></div>
</div>

<div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Medicine / Dosage</th>
                    <th>Description</th>
                    <th>Stock</th>
                    <th>Manufactured</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
        <tbody id="medicineTableBody">
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--txt-3);">Loading medicines…</td></tr>
        </tbody>
    </table>
</div>

<!-- ── Add / Edit Medicine Modal ── -->
<div class="modal-overlay" id="medicineModal" onclick="handleMedOverlayClick(event)">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header">
            <span class="modal-title" id="medModalTitle">Add Medicine</span>
            <button class="modal-close" onclick="closeMedModal()"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body">
                <form id="medicineForm">
                <input type="hidden" id="editId" name="medicine_id">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div class="form-group" style="grid-column:1/3;">
                        <label class="form-label">Medicine Name *</label>
                        <input type="text" id="medicine_name" name="medicine_name" class="form-control" placeholder="e.g. Biogesic" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Milligrams (mg)</label>
                        <input type="text" id="milligrams" name="milligrams" class="form-control" placeholder="e.g. 500">
                    </div>
                    <div class="form-group" id="stockFieldGroup">
                        <label class="form-label">Initial Stock *</label>
                        <input type="number" id="current_stock" name="current_stock" class="form-control" min="0" value="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Manufactured Date</label>
                        <input type="date" id="manufactured_date" name="manufactured_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" id="expiry_date" name="expiry_date" class="form-control">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:4px;">
                    <label class="form-label">Description / Notes</label>
                    <textarea id="description" name="description" class="form-control" placeholder="Usage instructions, notes…"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeMedModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class='bx bx-save'></i> Add Medicine
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Update Stock Modal ── -->
<div class="modal-overlay" id="stockModal" onclick="handleStockOverlayClick(event)">
    <div class="modal-box" style="max-width:440px;">
        <div class="modal-header">
            <span class="modal-title">Update Stock</span>
            <button class="modal-close" onclick="closeStockModal()"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body">
            <div style="background:var(--primary-light);border-radius:var(--r-md);padding:14px 16px;margin-bottom:18px;">
                <div style="font-weight:700;color:var(--txt-1);margin-bottom:2px;" id="stock_medicine_name">—</div>
                <div style="font-size:0.85em;color:var(--txt-2);">Current stock: <strong id="stock_current">—</strong></div>
            </div>
            <form id="stockForm">
                <input type="hidden" id="stock_medicine_id">
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">Action *</label>
                    <select id="stock_action" class="form-control" required>
                        <option value="add">➕ Add Stock</option>
                        <option value="subtract">➖ Remove Stock</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:4px;">
                    <label class="form-label">Quantity *</label>
                    <input type="number" id="stock_quantity" class="form-control" min="1" placeholder="Enter quantity" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeStockModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> Update Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../js/admin-medicine.js"></script>
<?php require_once __DIR__ . '/../../app/includes/admin_footer.php'; ?>
