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
     * BookmarkButton - Custom bookmark handling with debounce and proper UI updates
     *
     * Problem: Vanilla's Hijack system replaces DOM elements, losing our custom classes.
     * Solution: Completely take over bookmark handling with our own AJAX requests.
     */
    class BookmarkButton {
        constructor() {
            this.handleClick = null;
            this.debounceTimers = {};
            this.pendingRequests = new Set();
            this.DEBOUNCE_MS = 500;
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            // Intercept ALL clicks on bookmark buttons/links
            this.handleClick = (e) => {
                // Match our custom bookmark button OR Vanilla's replaced bookmark link
                const bookmarkBtn = e.target.closest('.menu-bookmark, a[href*="/bookmark/"], .Bookmarked, .Bookmark');
                if (!bookmarkBtn) return;

                // Only handle if it's a bookmark URL
                const href = bookmarkBtn.getAttribute('href');
                if (!href || !href.includes('/bookmark/')) return;

                // Prevent default navigation and Vanilla's Hijack
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                // Extract discussion ID from URL
                const match = href.match(/\/bookmark\/(\d+)/);
                if (!match) return;
                const discussionID = match[1];

                // Check button-level lock (synchronous, immediate)
                if (bookmarkBtn.dataset.bitsProcessing === 'true') {
                    return;
                }

                // Debounce: prevent rapid clicks
                if (this.debounceTimers[discussionID]) {
                    return;
                }

                // Prevent if already processing
                if (this.pendingRequests.has(discussionID)) {
                    return;
                }

                // Set immediate lock on button element
                bookmarkBtn.dataset.bitsProcessing = 'true';

                // Make AJAX request ourselves
                this.toggleBookmark(bookmarkBtn, discussionID, href);
            };

            // Use capture phase to intercept before Vanilla's handler
            document.addEventListener('click', this.handleClick, true);
        }

        /**
         * Toggle bookmark state via AJAX
         */
        toggleBookmark(button, discussionID, url) {
            // Mark as processing
            this.pendingRequests.add(discussionID);
            button.classList.add('bits-loading');

            // Set debounce timer
            this.debounceTimers[discussionID] = setTimeout(() => {
                delete this.debounceTimers[discussionID];
            }, this.DEBOUNCE_MS);

            // Determine current state
            const wasBookmarked = button.classList.contains('bookmarked') ||
                                  button.classList.contains('Bookmarked') ||
                                  button.title === 'Unbookmark' ||
                                  button.title === '取消收藏';

            // Store original parent for restoring structure
            const parent = button.closest('.comment-menu');

            // Extract TransientKey from URL or get from gdn meta
            const transientKey = url.split('/').pop() || (window.gdn && gdn.meta && gdn.meta.TransientKey) || '';

            // Use fetch for AJAX - MUST be POST with DeliveryType/DeliveryMethod for JSON response
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                credentials: 'same-origin',
                body: `DeliveryType=VIEW&DeliveryMethod=JSON&TransientKey=${encodeURIComponent(transientKey)}`
            })
            .then(response => response.json())
            .then(data => {
                this.handleResponse(button, discussionID, data, wasBookmarked, parent);
            })
            .catch(error => {
                console.error('Bookmark error:', error);
                this.cleanupPending(discussionID, button);
            });
        }

        /**
         * Handle bookmark response and update UI
         */
        handleResponse(button, discussionID, response, wasBookmarked, parent) {
            // Determine new state from response
            let isBookmarked = !wasBookmarked; // Toggle by default
            let newHref = null;

            if (response.Targets) {
                const elementTarget = response.Targets.find(t => t.Target === '!element');
                if (elementTarget && elementTarget.Data) {
                    // Check the replacement HTML for state
                    isBookmarked = !elementTarget.Data.includes('title="Bookmark"');
                    // Extract new href with updated transient key
                    const hrefMatch = elementTarget.Data.match(/href="([^"]+)"/);
                    if (hrefMatch) {
                        newHref = hrefMatch[1];
                    }
                }
            }

            // Remove loading state
            button.classList.remove('bits-loading');

            // Update or restore button UI
            this.updateButtonUI(button, discussionID, isBookmarked, newHref, wasBookmarked, parent);

            this.cleanupPending(discussionID, button);
        }

        /**
         * Update button UI - restore proper structure if Vanilla replaced it
         */
        updateButtonUI(button, discussionID, isBookmarked, newHref, wasBookmarked, parent) {
            // Check if this is our custom menu-bookmark or Vanilla's replaced link
            const isCustomButton = button.classList.contains('menu-bookmark') &&
                                   button.classList.contains('menu-item');

            if (isCustomButton) {
                // Our custom button - update directly
                if (isBookmarked) {
                    button.classList.add('bookmarked');
                    button.title = '取消收藏';
                } else {
                    button.classList.remove('bookmarked');
                    button.title = '收藏';
                }

                // Update SVG icon
                const svgUse = button.querySelector('use');
                if (svgUse) {
                    svgUse.setAttribute('href', isBookmarked ? '#star-one' : '#star');
                }

                // Update href
                if (newHref) button.setAttribute('href', newHref);

                // Update count
                this.updateBookmarkCount(button, isBookmarked, wasBookmarked);
            } else if (parent) {
                // Vanilla replaced the DOM - need to restore our structure
                this.restoreButtonStructure(button, parent, discussionID, isBookmarked, newHref, wasBookmarked);
            }
        }

        /**
         * Restore our custom button structure when Vanilla replaces it
         */
        restoreButtonStructure(oldButton, parent, discussionID, isBookmarked, newHref, wasBookmarked) {
            // Get existing bookmark count from data attribute or estimate
            let bookmarkCount = parseInt(parent.dataset.bookmarkCount) || 0;

            // Adjust count based on state change
            if (isBookmarked && !wasBookmarked) {
                bookmarkCount++;
            } else if (!isBookmarked && wasBookmarked) {
                bookmarkCount = Math.max(0, bookmarkCount - 1);
            }

            // Store updated count
            parent.dataset.bookmarkCount = bookmarkCount;

            // Create new button with proper structure
            const newButton = document.createElement('a');
            newButton.href = newHref || oldButton.getAttribute('href');
            newButton.className = `menu-item menu-bookmark Hijack${isBookmarked ? ' bookmarked' : ''}`;
            newButton.dataset.discussionId = discussionID;
            newButton.title = isBookmarked ? '取消收藏' : '收藏';
            newButton.innerHTML = `
                <svg class="iconpark-icon" width="12" height="12">
                    <use href="#${isBookmarked ? 'star-one' : 'star'}"></use>
                </svg>
                <span class="bookmark-count">${bookmarkCount > 0 ? bookmarkCount : ''}</span>
            `;

            // Replace old button
            if (oldButton.parentNode) {
                oldButton.parentNode.replaceChild(newButton, oldButton);
            }
        }

        /**
         * Update bookmark count display
         */
        updateBookmarkCount(button, isBookmarked, wasBookmarked) {
            const countSpan = button.querySelector('.bookmark-count');
            if (!countSpan) return;

            const currentCount = parseInt(countSpan.textContent) || 0;
            let newCount = currentCount;

            if (isBookmarked && !wasBookmarked) {
                newCount = currentCount + 1;
            } else if (!isBookmarked && wasBookmarked) {
                newCount = Math.max(0, currentCount - 1);
            }

            countSpan.textContent = newCount > 0 ? newCount : '';

            // Also store in parent for restoration
            const parent = button.closest('.comment-menu');
            if (parent) {
                parent.dataset.bookmarkCount = newCount;
            }
        }

        cleanupPending(discussionID, button) {
            this.pendingRequests.delete(discussionID);
            if (button) {
                button.classList.remove('bits-loading');
                delete button.dataset.bitsProcessing;
            }
        }

        destroy() {
            if (this.handleClick) {
                document.removeEventListener('click', this.handleClick, true);
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
     * NotificationPage - Handle notification page interactions
     * Tab switching, AJAX loading, mark as read, conversation handling
     */
    class NotificationPage {
        constructor() {
            this.page = null;
            this.tabs = null;
            this.panels = null;
            this.currentConversationID = null;
            this.handleTabClick = null;
            this.handleConversationClick = null;
            this.handleHashChange = null;
        }

        init() {
            this.page = document.querySelector('.notification-page');
            if (!this.page) return;

            this.tabs = this.page.querySelectorAll('.notification-tabs .tab-item');
            this.panels = this.page.querySelectorAll('.tab-panel');

            this.bindEvents();
            this.handleInitialHash();
        }

        bindEvents() {
            // Tab click handling
            this.handleTabClick = (e) => {
                const tab = e.target.closest('.tab-item');
                if (!tab) return;

                e.preventDefault();
                const targetId = tab.getAttribute('data-tab');
                this.switchTab(targetId);

                // Update URL hash
                history.pushState(null, '', '#' + targetId);
            };
            this.tabs.forEach(tab => tab.addEventListener('click', this.handleTabClick));

            // Hash change handling
            this.handleHashChange = () => {
                const hash = window.location.hash.slice(1) || 'atMe';
                this.switchTab(hash);
            };
            window.addEventListener('hashchange', this.handleHashChange);

            // Mark all read button
            const markAllBtn = document.getElementById('MarkAllReadBtn');
            if (markAllBtn) {
                markAllBtn.addEventListener('click', () => this.markAllRead());
            }

            // Conversation item click
            this.handleConversationClick = (e) => {
                const convItem = e.target.closest('.conversation-item');
                if (!convItem) return;

                // Don't interfere with avatar link clicks
                if (e.target.closest('.conversation-avatar')) return;

                const conversationID = convItem.dataset.conversationId;
                if (conversationID) {
                    this.loadConversation(conversationID, convItem);
                }
            };
            const convList = this.page.querySelector('.conversation-list');
            if (convList) {
                convList.addEventListener('click', this.handleConversationClick);
            }

            // Back/Close button for chat panel (delegated)
            this.page.addEventListener('click', (e) => {
                if (e.target.closest('#BackToListBtn') || e.target.closest('#CloseChatBtn')) {
                    this.closeChat();
                }
            });

            // Send message form (delegated)
            this.page.addEventListener('submit', (e) => {
                if (e.target.matches('#SendMessageForm')) {
                    e.preventDefault();
                    this.sendMessage(e.target);
                }
                // Handle new conversation form
                if (e.target.matches('#NewMessageForm')) {
                    e.preventDefault();
                    this.sendNewConversation(e.target);
                }
            });
        }

        handleInitialHash() {
            const hash = window.location.hash.slice(1);
            if (hash && ['atMe', 'reply', 'message'].includes(hash)) {
                this.switchTab(hash);
            }
        }

        switchTab(tabId) {
            // Normalize tab ID
            tabId = tabId.toLowerCase();

            // Update tab active state
            this.tabs.forEach(tab => {
                const isActive = tab.getAttribute('data-tab').toLowerCase() === tabId;
                tab.classList.toggle('active', isActive);
            });

            // Update panel active state
            this.panels.forEach(panel => {
                const isActive = panel.id.toLowerCase() === tabId;
                panel.classList.toggle('active', isActive);
            });
        }

        async markAllRead() {
            const btn = document.getElementById('MarkAllReadBtn');
            if (!btn) return;

            const transientKey = btn.dataset.transientKey || this.getTransientKey();
            if (!transientKey) {
                console.error('NotificationPage: TransientKey not found');
                return;
            }

            // Disable button and show loading
            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="loading-spinner"></span>';

            try {
                const response = await fetch('/profile/marknotificationsread', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `type=all&TransientKey=${encodeURIComponent(transientKey)}`
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.Success) {
                    // Update UI - remove unread states and badges
                    this.page.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                        const dot = item.querySelector('.unread-dot');
                        if (dot) dot.remove();
                    });

                    this.page.querySelectorAll('.notification-tabs .badge').forEach(badge => {
                        badge.remove();
                    });

                    // Update sidebar badge if exists
                    const sidebarBadge = document.querySelector('.sidebar-nav-item[href*="notification"] .unread-badge');
                    if (sidebarBadge) {
                        sidebarBadge.remove();
                    }

                    this.showToast('已将所有通知标记为已读', 'success');
                } else {
                    this.showToast(data.Error || '操作失败', 'error');
                }
            } catch (error) {
                console.error('NotificationPage: Mark all read failed', error);
                this.showToast('网络错误，请重试', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }

        async loadConversation(conversationID, listItem) {
            const chatPanel = document.getElementById('ChatPanel');
            const conversationList = document.getElementById('ConversationList');
            if (!chatPanel) return;

            // Mark current conversation as active
            this.page.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            if (listItem) {
                listItem.classList.add('active');
                listItem.classList.remove('has-unread');

                // Remove unread count
                const unreadCount = listItem.querySelector('.unread-count');
                if (unreadCount) unreadCount.remove();
            }

            // Store current conversation ID
            this.currentConversationID = conversationID;

            // Hide conversation list, show chat panel
            if (conversationList) conversationList.style.display = 'none';
            chatPanel.style.display = 'flex';

            // Show loading state
            chatPanel.innerHTML = '<div class="chat-placeholder"><span class="loading-spinner"></span></div>';

            try {
                const response = await fetch(`/profile/conversationmessages/${conversationID}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const html = await response.text();
                chatPanel.innerHTML = html;

                // Scroll to bottom of messages
                const messagesArea = chatPanel.querySelector('#ChatMessages');
                if (messagesArea) {
                    messagesArea.scrollTop = messagesArea.scrollHeight;
                }

                // Auto-resize textarea
                const textarea = chatPanel.querySelector('#MessageBody');
                if (textarea) {
                    this.setupAutoResize(textarea);
                }

                // Setup back button event
                const backBtn = chatPanel.querySelector('#BackToListBtn');
                if (backBtn) {
                    backBtn.addEventListener('click', () => this.closeChat());
                }
            } catch (error) {
                console.error('NotificationPage: Load conversation failed', error);
                chatPanel.innerHTML = `
                    <div class="chat-header">
                        <button type="button" class="back-btn" id="BackToListBtn">
                            <svg class="iconpark-icon" width="16" height="16"><use href="#left"></use></svg>
                            <span>返回</span>
                        </button>
                    </div>
                    <div class="chat-placeholder">
                        <p>加载失败，请重试</p>
                    </div>
                `;
                // Setup back button on error state
                const backBtn = chatPanel.querySelector('#BackToListBtn');
                if (backBtn) {
                    backBtn.addEventListener('click', () => this.closeChat());
                }
            }
        }

        closeChat() {
            const chatPanel = document.getElementById('ChatPanel');
            const conversationList = document.getElementById('ConversationList');
            if (!chatPanel) return;

            this.currentConversationID = null;

            // Remove active state from conversation list
            this.page.querySelectorAll('.conversation-item.active').forEach(item => {
                item.classList.remove('active');
            });

            // Hide chat panel, show conversation list
            chatPanel.style.display = 'none';
            chatPanel.innerHTML = '';
            if (conversationList) conversationList.style.display = 'block';
        }

        async sendMessage(form) {
            const body = form.querySelector('#MessageBody');
            const submitBtn = form.querySelector('.send-btn');
            const conversationID = form.querySelector('input[name="ConversationID"]')?.value;
            const transientKey = form.querySelector('input[name="TransientKey"]')?.value;

            if (!body || !body.value.trim()) {
                body?.focus();
                return;
            }

            if (!conversationID || !transientKey) {
                console.error('NotificationPage: Missing form data');
                return;
            }

            // Disable form
            body.disabled = true;
            submitBtn.disabled = true;
            const messageText = body.value.trim();

            try {
                const response = await fetch('/profile/sendmessage', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `ConversationID=${encodeURIComponent(conversationID)}&Body=${encodeURIComponent(messageText)}&TransientKey=${encodeURIComponent(transientKey)}`
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.Success) {
                    // Clear input
                    body.value = '';

                    // Add message to chat
                    this.appendSentMessage(messageText);

                    // Update conversation list item preview
                    this.updateConversationPreview(conversationID, messageText);
                } else {
                    this.showToast(data.Error || '发送失败', 'error');
                }
            } catch (error) {
                console.error('NotificationPage: Send message failed', error);
                this.showToast('网络错误，请重试', 'error');
            } finally {
                body.disabled = false;
                submitBtn.disabled = false;
                body.focus();
            }
        }

        appendSentMessage(text) {
            const messagesArea = document.getElementById('ChatMessages');
            if (!messagesArea) return;

            const now = new Date();
            const timeStr = now.toLocaleString('zh-CN', {
                month: 'numeric',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            // Get current user photo from sidebar or session
            const userPhoto = document.querySelector('.bits-user-head img.bits-avatar-normal')?.src ||
                             document.querySelector('.sidebar-user-avatar img')?.src ||
                             document.querySelector('.Profile .PhotoWrap img')?.src ||
                             '/applications/dashboard/design/images/defaulticon.png';

            const messageHtml = `
                <div class="message-row sent">
                    <div class="message-content">
                        <div class="message-bubble sent">
                            <div class="message-text">${this.escapeHtml(text)}</div>
                        </div>
                        <div class="message-time">${timeStr}</div>
                    </div>
                    <a href="#" class="message-avatar">
                        <img src="${userPhoto}" alt="" class="avatar">
                    </a>
                </div>
            `;

            messagesArea.insertAdjacentHTML('beforeend', messageHtml);
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }

        updateConversationPreview(conversationID, text) {
            const convItem = this.page.querySelector(`.conversation-item[data-conversation-id="${conversationID}"]`);
            if (!convItem) return;

            const excerpt = convItem.querySelector('.conversation-excerpt');
            if (excerpt) {
                const truncated = text.length > 50 ? text.substring(0, 50) + '...' : text;
                excerpt.textContent = truncated;
            }

            const time = convItem.querySelector('.conversation-time');
            if (time) {
                time.textContent = '刚刚';
            }
        }

        /**
         * Send a new conversation (first message to a user)
         * @param {HTMLFormElement} form - The form element
         */
        async sendNewConversation(form) {
            const body = form.querySelector('#MessageBody');
            const submitBtn = form.querySelector('.send-btn');
            const toUsername = form.dataset.toUsername; // Use username, not UserID
            const transientKey = form.querySelector('input[name="TransientKey"]')?.value || this.getTransientKey();

            if (!body || !body.value.trim()) {
                body?.focus();
                return;
            }

            if (!toUsername || !transientKey) {
                console.error('NotificationPage: Missing username or transient key');
                return;
            }

            // Disable form
            body.disabled = true;
            submitBtn.disabled = true;
            const messageText = body.value.trim();

            try {
                // Use Vanilla's built-in messages/add endpoint
                // IMPORTANT: 'To' parameter must be username, not UserID (see MessagesController::add)
                const response = await fetch('/messages/add.json', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `To=${encodeURIComponent(toUsername)}&Body=${encodeURIComponent(messageText)}&TransientKey=${encodeURIComponent(transientKey)}`
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                // Check for success: Vanilla returns RedirectTo on success, or no error fields
                // If FormSaved is explicitly false with StatusMessage, it's an error
                if (data.FormSaved === false && data.StatusMessage) {
                    this.showToast(data.StatusMessage, 'error');
                } else if (data.FormSaved === false && data.ErrorMessages) {
                    // Handle validation errors
                    const errorMsg = Array.isArray(data.ErrorMessages)
                        ? data.ErrorMessages.join(', ')
                        : '发送失败，请检查输入';
                    this.showToast(errorMsg, 'error');
                } else {
                    // Success - HTTP 200 without error indicators means the message was sent
                    this.showToast('消息已发送', 'success');

                    // Reload to update conversation list
                    setTimeout(() => {
                        window.location.href = '/notification#message';
                    }, 1000);
                }
            } catch (error) {
                console.error('NotificationPage: Send new conversation failed', error);
                this.showToast('网络错误，请重试', 'error');
            } finally {
                body.disabled = false;
                submitBtn.disabled = false;
                body.focus();
            }
        }

        /**
         * Show the new conversation user search panel
         */
        showNewConversationSearch() {
            const chatPanel = document.getElementById('ChatPanel');
            if (!chatPanel) return;

            // Clear active conversation
            this.page.querySelectorAll('.conversation-item.active').forEach(item => {
                item.classList.remove('active');
            });

            // Show user search interface
            chatPanel.innerHTML = `
                <div class="new-conversation-panel" id="NewConversationPanel">
                    <div class="chat-header">
                        <span class="chat-username">新建私信</span>
                    </div>
                    <div class="chat-messages" id="ChatMessages">
                        <div class="user-search-panel">
                            <div class="search-input-wrapper">
                                <input type="text" id="UserSearchInput" placeholder="输入用户名搜索..." autocomplete="off">
                            </div>
                            <div class="user-search-results" id="UserSearchResults">
                                <p class="search-hint">输入用户名开始搜索</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Setup user search
            const searchInput = document.getElementById('UserSearchInput');
            const resultsContainer = document.getElementById('UserSearchResults');
            let searchTimeout;

            if (searchInput) {
                searchInput.focus();
                searchInput.addEventListener('input', () => {
                    clearTimeout(searchTimeout);
                    const query = searchInput.value.trim();

                    if (query.length < 2) {
                        resultsContainer.innerHTML = '<p class="search-hint">输入至少2个字符</p>';
                        return;
                    }

                    resultsContainer.innerHTML = '<p class="search-hint">搜索中...</p>';

                    searchTimeout = setTimeout(() => {
                        this.searchUsers(query, resultsContainer);
                    }, 300);
                });
            }
        }

        /**
         * Search for users by name
         * @param {string} query - Search query
         * @param {HTMLElement} container - Results container
         */
        async searchUsers(query, container) {
            try {
                const response = await fetch(`/api/v2/users?query=${encodeURIComponent(query)}&limit=10`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const users = await response.json();

                if (!users || users.length === 0) {
                    container.innerHTML = '<p class="search-hint">未找到用户</p>';
                    return;
                }

                const resultsHtml = users.map(user => `
                    <div class="user-search-item" data-user-id="${user.userID}" data-user-name="${this.escapeHtml(user.name)}" data-user-photo="${user.photoUrl || ''}">
                        <img src="${user.photoUrl || '/applications/dashboard/design/images/defaulticon.png'}" alt="" class="avatar">
                        <span class="user-name">${this.escapeHtml(user.name)}</span>
                    </div>
                `).join('');

                container.innerHTML = resultsHtml;

                // Bind click events on results
                container.querySelectorAll('.user-search-item').forEach(item => {
                    item.addEventListener('click', () => {
                        this.selectUserForNewConversation(
                            item.dataset.userId,
                            item.dataset.userName,
                            item.dataset.userPhoto
                        );
                    });
                });
            } catch (error) {
                console.error('NotificationPage: User search failed', error);
                container.innerHTML = '<p class="search-hint">搜索失败，请重试</p>';
            }
        }

        /**
         * Select a user and show the message input
         * @param {string} userID - User ID
         * @param {string} userName - User name
         * @param {string} userPhoto - User photo URL
         */
        selectUserForNewConversation(userID, userName, userPhoto) {
            const chatPanel = document.getElementById('ChatPanel');
            if (!chatPanel) return;

            const transientKey = this.getTransientKey();
            const defaultPhoto = '/applications/dashboard/design/images/defaulticon.png';

            chatPanel.innerHTML = `
                <div class="new-conversation-panel" id="NewConversationPanel">
                    <div class="chat-header">
                        <img src="${userPhoto || defaultPhoto}" alt="" class="avatar">
                        <span class="chat-username">${this.escapeHtml(userName)}</span>
                    </div>
                    <div class="chat-messages" id="ChatMessages">
                        <div class="chat-welcome">
                            <p>开始与 ${this.escapeHtml(userName)} 的对话</p>
                        </div>
                    </div>
                    <div class="chat-input-area">
                        <form id="NewMessageForm" data-to-user-id="${userID}" data-to-username="${this.escapeHtml(userName)}">
                            <input type="hidden" name="TransientKey" value="${transientKey}">
                            <textarea name="Body" id="MessageBody" placeholder="输入消息..." rows="2"></textarea>
                            <button type="submit" class="send-btn">
                                <svg class="iconpark-icon" width="18" height="18"><use href="#send"></use></svg>
                            </button>
                        </form>
                    </div>
                </div>
            `;

            // Auto-resize textarea
            const textarea = chatPanel.querySelector('#MessageBody');
            if (textarea) {
                this.setupAutoResize(textarea);
                textarea.focus();
            }
        }

        setupAutoResize(textarea) {
            const resize = () => {
                textarea.style.height = 'auto';
                textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
            };

            textarea.addEventListener('input', resize);
            resize();
        }

        showToast(message, type = 'info') {
            // Remove existing toast
            const existing = document.querySelector('.notification-toast');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.className = `notification-toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        getTransientKey() {
            // Try multiple sources
            if (typeof gdn !== 'undefined' && gdn.definition) {
                const tk = gdn.definition('TransientKey');
                if (tk) return tk;
            }

            const tkInput = document.querySelector('input[name="TransientKey"]');
            if (tkInput) return tkInput.value;

            if (window.BitsNotification && window.BitsNotification.transientKey) {
                return window.BitsNotification.transientKey;
            }

            return null;
        }

        destroy() {
            if (this.handleHashChange) {
                window.removeEventListener('hashchange', this.handleHashChange);
            }
        }
    }

    /**
     * CommentMenuHandler - Handle comment reactions (like/dislike) and chicken leg
     *
     * Features:
     * - Like/Dislike buttons with toggle and switch functionality
     * - Chicken leg (appreciation) with daily quota
     * - Debounce to prevent rapid clicks
     * - Loading state feedback
     * - Error handling with toast messages
     */
    class CommentMenuHandler {
        constructor() {
            this.handleClick = null;
            this.pendingRequests = new Set(); // Track pending requests by comment ID
            this.debounceDelay = 300; // ms
            this.lastClickTime = {};
        }

        init() {
            this.bindEvents();
            this.loadInitialReactionData();
        }

        bindEvents() {
            this.handleClick = (e) => {
                // Handle like button
                const likeBtn = e.target.closest('.menu-like');
                if (likeBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handleReaction(likeBtn, 1);
                    return;
                }

                // Handle dislike button
                const dislikeBtn = e.target.closest('.menu-dislike');
                if (dislikeBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handleReaction(dislikeBtn, -1);
                    return;
                }

                // Handle chicken leg button
                const chickenBtn = e.target.closest('.menu-credit');
                if (chickenBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handleChickenLeg(chickenBtn);
                    return;
                }
            };

            document.addEventListener('click', this.handleClick);
        }

        /**
         * Load initial reaction data for all comments on the page
         */
        async loadInitialReactionData() {
            // Get discussion ID from page
            const discussionMeta = document.querySelector('[data-discussion-id]');
            if (!discussionMeta) return;

            const discussionID = discussionMeta.dataset.discussionId;
            if (!discussionID) return;

            try {
                const response = await fetch(`/discussion/commentreactions.json?DiscussionID=${discussionID}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) return;

                const data = await response.json();
                if (data.Success && data.Reactions) {
                    this.updateAllReactionCounts(data.Reactions);
                }
            } catch (error) {
                console.error('CommentMenuHandler: Failed to load reactions', error);
            }
        }

        /**
         * Update reaction counts for all comments
         */
        updateAllReactionCounts(reactions) {
            Object.entries(reactions).forEach(([key, counts]) => {
                let containerEl;

                if (key === 'discussion') {
                    // Main discussion (OP)
                    containerEl = document.querySelector('.bits-discussion-content');
                } else {
                    // Comment
                    containerEl = document.querySelector(`[data-comment-id="${key}"]`);
                }

                if (!containerEl) return;

                const menu = containerEl.querySelector('.comment-menu');
                if (!menu) return;

                // Update like count
                const likeBtn = menu.querySelector('.menu-like');
                if (likeBtn) {
                    const countSpan = likeBtn.querySelector('span');
                    if (countSpan) {
                        countSpan.textContent = counts.likeCount || 0;
                    }
                    // Update active state
                    if (counts.userScore === 1) {
                        likeBtn.classList.add('active');
                    } else {
                        likeBtn.classList.remove('active');
                    }
                }

                // Update dislike count
                const dislikeBtn = menu.querySelector('.menu-dislike');
                if (dislikeBtn) {
                    const countSpan = dislikeBtn.querySelector('span');
                    if (countSpan) {
                        countSpan.textContent = counts.dislikeCount || 0;
                    }
                    // Update active state
                    if (counts.userScore === -1) {
                        dislikeBtn.classList.add('active');
                    } else {
                        dislikeBtn.classList.remove('active');
                    }
                }

                // Update chicken leg count
                const chickenLegBtn = menu.querySelector('.menu-credit');
                if (chickenLegBtn) {
                    const countSpan = chickenLegBtn.querySelector('span');
                    if (countSpan) {
                        countSpan.textContent = counts.chickenLegCount || 0;
                    }
                }
            });
        }

        /**
         * Handle like/dislike reaction
         */
        async handleReaction(button, score) {
            // Determine record type and ID
            let recordType, recordID, containerEl;

            // Check if this is on the main discussion (OP)
            const discussionEl = button.closest('.bits-discussion-content');
            if (discussionEl) {
                recordType = 'Discussion';
                recordID = discussionEl.dataset.recordId || discussionEl.dataset.discussionId;
                containerEl = discussionEl;
            } else {
                // It's a comment
                const commentEl = button.closest('[data-comment-id]');
                if (commentEl) {
                    recordType = 'Comment';
                    recordID = commentEl.dataset.commentId;
                    containerEl = commentEl;
                }
            }

            if (!recordType || !recordID || !containerEl) {
                console.error('CommentMenuHandler: Cannot determine record type/ID');
                return;
            }

            // Debounce check
            const now = Date.now();
            const key = `reaction_${recordType}_${recordID}_${score}`;
            if (this.lastClickTime[key] && now - this.lastClickTime[key] < this.debounceDelay) {
                return;
            }
            this.lastClickTime[key] = now;

            // Prevent duplicate requests
            const requestKey = `reaction_${recordType}_${recordID}`;
            if (this.pendingRequests.has(requestKey)) {
                return;
            }
            this.pendingRequests.add(requestKey);

            // Get TransientKey
            const transientKey = this.getTransientKey();
            if (!transientKey) {
                this.showToast('请先登录', 'error');
                this.pendingRequests.delete(requestKey);
                return;
            }

            // Show loading state
            button.classList.add('bits-loading');

            try {
                const response = await fetch('/discussion/reactcontent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `RecordType=${recordType}&RecordID=${recordID}&Score=${score}&TransientKey=${encodeURIComponent(transientKey)}`
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.Success) {
                    this.updateReactionUI(containerEl, data, score);
                } else {
                    if (data.Error === 'NotLoggedIn') {
                        this.showToast('请先登录', 'error');
                    } else {
                        this.showToast(data.Message || '操作失败', 'error');
                    }
                }
            } catch (error) {
                console.error('CommentMenuHandler: Reaction failed', error);
                this.showToast('网络错误，请重试', 'error');
            } finally {
                button.classList.remove('bits-loading');
                this.pendingRequests.delete(requestKey);
            }
        }

        /**
         * Update reaction UI after successful action
         */
        updateReactionUI(commentEl, data, clickedScore) {
            const menu = commentEl.querySelector('.comment-menu');
            if (!menu) return;

            const likeBtn = menu.querySelector('.menu-like');
            const dislikeBtn = menu.querySelector('.menu-dislike');

            // Update counts
            if (likeBtn) {
                const countSpan = likeBtn.querySelector('span');
                if (countSpan) {
                    countSpan.textContent = data.LikeCount || 0;
                }
            }

            if (dislikeBtn) {
                const countSpan = dislikeBtn.querySelector('span');
                if (countSpan) {
                    countSpan.textContent = data.DislikeCount || 0;
                }
            }

            // Update active states based on new score
            const newScore = data.NewScore;

            if (likeBtn) {
                likeBtn.classList.toggle('active', newScore === 1);
            }

            if (dislikeBtn) {
                dislikeBtn.classList.toggle('active', newScore === -1);
            }
        }

        /**
         * Handle chicken leg gift
         */
        async handleChickenLeg(button) {
            // Determine record type and ID
            let recordType, recordID;

            // Check if this is on the main discussion (OP)
            const discussionEl = button.closest('.bits-discussion-content');
            if (discussionEl) {
                recordType = 'Discussion';
                const discussionMeta = document.querySelector('[data-discussion-id]');
                recordID = discussionMeta ? discussionMeta.dataset.discussionId : null;
            } else {
                // It's a comment
                const commentEl = button.closest('[data-comment-id]');
                if (commentEl) {
                    recordType = 'Comment';
                    recordID = commentEl.dataset.commentId;
                }
            }

            if (!recordType || !recordID) {
                console.error('CommentMenuHandler: Cannot determine record type/ID');
                return;
            }

            // Debounce check
            const now = Date.now();
            const key = `chicken_${recordType}_${recordID}`;
            if (this.lastClickTime[key] && now - this.lastClickTime[key] < this.debounceDelay) {
                return;
            }
            this.lastClickTime[key] = now;

            // Prevent duplicate requests
            const requestKey = `${recordType}_${recordID}`;
            if (this.pendingRequests.has(requestKey)) {
                return;
            }
            this.pendingRequests.add(requestKey);

            // Get TransientKey
            const transientKey = this.getTransientKey();
            if (!transientKey) {
                this.showToast('请先登录', 'error');
                this.pendingRequests.delete(requestKey);
                return;
            }

            // Show loading state
            button.classList.add('bits-loading');

            try {
                const response = await fetch('/discussion/givechickenleg', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `RecordType=${recordType}&RecordID=${recordID}&TransientKey=${encodeURIComponent(transientKey)}`
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.Success) {
                    // Update count display
                    const countSpan = button.querySelector('span');
                    if (countSpan) {
                        countSpan.textContent = data.NewCount || 0;
                    }
                    // Add visual feedback
                    button.classList.add('given');
                    this.showToast(data.Message || '鸡腿已送出！', 'success');
                } else {
                    this.showToast(data.Message || '操作失败', 'error');
                }
            } catch (error) {
                console.error('CommentMenuHandler: Chicken leg failed', error);
                this.showToast('网络错误，请重试', 'error');
            } finally {
                button.classList.remove('bits-loading');
                this.pendingRequests.delete(requestKey);
            }
        }

        /**
         * Show toast notification
         */
        showToast(message, type = 'info') {
            // Remove existing toasts
            document.querySelectorAll('.bits-toast').forEach(t => t.remove());

            const toast = document.createElement('div');
            toast.className = `bits-toast bits-toast-${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);

            // Trigger animation
            requestAnimationFrame(() => {
                toast.classList.add('show');
            });

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        /**
         * Get TransientKey for CSRF protection
         */
        getTransientKey() {
            if (typeof gdn !== 'undefined' && gdn.definition) {
                const tk = gdn.definition('TransientKey');
                if (tk) return tk;
            }

            const tkInput = document.querySelector('input[name="TransientKey"]');
            if (tkInput) return tkInput.value;

            // Try to find in sidebar signout link
            const signoutLink = document.querySelector('a[href*="signout?TransientKey="]');
            if (signoutLink) {
                const match = signoutLink.href.match(/TransientKey=([^&]+)/);
                if (match) return match[1];
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

        // Initialize NotificationPage (notification center functionality)
        bitsTheme.notificationPage = new NotificationPage();
        bitsTheme.notificationPage.init();

        // Initialize CommentMenuHandler (like/dislike/chicken leg)
        bitsTheme.commentMenuHandler = new CommentMenuHandler();
        bitsTheme.commentMenuHandler.init();

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
