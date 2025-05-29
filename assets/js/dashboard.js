// Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Mobile sidebar toggle
    const sidebarToggle = document.querySelector('.navbar-toggler');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768) {
            const isClickInside = sidebar?.contains(event.target) || sidebarToggle?.contains(event.target);
            if (!isClickInside && sidebar?.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        }
    });

    // Handle responsive behavior
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar?.classList.remove('show');
        }
    });

    // Initialize any data tables
    const tables = document.querySelectorAll('.dashboard-table');
    tables.forEach(table => {
        if (table.classList.contains('sortable')) {
            // Add sorting functionality if needed
        }
    });

    // Handle form submissions
    const forms = document.querySelectorAll('.dashboard-form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="loading-spinner"></span> Processing...';
            }
        });
    });

    // Add confirmation for delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.dashboard-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}); 