<?php
require_once __DIR__ . '/../../app/includes/check_session.php';
checkSession('admin');
$user      = getSessionUser();
$pageTitle = 'Activity Logs';
require_once __DIR__ . '/../../app/includes/admin_header.php';
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Activity Logs</h2>
        <p class="page-subtitle">Track all admin actions and system events</p>
    </div>
    <div class="page-header-actions">
        <button class="btn btn-secondary btn-sm" onclick="resetFilters()">
            <i class='bx bx-x'></i> Reset
        </button>
        <button class="btn btn-primary btn-sm" onclick="applyFilters()">
            <i class='bx bx-filter-alt'></i> Apply Filters
        </button>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <h3><i class='bx bx-filter-alt'></i> Filter Logs</h3>
    <div class="filter-grid">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" id="filterSearch" placeholder="Admin name, description…">
        </div>
        <div class="filter-group">
            <label>Module</label>
            <select id="filterModule">
                <option value="">All Modules</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Action</label>
            <select id="filterAction">
                <option value="">All Actions</option>
            </select>
        </div>
        <div class="filter-group">
            <label>From Date</label>
            <input type="date" id="filterDateFrom">
        </div>
        <div class="filter-group">
            <label>To Date</label>
            <input type="date" id="filterDateTo">
        </div>
        <div class="filter-actions">
            <button class="btn btn-primary btn-sm" onclick="applyFilters()"><i class='bx bx-filter-alt'></i> Apply</button>
            <button class="btn btn-secondary btn-sm" onclick="resetFilters()">Reset</button>
        </div>
    </div>
</div>

<!-- Stats row -->
<div style="display:flex;gap:12px;margin-bottom:18px;flex-wrap:wrap;">
    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 18px;font-size:0.85em;font-weight:600;color:var(--txt-2);" id="statsTotal">Total: —</div>
    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 18px;font-size:0.85em;font-weight:600;color:var(--txt-2);" id="statsPage">Page: —</div>
</div>

<!-- Table -->
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Admin</th>
                <th>Action</th>
                <th>Module</th>
                <th>Description</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody id="logsTableBody">
            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--txt-3);">Loading logs…</td></tr>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div id="paginationBar" style="display:flex;gap:8px;justify-content:center;padding:20px 0;flex-wrap:wrap;"></div>

<script>
const LOGS_API = '../../routes/logs_api.php';
let currentPage    = 1;
let currentFilters = {};

async function loadLogs(page = 1) {
    currentPage = page;
    const params = new URLSearchParams({ action:'read', page, limit:20, ...currentFilters });
    try {
        const d = await (await fetch(`${LOGS_API}?${params}`)).json();
        const tbody = document.getElementById('logsTableBody');
        if (!d.success) { tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--txt-3);">${esc(d.message||'Failed to load.')}</td></tr>`; return; }

        document.getElementById('statsTotal').textContent = `Total: ${d.total} logs`;
        document.getElementById('statsPage').textContent  = `Page ${d.page} of ${d.total_pages}`;

        if (!d.data.length) {
            tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class=\'bx bx-list-ul\'></i><p>No logs match your filters.</p></div></td></tr>';
        } else {
            tbody.innerHTML = d.data.map(l => `<tr>
                <td style="white-space:nowrap;font-size:0.82em;color:var(--txt-2);">${fmtDateTime(l.created_at)}</td>
                <td>
                    <div style="font-weight:600;font-size:0.88em;">${esc(l.admin_fullname||'—')}</div>
                    <div style="font-size:0.76em;color:var(--txt-3);">${esc(l.admin_username||'')}</div>
                </td>
                <td>${actionBadge(l.action)}</td>
                <td><span style="background:var(--primary-light);color:var(--primary);font-size:0.75em;font-weight:700;padding:3px 9px;border-radius:12px;">${esc(l.module||'—')}</span></td>
                <td style="font-size:0.86em;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${esc(l.description||'')}">${esc(l.description||'—')}</td>
                <td style="font-size:0.8em;font-family:monospace;color:var(--txt-3);">${esc(l.ip_address||'—')}</td>
            </tr>`).join('');
        }

        renderPagination(d.page, d.total_pages);
    } catch(e) { document.getElementById('logsTableBody').innerHTML = '<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--danger);">Error loading logs.</td></tr>'; }
}

function renderPagination(page, total) {
    const bar = document.getElementById('paginationBar');
    if (total <= 1) { bar.innerHTML = ''; return; }
    let html = '';
    const btnStyle = (active) => `style="padding:7px 14px;border:1.5px solid ${active?'var(--primary)':'var(--border)'};background:${active?'var(--primary)':'var(--card-bg)'};color:${active?'white':'var(--txt-2)'};border-radius:var(--r-sm);cursor:pointer;font-size:0.85em;font-weight:600;font-family:inherit;transition:all 0.2s;"`;
    if (page > 1)  html += `<button ${btnStyle(false)} onclick="loadLogs(${page-1})" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">‹ Prev</button>`;
    const start = Math.max(1, page-2), end = Math.min(total, page+2);
    if (start > 1) html += `<button ${btnStyle(false)} onclick="loadLogs(1)">1</button>` + (start > 2 ? '<span style="padding:0 4px;color:var(--txt-3);">…</span>' : '');
    for (let i = start; i <= end; i++) html += `<button ${btnStyle(i===page)} onclick="loadLogs(${i})">${i}</button>`;
    if (end < total) html += (end < total-1 ? '<span style="padding:0 4px;color:var(--txt-3);">…</span>' : '') + `<button ${btnStyle(false)} onclick="loadLogs(${total})">${total}</button>`;
    if (page < total) html += `<button ${btnStyle(false)} onclick="loadLogs(${page+1})">Next ›</button>`;
    bar.innerHTML = html;
}

async function loadFilterOptions() {
    try {
        const d = await (await fetch(`${LOGS_API}?action=filters`)).json();
        if (!d.success) return;
        const mod = document.getElementById('filterModule');
        const act = document.getElementById('filterAction');
        (d.modules||[]).forEach(m => { const o = document.createElement('option'); o.value = m; o.textContent = m; mod.appendChild(o); });
        (d.actions||[]).forEach(a => { const o = document.createElement('option'); o.value = a; o.textContent = a; act.appendChild(o); });
    } catch(e) {}
}

function applyFilters() {
    currentFilters = {};
    const s  = document.getElementById('filterSearch').value.trim();
    const m  = document.getElementById('filterModule').value;
    const a  = document.getElementById('filterAction').value;
    const f  = document.getElementById('filterDateFrom').value;
    const t  = document.getElementById('filterDateTo').value;
    if (s) currentFilters.search    = s;
    if (m) currentFilters.module    = m;
    if (a) currentFilters.action    = a;
    if (f) currentFilters.date_from = f;
    if (t) currentFilters.date_to   = t;
    loadLogs(1);
}

function resetFilters() {
    document.getElementById('filterSearch').value   = '';
    document.getElementById('filterModule').value   = '';
    document.getElementById('filterAction').value   = '';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value   = '';
    currentFilters = {};
    loadLogs(1);
}

function actionBadge(action) {
    if (!action) return '<span class="action-badge badge-DEF">N/A</span>';
    const a = action.toUpperCase();
    let cls = 'badge-DEF';
    if (a.includes('LOGIN'))   cls = 'badge-LOGIN';
    else if (a.includes('LOGOUT')) cls = 'badge-LOGOUT';
    else if (a.includes('FAIL'))   cls = 'badge-FAILED';
    else if (a.includes('ADD') || a.includes('CREATE')) cls = 'badge-ADD';
    else if (a.includes('UPDATE') || a.includes('EDIT')) cls = 'badge-UPDATE';
    else if (a.includes('DELETE') || a.includes('REMOVE')) cls = 'badge-DELETE';
    else if (a.includes('STOCK'))  cls = 'badge-STOCK';
    else if (a.includes('SYM'))    cls = 'badge-SYM';
    return `<span class="action-badge ${cls}">${esc(action)}</span>`;
}

function fmtDateTime(s) {
    if (!s) return 'N/A';
    try {
        const d = new Date(s);
        return d.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})
             + '<br><span style="color:var(--txt-3);">' + d.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'}) + '</span>';
    } catch { return s; }
}
function esc(t) { const el=document.createElement('div');el.appendChild(document.createTextNode(String(t??'')));return el.innerHTML; }

document.getElementById('filterSearch').addEventListener('keydown', e => { if (e.key==='Enter') applyFilters(); });

loadFilterOptions();
loadLogs(1);
</script>

<?php require_once __DIR__ . '/../../app/includes/admin_footer.php'; ?>
