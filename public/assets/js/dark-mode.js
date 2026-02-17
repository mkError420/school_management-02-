/**
 * School Management System - Dark Mode JavaScript
 */

/**
 * Dark Mode Manager
 */
class DarkModeManager {
    constructor() {
        this.storageKey = 'sms-theme';
        this.darkModeClass = 'dark-mode';
        this.transitionDuration = 300;
        
        this.init();
    }
    
    /**
     * Initialize dark mode
     */
    init() {
        this.loadTheme();
        this.setupEventListeners();
        this.setupSystemPreference();
        this.setupMediaQuery();
    }
    
    /**
     * Load saved theme
     */
    loadTheme() {
        const savedTheme = localStorage.getItem(this.storageKey);
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        const theme = savedTheme || (systemPrefersDark ? 'dark' : 'light');
        
        if (theme === 'dark') {
            this.enableDarkMode();
        } else {
            this.disableDarkMode();
        }
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Dark mode toggle button
        const toggleButton = document.getElementById('dark-mode-toggle');
        if (toggleButton) {
            toggleButton.addEventListener('click', () => {
                this.toggle();
            });
        }
        
        // Keyboard shortcut (Ctrl/Cmd + Shift + D)
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                this.toggle();
            }
        });
        
        // Theme change events
        document.addEventListener('themeChanged', (e) => {
            this.onThemeChanged(e.detail.theme);
        });
    }
    
    /**
     * Setup system preference detection
     */
    setupSystemPreference() {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        mediaQuery.addEventListener('change', (e) => {
            // Only auto-switch if user hasn't manually set a preference
            if (!localStorage.getItem(this.storageKey)) {
                if (e.matches) {
                    this.enableDarkMode();
                } else {
                    this.disableDarkMode();
                }
            }
        });
    }
    
    /**
     * Setup media query for responsive behavior
     */
    setupMediaQuery() {
        // Add CSS custom properties for dynamic theming
        this.updateCSSVariables();
    }
    
    /**
     * Toggle dark mode
     */
    toggle() {
        const isDark = this.isDarkMode();
        
        if (isDark) {
            this.disableDarkMode();
            this.setTheme('light');
        } else {
            this.enableDarkMode();
            this.setTheme('dark');
        }
        
        // Dispatch theme change event
        this.dispatchThemeChangeEvent();
    }
    
    /**
     * Enable dark mode
     */
    enableDarkMode() {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.body.classList.add(this.darkModeClass);
        
        // Update toggle button icon
        this.updateToggleButton(true);
        
        // Update CSS variables
        this.updateCSSVariables();
        
        // Add transition class for smooth switching
        this.addTransitionClass();
    }
    
    /**
     * Disable dark mode
     */
    disableDarkMode() {
        document.documentElement.removeAttribute('data-theme');
        document.body.classList.remove(this.darkModeClass);
        
        // Update toggle button icon
        this.updateToggleButton(false);
        
        // Update CSS variables
        this.updateCSSVariables();
        
        // Add transition class for smooth switching
        this.addTransitionClass();
    }
    
    /**
     * Check if dark mode is active
     */
    isDarkMode() {
        return document.documentElement.getAttribute('data-theme') === 'dark';
    }
    
    /**
     * Set theme preference
     */
    setTheme(theme) {
        localStorage.setItem(this.storageKey, theme);
    }
    
    /**
     * Get current theme
     */
    getCurrentTheme() {
        return this.isDarkMode() ? 'dark' : 'light';
    }
    
    /**
     * Update toggle button icon
     */
    updateToggleButton(isDark) {
        const icon = document.getElementById('dark-mode-icon');
        if (icon) {
            if (isDark) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                icon.title = 'Switch to Light Mode';
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
                icon.title = 'Switch to Dark Mode';
            }
        }
    }
    
    /**
     * Update CSS custom properties
     */
    updateCSSVariables() {
        const root = document.documentElement;
        const isDark = this.isDarkMode();
        
        if (isDark) {
            root.style.setProperty('--bg-primary', '#1a1a1a');
            root.style.setProperty('--bg-secondary', '#2d2d2d');
            root.style.setProperty('--bg-tertiary', '#404040');
            root.style.setProperty('--text-primary', '#ffffff');
            root.style.setProperty('--text-secondary', '#b3b3b3');
            root.style.setProperty('--text-muted', '#808080');
            root.style.setProperty('--border-color', '#404040');
            root.style.setProperty('--card-bg', '#2d2d2d');
            root.style.setProperty('--sidebar-bg', '#1a1a1a');
            root.style.setProperty('--navbar-bg', '#2d2d2d');
        } else {
            root.style.setProperty('--bg-primary', '#ffffff');
            root.style.setProperty('--bg-secondary', '#f8f9fa');
            root.style.setProperty('--bg-tertiary', '#e9ecef');
            root.style.setProperty('--text-primary', '#212529');
            root.style.setProperty('--text-secondary', '#6c757d');
            root.style.setProperty('--text-muted', '#adb5bd');
            root.style.setProperty('--border-color', '#dee2e6');
            root.style.setProperty('--card-bg', '#ffffff');
            root.style.setProperty('--sidebar-bg', '#ffffff');
            root.style.setProperty('--navbar-bg', '#ffffff');
        }
    }
    
    /**
     * Add transition class for smooth theme switching
     */
    addTransitionClass() {
        document.body.classList.add('theme-transitioning');
        
        setTimeout(() => {
            document.body.classList.remove('theme-transitioning');
        }, this.transitionDuration);
    }
    
    /**
     * Dispatch theme change event
     */
    dispatchThemeChangeEvent() {
        const event = new CustomEvent('themeChanged', {
            detail: {
                theme: this.getCurrentTheme(),
                isDark: this.isDarkMode()
            }
        });
        
        document.dispatchEvent(event);
    }
    
    /**
     * Handle theme change event
     */
    onThemeChanged(themeData) {
        // Update any components that need to respond to theme changes
        this.updateCharts(themeData);
        this.updateMaps(themeData);
        this.updateThirdPartyComponents(themeData);
    }
    
    /**
     * Update charts for theme
     */
    updateCharts(themeData) {
        // Update Chart.js charts
        if (window.Chart) {
            Chart.defaults.color = themeData.isDark ? '#b3b3b3' : '#666';
            Chart.defaults.borderColor = themeData.isDark ? '#404040' : '#dee2e6';
            
            // Trigger chart redraw
            document.querySelectorAll('canvas[data-chart]').forEach(canvas => {
                const chart = Chart.getChart(canvas);
                if (chart) {
                    chart.update();
                }
            });
        }
    }
    
    /**
     * Update maps for theme
     */
    updateMaps(themeData) {
        // Update any map components
        if (window.L && window.L.map) {
            // Leaflet maps
            document.querySelectorAll('.leaflet-container').forEach(mapContainer => {
                // Update map theme
            });
        }
    }
    
    /**
     * Update third-party components
     */
    updateThirdPartyComponents(themeData) {
        // Update any third-party components that need theme awareness
        
        // Update Select2
        if (window.jQuery && window.jQuery.fn.select2) {
            window.jQuery('.select2-selection').css('background-color', 
                themeData.isDark ? 'var(--bg-tertiary)' : 'var(--bg-secondary)');
        }
        
        // Update DataTables
        if (window.jQuery && window.jQuery.fn.dataTable) {
            window.jQuery('.dataTables_wrapper').css('color', 
                themeData.isDark ? 'var(--text-primary)' : 'var(--text-secondary)');
        }
    }
    
    /**
     * Get system preference
     */
    getSystemPreference() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    
    /**
     * Reset to system preference
     */
    resetToSystemPreference() {
        localStorage.removeItem(this.storageKey);
        const systemPref = this.getSystemPreference();
        
        if (systemPref === 'dark') {
            this.enableDarkMode();
        } else {
            this.disableDarkMode();
        }
        
        this.dispatchThemeChangeEvent();
    }
    
    /**
     * Get theme statistics
     */
    getThemeStats() {
        return {
            current: this.getCurrentTheme(),
            system: this.getSystemPreference(),
            isAuto: !localStorage.getItem(this.storageKey),
            toggleCount: parseInt(localStorage.getItem('theme-toggle-count') || '0')
        };
    }
    
    /**
     * Increment toggle counter
     */
    incrementToggleCount() {
        const count = parseInt(localStorage.getItem('theme-toggle-count') || '0');
        localStorage.setItem('theme-toggle-count', (count + 1).toString());
    }
}

/**
 * Initialize dark mode when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    window.darkModeManager = new DarkModeManager();
});

/**
 * Global dark mode functions for backward compatibility
 */
window.toggleDarkMode = () => {
    if (window.darkModeManager) {
        window.darkModeManager.toggle();
        window.darkModeManager.incrementToggleCount();
    }
};

window.enableDarkMode = () => {
    if (window.darkModeManager) {
        window.darkModeManager.enableDarkMode();
        window.darkModeManager.setTheme('dark');
        window.darkModeManager.dispatchThemeChangeEvent();
    }
};

window.disableDarkMode = () => {
    if (window.darkModeManager) {
        window.darkModeManager.disableDarkMode();
        window.darkModeManager.setTheme('light');
        window.darkModeManager.dispatchThemeChangeEvent();
    }
};

window.isDarkMode = () => {
    return window.darkModeManager ? window.darkModeManager.isDarkMode() : false;
};

window.getCurrentTheme = () => {
    return window.darkModeManager ? window.darkModeManager.getCurrentTheme() : 'light';
};

/**
 * CSS for theme transitions
 */
const themeTransitionCSS = `
.theme-transitioning *,
.theme-transitioning *::before,
.theme-transitioning *::after {
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease !important;
}
`;

// Inject transition CSS
const style = document.createElement('style');
style.textContent = themeTransitionCSS;
document.head.appendChild(style);
