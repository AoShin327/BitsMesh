/**
 * BitsMesh Theme Color Settings
 *
 * Handles color picker and preset scheme interactions.
 *
 * @copyright 2026 BitsMesh
 * @license GPL-2.0-only
 */

(function() {
    'use strict';

    /**
     * Color field mappings
     */
    var colorFields = {
        primary: {
            picker: 'ColorPicker_Primary',
            input: 'Form_Themes_BitsMesh_PrimaryColor'
        },
        secondary: {
            picker: 'ColorPicker_Secondary',
            input: 'Form_Themes_BitsMesh_SecondaryColor'
        },
        darkPrimary: {
            picker: 'ColorPicker_DarkPrimary',
            input: 'Form_Themes_BitsMesh_DarkPrimaryColor'
        },
        darkSecondary: {
            picker: 'ColorPicker_DarkSecondary',
            input: 'Form_Themes_BitsMesh_DarkSecondaryColor'
        },
        grid: {
            picker: 'ColorPicker_Grid',
            input: 'Form_Themes_BitsMesh_GridColor'
        },
        darkGrid: {
            picker: 'ColorPicker_DarkGrid',
            input: 'Form_Themes_BitsMesh_DarkGridColor'
        },
        gridBg: {
            picker: 'ColorPicker_GridBg',
            input: 'Form_Themes_BitsMesh_GridBgColor'
        },
        darkGridBg: {
            picker: 'ColorPicker_DarkGridBg',
            input: 'Form_Themes_BitsMesh_DarkGridBgColor'
        }
    };

    /**
     * Initialize color settings functionality
     */
    function initColorSettings() {
        // Bind color pickers to text inputs
        bindColorPickers();

        // Bind preset cards
        bindPresetCards();

        // Bind reset defaults button
        bindResetDefaults();

        // Bind grid enabled toggle
        bindGridToggle();

        // Set initial active preset
        updateActivePreset();
    }

    /**
     * Sync color picker with text input
     */
    function bindColorPickers() {
        Object.keys(colorFields).forEach(function(key) {
            var config = colorFields[key];
            var picker = document.getElementById(config.picker);
            var input = document.getElementById(config.input);

            if (!picker || !input) {
                return;
            }

            // Picker -> Input
            picker.addEventListener('input', function() {
                input.value = picker.value.toUpperCase();
                updateActivePreset();
            });

            // Input -> Picker
            input.addEventListener('input', function() {
                var value = input.value.trim();
                if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                    picker.value = value;
                }
                updateActivePreset();
            });

            // Initial sync
            if (input.value && /^#[0-9A-Fa-f]{6}$/.test(input.value)) {
                picker.value = input.value;
            }
        });
    }

    /**
     * Bind preset card click events
     */
    function bindPresetCards() {
        var grid = document.getElementById('ColorPresetGrid');
        if (!grid) {
            return;
        }

        grid.addEventListener('click', function(e) {
            var card = e.target.closest('.bits-preset-card');
            if (!card) {
                return;
            }

            e.preventDefault();

            // Get preset colors from data attributes
            var primary = card.dataset.primary;
            var secondary = card.dataset.secondary;
            var darkPrimary = card.dataset.darkPrimary;
            var darkSecondary = card.dataset.darkSecondary;

            // Apply colors to fields
            setColorField('primary', primary);
            setColorField('secondary', secondary);
            setColorField('darkPrimary', darkPrimary);
            setColorField('darkSecondary', darkSecondary);

            // Update active state
            var cards = grid.querySelectorAll('.bits-preset-card');
            cards.forEach(function(c) {
                c.classList.remove('active');
            });
            card.classList.add('active');
        });
    }

    /**
     * Set a color field value
     */
    function setColorField(key, value) {
        var config = colorFields[key];
        if (!config) {
            return;
        }

        var picker = document.getElementById(config.picker);
        var input = document.getElementById(config.input);

        if (picker) {
            picker.value = value;
        }
        if (input) {
            input.value = value.toUpperCase();
        }
    }

    /**
     * Get a color field value
     */
    function getColorField(key) {
        var config = colorFields[key];
        if (!config) {
            return '';
        }

        var input = document.getElementById(config.input);
        return input ? input.value : '';
    }

    /**
     * Update active preset based on current values
     */
    function updateActivePreset() {
        var grid = document.getElementById('ColorPresetGrid');
        if (!grid) {
            return;
        }

        var currentPrimary = getColorField('primary').toUpperCase();
        var currentSecondary = getColorField('secondary').toUpperCase();

        var cards = grid.querySelectorAll('.bits-preset-card');
        var foundMatch = false;

        cards.forEach(function(card) {
            var presetPrimary = card.dataset.primary.toUpperCase();
            var presetSecondary = card.dataset.secondary.toUpperCase();

            if (presetPrimary === currentPrimary && presetSecondary === currentSecondary) {
                card.classList.add('active');
                foundMatch = true;
            } else {
                card.classList.remove('active');
            }
        });
    }

    /**
     * Bind reset defaults button
     */
    function bindResetDefaults() {
        var btn = document.getElementById('ResetDefaultsBtn');
        if (!btn) {
            return;
        }

        btn.addEventListener('click', function(e) {
            e.preventDefault();

            if (!window.bitsDefaultColors) {
                return;
            }

            var defaults = window.bitsDefaultColors;

            // Apply default colors
            setColorField('primary', defaults.primaryColor);
            setColorField('secondary', defaults.secondaryColor);
            setColorField('darkPrimary', defaults.darkPrimaryColor);
            setColorField('darkSecondary', defaults.darkSecondaryColor);
            setColorField('grid', defaults.gridColor);
            setColorField('darkGrid', defaults.darkGridColor);
            setColorField('gridBg', defaults.gridBgColor);
            setColorField('darkGridBg', defaults.darkGridBgColor);

            // Reset grid enabled checkbox
            var gridCheckbox = document.getElementById('Form_Themes_BitsMesh_GridEnabled');
            if (gridCheckbox) {
                gridCheckbox.checked = defaults.gridEnabled;
                toggleGridColorFields(defaults.gridEnabled);
            }

            // Update active preset
            updateActivePreset();
        });
    }

    /**
     * Bind grid enabled toggle
     */
    function bindGridToggle() {
        var checkbox = document.getElementById('Form_Themes_BitsMesh_GridEnabled');
        if (!checkbox) {
            return;
        }

        checkbox.addEventListener('change', function() {
            toggleGridColorFields(checkbox.checked);
        });

        // Initial state
        toggleGridColorFields(checkbox.checked);
    }

    /**
     * Toggle grid color fields visibility
     */
    function toggleGridColorFields(enabled) {
        var gridGroup = document.getElementById('GridColorGroup');
        var darkGridGroup = document.getElementById('DarkGridColorGroup');
        var gridBgGroup = document.getElementById('GridBgColorGroup');
        var darkGridBgGroup = document.getElementById('DarkGridBgColorGroup');

        if (gridGroup) {
            gridGroup.style.display = enabled ? '' : 'none';
        }
        if (darkGridGroup) {
            darkGridGroup.style.display = enabled ? '' : 'none';
        }
        if (gridBgGroup) {
            gridBgGroup.style.display = enabled ? '' : 'none';
        }
        if (darkGridBgGroup) {
            darkGridBgGroup.style.display = enabled ? '' : 'none';
        }
    }

    /**
     * Initialize when DOM is ready
     */
    function init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initColorSettings);
        } else {
            initColorSettings();
        }
    }

    init();
})();
