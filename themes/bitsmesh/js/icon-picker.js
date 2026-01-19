/**
 * BitsMesh Icon Picker
 *
 * Provides a visual icon picker for category icon selection.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

(function() {
    'use strict';

    /**
     * Icon names mapping for display.
     */
    var iconNames = {
        'all-application': 'All Applications (Default)',
        'tea': 'Tea (Daily)',
        'formula': 'Formula (Tech)',
        'receiver': 'Receiver (Info)',
        'dashboard-one': 'Dashboard (Review)',
        'dollar': 'Dollar (Trade)',
        'car': 'Car (Carpool)',
        'hold-interface': 'Hold Interface (Promo)',
        'oval-love-two': 'Love (Life)',
        'terminal': 'Terminal (Dev)',
        'pic-one': 'Picture (Pics)',
        'face-recognition': 'Face (Expose)',
        'open-one': 'Open (Internal)',
        'experiment': 'Experiment (Sandbox)'
    };

    /**
     * Initialize the icon picker using event delegation.
     * This ensures handlers work even for dynamically injected HTML.
     */
    function initIconPicker() {
        // Use event delegation on document body for toggle button
        document.body.addEventListener('click', function(e) {
            // Handle toggle button click
            var toggleBtn = e.target.closest('#IconPickerToggle');
            if (toggleBtn) {
                e.preventDefault();
                e.stopPropagation();
                var grid = document.getElementById('IconGrid');
                if (grid) {
                    if (grid.style.display === 'none' || grid.style.display === '') {
                        grid.style.display = 'flex';
                        toggleBtn.textContent = 'Close';
                    } else {
                        grid.style.display = 'none';
                        toggleBtn.textContent = 'Change';
                    }
                }
                return;
            }

            // Handle icon option click
            var iconOption = e.target.closest('.bits-icon-option');
            if (iconOption) {
                e.preventDefault();
                e.stopPropagation();
                var iconId = iconOption.getAttribute('data-icon');
                var input = document.getElementById('Form_IconID');
                var preview = document.getElementById('IconPreview');
                var nameSpan = document.getElementById('IconName');
                var grid = document.getElementById('IconGrid');
                var toggleBtn = document.getElementById('IconPickerToggle');

                // Update hidden input
                if (input) {
                    input.value = iconId;
                }

                // Update preview
                if (preview) {
                    var useEl = preview.querySelector('use');
                    if (useEl) {
                        useEl.setAttribute('href', '#' + iconId);
                    }
                }

                // Update name
                if (nameSpan) {
                    nameSpan.textContent = iconNames[iconId] || iconId;
                }

                // Update selected state
                var allOptions = document.querySelectorAll('.bits-icon-option');
                allOptions.forEach(function(opt) {
                    opt.classList.remove('selected');
                });
                iconOption.classList.add('selected');

                // Hide grid
                if (grid) {
                    grid.style.display = 'none';
                }
                if (toggleBtn) {
                    toggleBtn.textContent = 'Change';
                }
                return;
            }

            // Close grid when clicking outside
            var picker = document.querySelector('.bits-icon-picker');
            var grid = document.getElementById('IconGrid');
            var toggleBtn = document.getElementById('IconPickerToggle');
            if (picker && grid && !picker.contains(e.target)) {
                grid.style.display = 'none';
                if (toggleBtn) {
                    toggleBtn.textContent = 'Change';
                }
            }
        });
    }

    // Initialize when DOM is ready
    function init() {
        if (document.body) {
            initIconPicker();
        } else {
            document.addEventListener('DOMContentLoaded', initIconPicker);
        }
    }

    init();
})();
