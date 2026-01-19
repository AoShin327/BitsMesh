/**
 * BitsMesh Theme - Main Script
 * Initializes all theme components
 */
(function() {
    'use strict';

    window.bitsTheme = window.bitsTheme || {};

    /**
     * ImageBox - Lightbox for images
     */
    class ImageBox {
        constructor() {
            this.overlay = null;
            this.img = null;
            this.isOpen = false;
            // Store handler references for cleanup
            this.handleImageClick = null;
            this.handleKeydown = null;
        }

        init() {
            this.createOverlay();
            this.bindEvents();
        }

        createOverlay() {
            this.overlay = document.createElement('div');
            this.overlay.className = 'bits-image-box';
            this.overlay.innerHTML = `
                <div class="bits-image-box-content">
                    <img src="" alt="">
                    <button class="bits-image-box-close" aria-label="Close">&times;</button>
                </div>
            `;
            document.body.appendChild(this.overlay);
            this.img = this.overlay.querySelector('img');
        }

        bindEvents() {
            // Click on post images to open lightbox
            this.handleImageClick = (e) => {
                const img = e.target.closest('.bits-post-content img, .bits-comment-body img');
                if (img && !img.closest('a')) {
                    e.preventDefault();
                    this.open(img.src);
                }
            };
            document.addEventListener('click', this.handleImageClick);

            // Close on overlay click
            this.overlay.addEventListener('click', (e) => {
                if (e.target === this.overlay || e.target.classList.contains('bits-image-box-close')) {
                    this.close();
                }
            });

            // Close on Escape key
            this.handleKeydown = (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            };
            document.addEventListener('keydown', this.handleKeydown);
        }

        open(src) {
            // Preload image with error handling
            const tempImg = new Image();
            tempImg.onload = () => {
                this.img.src = src;
                this.overlay.classList.add('active');
                this.isOpen = true;
                document.body.style.overflow = 'hidden';
            };
            tempImg.onerror = () => {
                console.error('Failed to load image:', src);
            };
            tempImg.src = src;
        }

        close() {
            this.overlay.classList.remove('active');
            this.isOpen = false;
            document.body.style.overflow = '';

            // Clear image source after transition to free memory
            setTimeout(() => {
                if (!this.isOpen) {
                    this.img.src = '';
                }
            }, 300); // Match CSS transition duration
        }

        destroy() {
            // Remove event listeners to prevent memory leaks
            if (this.handleImageClick) {
                document.removeEventListener('click', this.handleImageClick);
            }
            if (this.handleKeydown) {
                document.removeEventListener('keydown', this.handleKeydown);
            }
            if (this.overlay) {
                this.overlay.remove();
            }
        }
    }

    /**
     * BackToTop - Scroll to top button
     */
    class BackToTop {
        constructor() {
            this.button = null;
            this.threshold = 300;
            this.scrollTimeout = null;
        }

        init() {
            this.createButton();
            this.bindEvents();
        }

        createButton() {
            this.button = document.createElement('button');
            this.button.className = 'bits-back-to-top';
            this.button.innerHTML = '↑';
            this.button.setAttribute('aria-label', 'Back to top');
            document.body.appendChild(this.button);
        }

        bindEvents() {
            // Show/hide on scroll with throttle
            window.addEventListener('scroll', () => {
                if (this.scrollTimeout) {
                    return;
                }
                this.scrollTimeout = setTimeout(() => {
                    this.toggleVisibility();
                    this.scrollTimeout = null;
                }, 100); // Throttle to max 10 calls per second
            }, { passive: true });

            // Scroll to top on click
            this.button.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        toggleVisibility() {
            if (window.scrollY > this.threshold) {
                this.button.classList.add('visible');
            } else {
                this.button.classList.remove('visible');
            }
        }
    }

    /**
     * MobileNav - Hamburger menu for mobile
     */
    class MobileNav {
        constructor() {
            this.hamburger = null;
            this.nav = null;
            this.isOpen = false;
            // Store handler references for cleanup
            this.handleOutsideClick = null;
            this.handleKeydown = null;
        }

        init() {
            this.hamburger = document.querySelector('.bits-hamburger');
            this.nav = document.querySelector('.bits-mobile-nav');

            if (!this.hamburger || !this.nav) {
                return;
            }

            this.bindEvents();
        }

        bindEvents() {
            this.hamburger.addEventListener('click', () => {
                this.toggle();
            });

            // Close on outside click
            this.handleOutsideClick = (e) => {
                if (this.isOpen && !e.target.closest('.bits-hamburger, .bits-mobile-nav')) {
                    this.close();
                }
            };
            document.addEventListener('click', this.handleOutsideClick);

            // Close on Escape
            this.handleKeydown = (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            };
            document.addEventListener('keydown', this.handleKeydown);
        }

        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        }

        open() {
            this.hamburger.classList.add('active');
            this.nav.classList.add('active');
            this.isOpen = true;
        }

        close() {
            this.hamburger.classList.remove('active');
            this.nav.classList.remove('active');
            this.isOpen = false;
        }

        destroy() {
            // Remove event listeners to prevent memory leaks
            if (this.handleOutsideClick) {
                document.removeEventListener('click', this.handleOutsideClick);
            }
            if (this.handleKeydown) {
                document.removeEventListener('keydown', this.handleKeydown);
            }
        }
    }

    /**
     * MetaIcons - Lightweight Meta Discussion enhancement
     *
     * v1.4.6: Performance optimization
     * - Removed MutationObserver (high overhead watching entire body)
     * - Removed SVG icon injection (now handled by CSS ::before + mask-image)
     * - Removed TreeWalker text traversal (CSS handles text hiding)
     *
     * Icons are now rendered via CSS in bits-meta-discussion.css using:
     * - CSS ::before pseudo-elements
     * - mask-image with inline SVG data URIs
     * - font-size: 0 technique to hide prefix text
     *
     * This class is kept for backward compatibility and future enhancements.
     */
    class MetaIcons {
        constructor() {
            // No longer needed - CSS handles icons via ::before pseudo-elements
        }

        init() {
            // Mark all Meta containers as CSS-enhanced for debugging
            this.markContainers();
        }

        /**
         * Mark containers for debugging purposes
         * No DOM manipulation - just adds data attribute
         */
        markContainers() {
            const metaContainers = document.querySelectorAll('.Meta.Meta-Discussion');
            metaContainers.forEach(meta => {
                if (!meta.dataset.bitsEnhanced) {
                    meta.dataset.bitsEnhanced = 'css';
                }
            });
        }
    }

    /**
     * SorterPreference - Remember user's sort preference in localStorage
     *
     * Handles:
     * - Storing user's sort choice (replyTime/postTime) to localStorage
     * - Auto-applying saved preference on page load when no URL parameter exists
     * - Default sort is 'replyTime' (新评论 - new comments)
     */
    class SorterPreference {
        constructor() {
            this.storageKey = 'bits_sort_preference';
            this.defaultSort = 'replyTime'; // Default: new comments
            this.sorterContainer = null;
        }

        init() {
            this.sorterContainer = document.querySelector('.bits-sorter');
            if (!this.sorterContainer) {
                return; // Not on a page with sorter
            }

            // Apply preference first (may redirect), then bind events
            this.applyPreference();
            this.bindEvents();
        }

        /**
         * Get saved sort preference from localStorage
         * @returns {string} Sort value ('replyTime' or 'postTime')
         */
        getPreference() {
            try {
                const saved = localStorage.getItem(this.storageKey);
                // Validate saved value
                if (saved === 'replyTime' || saved === 'postTime') {
                    return saved;
                }
                return this.defaultSort;
            } catch (e) {
                // localStorage unavailable (private browsing, etc.)
                return this.defaultSort;
            }
        }

        /**
         * Save sort preference to localStorage
         * @param {string} sort - Sort value ('replyTime' or 'postTime')
         */
        setPreference(sort) {
            // Validate before saving
            if (sort !== 'replyTime' && sort !== 'postTime') {
                return;
            }
            try {
                localStorage.setItem(this.storageKey, sort);
            } catch (e) {
                // localStorage unavailable - fail silently
            }
        }

        /**
         * Get sortBy parameter from current URL
         * @returns {string} Sort value or empty string if not set
         */
        getCurrentUrlSort() {
            try {
                const url = new URL(window.location.href);
                return url.searchParams.get('sortBy') || '';
            } catch (e) {
                return '';
            }
        }

        /**
         * Apply saved preference on page load
         * - If URL has sortBy: save it and use
         * - If URL has no sortBy: check localStorage and redirect if needed
         */
        applyPreference() {
            const urlSort = this.getCurrentUrlSort();

            if (urlSort) {
                // URL has explicit sort - save as preference
                this.setPreference(urlSort);
                return;
            }

            // No URL sort - check localStorage
            const savedSort = this.getPreference();

            // Only redirect if user has non-default preference
            // This prevents redirect loop and respects default behavior
            if (savedSort && savedSort !== this.defaultSort) {
                this.redirectWithSort(savedSort);
            }
        }

        /**
         * Redirect to current page with sort parameter
         * Uses replace() to avoid polluting browser history
         * @param {string} sort - Sort value to apply
         */
        redirectWithSort(sort) {
            try {
                const url = new URL(window.location.href);
                url.searchParams.set('sortBy', sort);
                // Use replace to avoid back button issues
                window.location.replace(url.toString());
            } catch (e) {
                // URL parsing failed - skip redirect
            }
        }

        /**
         * Bind click events on sorter buttons
         * Saves preference when user clicks
         */
        bindEvents() {
            this.sorterContainer.addEventListener('click', (e) => {
                const link = e.target.closest('a[data-sort]');
                if (!link) {
                    return;
                }

                // Map UI sort value to URL parameter
                const uiSort = link.dataset.sort;
                const sortValue = uiSort === 'comments' ? 'replyTime' : 'postTime';

                // Save preference - the link will handle navigation
                this.setPreference(sortValue);
            });
        }
    }

    /**
     * Initialize all theme components
     */
    function initTheme() {
        // Initialize SorterPreference first (may redirect page)
        bitsTheme.sorterPreference = new SorterPreference();
        bitsTheme.sorterPreference.init();

        // Initialize ImageBox
        bitsTheme.imageBox = new ImageBox();
        bitsTheme.imageBox.init();

        // Initialize BackToTop
        bitsTheme.backToTop = new BackToTop();
        bitsTheme.backToTop.init();

        // Initialize MobileNav
        bitsTheme.mobileNav = new MobileNav();
        bitsTheme.mobileNav.init();

        // Initialize MetaIcons (SVG icons for post info)
        bitsTheme.metaIcons = new MetaIcons();
        bitsTheme.metaIcons.init();

        // Initialize DarkMode if available
        if (typeof DarkModeToggle !== 'undefined') {
            bitsTheme.darkMode = new DarkModeToggle();
            bitsTheme.darkMode.init();
        }

        console.log('BitsMesh theme initialized');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }

})();
