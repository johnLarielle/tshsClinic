<?php
require_once __DIR__ . '/../../app/includes/check_session.php';
checkSession('admin');
$user      = getSessionUser();
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../../app/includes/admin_header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2 class="page-title">Dashboard</h2>
        <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($user['fullname'] ?? 'Admin'); ?> 👋</p>
    </div>
    <div class="page-header-actions">
        <a href="records.php" class="btn btn-primary"><i class='bx bx-plus'></i> New Record</a>
    </div>
</div>

<!-- KPI Cards -->
<div class="kpi-grid" id="kpiGrid">
    <?php for ($i = 0; $i < 6; $i++): ?>
    <div class="kpi-card">
        <div class="kpi-icon-wrap icon-blue">
            <div class="shimmer" style="width:28px;height:28px;border-radius:6px;"></div>
        </div>
        <div class="kpi-body">
            <div class="kpi-value"><div class="shimmer" style="width:60px;height:30px;"></div></div>
            <div class="kpi-label" style="margin-top:6px;"><div class="shimmer" style="width:100px;height:12px;"></div></div>
        </div>
    </div>
    <?php endfor; ?>
</div>

<!-- Bottom grid: recent records + low stock -->
<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start;" class="dashboard-grid">

    <!-- Recent Records -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Recent Patient Records</div>
                <div class="card-subtitle">Last 8 visits</div>
            </div>
            <a href="records.php" class="btn btn-ghost btn-sm">View all →</a>
        </div>
        <div class="card-body p-0">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:11px 16px;text-align:left;font-size:0.74em;font-weight:700;color:var(--txt-2);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1.5px solid var(--border);">Patient</th>
                        <th style="padding:11px 16px;text-align:left;font-size:0.74em;font-weight:700;color:var(--txt-2);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1.5px solid var(--border);">Medicine</th>
                        <th style="padding:11px 16px;text-align:left;font-size:0.74em;font-weight:700;color:var(--txt-2);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1.5px solid var(--border);">Date</th>
                        <th style="padding:11px 16px;text-align:left;font-size:0.74em;font-weight:700;color:var(--txt-2);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1.5px solid var(--border);">Type</th>
                    </tr>
                </thead>
                <tbody id="recentTableBody">
                    <tr><td colspan="4" class="loading-row" style="text-align:center;padding:30px;color:var(--txt-3);">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right column -->
    <div style="display:flex;flex-direction:column;gap:18px;">

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Quick Actions</div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
                <a href="records.php"  class="btn btn-primary"  style="justify-content:center;"><i class='bx bx-user-plus'></i> Add Patient Record</a>
                <a href="medicine.php" class="btn btn-success"  style="justify-content:center;"><i class='bx bx-plus'></i> Add Medicine</a>
                <a href="analytics.php" class="btn btn-secondary" style="justify-content:center;"><i class='bx bx-bar-chart-alt-2'></i> View Analytics</a>
                <a href="symptoms.php" class="btn btn-ghost"    style="justify-content:center;"><i class='bx bx-heart'></i> Manage Symptoms</a>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">⚠️ Low Stock Medicines</div>
                    <div class="card-subtitle">Needs restock soon</div>
                </div>
                <a href="medicine.php" class="btn btn-ghost btn-sm">Manage →</a>
            </div>
            <div class="card-body p-0">
                <div id="lowStockList" style="padding:8px 0;">
                    <div style="text-align:center;padding:20px;color:var(--txt-3);">Loading…</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 900px) {
    .dashboard-grid { grid-template-columns: 1fr !important; }
}
</style>

<script>
const ANALYTICS = '../../routes/analytics_api.php';
const MEDICINE  = '../../routes/medicine_api.php';

async function loadKPIs() {
    try {
        const res  = await fetch(`${ANALYTICS}?action=overview`);
        const data = await res.json();
        if (!data.success) return;

        const cards = [
            { value: data.total_records,   label: 'Total Records',     sub: `${data.records_today} today`,  iconClass: 'icon-blue',   icon: 'bx-clipboard' },
            { value: data.total_patients,  label: 'Unique Patients',   sub: 'ever registered',               iconClass: 'icon-green',  icon: 'bxs-user' },
            { value: data.total_medicines, label: 'Medicines',         sub: 'in formulary',                  iconClass: 'icon-purple', icon: 'bx-capsule' },
            { value: data.total_dispensed, label: 'Units Dispensed',   sub: 'all time',                      iconClass: 'icon-teal',   icon: 'bx-package' },
            { value: data.records_month,   label: 'This Month',        sub: 'records this month',            iconClass: 'icon-indigo', icon: 'bx-calendar' },
            { value: data.low_stock_count, label: 'Low / Out of Stock',sub: 'need restock',                  iconClass: 'icon-red',    icon: 'bx-error-circle' },
        ];

        document.getElementById('kpiGrid').innerHTML = cards.map(c => `
            <div class="kpi-card">
                <div class="kpi-icon-wrap ${c.iconClass}"><i class='bx ${c.icon}'></i></div>
                <div class="kpi-body">
                    <div class="kpi-value">${Number(c.value).toLocaleString()}</div>
                    <div class="kpi-label">${c.label}</div>
                    <div class="kpi-sub">${c.sub}</div>
                </div>
            </div>
        `).join('');
    } catch(e) { console.error(e); }
}

async function loadRecentRecords() {
    try {
        const res  = await fetch(`${ANALYTICS}?action=recent_records&limit=8`);
        const data = await res.json();
        const tbody = document.getElementById('recentTableBody');
        if (!data.success || !data.data.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:30px;color:var(--txt-3);">No records yet.</td></tr>';
            return;
        }
        tbody.innerHTML = data.data.map(r => {
            const type = r.patient_type || 'N/A';
            const cls  = ['Student','Faculty','Staff','Visitor'].includes(type) ? `badge-${type}` : 'badge-gray';
            return `<tr>
                <td style="padding:12px 16px;border-bottom:1px solid var(--border);font-size:0.88em;">
                    <div style="font-weight:600;">${esc(r.patient_name || 'N/A')}</div>
                    <div style="font-size:0.8em;color:var(--txt-3);">${esc(r.reason || '')}</div>
                </td>
                <td style="padding:12px 16px;border-bottom:1px solid var(--border);font-size:0.88em;">${esc(r.medicine_name || 'N/A')} <span style="color:var(--txt-3);">×${r.quantity}</span></td>
                <td style="padding:12px 16px;border-bottom:1px solid var(--border);font-size:0.82em;white-space:nowrap;color:var(--txt-2);">${fmtDate(r.date_given)}</td>
                <td style="padding:12px 16px;border-bottom:1px solid var(--border);"><span class="badge ${cls}">${esc(type)}</span></td>
            </tr>`;
        }).join('');
    } catch(e) { console.error(e); }
}

async function loadLowStock() {
    try {
        const res  = await fetch(`${MEDICINE}?action=read`);
        const data = await res.json();
        const list = document.getElementById('lowStockList');
        if (!data.success) { list.innerHTML = '<div style="padding:16px;color:var(--txt-3);text-align:center;">Unable to load</div>'; return; }
        const low = (data.data || []).filter(m => parseInt(m.current_stock) < 10);
        if (!low.length) {
            list.innerHTML = '<div style="padding:16px 20px;color:var(--success);font-weight:600;font-size:0.88em;">✓ All medicines are well stocked</div>';
            return;
        }
        list.innerHTML = low.map(m => {
            const s = parseInt(m.current_stock);
            const cls = s === 0 ? 'stock-low' : 'stock-low';
            return `<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 18px;border-bottom:1px solid var(--border);">
                <span style="font-size:0.88em;font-weight:500;">${esc(m.medicine_name)}</span>
                <span class="stock-badge ${cls}">${s === 0 ? 'Out of Stock' : s + ' left'}</span>
            </div>`;
        }).join('');
    } catch(e) { console.error(e); }
}

function esc(t) { const el = document.createElement('div'); el.appendChild(document.createTextNode(String(t ?? ''))); return el.innerHTML; }
function fmtDate(s) { if (!s) return 'N/A'; try { return new Date(s).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}); } catch { return 'N/A'; } }

loadKPIs();
loadRecentRecords();
loadLowStock();
</script>

<?php require_once __DIR__ . '/../../app/includes/admin_footer.php'; ?>
