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
     * Initialize all theme components
     */
    function initTheme() {
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
