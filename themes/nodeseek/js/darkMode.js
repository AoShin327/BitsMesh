/**
 * BitsMesh Theme - Dark Mode Toggle
 * Handles dark/light mode switching with localStorage persistence
 */

class DarkModeToggle {
    constructor() {
        this.storageKey = 'bits-theme-mode';
        this.darkClass = 'dark-layout';
        this.toggleSelector = '.bits-dark-toggle';
    }

    /**
     * Initialize dark mode based on saved preference
     */
    init() {
        const saved = localStorage.getItem(this.storageKey);
        if (saved === 'dark') {
            document.body.classList.add(this.darkClass);
            this.updateIcon(true);
        }
        this.bindToggleButton();
    }

    /**
     * Bind click event to toggle button
     */
    bindToggleButton() {
        const toggleBtn = document.querySelector(this.toggleSelector);
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggle());
        }
    }

    /**
     * Toggle between dark and light mode
     */
    toggle() {
        const isDark = document.body.classList.toggle(this.darkClass);
        localStorage.setItem(this.storageKey, isDark ? 'dark' : 'light');
        this.updateIcon(isDark);
    }

    /**
     * Update the toggle button icon
     * @param {boolean} isDark - Current dark mode state
     */
    updateIcon(isDark) {
        const toggleBtn = document.querySelector(this.toggleSelector);
        if (toggleBtn) {
            toggleBtn.textContent = isDark ? '☀️' : '🌙';
            toggleBtn.title = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
        }
    }

    /**
     * Get current mode
     * @returns {string} 'dark' or 'light'
     */
    getMode() {
        return document.body.classList.contains(this.darkClass) ? 'dark' : 'light';
    }

    /**
     * Set mode programmatically
     * @param {string} mode - 'dark' or 'light'
     */
    setMode(mode) {
        if (mode === 'dark') {
            document.body.classList.add(this.darkClass);
        } else {
            document.body.classList.remove(this.darkClass);
        }
        localStorage.setItem(this.storageKey, mode);
        this.updateIcon(mode === 'dark');
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.bitsTheme = window.bitsTheme || {};
    window.bitsTheme.darkMode = new DarkModeToggle();
    window.bitsTheme.darkMode.init();
});
