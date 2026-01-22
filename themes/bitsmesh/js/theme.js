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
     * ReplyButton - Handle reply button clicks
     * Inserts @username #floor format into comment form
     */
    class ReplyButton {
        constructor() {
            this.handleClick = null;
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            this.handleClick = (e) => {
                const replyBtn = e.target.closest('.bits-reply-btn');
                if (!replyBtn) return;

                e.preventDefault();

                const author = replyBtn.dataset.author;
                const floor = replyBtn.dataset.floor;
                const floorUrl = replyBtn.dataset.floorUrl;

                if (!author) return;

                // Find the comment form textarea
                const textarea = document.querySelector('#Form_Body, .BodyBox textarea, textarea.TextBox');
                if (!textarea) {
                    // Fallback: just scroll to comment form
                    const commentForm = document.getElementById('CommentForm');
                    if (commentForm) {
                        commentForm.scrollIntoView({ behavior: 'smooth' });
                    }
                    return;
                }

                // Build reply text: @username #floor
                let replyText = `@${author} [#${floor}](${floorUrl}) `;

                // Insert at cursor position or append
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const currentValue = textarea.value;

                // If there's already content, add newline before reply text
                if (currentValue.trim() && start === currentValue.length) {
                    replyText = '\n' + replyText;
                }

                textarea.value = currentValue.substring(0, start) + replyText + currentValue.substring(end);

                // Move cursor to end of inserted text
                const newPos = start + replyText.length;
                textarea.setSelectionRange(newPos, newPos);

                // Focus and scroll to textarea
                textarea.focus();
                const commentForm = document.getElementById('CommentForm');
                if (commentForm) {
                    commentForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                // Trigger resize event for auto-resize textareas
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
            };

            document.addEventListener('click', this.handleClick);
        }

        destroy() {
            if (this.handleClick) {
                document.removeEventListener('click', this.handleClick);
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
     * BookmarkButton - Handle bookmark button UI updates
     *
     * Vanilla's Hijack class handles AJAX requests, this class updates
     * the UI (icon and class) after bookmark state changes.
     */
    class BookmarkButton {
        constructor() {
            this.handleClick = null;
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            // Listen for clicks on bookmark buttons with Hijack class
            this.handleClick = (e) => {
                const bookmarkBtn = e.target.closest('.menu-bookmark.Hijack, a.Hijack[href*="/bookmark/"]');
                if (!bookmarkBtn) return;

                // Store reference to button for AJAX callback
                const discussionID = bookmarkBtn.dataset.discussionId;
                if (!discussionID) return;

                // Use jQuery ajaxComplete to detect when Vanilla's Hijack finishes
                // This is more reliable than trying to intercept the click
                this.setupAjaxCallback(bookmarkBtn, discussionID);
            };

            document.addEventListener('click', this.handleClick);

            // Also listen for Vanilla's AJAX complete events
            this.setupGlobalAjaxHandler();
        }

        /**
         * Setup callback to handle UI update after AJAX completes
         */
        setupAjaxCallback(button, discussionID) {
            // Mark button as pending
            button.classList.add('bits-loading');

            // Store the button reference for the AJAX handler
            if (!window._bitsBookmarkPending) {
                window._bitsBookmarkPending = {};
            }
            window._bitsBookmarkPending[discussionID] = button;
        }

        /**
         * Setup global AJAX handler to catch bookmark responses
         */
        setupGlobalAjaxHandler() {
            // Check if jQuery is available (Vanilla uses jQuery)
            if (typeof jQuery !== 'undefined') {
                jQuery(document).ajaxComplete((event, xhr, settings) => {
                    // Check if this is a bookmark request
                    if (settings.url && settings.url.includes('/bookmark/')) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response && typeof response.Bookmarked !== 'undefined') {
                                this.handleBookmarkResponse(response);
                            }
                        } catch (e) {
                            // Not JSON or parsing error
                        }
                    }
                });
            }

            // Also listen for Vanilla's custom events
            jQuery(document).on('ajaxComplete', '.Hijack', (e) => {
                // Remove loading state from any pending buttons
                document.querySelectorAll('.menu-bookmark.bits-loading').forEach(btn => {
                    btn.classList.remove('bits-loading');
                });
            });
        }

        /**
         * Handle bookmark response and update UI
         */
        handleBookmarkResponse(response) {
            const discussionID = response.DiscussionID;
            const isBookmarked = response.Bookmarked;

            // Find the button by discussion ID
            const button = document.querySelector(`.menu-bookmark[data-discussion-id="${discussionID}"]`);
            if (!button) return;

            // Update button state
            button.classList.remove('bits-loading');

            if (isBookmarked) {
                button.classList.add('bookmarked');
                button.title = '取消收藏';
                // Update icon to filled star
                const svgUse = button.querySelector('use');
                if (svgUse) {
                    svgUse.setAttribute('href', '#star-one');
                }
            } else {
                button.classList.remove('bookmarked');
                button.title = '收藏';
                // Update icon to outline star
                const svgUse = button.querySelector('use');
                if (svgUse) {
                    svgUse.setAttribute('href', '#star');
                }
            }

            // Update bookmark count display
            const countSpan = button.querySelector('.bookmark-count');
            if (countSpan) {
                // Count changed - we need to refresh or estimate
                // For now, just toggle the visual state
            }

            // Update URL with new transient key if provided
            if (response.TransientKey) {
                const href = button.getAttribute('href');
                if (href) {
                    const newHref = href.replace(/\/[^\/]+$/, '/' + response.TransientKey);
                    button.setAttribute('href', newHref);
                }
            }

            // Clean up pending reference
            if (window._bitsBookmarkPending && window._bitsBookmarkPending[discussionID]) {
                delete window._bitsBookmarkPending[discussionID];
            }
        }

        destroy() {
            if (this.handleClick) {
                document.removeEventListener('click', this.handleClick);
            }
        }
    }

    /**
     * FollowButton - Handle follow/unfollow button interactions
     * Uses AJAX to toggle follow state without page reload.
     */
    class FollowButton {
        constructor() {
            this.handleClick = null;
            this.pendingRequests = new Set(); // Prevent duplicate requests
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            this.handleClick = (e) => {
                const followBtn = e.target.closest('.bits-follow-btn');
                if (!followBtn) return;

                e.preventDefault();
                e.stopPropagation();

                this.toggleFollow(followBtn);
            };

            document.addEventListener('click', this.handleClick);
        }

        /**
         * Toggle follow state for a user
         * @param {HTMLElement} button - The follow button element
         */
        async toggleFollow(button) {
            const userID = button.dataset.userid;
            if (!userID) {
                console.error('FollowButton: Missing userid');
                return;
            }

            // Prevent duplicate requests
            if (this.pendingRequests.has(userID)) {
                return;
            }

            // Get TransientKey for CSRF protection
            const transientKey = this.getTransientKey();
            if (!transientKey) {
                console.error('FollowButton: TransientKey not found');
                return;
            }

            // Mark as pending
            this.pendingRequests.add(userID);
            button.classList.add('bits-loading');
            button.disabled = true;

            try {
                const response = await fetch('/profile/togglefollow.json', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `UserID=${encodeURIComponent(userID)}&TransientKey=${encodeURIComponent(transientKey)}`
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.Success) {
                    this.updateButtonState(button, data.IsFollowing);
                    // Also update any other follow buttons for the same user on the page
                    this.updateAllButtonsForUser(userID, data.IsFollowing, button);
                } else {
                    console.error('FollowButton: Server error', data.Message || 'Unknown error');
                    this.showError(button, data.Message || '操作失败');
                }
            } catch (error) {
                console.error('FollowButton: Request failed', error);
                this.showError(button, '网络错误，请重试');
            } finally {
                // Remove pending state
                this.pendingRequests.delete(userID);
                button.classList.remove('bits-loading');
                button.disabled = false;
            }
        }

        /**
         * Update button visual state
         * @param {HTMLElement} button - The button element
         * @param {boolean} isFollowing - New follow state
         */
        updateButtonState(button, isFollowing) {
            button.dataset.following = isFollowing ? '1' : '0';

            if (isFollowing) {
                button.classList.remove('bits-btn-follow');
                button.classList.add('bits-btn-following');
                button.textContent = '已关注';
            } else {
                button.classList.remove('bits-btn-following');
                button.classList.add('bits-btn-follow');
                button.textContent = '关注';
            }
        }

        /**
         * Update all follow buttons for the same user on the page
         * @param {string} userID - User ID
         * @param {boolean} isFollowing - New follow state
         * @param {HTMLElement} excludeButton - Button to exclude (already updated)
         */
        updateAllButtonsForUser(userID, isFollowing, excludeButton) {
            const buttons = document.querySelectorAll(`.bits-follow-btn[data-userid="${userID}"]`);
            buttons.forEach(btn => {
                if (btn !== excludeButton) {
                    this.updateButtonState(btn, isFollowing);
                }
            });
        }

        /**
         * Show error message temporarily
         * @param {HTMLElement} button - The button element
         * @param {string} message - Error message
         */
        showError(button, message) {
            const originalText = button.textContent;
            button.textContent = message;
            button.classList.add('bits-btn-error');

            setTimeout(() => {
                // Restore original state
                const isFollowing = button.dataset.following === '1';
                this.updateButtonState(button, isFollowing);
                button.classList.remove('bits-btn-error');
            }, 2000);
        }

        /**
         * Get CSRF TransientKey from page
         * @returns {string|null}
         */
        getTransientKey() {
            // Try multiple sources
            // 1. gdn.definition (Vanilla's JS config)
            if (typeof gdn !== 'undefined' && gdn.definition) {
                const tk = gdn.definition('TransientKey');
                if (tk) return tk;
            }

            // 2. Hidden input field
            const tkInput = document.querySelector('input[name="TransientKey"]');
            if (tkInput) return tkInput.value;

            // 3. Meta tag
            const tkMeta = document.querySelector('meta[name="TransientKey"]');
            if (tkMeta) return tkMeta.content;

            // 4. URL parameter in existing links
            const tkLink = document.querySelector('a[href*="TransientKey="]');
            if (tkLink) {
                const match = tkLink.href.match(/TransientKey=([^&]+)/);
                if (match) return decodeURIComponent(match[1]);
            }

            return null;
        }

        destroy() {
            if (this.handleClick) {
                document.removeEventListener('click', this.handleClick);
            }
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

        // Initialize ReplyButton (handle @username #floor reply)
        bitsTheme.replyButton = new ReplyButton();
        bitsTheme.replyButton.init();

        // Initialize BookmarkButton (handle bookmark UI updates)
        bitsTheme.bookmarkButton = new BookmarkButton();
        bitsTheme.bookmarkButton.init();

        // Initialize FollowButton (handle follow/unfollow interactions)
        bitsTheme.followButton = new FollowButton();
        bitsTheme.followButton.init();

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
