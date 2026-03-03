// Argonar Construction - Global JavaScript

document.addEventListener('DOMContentLoaded', function() {

    // Flash message auto-dismiss
    document.querySelectorAll('.flash-container .alert').forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(function() { alert.remove(); }, 300);
        }, 4000);
    });

    // Sidebar toggle (mobile)
    var toggle = document.getElementById('sidebarToggle');
    var sidebar = document.querySelector('.sidebar');
    var overlay = document.querySelector('.sidebar-overlay');

    if (toggle) {
        toggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('show');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }

    // Tooltips
    var tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipEls.forEach(function(el) { new bootstrap.Tooltip(el); });
});

// Confirm delete helper
function confirmDelete(formId, message) {
    if (confirm(message || 'Are you sure you want to delete this?')) {
        document.getElementById(formId).submit();
    }
}

// Format currency
function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
