/**
 * BitsMesh Theme - Dark Mode Toggle
 * Handles dark/light mode switching with localStorage persistence
 */

class DarkModeToggle {
    constructor() {
        this.storageKey = 'bits-theme-mode';
        this.darkClass = 'dark-layout';
        this.toggleSelector = '.bits-dark-toggle';
        this._boundHandler = null;
    }

    /**
     * Initialize dark mode based on saved preference
     */
    init() {
        const saved = localStorage.getItem(this.storageKey);
        if (saved === 'dark') {
            document.body.classList.add(this.darkClass);
        }
        this.updateTitle(saved === 'dark');
        this.bindToggleButton();
    }

    /**
     * Bind click event using event delegation for robustness
     */
    bindToggleButton() {
        // Use event delegation on document to handle dynamically added elements
        this._boundHandler = (e) => {
            const toggleBtn = e.target.closest(this.toggleSelector);
            if (toggleBtn) {
                e.preventDefault();
                this.toggle();
            }
        };
        document.addEventListener('click', this._boundHandler);
    }

    /**
     * Toggle between dark and light mode
     */
    toggle() {
        const isDark = document.body.classList.toggle(this.darkClass);
        localStorage.setItem(this.storageKey, isDark ? 'dark' : 'light');
        this.updateTitle(isDark);
    }

    /**
     * Update the toggle button title/tooltip
     * SVG icon visibility is handled by CSS based on body.dark-layout class
     * @param {boolean} isDark - Current dark mode state
     */
    updateTitle(isDark) {
        const toggleBtn = document.querySelector(this.toggleSelector);
        if (toggleBtn) {
            toggleBtn.title = isDark ? '切换到亮色模式' : '切换到深色模式';
            toggleBtn.setAttribute('aria-label', toggleBtn.title);
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
        this.updateTitle(mode === 'dark');
    }
}

// Note: Initialization is handled by theme.js to avoid duplicate event bindings
