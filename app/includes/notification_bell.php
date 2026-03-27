<?php
/**
 * Notification Bell Component
 * Include this inside admin page headers to show low stock alerts
 * Usage: include __DIR__ . '/../../app/includes/notification_bell.php';
 */
?>

<!-- Notification Bell Styles -->
<style>
    /* ── Bell wrapper ── */
    .notif-wrapper {
        position: relative;
        display: inline-block;
    }

    .notif-bell-btn {
        background: none;
        border: 2px solid transparent;
        border-radius: 10px;
        padding: 8px 12px;
        cursor: pointer;
        font-size: 1.4em;
        position: relative;
        transition: background 0.2s, border-color 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .notif-bell-btn:hover {
        background: rgba(30, 58, 138, 0.08);
        border-color: rgba(30, 58, 138, 0.2);
    }

    /* ── Badge ── */
    .notif-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        background: #dc2626;
        color: white;
        font-size: 0.55em;
        font-weight: 700;
        min-width: 18px;
        height: 18px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        line-height: 1;
        animation: pulse-badge 2s infinite;
        display: none; /* Hidden until count > 0 */
    }

    @keyframes pulse-badge {
        0%, 100% { transform: scale(1); }
        50%       { transform: scale(1.15); }
    }

    /* ── Dropdown panel ── */
    .notif-dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: 320px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.18);
        z-index: 9999;
        overflow: hidden;
        animation: slideDown 0.2s ease;
    }

    .notif-dropdown.open {
        display: block;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Header ── */
    .notif-header {
        background: #1e3a8a;
        color: white;
        padding: 14px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notif-header-title {
        font-weight: 700;
        font-size: 0.95em;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .notif-count-label {
        font-size: 0.8em;
        opacity: 0.85;
    }

    /* ── Items list ── */
    .notif-list {
        max-height: 320px;
        overflow-y: auto;
    }

    .notif-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.15s;
        text-decoration: none;
        color: inherit;
    }

    .notif-item:last-child {
        border-bottom: none;
    }

    .notif-item:hover {
        background: #f8f9fa;
    }

    .notif-item-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1em;
        flex-shrink: 0;
    }

    .notif-item-icon.out    { background: #fee2e2; }
    .notif-item-icon.low    { background: #fef3c7; }

    .notif-item-body {
        flex: 1;
        min-width: 0;
    }

    .notif-item-name {
        font-weight: 600;
        font-size: 0.9em;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .notif-item-detail {
        font-size: 0.78em;
        color: #64748b;
        margin-top: 2px;
    }

    .notif-item-stock {
        font-size: 0.8em;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 20px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .notif-item-stock.out { background: #fee2e2; color: #dc2626; }
    .notif-item-stock.low { background: #fef3c7; color: #92400e; }

    /* ── Empty state ── */
    .notif-empty {
        text-align: center;
        padding: 30px 20px;
        color: #94a3b8;
    }

    .notif-empty-icon {
        font-size: 2.5em;
        margin-bottom: 8px;
    }

    .notif-empty-text {
        font-size: 0.9em;
    }

    /* ── Footer ── */
    .notif-footer {
        border-top: 1px solid #f0f0f0;
        padding: 10px 16px;
        text-align: center;
    }

    .notif-footer a {
        color: #1e3a8a;
        font-size: 0.85em;
        font-weight: 600;
        text-decoration: none;
    }

    .notif-footer a:hover {
        text-decoration: underline;
    }

    /* ── Loading state ── */
    .notif-loading {
        text-align: center;
        padding: 20px;
        color: #94a3b8;
        font-size: 0.85em;
    }
</style>

<!-- Bell HTML -->
<div class="notif-wrapper" id="notifWrapper">
    <button class="notif-bell-btn" id="notifBellBtn" onclick="toggleNotifications()" title="Low Stock Alerts">
        🔔
        <span class="notif-badge" id="notifBadge">0</span>
    </button>

    <div class="notif-dropdown" id="notifDropdown">
        <div class="notif-header">
            <span class="notif-header-title">🔔 Low Stock Alerts</span>
            <span class="notif-count-label" id="notifCountLabel">Loading...</span>
        </div>
        <div class="notif-list" id="notifList">
            <div class="notif-loading">Checking stock levels...</div>
        </div>
        <div class="notif-footer">
            <?php
            // Resolve correct link depending on which admin page includes this
            $current = basename($_SERVER['PHP_SELF']);
            $medicineLink = ($current === 'medicine.php') ? '#' : 'medicine.php';
            ?>
            <a href="<?php echo $medicineLink; ?>"
               <?php if ($medicineLink === '#') echo 'onclick="toggleNotifications(); return false;"'; ?>>
                <?php echo ($medicineLink === '#') ? 'All stocks listed above ↑' : 'Manage Medicine Inventory →'; ?>
            </a>
        </div>
    </div>
</div>

<!-- Notification Bell Script -->
<script>
    const LOW_STOCK_THRESHOLD = 10;
    const MEDICINE_API = '../../routes/medicine_api.php';
    let notifOpen = false;

    // Load on page ready
    document.addEventListener('DOMContentLoaded', loadNotifications);

    // Auto-refresh every 60 seconds
    setInterval(loadNotifications, 60000);

    async function loadNotifications() {
        try {
            const res = await fetch(`${MEDICINE_API}?action=read`);
            const result = await res.json();

            if (!result.success || !result.data) return;

            const lowStock = result.data.filter(m => parseInt(m.current_stock) <= LOW_STOCK_THRESHOLD);

            updateBadge(lowStock.length);
            renderNotifications(lowStock);
        } catch (err) {
            console.error('Notification fetch error:', err);
        }
    }

    function updateBadge(count) {
        const badge = document.getElementById('notifBadge');
        const label = document.getElementById('notifCountLabel');

        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
            label.textContent = `${count} item${count > 1 ? 's' : ''} need attention`;
        } else {
            badge.style.display = 'none';
            label.textContent = 'All stocks are good';
        }
    }

    function renderNotifications(lowStock) {
        const list = document.getElementById('notifList');

        if (lowStock.length === 0) {
            list.innerHTML = `
                <div class="notif-empty">
                    <div class="notif-empty-icon">✅</div>
                    <div class="notif-empty-text">All medicines are well stocked!</div>
                </div>`;
            return;
        }

        // Sort: out of stock first, then by lowest stock
        lowStock.sort((a, b) => parseInt(a.current_stock) - parseInt(b.current_stock));

        list.innerHTML = lowStock.map(m => {
            const stock = parseInt(m.current_stock);
            const isOut  = stock === 0;
            const cls    = isOut ? 'out' : 'low';
            const icon   = isOut ? '🚫' : '⚠️';
            const label  = isOut ? 'Out of Stock' : `${stock} left`;
            const detail = isOut
                ? 'Needs immediate restocking'
                : `Below threshold of ${LOW_STOCK_THRESHOLD}`;

            const name = escapeHtmlNotif(m.medicine_name);

            return `
                <a href="medicine.php" class="notif-item">
                    <div class="notif-item-icon ${cls}">${icon}</div>
                    <div class="notif-item-body">
                        <div class="notif-item-name">${name}</div>
                        <div class="notif-item-detail">${detail}</div>
                    </div>
                    <span class="notif-item-stock ${cls}">${label}</span>
                </a>`;
        }).join('');
    }

    function toggleNotifications() {
        notifOpen = !notifOpen;
        document.getElementById('notifDropdown').classList.toggle('open', notifOpen);
        if (notifOpen) loadNotifications(); // refresh when opened
    }

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!document.getElementById('notifWrapper').contains(e.target)) {
            notifOpen = false;
            document.getElementById('notifDropdown').classList.remove('open');
        }
    });

    function escapeHtmlNotif(text) {
        const d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }
</script>
