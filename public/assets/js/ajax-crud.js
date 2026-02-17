/**
 * School Management System - AJAX CRUD Operations
 */

class AjaxCRUD {
    constructor() {
        this.init();
    }
    
    /**
     * Initialize AJAX CRUD
     */
    init() {
        this.setupEventListeners();
        this.setupFormHandlers();
        this.setupDeleteHandlers();
        this.setupEditHandlers();
        this.setupPagination();
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Handle create buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="create"]')) {
                e.preventDefault();
                this.handleCreate(e.target);
            }
        });
        
        // Handle edit buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="edit"]')) {
                e.preventDefault();
                this.handleEdit(e.target);
            }
        });
        
        // Handle delete buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="delete"]')) {
                e.preventDefault();
                this.handleDelete(e.target);
            }
        });
        
        // Handle view buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="view"]')) {
                e.preventDefault();
                this.handleView(e.target);
            }
        });
        
        // Handle form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.matches('[data-ajax="true"]')) {
                e.preventDefault();
                this.handleFormSubmit(e.target);
            }
        });
    }
    
    /**
     * Setup form handlers
     */
    setupFormHandlers() {
        // Auto-save functionality
        const autoSaveForms = document.querySelectorAll('[data-auto-save="true"]');
        autoSaveForms.forEach(form => {
            this.setupAutoSave(form);
        });
        
        // Form validation
        const validatedForms = document.querySelectorAll('[data-validate="true"]');
        validatedForms.forEach(form => {
            this.setupFormValidation(form);
        });
    }
    
    /**
     * Setup delete handlers
     */
    setupDeleteHandlers() {
        // Bulk delete functionality
        const bulkDeleteBtn = document.querySelector('[data-bulk-delete="true"]');
        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', () => {
                this.handleBulkDelete();
            });
        }
        
        // Checkbox selection
        const selectAllCheckbox = document.querySelector('[data-select-all="true"]');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                this.handleSelectAll(e.target);
            });
        }
        
        const itemCheckboxes = document.querySelectorAll('[data-select-item="true"]');
        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.updateBulkActions();
            });
        });
    }
    
    /**
     * Setup edit handlers
     */
    setupEditHandlers() {
        // Inline editing
        const inlineEditElements = document.querySelectorAll('[data-inline-edit="true"]');
        inlineEditElements.forEach(element => {
            this.setupInlineEdit(element);
        });
    }
    
    /**
     * Setup pagination
     */
    setupPagination() {
        // AJAX pagination
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-ajax-page="true"]')) {
                e.preventDefault();
                this.handlePagination(e.target);
            }
        });
        
        // Infinite scroll
        const infiniteScrollContainer = document.querySelector('[data-infinite-scroll="true"]');
        if (infiniteScrollContainer) {
            this.setupInfiniteScroll(infiniteScrollContainer);
        }
    }
    
    /**
     * Handle create operation
     */
    handleCreate(button) {
        const url = button.dataset.url || button.getAttribute('href');
        const modalId = button.dataset.modal;
        
        if (modalId) {
            this.loadCreateForm(url, modalId);
        } else {
            this.showCreateModal(url);
        }
    }
    
    /**
     * Load create form in modal
     */
    loadCreateForm(url, modalId) {
        this.showLoading();
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const modal = document.getElementById(modalId);
            if (modal) {
                const modalBody = modal.querySelector('.modal-body');
                modalBody.innerHTML = html;
                
                // Initialize form
                const form = modalBody.querySelector('form');
                if (form) {
                    this.setupFormHandlers();
                }
                
                // Show modal
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
            }
        })
        .catch(error => {
            this.showError('Failed to load form');
            console.error('Error loading form:', error);
        })
        .finally(() => {
            this.hideLoading();
        });
    }
    
    /**
     * Show create modal
     */
    showCreateModal(url) {
        const modalHtml = `
            <div class="modal fade" id="createModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Create New Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center">
                                <div class="spinner"></div>
                                <p>Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        const modal = document.getElementById('createModal');
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
        
        // Load form content
        this.loadCreateForm(url, 'createModal');
        
        // Remove modal from DOM when hidden
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
    
    /**
     * Handle edit operation
     */
    handleEdit(button) {
        const id = button.dataset.id;
        const url = button.dataset.url || `${button.getAttribute('href')}/${id}`;
        const modalId = button.dataset.modal;
        
        if (modalId) {
            this.loadEditForm(url, modalId, id);
        } else {
            this.showEditModal(url, id);
        }
    }
    
    /**
     * Load edit form in modal
     */
    loadEditForm(url, modalId, id) {
        this.showLoading();
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const modal = document.getElementById(modalId);
            if (modal) {
                const modalBody = modal.querySelector('.modal-body');
                modalBody.innerHTML = html;
                
                // Initialize form
                const form = modalBody.querySelector('form');
                if (form) {
                    this.setupFormHandlers();
                }
                
                // Show modal
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
            }
        })
        .catch(error => {
            this.showError('Failed to load form');
            console.error('Error loading form:', error);
        })
        .finally(() => {
            this.hideLoading();
        });
    }
    
    /**
     * Show edit modal
     */
    showEditModal(url, id) {
        const modalHtml = `
            <div class="modal fade" id="editModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center">
                                <div class="spinner"></div>
                                <p>Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        const modal = document.getElementById('editModal');
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
        
        // Load form content
        this.loadEditForm(url, 'editModal', id);
        
        // Remove modal from DOM when hidden
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
    
    /**
     * Handle delete operation
     */
    handleDelete(button) {
        const id = button.dataset.id;
        const url = button.dataset.url || `${button.getAttribute('href')}/${id}`;
        const name = button.dataset.name || 'this item';
        
        if (confirm(`Are you sure you want to delete ${name}?`)) {
            this.deleteItem(url, id);
        }
    }
    
    /**
     * Delete item
     */
    deleteItem(url, id) {
        this.showLoading();
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': this.getCSRFToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showSuccess(data.message || 'Item deleted successfully');
                this.removeItem(id);
                
                // Refresh table if needed
                if (data.reload) {
                    location.reload();
                }
            } else {
                this.showError(data.message || 'Failed to delete item');
            }
        })
        .catch(error => {
            this.showError('An error occurred while deleting the item');
            console.error('Delete error:', error);
        })
        .finally(() => {
            this.hideLoading();
        });
    }
    
    /**
     * Handle view operation
     */
    handleView(button) {
        const id = button.dataset.id;
        const url = button.dataset.url || `${button.getAttribute('href')}/${id}`;
        
        this.showLoading();
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            this.showViewModal(html);
        })
        .catch(error => {
            this.showError('Failed to load details');
            console.error('Error loading details:', error);
        })
        .finally(() => {
            this.hideLoading();
        });
    }
    
    /**
     * Show view modal
     */
    showViewModal(html) {
        const modalHtml = `
            <div class="modal fade" id="viewModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Item Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${html}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        const modal = document.getElementById('viewModal');
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
        
        // Remove modal from DOM when hidden
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
    
    /**
     * Handle form submission
     */
    handleFormSubmit(form) {
        const url = form.action || form.dataset.url;
        const method = form.method || 'POST';
        const formData = new FormData(form);
        
        this.showLoading();
        
        fetch(url, {
            method: method,
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': this.getCSRFToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showSuccess(data.message || 'Operation completed successfully');
                
                // Close modal if inside one
                const modal = form.closest('.modal');
                if (modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
                
                // Reload or redirect if needed
                if (data.reload) {
                    location.reload();
                } else if (data.redirect) {
                    location.href = data.redirect;
                } else if (data.updateRow) {
                    this.updateRow(data.data);
                }
            } else {
                this.showError(data.message || 'Operation failed');
                
                // Show validation errors if any
                if (data.errors) {
                    this.showFormErrors(form, data.errors);
                }
            }
        })
        .catch(error => {
            this.showError('An error occurred while processing your request');
            console.error('Form submission error:', error);
        })
        .finally(() => {
            this.hideLoading();
        });
    }
    
    /**
     * Setup auto-save
     */
    setupAutoSave(form) {
        let saveTimeout;
        
        form.addEventListener('input', () => {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                this.autoSave(form);
            }, 2000);
        });
    }
    
    /**
     * Auto-save form
     */
    autoSave(form) {
        const url = form.dataset.autoSaveUrl;
        if (!url) return;
        
        const formData = new FormData(form);
        
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': this.getCSRFToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showInfo('Auto-saved');
            }
        })
        .catch(error => {
            console.error('Auto-save error:', error);
        });
    }
    
    /**
     * Setup form validation
     */
    setupFormValidation(form) {
        form.addEventListener('input', (e) => {
            this.validateField(e.target);
        });
        
        form.addEventListener('submit', (e) => {
            if (!this.validateForm(form)) {
                e.preventDefault();
            }
        });
    }
    
    /**
     * Validate field
     */
    validateField(field) {
        const value = field.value.trim();
        const required = field.hasAttribute('required');
        const type = field.type;
        const pattern = field.pattern;
        
        // Remove existing error
        this.removeFieldError(field);
        
        // Check required
        if (required && !value) {
            this.showFieldError(field, 'This field is required');
            return false;
        }
        
        // Check pattern
        if (pattern && value && !new RegExp(pattern).test(value)) {
            this.showFieldError(field, 'Invalid format');
            return false;
        }
        
        // Check email
        if (type === 'email' && value && !this.isValidEmail(value)) {
            this.showFieldError(field, 'Please enter a valid email address');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate form
     */
    validateForm(form) {
        const fields = form.querySelectorAll('input, select, textarea');
        let isValid = true;
        
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    /**
     * Show field error
     */
    showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = message;
        
        field.parentNode.appendChild(feedback);
    }
    
    /**
     * Remove field error
     */
    removeFieldError(field) {
        field.classList.remove('is-invalid');
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }
    
    /**
     * Show form errors
     */
    showFormErrors(form, errors) {
        // Clear existing errors
        this.clearFormErrors(form);
        
        // Show field-specific errors
        Object.keys(errors).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                this.showFieldError(field, errors[fieldName]);
            }
        });
        
        // Show general errors
        if (errors.general) {
            this.showError(errors.general);
        }
    }
    
    /**
     * Clear form errors
     */
    clearFormErrors(form) {
        const fields = form.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            this.removeFieldError(field);
        });
    }
    
    /**
     * Handle bulk delete
     */
    handleBulkDelete() {
        const selectedItems = this.getSelectedItems();
        
        if (selectedItems.length === 0) {
            this.showError('Please select items to delete');
            return;
        }
        
        if (confirm(`Are you sure you want to delete ${selectedItems.length} items?`)) {
            this.bulkDelete(selectedItems);
        }
    }
    
    /**
     * Bulk delete items
     */
    bulkDelete(items) {
        const url = '[data-bulk-delete-url]';
        const bulkDeleteUrl = document.querySelector(url)?.dataset.bulkDeleteUrl;
        
        if (!bulkDeleteUrl) {
            this.showError('Bulk delete URL not configured');
            return;
        }
        
        this.showLoading();
        
        fetch(bulkDeleteUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': this.getCSRFToken()
            },
            body: JSON.stringify({ items: items })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showSuccess(data.message || 'Items deleted successfully');
                location.reload();
            } else {
                this.showError(data.message || 'Failed to delete items');
            }
        })
        .catch(error => {
            this.showError('An error occurred while deleting items');
            console.error('Bulk delete error:', error);
        })
        .finally(() => {
            this.hideLoading();
        });
    }
    
    /**
     * Handle select all
     */
    handleSelectAll(checkbox) {
        const itemCheckboxes = document.querySelectorAll('[data-select-item="true"]');
        itemCheckboxes.forEach(itemCheckbox => {
            itemCheckbox.checked = checkbox.checked;
        });
        
        this.updateBulkActions();
    }
    
    /**
     * Update bulk actions
     */
    updateBulkActions() {
        const selectedItems = this.getSelectedItems();
        const bulkActions = document.querySelectorAll('[data-bulk-action="true"]');
        
        bulkActions.forEach(action => {
            action.disabled = selectedItems.length === 0;
        });
    }
    
    /**
     * Get selected items
     */
    getSelectedItems() {
        const checkboxes = document.querySelectorAll('[data-select-item="true"]:checked');
        return Array.from(checkboxes).map(checkbox => checkbox.value);
    }
    
    /**
     * Setup inline edit
     */
    setupInlineEdit(element) {
        element.addEventListener('click', () => {
            this.makeEditable(element);
        });
    }
    
    /**
     * Make element editable
     */
    makeEditable(element) {
        const currentValue = element.textContent;
        const input = document.createElement('input');
        input.type = 'text';
        input.value = currentValue;
        input.className = 'form-control form-control-sm';
        
        input.addEventListener('blur', () => {
            this.saveInlineEdit(element, input.value);
        });
        
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                input.blur();
            } else if (e.key === 'Escape') {
                element.textContent = currentValue;
                element.style.display = '';
                input.remove();
            }
        });
        
        element.style.display = 'none';
        element.parentNode.insertBefore(input, element);
        input.focus();
        input.select();
    }
    
    /**
     * Save inline edit
     */
    saveInlineEdit(element, newValue) {
        const url = element.dataset.inlineEditUrl;
        const field = element.dataset.field;
        
        if (!url || !field) {
            element.textContent = newValue;
            element.style.display = '';
            return;
        }
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': this.getCSRFToken()
            },
            body: JSON.stringify({
                field: field,
                value: newValue
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                element.textContent = newValue;
                this.showInfo('Updated successfully');
            } else {
                this.showError('Failed to update');
            }
        })
        .catch(error => {
            this.showError('An error occurred');
            console.error('Inline edit error:', error);
        })
        .finally(() => {
            element.style.display = '';
            const input = element.parentNode.querySelector('input');
            if (input) {
                input.remove();
            }
        });
    }
    
    /**
     * Handle pagination
     */
    handlePagination(link) {
        const url = link.href;
        const container = document.querySelector('[data-ajax-container="true"]');
        
        if (!container) {
            location.href = url;
            return;
        }
        
        this.showLoading();
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            
            // Update URL without page reload
            history.pushState({}, '', url);
            
            // Reinitialize event listeners
            this.init();
        })
        .catch(error => {
            this.showError('Failed to load page');
            console.error('Pagination error:', error);
        })
        .finally(() => {
            this.hideLoading();
        });
    }
    
    /**
     * Setup infinite scroll
     */
    setupInfiniteScroll(container) {
        let loading = false;
        let page = 2;
        
        const loadMore = () => {
            if (loading) return;
            
            const url = container.dataset.infiniteScrollUrl;
            if (!url) return;
            
            loading = true;
            this.showLoading();
            
            fetch(`${url}?page=${page}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                if (html.trim()) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    
                    const items = tempDiv.querySelectorAll('[data-infinite-item="true"]');
                    items.forEach(item => {
                        container.appendChild(item);
                    });
                    
                    page++;
                } else {
                    // No more items
                    window.removeEventListener('scroll', handleScroll);
                }
            })
            .catch(error => {
                console.error('Infinite scroll error:', error);
            })
            .finally(() => {
                loading = false;
                this.hideLoading();
            });
        };
        
        const handleScroll = () => {
            if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 100) {
                loadMore();
            }
        };
        
        window.addEventListener('scroll', handleScroll);
    }
    
    /**
     * Remove item from DOM
     */
    removeItem(id) {
        const element = document.querySelector(`[data-item-id="${id}"]`);
        if (element) {
            element.remove();
        }
    }
    
    /**
     * Update row in table
     */
    updateRow(data) {
        const row = document.querySelector(`[data-item-id="${data.id}"]`);
        if (row) {
            // Update row content
            Object.keys(data).forEach(key => {
                const cell = row.querySelector(`[data-field="${key}"]`);
                if (cell) {
                    cell.textContent = data[key];
                }
            });
            
            // Add highlight effect
            row.classList.add('table-success');
            setTimeout(() => {
                row.classList.remove('table-success');
            }, 2000);
        }
    }
    
    /**
     * Get CSRF token
     */
    getCSRFToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }
    
    /**
     * Show loading indicator
     */
    showLoading() {
        const loader = document.createElement('div');
        loader.id = 'ajax-loader';
        loader.className = 'position-fixed top-50 start-50 translate-middle';
        loader.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;
        document.body.appendChild(loader);
    }
    
    /**
     * Hide loading indicator
     */
    hideLoading() {
        const loader = document.getElementById('ajax-loader');
        if (loader) {
            loader.remove();
        }
    }
    
    /**
     * Show success message
     */
    showSuccess(message) {
        this.showNotification(message, 'success');
    }
    
    /**
     * Show error message
     */
    showError(message) {
        this.showNotification(message, 'danger');
    }
    
    /**
     * Show info message
     */
    showInfo(message) {
        this.showNotification(message, 'info');
    }
    
    /**
     * Show notification
     */
    showNotification(message, type) {
        const container = document.getElementById('notification-container');
        if (!container) return;
        
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        container.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
    
    /**
     * Check if email is valid
     */
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
}

// Initialize AJAX CRUD when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.ajaxCRUD = new AjaxCRUD();
});
