/**
 * School Management System - Main JavaScript
 */

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize Application
 */
function initializeApp() {
    initializeSidebar();
    initializeNavbar();
    initializeForms();
    initializeTables();
    initializeModals();
    initializeTooltips();
    initializeNotifications();
    initializeSearch();
    initializeDarkMode();
    initializeCSRF();
    initializeAJAX();
    
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        hideAlerts();
    }, 5000);
}

/**
 * Initialize Sidebar
 */
function initializeSidebar() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar-wrapper');
    const pageContent = document.getElementById('page-content-wrapper');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            
            // Adjust page content margin
            if (window.innerWidth <= 768) {
                if (sidebar.classList.contains('show')) {
                    pageContent.style.marginLeft = '0';
                } else {
                    pageContent.style.marginLeft = '0';
                }
            }
        });
    }
    
    // Handle responsive sidebar
    handleResponsiveSidebar();
    window.addEventListener('resize', handleResponsiveSidebar);
}

/**
 * Handle Responsive Sidebar
 */
function handleResponsiveSidebar() {
    const sidebar = document.getElementById('sidebar-wrapper');
    const pageContent = document.getElementById('page-content-wrapper');
    
    if (window.innerWidth > 768) {
        sidebar.classList.remove('show');
        pageContent.style.marginLeft = '250px';
    } else {
        pageContent.style.marginLeft = '0';
    }
}

/**
 * Initialize Navbar
 */
function initializeNavbar() {
    // Initialize dropdowns
    const dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(dropdown => {
        new bootstrap.Dropdown(dropdown);
    });
    
    // Handle search form
    const searchForm = document.querySelector('form[action="search"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="q"]');
            if (!searchInput.value.trim()) {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }
}

/**
 * Initialize Forms
 */
function initializeForms() {
    // Add form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });
    
    // Handle file uploads
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            handleFileUpload(this);
        });
    });
    
    // Handle password strength
    const passwordInputs = document.querySelectorAll('input[type="password"][data-strength]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            checkPasswordStrength(this);
        });
    });
}

/**
 * Validate Form
 */
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showInputError(input, 'This field is required');
            isValid = false;
        } else {
            hideInputError(input);
        }
    });
    
    // Validate email fields
    const emailInputs = form.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        if (input.value && !isValidEmail(input.value)) {
            showInputError(input, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Show Input Error
 */
function showInputError(input, message) {
    const formGroup = input.closest('.form-group, .mb-3');
    if (!formGroup) return;
    
    // Remove existing error
    hideInputError(input);
    
    // Add error class
    input.classList.add('is-invalid');
    
    // Create error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    
    formGroup.appendChild(errorDiv);
}

/**
 * Hide Input Error
 */
function hideInputError(input) {
    input.classList.remove('is-invalid');
    const formGroup = input.closest('.form-group, .mb-3');
    if (formGroup) {
        const errorDiv = formGroup.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
}

/**
 * Handle File Upload
 */
function handleFileUpload(input) {
    const file = input.files[0];
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    
    if (file) {
        // Check file size
        if (file.size > maxSize) {
            showNotification('File size exceeds 5MB limit', 'error');
            input.value = '';
            return;
        }
        
        // Check file type
        if (!allowedTypes.includes(file.type)) {
            showNotification('Invalid file type. Only JPG, PNG, GIF, and PDF files are allowed', 'error');
            input.value = '';
            return;
        }
        
        // Show file info
        const fileInfo = document.createElement('small');
        fileInfo.className = 'text-muted d-block mt-1';
        fileInfo.textContent = `Selected: ${file.name} (${formatFileSize(file.size)})`;
        
        const existingInfo = input.parentNode.querySelector('small.text-muted');
        if (existingInfo) {
            existingInfo.remove();
        }
        
        input.parentNode.appendChild(fileInfo);
    }
}

/**
 * Check Password Strength
 */
function checkPasswordStrength(input) {
    const password = input.value;
    const strengthIndicator = input.parentNode.querySelector('.password-strength');
    
    if (!strengthIndicator) return;
    
    const strength = calculatePasswordStrength(password);
    
    // Update strength indicator
    strengthIndicator.className = `password-strength strength-${strength.level}`;
    strengthIndicator.textContent = `Password strength: ${strength.level}`;
    
    // Update color
    const colors = {
        'Very Weak': '#dc3545',
        'Weak': '#ffc107',
        'Fair': '#fd7e14',
        'Good': '#20c997',
        'Strong': '#28a745',
        'Very Strong': '#667eea'
    };
    
    strengthIndicator.style.color = colors[strength.level] || '#6c757d';
}

/**
 * Calculate Password Strength
 */
function calculatePasswordStrength(password) {
    let strength = 0;
    let feedback = [];
    
    if (password.length >= 8) strength++;
    else feedback.push('At least 8 characters');
    
    if (/[a-z]/.test(password)) strength++;
    else feedback.push('Lowercase letters');
    
    if (/[A-Z]/.test(password)) strength++;
    else feedback.push('Uppercase letters');
    
    if (/[0-9]/.test(password)) strength++;
    else feedback.push('Numbers');
    
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    else feedback.push('Special characters');
    
    const levels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
    
    return {
        strength: strength,
        level: levels[strength] || 'Very Weak',
        feedback: feedback
    };
}

/**
 * Initialize Tables
 */
function initializeTables() {
    // Initialize data tables
    const tables = document.querySelectorAll('.table[data-table]');
    tables.forEach(table => {
        initializeDataTable(table);
    });
    
    // Handle table actions
    const actionButtons = document.querySelectorAll('.table-action');
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            handleTableAction(this, e);
        });
    });
}

/**
 * Initialize Data Table
 */
function initializeDataTable(table) {
    // Add search functionality
    const searchInput = document.querySelector(`[data-table-search="${table.dataset.table}"]`);
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterTable(table, this.value);
        });
    }
    
    // Add sort functionality
    const sortableHeaders = table.querySelectorAll('th[data-sort]');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            sortTable(table, this.dataset.sort);
        });
    });
}

/**
 * Filter Table
 */
function filterTable(table, searchTerm) {
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matches = text.includes(searchTerm.toLowerCase());
        row.style.display = matches ? '' : 'none';
    });
}

/**
 * Sort Table
 */
function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aText = a.children[columnIndex].textContent.trim();
        const bText = b.children[columnIndex].textContent.trim();
        
        return aText.localeCompare(bText);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * Handle Table Action
 */
function handleTableAction(button, event) {
    event.preventDefault();
    
    const action = button.dataset.action;
    const id = button.dataset.id;
    
    switch (action) {
        case 'edit':
            editItem(id);
            break;
        case 'delete':
            deleteItem(id);
            break;
        case 'view':
            viewItem(id);
            break;
        default:
            console.log('Unknown action:', action);
    }
}

/**
 * Initialize Modals
 */
function initializeModals() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        new bootstrap.Modal(modal);
    });
    
    // Handle modal forms
    const modalForms = document.querySelectorAll('.modal form');
    modalForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            handleModalForm(this, e);
        });
    });
}

/**
 * Handle Modal Form
 */
function handleModalForm(form, event) {
    event.preventDefault();
    
    const modal = form.closest('.modal');
    const modalInstance = bootstrap.Modal.getInstance(modal);
    
    // Submit form via AJAX
    submitFormAJAX(form)
        .then(response => {
            if (response.success) {
                showNotification(response.message || 'Operation completed successfully', 'success');
                modalInstance.hide();
                
                // Reload page or update content
                if (response.reload) {
                    location.reload();
                } else if (response.redirect) {
                    location.href = response.redirect;
                }
            } else {
                showNotification(response.message || 'Operation failed', 'error');
            }
        })
        .catch(error => {
            showNotification('An error occurred. Please try again.', 'error');
            console.error('Form submission error:', error);
        });
}

/**
 * Initialize Tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(tooltipTriggerEl => {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize Notifications
 */
function initializeNotifications() {
    // Check for server-sent notifications
    checkNotifications();
    
    // Auto-refresh notifications every 30 seconds
    setInterval(checkNotifications, 30000);
}

/**
 * Check Notifications
 */
function checkNotifications() {
    fetch('/api/notifications')
        .then(response => response.json())
        .then(data => {
            if (data.notifications) {
                updateNotificationBadge(data.notifications.length);
                updateNotificationList(data.notifications);
            }
        })
        .catch(error => {
            console.error('Error checking notifications:', error);
        });
}

/**
 * Update Notification Badge
 */
function updateNotificationBadge(count) {
    const badge = document.getElementById('notification-count');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline-block' : 'none';
    }
}

/**
 * Update Notification List
 */
function updateNotificationList(notifications) {
    const list = document.getElementById('notification-list');
    if (!list) return;
    
    // Clear existing notifications (except header and footer)
    const items = list.querySelectorAll('.dropdown-item:not(.dropdown-header):not(.dropdown-divider)');
    items.forEach(item => item.remove());
    
    // Add new notifications
    notifications.forEach(notification => {
        const item = createNotificationItem(notification);
        list.insertBefore(item, list.querySelector('.dropdown-divider:last-of-type'));
    });
}

/**
 * Create Notification Item
 */
function createNotificationItem(notification) {
    const item = document.createElement('a');
    item.className = 'dropdown-item';
    item.href = notification.link || '#';
    
    const icon = getNotificationIcon(notification.type);
    const time = timeAgo(notification.created_at);
    
    item.innerHTML = `
        <div class="d-flex">
            <div class="flex-shrink-0">
                <i class="${icon} ${getNotificationColor(notification.type)}"></i>
            </div>
            <div class="flex-grow-1 ms-2">
                <small class="text-muted">${time}</small>
                <div>${notification.message}</div>
            </div>
        </div>
    `;
    
    return item;
}

/**
 * Get Notification Icon
 */
function getNotificationIcon(type) {
    const icons = {
        'info': 'fas fa-info-circle',
        'success': 'fas fa-check-circle',
        'warning': 'fas fa-exclamation-triangle',
        'error': 'fas fa-exclamation-circle',
        'exam': 'fas fa-clipboard-list',
        'attendance': 'fas fa-calendar-check',
        'result': 'fas fa-chart-line',
        'user': 'fas fa-user'
    };
    
    return icons[type] || 'fas fa-bell';
}

/**
 * Get Notification Color
 */
function getNotificationColor(type) {
    const colors = {
        'info': 'text-info',
        'success': 'text-success',
        'warning': 'text-warning',
        'error': 'text-danger',
        'exam': 'text-primary',
        'attendance': 'text-success',
        'result': 'text-info',
        'user': 'text-secondary'
    };
    
    return colors[type] || 'text-secondary';
}

/**
 * Initialize Search
 */
function initializeSearch() {
    const searchInputs = document.querySelectorAll('[data-search]');
    searchInputs.forEach(input => {
        input.addEventListener('input', debounce(function() {
            handleSearch(this);
        }, 300));
    });
}

/**
 * Handle Search
 */
function handleSearch(input) {
    const searchType = input.dataset.search;
    const searchTerm = input.value.trim();
    const resultsContainer = document.querySelector(`[data-search-results="${searchType}"]`);
    
    if (!resultsContainer) return;
    
    if (searchTerm.length < 2) {
        resultsContainer.innerHTML = '';
        return;
    }
    
    // Show loading
    resultsContainer.innerHTML = '<div class="text-center p-3"><div class="spinner"></div></div>';
    
    // Perform search
    fetch(`/api/search?q=${encodeURIComponent(searchTerm)}&type=${searchType}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data, resultsContainer);
        })
        .catch(error => {
            resultsContainer.innerHTML = '<div class="text-center p-3 text-muted">Search failed. Please try again.</div>';
            console.error('Search error:', error);
        });
}

/**
 * Display Search Results
 */
function displaySearchResults(data, container) {
    if (!data.results || data.results.length === 0) {
        container.innerHTML = '<div class="text-center p-3 text-muted">No results found</div>';
        return;
    }
    
    const results = data.results.map(item => createSearchResultItem(item)).join('');
    container.innerHTML = results;
}

/**
 * Create Search Result Item
 */
function createSearchResultItem(item) {
    return `
        <div class="search-result-item p-2 border-bottom">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <img src="${item.avatar || '/assets/images/default-avatar.png'}" 
                         alt="${item.name}" 
                         class="rounded-circle" 
                         width="40" 
                         height="40">
                </div>
                <div class="flex-grow-1 ms-3">
                    <div class="fw-bold">${item.name}</div>
                    <small class="text-muted">${item.description || item.type}</small>
                </div>
                <div class="flex-shrink-0">
                    <a href="${item.link}" class="btn btn-sm btn-outline-primary">View</a>
                </div>
            </div>
        </div>
    `;
}

/**
 * Initialize Dark Mode
 */
function initializeDarkMode() {
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const darkModeIcon = document.getElementById('dark-mode-icon');
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', toggleDarkMode);
    }
    
    // Load saved preference
    const savedTheme = localStorage.getItem('theme') || 'light';
    if (savedTheme === 'dark') {
        enableDarkMode();
    }
}

/**
 * Toggle Dark Mode
 */
function toggleDarkMode() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    if (newTheme === 'dark') {
        enableDarkMode();
    } else {
        disableDarkMode();
    }
    
    localStorage.setItem('theme', newTheme);
}

/**
 * Enable Dark Mode
 */
function enableDarkMode() {
    document.documentElement.setAttribute('data-theme', 'dark');
    const icon = document.getElementById('dark-mode-icon');
    if (icon) {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    }
}

/**
 * Disable Dark Mode
 */
function disableDarkMode() {
    document.documentElement.removeAttribute('data-theme');
    const icon = document.getElementById('dark-mode-icon');
    if (icon) {
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
    }
}

/**
 * Initialize CSRF
 */
function initializeCSRF() {
    // Add CSRF token to all AJAX requests
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        if (args[1] && args[1].method && ['POST', 'PUT', 'DELETE'].includes(args[1].method)) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                args[1].headers = args[1].headers || {};
                args[1].headers['X-CSRF-Token'] = csrfToken;
            }
        }
        return originalFetch.apply(this, args);
    };
}

/**
 * Initialize AJAX
 */
function initializeAJAX() {
    // Global AJAX error handler
    window.addEventListener('unhandledrejection', function(event) {
        if (event.reason instanceof Response && event.reason.status === 401) {
            // Redirect to login on unauthorized
            location.href = '/login';
        }
    });
}

/**
 * Utility Functions
 */

/**
 * Debounce Function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Show Notification
 */
function showNotification(message, type = 'info', duration = 5000) {
    const container = document.getElementById('notification-container');
    if (!container) return;
    
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    container.appendChild(notification);
    
    // Auto-remove after duration
    setTimeout(() => {
        notification.remove();
    }, duration);
}

/**
 * Hide Alerts
 */
function hideAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-dismissible)');
    alerts.forEach(alert => alert.remove());
}

/**
 * Format File Size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Time Ago
 */
function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (days > 0) return `${days} day${days > 1 ? 's' : ''} ago`;
    if (hours > 0) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    if (minutes > 0) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    return 'Just now';
}

/**
 * Is Valid Email
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Submit Form via AJAX
 */
function submitFormAJAX(form) {
    const formData = new FormData(form);
    const method = form.method || 'POST';
    const action = form.action || location.href;
    
    return fetch(action, {
        method: method,
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json());
}

/**
 * Edit Item
 */
function editItem(id) {
    // Implementation depends on context
    console.log('Edit item:', id);
}

/**
 * Delete Item
 */
function deleteItem(id) {
    if (confirm('Are you sure you want to delete this item?')) {
        // Implementation depends on context
        console.log('Delete item:', id);
    }
}

/**
 * View Item
 */
function viewItem(id) {
    // Implementation depends on context
    console.log('View item:', id);
}
