/**
 * School Management System - Pagination Component
 */

class Pagination {
    constructor(options = {}) {
        this.currentPage = options.currentPage || 1;
        this.totalPages = options.totalPages || 1;
        this.totalItems = options.totalItems || 0;
        this.itemsPerPage = options.itemsPerPage || 10;
        this.url = options.url || '';
        this.container = options.container || null;
        this.onPageChange = options.onPageChange || null;
        
        this.init();
    }
    
    /**
     * Initialize pagination
     */
    init() {
        if (this.container) {
            this.render();
        }
    }
    
    /**
     * Render pagination HTML
     */
    render() {
        if (!this.container || this.totalPages <= 1) {
            return;
        }
        
        const paginationHTML = this.generateHTML();
        this.container.innerHTML = paginationHTML;
        
        // Add event listeners
        this.attachEventListeners();
    }
    
    /**
     * Generate pagination HTML
     */
    generateHTML() {
        let html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        
        // Previous button
        if (this.currentPage > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="${this.buildUrl(this.currentPage - 1)}" data-page="${this.currentPage - 1}">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            </li>`;
        } else {
            html += '<li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i> Previous</a></li>';
        }
        
        // Page numbers
        const pageNumbers = this.getPageNumbers();
        pageNumbers.forEach(pageNum => {
            if (pageNum === '...') {
                html += '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
            } else {
                const activeClass = pageNum === this.currentPage ? 'active' : '';
                html += `<li class="page-item ${activeClass}">
                    <a class="page-link" href="${this.buildUrl(pageNum)}" data-page="${pageNum}">${pageNum}</a>
                </li>`;
            }
        });
        
        // Next button
        if (this.currentPage < this.totalPages) {
            html += `<li class="page-item">
                <a class="page-link" href="${this.buildUrl(this.currentPage + 1)}" data-page="${this.currentPage + 1}">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;
        } else {
            html += '<li class="page-item disabled"><a class="page-link" href="#">Next <i class="fas fa-chevron-right"></i></a></li>';
        }
        
        html += '</ul></nav>';
        
        // Add info text
        if (this.totalItems > 0) {
            const startItem = (this.currentPage - 1) * this.itemsPerPage + 1;
            const endItem = Math.min(this.currentPage * this.itemsPerPage, this.totalItems);
            
            html += `<div class="text-center mt-2 text-muted">
                Showing ${startItem} to ${endItem} of ${this.totalItems} entries
            </div>`;
        }
        
        return html;
    }
    
    /**
     * Get page numbers to display
     */
    getPageNumbers() {
        const pages = [];
        const maxVisible = 7;
        const halfVisible = Math.floor(maxVisible / 2);
        
        if (this.totalPages <= maxVisible) {
            for (let i = 1; i <= this.totalPages; i++) {
                pages.push(i);
            }
        } else {
            if (this.currentPage <= halfVisible) {
                for (let i = 1; i <= maxVisible - 1; i++) {
                    pages.push(i);
                }
                pages.push('...');
                pages.push(this.totalPages);
            } else if (this.currentPage >= this.totalPages - halfVisible) {
                pages.push(1);
                pages.push('...');
                for (let i = this.totalPages - maxVisible + 2; i <= this.totalPages; i++) {
                    pages.push(i);
                }
            } else {
                pages.push(1);
                pages.push('...');
                for (let i = this.currentPage - halfVisible + 1; i <= this.currentPage + halfVisible - 1; i++) {
                    pages.push(i);
                }
                pages.push('...');
                pages.push(this.totalPages);
            }
        }
        
        return pages;
    }
    
    /**
     * Build URL for page
     */
    buildUrl(page) {
        const url = new URL(this.url, window.location.origin);
        url.searchParams.set('page', page);
        return url.pathname + url.search;
    }
    
    /**
     * Attach event listeners
     */
    attachEventListeners() {
        const links = this.container.querySelectorAll('.page-link[data-page]');
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.goToPage(parseInt(link.dataset.page));
            });
        });
    }
    
    /**
     * Go to specific page
     */
    goToPage(page) {
        if (page < 1 || page > this.totalPages || page === this.currentPage) {
            return;
        }
        
        this.currentPage = page;
        
        // Update URL
        const newUrl = this.buildUrl(page);
        history.pushState({}, '', newUrl);
        
        // Call callback
        if (this.onPageChange) {
            this.onPageChange(page);
        }
        
        // Re-render
        this.render();
    }
    
    /**
     * Update pagination data
     */
    update(data) {
        this.currentPage = data.currentPage || this.currentPage;
        this.totalPages = data.totalPages || this.totalPages;
        this.totalItems = data.totalItems || this.totalItems;
        this.itemsPerPage = data.itemsPerPage || this.itemsPerPage;
        
        this.render();
    }
    
    /**
     * Reset pagination
     */
    reset() {
        this.currentPage = 1;
        this.render();
    }
    
    /**
     * Get current page
     */
    getCurrentPage() {
        return this.currentPage;
    }
    
    /**
     * Get total pages
     */
    getTotalPages() {
        return this.totalPages;
    }
    
    /**
     * Get total items
     */
    getTotalItems() {
        return this.totalItems;
    }
    
    /**
     * Get items per page
     */
    getItemsPerPage() {
        return this.itemsPerPage;
    }
    
    /**
     * Set items per page
     */
    setItemsPerPage(itemsPerPage) {
        this.itemsPerPage = itemsPerPage;
        this.totalPages = Math.ceil(this.totalItems / itemsPerPage);
        this.currentPage = 1;
        this.render();
    }
}

/**
 * Server-side Pagination
 */
class ServerPagination extends Pagination {
    constructor(options = {}) {
        super(options);
        this.loading = false;
        this.dataContainer = options.dataContainer || null;
        this.loadingTemplate = options.loadingTemplate || null;
        this.errorTemplate = options.errorTemplate || null;
    }
    
    /**
     * Go to page with server request
     */
    async goToPage(page) {
        if (this.loading || page < 1 || page > this.totalPages || page === this.currentPage) {
            return;
        }
        
        this.loading = true;
        this.showLoading();
        
        try {
            const url = this.buildUrl(page);
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.currentPage = page;
                this.totalPages = data.pagination.totalPages;
                this.totalItems = data.pagination.totalItems;
                this.itemsPerPage = data.pagination.itemsPerPage;
                
                // Update data container
                if (this.dataContainer && data.html) {
                    this.dataContainer.innerHTML = data.html;
                }
                
                // Update URL
                const newUrl = this.buildUrl(page);
                history.pushState({}, '', newUrl);
                
                // Call callback
                if (this.onPageChange) {
                    this.onPageChange(page, data);
                }
                
                // Re-render pagination
                this.render();
            } else {
                throw new Error(data.message || 'Failed to load data');
            }
        } catch (error) {
            this.showError(error.message);
            console.error('Pagination error:', error);
        } finally {
            this.loading = false;
            this.hideLoading();
        }
    }
    
    /**
     * Show loading indicator
     */
    showLoading() {
        if (this.dataContainer && this.loadingTemplate) {
            this.dataContainer.innerHTML = this.loadingTemplate;
        }
    }
    
    /**
     * Hide loading indicator
     */
    hideLoading() {
        // Loading will be hidden when new content is loaded
    }
    
    /**
     * Show error message
     */
    showError(message) {
        if (this.dataContainer && this.errorTemplate) {
            this.dataContainer.innerHTML = this.errorTemplate.replace('{{message}}', message);
        } else {
            alert(message);
        }
    }
}

/**
 * Infinite Scroll Pagination
 */
class InfinitePagination {
    constructor(options = {}) {
        this.currentPage = options.currentPage || 1;
        this.totalPages = options.totalPages || 1;
        this.totalItems = options.totalItems || 0;
        this.itemsPerPage = options.itemsPerPage || 10;
        this.url = options.url || '';
        this.container = options.container || null;
        this.loading = false;
        this.hasMore = true;
        this.threshold = options.threshold || 100;
        this.onLoad = options.onLoad || null;
        this.loadingTemplate = options.loadingTemplate || null;
        
        this.init();
    }
    
    /**
     * Initialize infinite scroll
     */
    init() {
        if (!this.container) return;
        
        window.addEventListener('scroll', () => {
            this.handleScroll();
        });
        
        // Initial load
        this.loadMore();
    }
    
    /**
     * Handle scroll event
     */
    handleScroll() {
        if (this.loading || !this.hasMore) return;
        
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;
        
        if (scrollTop + windowHeight >= documentHeight - this.threshold) {
            this.loadMore();
        }
    }
    
    /**
     * Load more items
     */
    async loadMore() {
        if (this.loading || !this.hasMore) return;
        
        this.loading = true;
        this.showLoading();
        
        try {
            const url = this.buildUrl(this.currentPage + 1);
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.html) {
                // Append new content
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.html;
                
                const items = tempDiv.children;
                Array.from(items).forEach(item => {
                    this.container.appendChild(item);
                });
                
                this.currentPage++;
                this.hasMore = this.currentPage < this.totalPages;
                
                // Call callback
                if (this.onLoad) {
                    this.onLoad(data);
                }
            } else {
                this.hasMore = false;
                throw new Error(data.message || 'Failed to load more items');
            }
        } catch (error) {
            console.error('Infinite scroll error:', error);
            this.hasMore = false;
        } finally {
            this.loading = false;
            this.hideLoading();
        }
    }
    
    /**
     * Build URL for page
     */
    buildUrl(page) {
        const url = new URL(this.url, window.location.origin);
        url.searchParams.set('page', page);
        return url.pathname + url.search;
    }
    
    /**
     * Show loading indicator
     */
    showLoading() {
        if (this.loadingTemplate) {
            const loader = document.createElement('div');
            loader.className = 'infinite-scroll-loader';
            loader.innerHTML = this.loadingTemplate;
            this.container.appendChild(loader);
        }
    }
    
    /**
     * Hide loading indicator
     */
    hideLoading() {
        const loader = this.container.querySelector('.infinite-scroll-loader');
        if (loader) {
            loader.remove();
        }
    }
    
    /**
     * Reset infinite scroll
     */
    reset() {
        this.currentPage = 1;
        this.hasMore = true;
        this.loading = false;
        
        // Clear container
        if (this.container) {
            this.container.innerHTML = '';
        }
        
        // Load initial data
        this.loadMore();
    }
    
    /**
     * Check if has more items
     */
    hasMoreItems() {
        return this.hasMore;
    }
    
    /**
     * Get current page
     */
    getCurrentPage() {
        return this.currentPage;
    }
}

/**
 * Initialize pagination components
 */
document.addEventListener('DOMContentLoaded', () => {
    // Auto-initialize pagination components
    const paginationContainers = document.querySelectorAll('[data-pagination]');
    paginationContainers.forEach(container => {
        const options = {
            currentPage: parseInt(container.dataset.currentPage) || 1,
            totalPages: parseInt(container.dataset.totalPages) || 1,
            totalItems: parseInt(container.dataset.totalItems) || 0,
            itemsPerPage: parseInt(container.dataset.itemsPerPage) || 10,
            url: container.dataset.url || '',
            container: container
        };
        
        if (container.dataset.serverSide === 'true') {
            options.dataContainer = document.querySelector(container.dataset.dataContainer);
            new ServerPagination(options);
        } else {
            new Pagination(options);
        }
    });
    
    // Auto-initialize infinite scroll
    const infiniteContainers = document.querySelectorAll('[data-infinite-scroll]');
    infiniteContainers.forEach(container => {
        const options = {
            currentPage: parseInt(container.dataset.currentPage) || 1,
            totalPages: parseInt(container.dataset.totalPages) || 1,
            totalItems: parseInt(container.dataset.totalItems) || 0,
            itemsPerPage: parseInt(container.dataset.itemsPerPage) || 10,
            url: container.dataset.url || '',
            container: container,
            loadingTemplate: container.dataset.loadingTemplate || '<div class="text-center p-3"><div class="spinner"></div></div>'
        };
        
        new InfinitePagination(options);
    });
});

// Export classes for global access
window.Pagination = Pagination;
window.ServerPagination = ServerPagination;
window.InfinitePagination = InfinitePagination;
