// FILE: /public/assets/js/app.js

/**
 * SplashWhats - Main JavaScript
 * Handles interactive features and UI enhancements
 */

document.addEventListener('DOMContentLoaded', function() {

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Active navigation highlighting
    const currentPath = window.location.pathname;
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(function(item) {
        const href = item.getAttribute('href');
        if (href === currentPath || (href !== '/' && currentPath.startsWith(href))) {
            item.style.background = 'rgba(255, 255, 255, 0.1)';
            item.style.color = 'white';
        }
    });

    // Confirmation dialogs
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const message = button.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#ef4444';
                } else {
                    field.style.borderColor = '#d1d5db';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    });

    // Auto-scroll to bottom of messages
    const messagesContainer = document.querySelector('.messages-container');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Search filter debounce
    const searchInputs = document.querySelectorAll('input[name="search"]');
    searchInputs.forEach(function(input) {
        let timeout;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                // Auto-submit search form after 500ms of no typing
                if (input.value.length > 2 || input.value.length === 0) {
                    const form = input.closest('form');
                    if (form) {
                        form.submit();
                    }
                }
            }, 500);
        });
    });

    // Simple chart rendering for dashboard
    renderMessagesChart();
});

/**
 * Render messages per day chart
 */
function renderMessagesChart() {
    const chartCanvas = document.getElementById('messagesChart');
    if (!chartCanvas) return;

    // This is a placeholder - in a real implementation,
    // you would use a charting library like Chart.js
    const ctx = chartCanvas.getContext('2d');
    chartCanvas.width = chartCanvas.parentElement.offsetWidth;
    chartCanvas.height = 300;

    // Simple bar chart visualization
    ctx.fillStyle = '#3B82F6';
    ctx.fillRect(50, 50, 30, 100);
    ctx.fillRect(100, 70, 30, 80);
    ctx.fillRect(150, 40, 30, 110);
    ctx.fillRect(200, 60, 30, 90);
    ctx.fillRect(250, 30, 30, 120);

    ctx.fillStyle = '#6b7280';
    ctx.font = '12px sans-serif';
    ctx.fillText('Messages sent over time', 10, 20);
    ctx.fillText('(Simple visualization - use Chart.js for production)', 10, 280);
}

/**
 * Show modal
 */
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
    }
}

/**
 * Hide modal
 */
function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    alert('Copied to clipboard!');
}

/**
 * Format phone number
 */
function formatPhone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 0 && value[0] !== '+') {
        value = '+' + value;
    }
    input.value = value;
}

/**
 * Preview image before upload
 */
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
