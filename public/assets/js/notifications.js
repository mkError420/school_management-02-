/**
 * School Management System - Notification System
 */

class NotificationSystem {
    constructor() {
        this.container = null;
        this.notifications = [];
        this.maxNotifications = 5;
        this.defaultDuration = 5000;
        this.soundEnabled = true;
        this.position = 'top-right';
        
        this.init();
    }
    
    /**
     * Initialize notification system
     */
    init() {
        this.createContainer();
        this.setupEventListeners();
        this.loadSettings();
        
        // Auto-hide notifications
        setInterval(() => {
            this.cleanupNotifications();
        }, 1000);
    }
    
    /**
     * Create notification container
     */
    createContainer() {
        this.container = document.createElement('div');
        this.container.id = 'notification-container';
        this.container.className = `notification-container notification-${this.position}`;
        document.body.appendChild(this.container);
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Listen for custom notification events
        document.addEventListener('showNotification', (e) => {
            this.show(e.detail.message, e.detail.type, e.detail.options);
        });
        
        // Listen for server-sent events
        this.setupEventSource();
    }
    
    /**
     * Setup EventSource for real-time notifications
     */
    setupEventSource() {
        if (!window.EventSource) return;
        
        try {
            const eventSource = new EventSource('/api/events');
            
            eventSource.onmessage = (e) => {
                const data = JSON.parse(e.data);
                if (data.type === 'notification') {
                    this.show(data.message, data.level, data.options);
                }
            };
            
            eventSource.onerror = (e) => {
                console.error('EventSource error:', e);
                eventSource.close();
            };
        } catch (error) {
            console.error('Failed to setup EventSource:', error);
        }
    }
    
    /**
     * Show notification
     */
    show(message, type = 'info', options = {}) {
        const notification = {
            id: this.generateId(),
            message: message,
            type: type,
            duration: options.duration || this.defaultDuration,
            persistent: options.persistent || false,
            icon: options.icon || this.getIcon(type),
            title: options.title || '',
            actions: options.actions || [],
            timestamp: new Date(),
            sound: options.sound !== false && this.soundEnabled
        };
        
        // Add to notifications array
        this.notifications.push(notification);
        
        // Limit notifications
        if (this.notifications.length > this.maxNotifications) {
            const removed = this.notifications.shift();
            this.removeNotificationElement(removed.id);
        }
        
        // Create notification element
        const element = this.createElement(notification);
        this.container.appendChild(element);
        
        // Play sound
        if (notification.sound) {
            this.playSound(type);
        }
        
        // Auto-hide if not persistent
        if (!notification.persistent) {
            setTimeout(() => {
                this.hide(notification.id);
            }, notification.duration);
        }
        
        // Trigger show event
        this.triggerEvent('notification:show', notification);
        
        return notification.id;
    }
    
    /**
     * Create notification element
     */
    createElement(notification) {
        const element = document.createElement('div');
        element.className = `notification notification-${notification.type}`;
        element.id = `notification-${notification.id}`;
        
        element.innerHTML = `
            <div class="notification-content">
                <div class="notification-icon">
                    <i class="${notification.icon}"></i>
                </div>
                <div class="notification-body">
                    ${notification.title ? `<div class="notification-title">${notification.title}</div>` : ''}
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-time">${this.formatTime(notification.timestamp)}</div>
                </div>
                <div class="notification-actions">
                    <button class="notification-close" data-id="${notification.id}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            ${notification.actions.length > 0 ? `
                <div class="notification-footer">
                    ${notification.actions.map(action => `
                        <button class="btn btn-sm ${action.class || 'btn-outline-primary'}" 
                                data-action="${action.action}" 
                                data-id="${notification.id}">
                            ${action.label}
                        </button>
                    `).join('')}
                </div>
            ` : ''}
            <div class="notification-progress"></div>
        `;
        
        // Add event listeners
        this.attachElementListeners(element, notification);
        
        // Add animation
        setTimeout(() => {
            element.classList.add('show');
        }, 10);
        
        return element;
    }
    
    /**
     * Attach event listeners to element
     */
    attachElementListeners(element, notification) {
        // Close button
        const closeBtn = element.querySelector('.notification-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.hide(notification.id);
            });
        }
        
        // Action buttons
        const actionBtns = element.querySelectorAll('[data-action]');
        actionBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = e.target.dataset.action;
                this.handleAction(notification.id, action);
            });
        });
        
        // Click to pause auto-hide
        element.addEventListener('mouseenter', () => {
            this.pauseAutoHide(notification.id);
        });
        
        element.addEventListener('mouseleave', () => {
            this.resumeAutoHide(notification.id);
        });
    }
    
    /**
     * Hide notification
     */
    hide(id) {
        const element = document.getElementById(`notification-${id}`);
        const notification = this.notifications.find(n => n.id === id);
        
        if (element) {
            element.classList.add('hide');
            
            setTimeout(() => {
                this.removeNotificationElement(id);
            }, 300);
        }
        
        if (notification) {
            notification.hidden = true;
            this.triggerEvent('notification:hide', notification);
        }
    }
    
    /**
     * Remove notification element
     */
    removeNotificationElement(id) {
        const element = document.getElementById(`notification-${id}`);
        if (element) {
            element.remove();
        }
        
        // Remove from notifications array
        this.notifications = this.notifications.filter(n => n.id !== id);
    }
    
    /**
     * Pause auto-hide
     */
    pauseAutoHide(id) {
        const notification = this.notifications.find(n => n.id === id);
        if (notification && !notification.persistent) {
            notification.paused = true;
            notification.remainingTime = notification.remainingTime || notification.duration;
            
            const element = document.getElementById(`notification-${id}`);
            if (element) {
                const progress = element.querySelector('.notification-progress');
                if (progress) {
                    progress.style.animationPlayState = 'paused';
                }
            }
        }
    }
    
    /**
     * Resume auto-hide
     */
    resumeAutoHide(id) {
        const notification = this.notifications.find(n => n.id === id);
        if (notification && !notification.persistent && notification.paused) {
            notification.paused = false;
            
            const element = document.getElementById(`notification-${id}`);
            if (element) {
                const progress = element.querySelector('.notification-progress');
                if (progress) {
                    progress.style.animationPlayState = 'running';
                }
            }
            
            setTimeout(() => {
                this.hide(id);
            }, notification.remainingTime);
        }
    }
    
    /**
     * Handle notification action
     */
    handleAction(notificationId, action) {
        const notification = this.notifications.find(n => n.id === notificationId);
        if (!notification) return;
        
        // Trigger action event
        this.triggerEvent('notification:action', {
            notification: notification,
            action: action
        });
        
        // Hide notification after action
        this.hide(notificationId);
    }
    
    /**
     * Show success notification
     */
    success(message, options = {}) {
        return this.show(message, 'success', options);
    }
    
    /**
     * Show error notification
     */
    error(message, options = {}) {
        return this.show(message, 'error', options);
    }
    
    /**
     * Show warning notification
     */
    warning(message, options = {}) {
        return this.show(message, 'warning', options);
    }
    
    /**
     * Show info notification
     */
    info(message, options = {}) {
        return this.show(message, 'info', options);
    }
    
    /**
     * Clear all notifications
     */
    clear() {
        this.notifications.forEach(notification => {
            this.hide(notification.id);
        });
    }
    
    /**
     * Clear notifications by type
     */
    clearByType(type) {
        const notifications = this.notifications.filter(n => n.type === type);
        notifications.forEach(notification => {
            this.hide(notification.id);
        });
    }
    
    /**
     * Get notification count
     */
    getCount() {
        return this.notifications.length;
    }
    
    /**
     * Get unread notifications count
     */
    getUnreadCount() {
        return this.notifications.filter(n => !n.read).length;
    }
    
    /**
     * Mark notification as read
     */
    markAsRead(id) {
        const notification = this.notifications.find(n => n.id === id);
        if (notification) {
            notification.read = true;
            this.triggerEvent('notification:read', notification);
        }
    }
    
    /**
     * Mark all notifications as read
     */
    markAllAsRead() {
        this.notifications.forEach(notification => {
            notification.read = true;
        });
        this.triggerEvent('notification:readAll', this.notifications);
    }
    
    /**
     * Get notification history
     */
    getHistory() {
        return this.notifications;
    }
    
    /**
     * Set position
     */
    setPosition(position) {
        this.position = position;
        if (this.container) {
            this.container.className = `notification-container notification-${position}`;
        }
    }
    
    /**
     * Enable/disable sound
     */
    setSoundEnabled(enabled) {
        this.soundEnabled = enabled;
        localStorage.setItem('notification-sound', enabled);
    }
    
    /**
     * Set max notifications
     */
    setMaxNotifications(max) {
        this.maxNotifications = max;
    }
    
    /**
     * Load settings from localStorage
     */
    loadSettings() {
        const soundEnabled = localStorage.getItem('notification-sound');
        if (soundEnabled !== null) {
            this.soundEnabled = soundEnabled === 'true';
        }
        
        const position = localStorage.getItem('notification-position');
        if (position) {
            this.setPosition(position);
        }
    }
    
    /**
     * Generate unique ID
     */
    generateId() {
        return 'notification-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    }
    
    /**
     * Get icon for notification type
     */
    getIcon(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        
        return icons[type] || icons.info;
    }
    
    /**
     * Format time
     */
    formatTime(date) {
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) {
            return 'Just now';
        } else if (diff < 3600000) {
            return Math.floor(diff / 60000) + ' minutes ago';
        } else if (diff < 86400000) {
            return Math.floor(diff / 3600000) + ' hours ago';
        } else {
            return date.toLocaleTimeString();
        }
    }
    
    /**
     * Play notification sound
     */
    playSound(type) {
        if (!this.soundEnabled) return;
        
        try {
            const audio = new Audio(`/assets/sounds/notification-${type}.mp3`);
            audio.volume = 0.3;
            audio.play().catch(error => {
                console.log('Could not play notification sound:', error);
            });
        } catch (error) {
            console.log('Could not play notification sound:', error);
        }
    }
    
    /**
     * Trigger custom event
     */
    triggerEvent(eventName, data) {
        const event = new CustomEvent(eventName, {
            detail: data
        });
        document.dispatchEvent(event);
    }
    
    /**
     * Cleanup old notifications
     */
    cleanupNotifications() {
        const now = Date.now();
        this.notifications = this.notifications.filter(notification => {
            // Remove notifications older than 10 minutes if not persistent
            if (!notification.persistent && (now - notification.timestamp.getTime()) > 600000) {
                this.removeNotificationElement(notification.id);
                return false;
            }
            return true;
        });
    }
}

/**
 * Notification Manager for different types
 */
class NotificationManager {
    constructor() {
        this.system = new NotificationSystem();
        this.setupGlobalMethods();
    }
    
    /**
     * Setup global methods
     */
    setupGlobalMethods() {
        // Global notification methods
        window.showNotification = (message, type, options) => {
            return this.system.show(message, type, options);
        };
        
        window.showSuccess = (message, options) => {
            return this.system.success(message, options);
        };
        
        window.showError = (message, options) => {
            return this.system.error(message, options);
        };
        
        window.showWarning = (message, options) => {
            return this.system.warning(message, options);
        };
        
        window.showInfo = (message, options) => {
            return this.system.info(message, options);
        };
        
        window.clearNotifications = () => {
            return this.system.clear();
        };
    }
    
    /**
     * Show login notification
     */
    showLogin(user) {
        return this.system.success(
            `Welcome back, ${user.first_name}!`,
            {
                title: 'Login Successful',
                duration: 3000,
                icon: 'fas fa-sign-in-alt'
            }
        );
    }
    
    /**
     * Show logout notification
     */
    showLogout() {
        return this.system.info(
            'You have been logged out successfully',
            {
                title: 'Logout',
                duration: 3000,
                icon: 'fas fa-sign-out-alt'
            }
        );
    }
    
    /**
     * Show save notification
     */
    showSave(item, action = 'saved') {
        return this.system.success(
            `${item} ${action} successfully`,
            {
                title: 'Success',
                duration: 3000,
                icon: 'fas fa-save'
            }
        );
    }
    
    /**
     * Show delete notification
     */
    showDelete(item) {
        return this.system.warning(
            `${item} deleted successfully`,
            {
                title: 'Deleted',
                duration: 3000,
                icon: 'fas fa-trash'
            }
        );
    }
    
    /**
     * Show error notification
     */
    showError(operation, error) {
        return this.system.error(
            `Failed to ${operation}: ${error}`,
            {
                title: 'Error',
                duration: 5000,
                icon: 'fas fa-exclamation-triangle',
                persistent: true
            }
        );
    }
    
    /**
     * Show network error
     */
    showNetworkError() {
        return this.system.error(
            'Network error. Please check your connection.',
            {
                title: 'Network Error',
                duration: 5000,
                icon: 'fas fa-wifi',
                persistent: true,
                actions: [
                    {
                        label: 'Retry',
                        action: 'retry',
                        class: 'btn-primary'
                    }
                ]
            }
        );
    }
    
    /**
     * Show session timeout warning
     */
    showSessionTimeout() {
        return this.system.warning(
            'Your session will expire in 5 minutes. Please save your work.',
            {
                title: 'Session Timeout Warning',
                duration: 10000,
                icon: 'fas fa-clock',
                persistent: true,
                actions: [
                    {
                        label: 'Extend Session',
                        action: 'extend',
                        class: 'btn-primary'
                    },
                    {
                        label: 'Logout',
                        action: 'logout',
                        class: 'btn-outline-secondary'
                    }
                ]
            }
        );
    }
    
    /**
     * Show new message notification
     */
    showNewMessage(message, sender) {
        return this.system.info(
            message,
            {
                title: `New message from ${sender}`,
                duration: 5000,
                icon: 'fas fa-envelope',
                actions: [
                    {
                        label: 'View',
                        action: 'view',
                        class: 'btn-primary'
                    }
                ]
            }
        );
    }
    
    /**
     * Show attendance reminder
     */
    showAttendanceReminder(classInfo) {
        return this.system.info(
            `Don't forget to mark attendance for ${classInfo}`,
            {
                title: 'Attendance Reminder',
                duration: 8000,
                icon: 'fas fa-calendar-check',
                actions: [
                    {
                        label: 'Mark Attendance',
                        action: 'attendance',
                        class: 'btn-primary'
                    }
                ]
            }
        );
    }
    
    /**
     * Show exam notification
     */
    showExamNotification(examInfo) {
        return this.system.info(
            `Exam "${examInfo.title}" scheduled for ${examInfo.date}`,
            {
                title: 'Exam Schedule',
                duration: 6000,
                icon: 'fas fa-clipboard-list'
            }
        );
    }
    
    /**
     * Show result notification
     */
    showResultNotification(resultInfo) {
        const grade = resultInfo.grade;
        const type = grade === 'F' ? 'warning' : 'success';
        
        return this.system[type](
            `Your result for ${resultInfo.subject}: ${resultInfo.marks} (${grade})`,
            {
                title: 'Result Published',
                duration: 5000,
                icon: grade === 'F' ? 'fas fa-exclamation-triangle' : 'fas fa-chart-line',
                actions: [
                    {
                        label: 'View Details',
                        action: 'view',
                        class: 'btn-primary'
                    }
                ]
            }
        );
    }
}

// Initialize notification system
document.addEventListener('DOMContentLoaded', () => {
    window.notificationManager = new NotificationManager();
    window.notificationSystem = window.notificationManager.system;
});

// Export classes
window.NotificationSystem = NotificationSystem;
window.NotificationManager = NotificationManager;
