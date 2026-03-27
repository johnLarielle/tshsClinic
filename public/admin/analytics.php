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

document.getElementById('lineSubtitle').textContent=`Records & units per day (last ${currentDays} days)`;
loadAll();
</script>

<?php require_once __DIR__ . '/../../app/includes/admin_footer.php'; ?>
