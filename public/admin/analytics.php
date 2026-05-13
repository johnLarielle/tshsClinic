<?php
require_once __DIR__ . '/../../app/includes/check_session.php';
checkSession('admin');
$user          = getSessionUser();
$pageTitle     = 'Analytics';
$pageHeadExtra = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';
require_once __DIR__ . '/../../app/includes/admin_header.php';
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Analytics</h2>
        <p class="page-subtitle">Insights and trends across patient records, medicine, and symptoms</p>
    </div>
    <div class="page-header-actions">
        <span style="font-size:0.85em;color:var(--txt-2);margin-right:6px;">Period:</span>
        <button class="period-btn active" onclick="setPeriod(7,this)">7 Days</button>
        <button class="period-btn"        onclick="setPeriod(30,this)">30 Days</button>
        <button class="period-btn"        onclick="setPeriod(90,this)">90 Days</button>
        <button class="btn btn-secondary btn-sm" onclick="loadAll()" style="margin-left:6px;">
            <i class='bx bx-refresh'></i> Refresh
        </button>
        <button class="btn btn-primary btn-sm" onclick="openPrintModal()" style="margin-left:6px;">
            <i class='bx bx-printer'></i> Print
        </button>
    </div>
</div>

<!-- KPI Cards -->
<div class="kpi-grid" id="kpiGrid">
    <?php for ($i = 0; $i < 7; $i++): ?>
    <div class="kpi-card">
        <div class="kpi-icon-wrap icon-blue"><div class="shimmer" style="width:26px;height:26px;border-radius:6px;"></div></div>
        <div class="kpi-body">
            <div class="kpi-value"><div class="shimmer" style="width:55px;height:28px;"></div></div>
            <div style="margin-top:6px;"><div class="shimmer" style="width:90px;height:11px;"></div></div>
        </div>
    </div>
    <?php endfor; ?>
</div>

<!-- Line + Donut -->
<div class="chart-grid">
    <div class="card col-8">
        <div class="card-header">
            <div>
                <div class="card-title">Records Over Time</div>
                <div class="card-subtitle" id="lineSubtitle">Records & units dispensed per day</div>
            </div>
        </div>
        <div class="card-body"><canvas id="lineChart" height="110"></canvas></div>
    </div>
    <div class="card col-4">
        <div class="card-header">
            <div class="card-title">Patient Breakdown</div>
        </div>
        <div class="card-body"><canvas id="donutChart" height="200"></canvas></div>
    </div>
</div>

<!-- Top Medicines + Stock -->
<div class="chart-grid">
    <div class="card col-6">
        <div class="card-header">
            <div>
                <div class="card-title">Top Dispensed Medicines</div>
                <div class="card-subtitle">By total units (all time)</div>
            </div>
        </div>
        <div class="card-body"><canvas id="barChart" height="260"></canvas></div>
    </div>
    <div class="card col-6">
        <div class="card-header">
            <div>
                <div class="card-title">Current Stock Levels</div>
                <div class="card-subtitle">Color: red = out, amber = low, green = good</div>
            </div>
        </div>
        <div class="card-body"><canvas id="stockChart" height="260"></canvas></div>
    </div>
</div>

<!-- Symptoms chart -->
<div class="chart-grid">
    <div class="card col-12">
        <div class="card-header">
            <div>
                <div class="card-title">Top Reported Symptoms</div>
                <div class="card-subtitle">Most frequent reasons for clinic visits (all time)</div>
            </div>
        </div>
        <div class="card-body"><canvas id="symptomsChart" height="80"></canvas></div>
    </div>
</div>

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
                <tr style="background:var(--hover-bg);">
                    <th style="padding:11px 16px;font-size:0.74em;font-weight:700;color:var(--txt-2);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1.5px solid var(--border);">#</th>
                    <th style="padding:11px 16px;font-size:0.74em;font-weight:700;color:var(--txt-2);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1.5px solid var(--border);">Patient</th>
                    <th style="padding:11px 16px;font-size:0.74em;font-weight:700;color:var(--txt-2);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1.5px solid var(--border);">Type</th>
                    <th style="padding:11px 16px;font-size:0.74em;font-weight:700;color:var(--txt-2);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1.5px solid var(--border);">Medicine</th>
                    <th style="padding:11px 16px;font-size:0.74em;font-weight:700;color:var(--txt-2);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1.5px solid var(--border);">Qty</th>
                    <th style="padding:11px 16px;font-size:0.74em;font-weight:700;color:var(--txt-2);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1.5px solid var(--border);">Date</th>
                    <th style="padding:11px 16px;font-size:0.74em;font-weight:700;color:var(--txt-2);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1.5px solid var(--border);">Reason</th>
                </tr>
            </thead>
            <tbody id="recentTableBody">
                <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--txt-3);">Loading…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Print Options Modal ── -->
<div class="modal-overlay" id="printModal" onclick="if(event.target===this)closePrintModal()">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <span class="modal-title"><i class='bx bx-printer' style="margin-right:6px;"></i>Print Analytics Report</span>
            <button class="modal-close" onclick="closePrintModal()"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body">
            <p style="font-size:0.85em;color:var(--txt-2);margin-bottom:16px;">
                Choose a date range for the report. The <strong>Records by Day</strong> table and period-based data will reflect this range.
            </p>

            <!-- Presets -->
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label">Quick Select</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button class="print-preset active" onclick="selectPreset(this,7)">Last 7 Days</button>
                    <button class="print-preset"        onclick="selectPreset(this,30)">Last 30 Days</button>
                    <button class="print-preset"        onclick="selectPreset(this,90)">Last 90 Days</button>
                    <button class="print-preset"        onclick="selectPreset(this,365)">Last Year</button>
                    <button class="print-preset"        onclick="selectPreset(this,0)">Custom Range</button>
                </div>
            </div>

            <!-- Custom date range (hidden by default) -->
            <div id="customRangeGroup" style="display:none;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:4px;">
                    <div class="form-group">
                        <label class="form-label">From Date</label>
                        <input type="date" id="printDateFrom" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">To Date</label>
                        <input type="date" id="printDateTo" class="form-control">
                    </div>
                </div>
                <p id="dateRangeError" style="font-size:0.78em;color:#dc2626;margin-top:4px;display:none;">
                    "From" date must be before "To" date.
                </p>
            </div>

            <div class="form-actions" style="margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closePrintModal()">Cancel</button>
                <button type="button" class="btn btn-primary" id="generateReportBtn" onclick="confirmPrint()">
                    <i class='bx bx-printer'></i> Generate Report
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.print-preset {
    padding: 6px 14px;
    border: 1.5px solid var(--border);
    border-radius: 20px;
    background: #fff;
    font-size: 0.8em;
    font-weight: 600;
    color: var(--txt-2);
    cursor: pointer;
    transition: all 0.15s;
}
.print-preset:hover { border-color: var(--primary); color: var(--primary); }
.print-preset.active { background: var(--primary); border-color: var(--primary); color: #fff; }
</style>

<script>
const ANALYTICS_API   = '../../routes/analytics_api.php';
let currentDays       = 7;
let lineChartInst     = null, donutChartInst  = null;
let barChartInst      = null, stockChartInst  = null;
let symptomsChartInst = null;

function setPeriod(days, btn) {
    currentDays = days;
    document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('lineSubtitle').textContent = `Records & units per day (last ${days} days)`;
    loadLineChart();
}

async function loadAll() {
    await Promise.all([loadKPIs(), loadLineChart(), loadDonutChart(), loadBarChart(), loadStockChart(), loadSymptomsChart(), loadRecentRecords()]);
}

async function loadKPIs() {
    try {
        const d = await (await fetch(`${ANALYTICS_API}?action=overview`)).json();
        if (!d.success) return;
        const cards = [
            { v: d.total_records,   l: 'Total Records',      s: `${d.records_today} today`,  ic: 'icon-blue',   i: 'bx-clipboard' },
            { v: d.total_patients,  l: 'Unique Patients',    s: 'ever registered',            ic: 'icon-green',  i: 'bxs-user' },
            { v: d.total_medicines, l: 'Medicines',          s: 'in formulary',               ic: 'icon-purple', i: 'bx-capsule' },
            { v: d.total_dispensed, l: 'Units Dispensed',    s: 'all time',                   ic: 'icon-teal',   i: 'bx-package' },
            { v: d.records_month,   l: 'This Month',         s: 'current month',              ic: 'icon-indigo', i: 'bx-calendar' },
            { v: d.records_today,   l: 'Today',              s: new Date().toLocaleDateString(), ic: 'icon-amber', i: 'bxs-sun' },
            { v: d.low_stock_count, l: 'Low / Out of Stock', s: 'need restock',               ic: 'icon-red',    i: 'bx-error-circle' },
        ];
        document.getElementById('kpiGrid').innerHTML = cards.map(c => `
            <div class="kpi-card">
                <div class="kpi-icon-wrap ${c.ic}"><i class='bx ${c.i}'></i></div>
                <div class="kpi-body">
                    <div class="kpi-value">${Number(c.v).toLocaleString()}</div>
                    <div class="kpi-label">${c.l}</div>
                    <div class="kpi-sub">${c.s}</div>
                </div>
            </div>`).join('');
    } catch(e) {}
}

async function loadLineChart() {
    try {
        const d = await (await fetch(`${ANALYTICS_API}?action=records_by_day&days=${currentDays}`)).json();
        if (!d.success) return;
        const labels = d.data.map(x => fmtShort(x.day));
        const counts = d.data.map(x => x.total_records);
        const qty    = d.data.map(x => x.total_qty);
        const ctx    = document.getElementById('lineChart').getContext('2d');
        if (lineChartInst) lineChartInst.destroy();
        lineChartInst = new Chart(ctx, { type:'line', data:{ labels, datasets:[
            { label:'Records', data:counts, borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,0.08)', borderWidth:2.5, pointRadius:3, fill:true, tension:0.4 },
            { label:'Units Dispensed', data:qty, borderColor:'#10b981', backgroundColor:'rgba(16,185,129,0.06)', borderWidth:2, pointRadius:3, fill:true, tension:0.4 },
        ]}, options:{ responsive:true, interaction:{mode:'index',intersect:false}, plugins:{legend:{position:'top',labels:{usePointStyle:true,font:{size:11}}}}, scales:{ x:{grid:{display:false},ticks:{font:{size:11}}}, y:{beginAtZero:true,grid:{color:'#f0f0f0'},ticks:{stepSize:1}} } }});
    } catch(e) {}
}

async function loadDonutChart() {
    try {
        const d = await (await fetch(`${ANALYTICS_API}?action=patient_types`)).json();
        if (!d.success) return;
        const colors = ['#3b82f6','#10b981','#8b5cf6','#f59e0b','#ef4444','#06b6d4'];
        const ctx = document.getElementById('donutChart').getContext('2d');
        if (donutChartInst) donutChartInst.destroy();
        donutChartInst = new Chart(ctx, { type:'doughnut', data:{ labels:d.data.map(x=>x.patient_type||'Unknown'), datasets:[{ data:d.data.map(x=>parseInt(x.total)), backgroundColor:colors, borderWidth:3, borderColor:'#fff' }]}, options:{ responsive:true, cutout:'65%', plugins:{ legend:{position:'bottom',labels:{usePointStyle:true,padding:12,font:{size:11}}}, tooltip:{callbacks:{label:c=>{ const t=c.dataset.data.reduce((a,b)=>a+b,0); return ` ${c.label}: ${c.raw} (${t?((c.raw/t)*100).toFixed(1):0}%)`;}}} }}});
    } catch(e) {}
}

async function loadBarChart() {
    try {
        const d = await (await fetch(`${ANALYTICS_API}?action=top_medicines&limit=10`)).json();
        if (!d.success) return;
        const ctx = document.getElementById('barChart').getContext('2d');
        if (barChartInst) barChartInst.destroy();
        barChartInst = new Chart(ctx, { type:'bar', data:{ labels:d.data.map(x=>x.medicine_name), datasets:[
            { label:'Total Units', data:d.data.map(x=>parseInt(x.total_qty)), backgroundColor:'rgba(99,102,241,0.8)', borderRadius:5 },
            { label:'Times Dispensed', data:d.data.map(x=>parseInt(x.times_dispensed)), backgroundColor:'rgba(16,185,129,0.7)', borderRadius:5 },
        ]}, options:{ responsive:true, indexAxis:'y', interaction:{mode:'index',intersect:false}, plugins:{legend:{position:'top',labels:{usePointStyle:true,font:{size:11}}}}, scales:{x:{beginAtZero:true,grid:{color:'#f0f0f0'}},y:{grid:{display:false},ticks:{font:{size:11}}}}}});
    } catch(e) {}
}

async function loadStockChart() {
    try {
        const d = await (await fetch(`${ANALYTICS_API}?action=stock_levels`)).json();
        if (!d.success) return;
        const stocks = d.data.map(x=>parseInt(x.current_stock));
        const colors = stocks.map(s=>s===0?'rgba(239,68,68,0.85)':s<10?'rgba(245,158,11,0.85)':s<50?'rgba(59,130,246,0.8)':'rgba(16,185,129,0.8)');
        const ctx = document.getElementById('stockChart').getContext('2d');
        if (stockChartInst) stockChartInst.destroy();
        stockChartInst = new Chart(ctx, { type:'bar', data:{ labels:d.data.map(x=>x.medicine_name), datasets:[{ label:'Stock', data:stocks, backgroundColor:colors, borderRadius:5 }]}, options:{ responsive:true, indexAxis:'y', plugins:{ legend:{display:false}, tooltip:{callbacks:{afterLabel:c=>{ const s=c.raw; return s===0?'Out of stock':s<10?'Low stock':s<50?'Medium':'Good stock'; }}}}, scales:{x:{beginAtZero:true,grid:{color:'#f0f0f0'}},y:{grid:{display:false},ticks:{font:{size:11}}}}}});
    } catch(e) {}
}

async function loadSymptomsChart() {
    try {
        const d = await (await fetch(`${ANALYTICS_API}?action=top_symptoms&limit=12`)).json();
        if (!d.success || !d.data.length) return;
        const palette = ['#6366f1','#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#84cc16','#ec4899','#14b8a6','#a855f7'];
        const ctx = document.getElementById('symptomsChart').getContext('2d');
        if (symptomsChartInst) symptomsChartInst.destroy();
        symptomsChartInst = new Chart(ctx, { type:'bar', data:{ labels:d.data.map(x=>x.symptom), datasets:[{ label:'Cases', data:d.data.map(x=>parseInt(x.total_cases)), backgroundColor:palette.map(c=>c+'cc'), borderColor:palette, borderWidth:1.5, borderRadius:5 }]}, options:{ responsive:true, plugins:{ legend:{display:false}, tooltip:{callbacks:{label:c=>` ${c.raw} case${c.raw!==1?'s':''}`}}}, scales:{x:{grid:{display:false},ticks:{font:{size:11}}},y:{beginAtZero:true,grid:{color:'#f0f0f0'},ticks:{stepSize:1}}}}});
    } catch(e) {}
}

async function loadRecentRecords() {
    try {
        const d = await (await fetch(`${ANALYTICS_API}?action=recent_records&limit=8`)).json();
        const tbody = document.getElementById('recentTableBody');
        if (!d.success || !d.data.length) { tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--txt-3);">No records.</td></tr>'; return; }
        tbody.innerHTML = d.data.map(r => {
            const type=r.patient_type||'N/A';
            const cls=['Student','Faculty','Staff','Visitor'].includes(type)?`badge-${type}`:'badge-gray';
            return `<tr>
                <td style="padding:12px 16px;border-bottom:1px solid var(--border);color:var(--txt-3);font-size:0.8em;">${esc(r.record_id)}</td>
                <td style="padding:12px 16px;border-bottom:1px solid var(--border);font-weight:600;font-size:0.88em;">${esc(r.patient_name||'N/A')}</td>
                <td style="padding:12px 16px;border-bottom:1px solid var(--border);"><span class="badge ${cls}">${esc(type)}</span></td>
                <td style="padding:12px 16px;border-bottom:1px solid var(--border);font-size:0.88em;">${esc(r.medicine_name||'N/A')}</td>
                <td style="padding:12px 16px;border-bottom:1px solid var(--border);font-weight:700;">${esc(r.quantity??0)}</td>
                <td style="padding:12px 16px;border-bottom:1px solid var(--border);white-space:nowrap;font-size:0.82em;color:var(--txt-2);">${fmtDate(r.date_given)}</td>
                <td style="padding:12px 16px;border-bottom:1px solid var(--border);font-size:0.85em;color:var(--txt-2);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${esc(r.reason||'N/A')}</td>
            </tr>`;
        }).join('');
    } catch(e) {}
}

function esc(t){const el=document.createElement('div');el.appendChild(document.createTextNode(String(t??'')));return el.innerHTML;}
function fmtDate(s){if(!s)return 'N/A';try{return new Date(s).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});}catch{return 'N/A';}}
function fmtShort(s){if(!s)return'';const d=new Date(s+'T00:00:00');return d.toLocaleDateString('en-US',{month:'short',day:'numeric'});}

// ─── Print Modal helpers ─────────────────────────────────────
let printDays   = 7;   // preset mode
let printCustom = false;

function openPrintModal() {
    // Pre-fill custom date inputs with sensible defaults
    const today = new Date();
    const prior = new Date(); prior.setDate(today.getDate() - 30);
    document.getElementById('printDateTo').value   = today.toISOString().split('T')[0];
    document.getElementById('printDateFrom').value = prior.toISOString().split('T')[0];

    // Match the active dashboard period preset button
    const presetMap = { 7: 0, 30: 1, 90: 2 };
    const idx = presetMap[currentDays] ?? 0;
    selectPreset(document.querySelectorAll('.print-preset')[idx], currentDays);
    document.getElementById('printModal').classList.add('open');
}
function closePrintModal() {
    document.getElementById('printModal').classList.remove('open');
}
function selectPreset(el, days) {
    document.querySelectorAll('.print-preset').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    printDays   = days;
    printCustom = (days === 0);
    document.getElementById('customRangeGroup').style.display = printCustom ? '' : 'none';
    document.getElementById('dateRangeError').style.display  = 'none';
}
function confirmPrint() {
    if (printCustom) {
        const from = document.getElementById('printDateFrom').value;
        const to   = document.getElementById('printDateTo').value;
        const err  = document.getElementById('dateRangeError');
        if (!from || !to) { err.textContent = 'Please fill in both From and To dates.'; err.style.display = ''; return; }
        if (from > to)    { err.textContent = '"From" date must be before "To" date.';  err.style.display = ''; return; }
        err.style.display = 'none';
        closePrintModal();
        printAnalytics({ mode: 'custom', from, to });
    } else {
        closePrintModal();
        printAnalytics({ mode: 'days', days: printDays });
    }
}

// ─── Print Analytics (summary report — no charts) ───────────
async function printAnalytics(opts = { mode: 'days', days: 7 }) {
    const btn = document.getElementById('generateReportBtn');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class=\'bx bx-loader-alt bx-spin\'></i> Preparing…'; }

    // Determine API days param and label
    let apiDays, periodLabel;
    if (opts.mode === 'custom') {
        // Calculate days between dates for the line chart endpoint
        const ms  = new Date(opts.to) - new Date(opts.from);
        apiDays   = Math.max(1, Math.ceil(ms / 86400000) + 1);
        periodLabel = `${fmtDate(opts.from + 'T00:00:00')} — ${fmtDate(opts.to + 'T00:00:00')}`;
    } else {
        apiDays     = opts.days;
        periodLabel = `Last ${opts.days} day${opts.days !== 1 ? 's' : ''}`;
    }

    try {
        // Fetch all data in parallel
        const [overview, patientTypes, topMeds, stockLevels, topSymptoms, recentRecs, lineData] = await Promise.all([
            fetch(`${ANALYTICS_API}?action=overview`).then(r => r.json()),
            fetch(`${ANALYTICS_API}?action=patient_types`).then(r => r.json()),
            fetch(`${ANALYTICS_API}?action=top_medicines&limit=10`).then(r => r.json()),
            fetch(`${ANALYTICS_API}?action=stock_levels`).then(r => r.json()),
            fetch(`${ANALYTICS_API}?action=top_symptoms&limit=12`).then(r => r.json()),
            fetch(`${ANALYTICS_API}?action=recent_records&limit=20`).then(r => r.json()),
            fetch(`${ANALYTICS_API}?action=records_by_day&days=${apiDays}`).then(r => r.json()),
        ]);

        const now = new Date().toLocaleString('en-US', { dateStyle: 'full', timeStyle: 'short' });

        // ── KPI summary ──
        const ov = overview.success ? overview : {};
        const kpiHtml = `
            <table>
                <thead><tr><th>Metric</th><th>Value</th><th>Note</th></tr></thead>
                <tbody>
                    <tr><td>Total Patient Records</td><td class="num">${Number(ov.total_records||0).toLocaleString()}</td><td>${ov.records_today||0} added today</td></tr>
                    <tr><td>Unique Patients</td><td class="num">${Number(ov.total_patients||0).toLocaleString()}</td><td>Ever registered</td></tr>
                    <tr><td>Medicines in Formulary</td><td class="num">${Number(ov.total_medicines||0).toLocaleString()}</td><td>Active medicines</td></tr>
                    <tr><td>Total Units Dispensed</td><td class="num">${Number(ov.total_dispensed||0).toLocaleString()}</td><td>All time</td></tr>
                    <tr><td>Records This Month</td><td class="num">${Number(ov.records_month||0).toLocaleString()}</td><td>Current month</td></tr>
                    <tr><td>Records Today</td><td class="num">${Number(ov.records_today||0).toLocaleString()}</td><td>${new Date().toLocaleDateString('en-US',{month:'long',day:'numeric',year:'numeric'})}</td></tr>
                    <tr><td>Low / Out of Stock</td><td class="num warn">${Number(ov.low_stock_count||0).toLocaleString()}</td><td>Medicines needing restock</td></tr>
                </tbody>
            </table>`;

        // ── Patient breakdown ──
        const ptRows = patientTypes.success && patientTypes.data.length
            ? patientTypes.data.map((r,i) => `<tr><td>${i+1}</td><td>${esc(r.patient_type||'Unknown')}</td><td class="num">${Number(r.total).toLocaleString()}</td></tr>`).join('')
            : '<tr><td colspan="3" class="empty">No data</td></tr>';
        const ptHtml = `<table><thead><tr><th>#</th><th>Patient Type</th><th>Count</th></tr></thead><tbody>${ptRows}</tbody></table>`;

        // ── Records by day ──
        const lineRows = lineData.success && lineData.data.length
            ? lineData.data.map(r => `<tr><td>${esc(fmtDate(r.day))}</td><td class="num">${r.total_records}</td><td class="num">${r.total_qty}</td></tr>`).join('')
            : '<tr><td colspan="3" class="empty">No data</td></tr>';
        const lineHtml = `<table><thead><tr><th>Date</th><th>Records</th><th>Units Dispensed</th></tr></thead><tbody>${lineRows}</tbody></table>`;

        // ── Top medicines ──
        const medRows = topMeds.success && topMeds.data.length
            ? topMeds.data.map((r,i) => `<tr><td>${i+1}</td><td>${esc(r.medicine_name)}</td><td class="num">${Number(r.total_qty).toLocaleString()}</td><td class="num">${Number(r.times_dispensed).toLocaleString()}</td></tr>`).join('')
            : '<tr><td colspan="4" class="empty">No data</td></tr>';
        const medHtml = `<table><thead><tr><th>#</th><th>Medicine</th><th>Units Dispensed</th><th>Times Given</th></tr></thead><tbody>${medRows}</tbody></table>`;

        // ── Stock levels ──
        const stockRows = stockLevels.success && stockLevels.data.length
            ? stockLevels.data.map((r,i) => {
                const s = parseInt(r.current_stock);
                const status = s === 0 ? 'Out of Stock' : s < 10 ? 'Low' : s < 50 ? 'Medium' : 'Good';
                const cls    = s === 0 ? 'stock-out' : s < 10 ? 'stock-low' : s < 50 ? 'stock-mid' : 'stock-ok';
                return `<tr><td>${i+1}</td><td>${esc(r.medicine_name)}</td><td class="num">${s.toLocaleString()}</td><td class="${cls}">${status}</td></tr>`;
              }).join('')
            : '<tr><td colspan="4" class="empty">No data</td></tr>';
        const stockHtml = `<table><thead><tr><th>#</th><th>Medicine</th><th>Current Stock</th><th>Status</th></tr></thead><tbody>${stockRows}</tbody></table>`;

        // ── Top symptoms ──
        const symRows = topSymptoms.success && topSymptoms.data.length
            ? topSymptoms.data.map((r,i) => `<tr><td>${i+1}</td><td>${esc(r.symptom)}</td><td class="num">${Number(r.total_cases).toLocaleString()}</td></tr>`).join('')
            : '<tr><td colspan="3" class="empty">No data</td></tr>';
        const symHtml = `<table><thead><tr><th>#</th><th>Symptom / Reason</th><th>Cases</th></tr></thead><tbody>${symRows}</tbody></table>`;

        // ── Recent records ──
        const recRows = recentRecs.success && recentRecs.data.length
            ? recentRecs.data.map((r,i) => `<tr>
                <td>${i+1}</td>
                <td>${esc(r.patient_name||'N/A')}</td>
                <td>${esc(r.patient_type||'N/A')}</td>
                <td>${esc(r.medicine_name||'N/A')}</td>
                <td class="num">${r.quantity??0}</td>
                <td>${esc(fmtDate(r.date_given))}</td>
                <td>${esc(r.reason||'N/A')}</td>
              </tr>`).join('')
            : '<tr><td colspan="7" class="empty">No records.</td></tr>';
        const recHtml = `<table><thead><tr><th>#</th><th>Patient</th><th>Type</th><th>Medicine</th><th>Qty</th><th>Date</th><th>Reason</th></tr></thead><tbody>${recRows}</tbody></table>`;

        // ── Build print window ──
        const win = window.open('', '_blank', 'width=1000,height=850');
        win.document.write(`<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>TSHS Clinic — Analytics Report</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #111827; font-size: 12px; padding: 28px 32px; }

    .rpt-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px solid #2563eb; padding-bottom: 14px; margin-bottom: 24px; }
    .rpt-brand  { font-size: 1.5em; font-weight: 900; color: #2563eb; letter-spacing: -0.5px; }
    .rpt-brand span { color: #111827; }
    .rpt-clinic { font-size: 0.8em; color: #6b7280; margin-top: 2px; }
    .rpt-meta   { text-align: right; font-size: 0.8em; color: #6b7280; line-height: 1.7; }
    .rpt-meta strong { display: block; font-size: 1.15em; color: #111827; font-weight: 800; }

    .section { margin-bottom: 22px; page-break-inside: avoid; }
    .section-hd { font-size: 0.68em; font-weight: 800; text-transform: uppercase; letter-spacing: 1.2px; color: #2563eb; background: #eff6ff; border-left: 3px solid #2563eb; padding: 5px 10px; margin-bottom: 8px; border-radius: 0 4px 4px 0; }

    .two-col { display: flex; gap: 20px; }
    .two-col .section { flex: 1; }

    table { width: 100%; border-collapse: collapse; font-size: 0.95em; }
    thead tr { background: #f9fafb; }
    th { padding: 7px 10px; text-align: left; font-size: 0.72em; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 1.5px solid #e5e7eb; white-space: nowrap; }
    td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; color: #374151; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:nth-child(even) { background: #fafafa; }

    td.num   { font-weight: 700; color: #111827; text-align: right; }
    td.warn  { font-weight: 700; color: #dc2626; text-align: right; }
    td.empty { text-align: center; color: #9ca3af; padding: 14px; font-style: italic; }

    td.stock-out { font-weight: 700; color: #dc2626; }
    td.stock-low { font-weight: 700; color: #d97706; }
    td.stock-mid { font-weight: 600; color: #2563eb; }
    td.stock-ok  { font-weight: 600; color: #16a34a; }

    .rpt-footer { margin-top: 28px; border-top: 1px solid #e5e7eb; padding-top: 10px; font-size: 0.72em; color: #9ca3af; text-align: center; }

    @media print {
        body { padding: 10px 14px; font-size: 11px; }
        .section { page-break-inside: avoid; }
        .two-col { display: flex; }
    }
</style>
</head>
<body>

<div class="rpt-header">
    <div>
        <div class="rpt-brand">TSHS <span>Clinic</span></div>
        <div class="rpt-clinic">Health Information System &mdash; Analytics Report</div>
    </div>
    <div class="rpt-meta">
        <strong>Analytics Summary</strong>
        Generated: ${esc(now)}<br>
        Period filter: ${esc(periodLabel)}
    </div>
</div>

<div class="section">
    <div class="section-hd">Overview — Key Numbers</div>
    ${kpiHtml}
</div>

<div class="two-col">
    <div class="section">
        <div class="section-hd">Patient Breakdown by Type</div>
        ${ptHtml}
    </div>
    <div class="section">
        <div class="section-hd">Top Reported Symptoms</div>
        ${symHtml}
    </div>
</div>

<div class="section">
    <div class="section-hd">Records by Day (${esc(periodLabel)})</div>
    ${lineHtml}
</div>

<div class="two-col">
    <div class="section">
        <div class="section-hd">Top Dispensed Medicines</div>
        ${medHtml}
    </div>
    <div class="section">
        <div class="section-hd">Current Stock Levels</div>
        ${stockHtml}
    </div>
</div>

<div class="section">
    <div class="section-hd">Recent Patient Records (last 20)</div>
    ${recHtml}
</div>

<div class="rpt-footer">
    TSHS Clinic &mdash; Health Information System &nbsp;|&nbsp; Printed on ${esc(now)}
</div>

<script>
    window.onload = function() { window.print(); };
<\/script>
</body>
</html>`);
        win.document.close();

    } catch (err) {
        console.error('Print error:', err);
        alert('Failed to prepare print report. Please try again.');
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class=\'bx bx-printer\'></i> Generate Report'; }
    }
}

document.getElementById('lineSubtitle').textContent=`Records & units per day (last ${currentDays} days)`;
loadAll();
</script>

<?php require_once __DIR__ . '/../../app/includes/admin_footer.php'; ?>
