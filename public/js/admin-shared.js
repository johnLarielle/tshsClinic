/* Admin Shared JS — sidebar toggle + toast notifications */

// ── Toast ──────────────────────────────────────────────────────
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const icons = { success: '✓', error: '✕', info: 'i', warning: '!' };
    const toast  = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-icon">${icons[type] || 'i'}</div>
        <div class="toast-message">${message}</div>
        <button class="toast-close" onclick="this.closest('.toast').remove()">✕</button>
    `;
    container.appendChild(toast);

    // Trigger animation
    requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));

    // Auto-remove
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 350);
    }, 4500);
}

// Alias so page-specific JS files work without changes
function showMessage(message, type) {
    showToast(message, type === 'error' ? 'error' : type === 'success' ? 'success' : 'info');
}

// ── Sidebar toggle ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const wrapper  = document.querySelector('.admin-wrapper');
    const sidebar  = document.getElementById('sidebar');
    const toggle   = document.getElementById('sidebarToggle');
    const backdrop = document.getElementById('sidebarBackdrop');
    const isMobile = () => window.innerWidth <= 1024;

    // Restore desktop collapse state
    if (!isMobile() && localStorage.getItem('sidebarCollapsed') === 'true') {
        wrapper.classList.add('sidebar-collapsed');
    }

    if (toggle) {
        toggle.addEventListener('click', () => {
            if (isMobile()) {
                sidebar.classList.toggle('mobile-open');
                if (backdrop) backdrop.classList.toggle('show');
            } else {
                wrapper.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', wrapper.classList.contains('sidebar-collapsed'));
            }
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            backdrop.classList.remove('show');
        });
    }

    // Close on resize
    window.addEventListener('resize', () => {
        if (!isMobile()) {
            sidebar.classList.remove('mobile-open');
            if (backdrop) backdrop.classList.remove('show');
        }
    });
});
