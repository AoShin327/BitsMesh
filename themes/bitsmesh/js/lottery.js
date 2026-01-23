/**
 * BitsMesh Lottery Tool
 *
 * A fair lottery tool based on Cloudflare drand random beacon.
 * Pure frontend implementation with verifiable results.
 *
 * @package BitsMesh
 * @since 1.0
 */

class LotteryTool {
    constructor() {
        // drand mainnet chain hash (quicknet)
        this.DRAND_CHAIN = '8990e7a9aaed2ffed73dbd7092123d6f289930540d7651336225dc172e51b2ce';
        // drand genesis time (2020-07-22 14:50:50 UTC)
        this.DRAND_GENESIS = 1595431050;
        // drand round period (30 seconds)
        this.DRAND_PERIOD = 30;

        // DOM elements
        this.elements = {};

        // State
        this.countdownTimer = null;
        this.currentParams = null;

        this.init();
    }

    /**
     * Initialize the lottery tool
     */
    init() {
        this.cacheElements();
        this.bindEvents();

        // Check if we have URL params for lottery mode
        const params = this.parseUrlParams();
        if (this.isValidParams(params)) {
            this.currentParams = params;
            this.runLottery(params);
        } else {
            this.showConfigForm();
        }
    }

    /**
     * Cache DOM elements
     */
    cacheElements() {
        this.elements = {
            // Sections
            configSection: document.getElementById('lottery-config'),
            loadingSection: document.getElementById('lottery-loading'),
            errorSection: document.getElementById('lottery-error'),
            waitingSection: document.getElementById('lottery-waiting'),
            resultsSection: document.getElementById('lottery-results'),

            // Form elements
            form: document.getElementById('lottery-form'),
            postUrl: document.getElementById('post-url'),
            drawDate: document.getElementById('draw-date'),
            drawTime: document.getElementById('draw-time'),
            winnerCount: document.getElementById('winner-count'),
            startFloor: document.getElementById('start-floor'),
            uniqueUsers: document.getElementById('unique-users'),

            // Generated link
            generatedLink: document.getElementById('generated-link'),
            lotteryLink: document.getElementById('lottery-link'),
            openLotteryLink: document.getElementById('open-lottery-link'),

            // Error
            errorText: document.getElementById('error-text'),
            retryBtn: document.getElementById('retry-btn'),

            // Waiting
            waitingPostTitle: document.getElementById('waiting-post-title'),
            waitingDrawTime: document.getElementById('waiting-draw-time'),
            countdownDays: document.getElementById('countdown-days'),
            countdownHours: document.getElementById('countdown-hours'),
            countdownMinutes: document.getElementById('countdown-minutes'),
            countdownSeconds: document.getElementById('countdown-seconds'),
            validCommentsCount: document.getElementById('valid-comments-count'),
            uniqueUsersCount: document.getElementById('unique-users-count'),

            // Results
            resultPostTitle: document.getElementById('result-post-title'),
            resultDrawTime: document.getElementById('result-draw-time'),
            resultWinnerCount: document.getElementById('result-winner-count'),
            resultValidCount: document.getElementById('result-valid-count'),
            resultUnique: document.getElementById('result-unique'),
            winnersList: document.getElementById('winners-list'),
            atMessage: document.getElementById('at-message'),

            // Verification
            verifyTimestamp: document.getElementById('verify-timestamp'),
            verifyRound: document.getElementById('verify-round'),
            verifyRandomness: document.getElementById('verify-randomness'),
            verifySource: document.getElementById('verify-source')
        };
    }

    /**
     * Bind event handlers
     */
    bindEvents() {
        // Form submission
        if (this.elements.form) {
            this.elements.form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.generateLink();
            });
        }

        // Retry button
        if (this.elements.retryBtn) {
            this.elements.retryBtn.addEventListener('click', () => {
                if (this.currentParams) {
                    this.runLottery(this.currentParams);
                }
            });
        }

        // Copy buttons
        document.querySelectorAll('.btn-copy').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.dataset.copyTarget;
                const target = document.getElementById(targetId);
                if (target) {
                    this.copyToClipboard(target.value || target.textContent);
                    this.showCopySuccess(btn);
                }
            });
        });

        // Set default date to tomorrow
        if (this.elements.drawDate) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            this.elements.drawDate.value = tomorrow.toISOString().split('T')[0];
        }

        // Set default time
        if (this.elements.drawTime) {
            this.elements.drawTime.value = '20:00';
        }
    }

    /**
     * Parse URL parameters
     */
    parseUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            post: parseInt(urlParams.get('post')) || 0,
            time: parseInt(urlParams.get('time')) || 0,
            count: parseInt(urlParams.get('count')) || 0,
            start: parseInt(urlParams.get('start')) || 1,
            unique: urlParams.get('unique') !== '0'
        };
    }

    /**
     * Check if params are valid for lottery mode
     */
    isValidParams(params) {
        return params.post > 0 && params.time > 0 && params.count > 0;
    }

    /**
     * Show configuration form
     */
    showConfigForm() {
        this.hideAllSections();
        this.elements.configSection.classList.remove('hidden');
    }

    /**
     * Hide all sections
     */
    hideAllSections() {
        ['configSection', 'loadingSection', 'errorSection', 'waitingSection', 'resultsSection'].forEach(key => {
            if (this.elements[key]) {
                this.elements[key].classList.add('hidden');
            }
        });
    }

    /**
     * Show loading state
     */
    showLoading() {
        this.hideAllSections();
        this.elements.loadingSection.classList.remove('hidden');
    }

    /**
     * Show error state
     */
    showError(message) {
        this.hideAllSections();
        this.elements.errorText.textContent = message;
        this.elements.errorSection.classList.remove('hidden');
    }

    /**
     * Generate lottery link from form
     */
    generateLink() {
        const postUrl = this.elements.postUrl.value.trim();
        const drawDate = this.elements.drawDate.value;
        const drawTime = this.elements.drawTime.value;
        const winnerCount = parseInt(this.elements.winnerCount.value) || 3;
        const startFloor = parseInt(this.elements.startFloor.value) || 1;
        const uniqueUsers = this.elements.uniqueUsers.checked;

        // Extract post ID from URL
        const postId = this.extractPostId(postUrl);
        if (!postId) {
            alert('无法从链接中提取帖子 ID，请检查链接格式');
            return;
        }

        // Calculate timestamp
        const drawDateTime = new Date(`${drawDate}T${drawTime}`);
        const timestamp = Math.floor(drawDateTime.getTime() / 1000);

        if (timestamp <= Math.floor(Date.now() / 1000)) {
            alert('开奖时间必须在未来');
            return;
        }

        // Build lottery URL
        const params = new URLSearchParams({
            post: postId,
            time: timestamp,
            count: winnerCount,
            start: startFloor,
            unique: uniqueUsers ? '1' : '0'
        });

        const lotteryUrl = `${window.location.origin}/lottery?${params.toString()}`;

        // Show generated link
        this.elements.lotteryLink.value = lotteryUrl;
        this.elements.openLotteryLink.href = lotteryUrl;
        this.elements.generatedLink.classList.remove('hidden');
    }

    /**
     * Extract post ID from various URL formats
     */
    extractPostId(url) {
        // Try /post-{id} format
        let match = url.match(/\/post-(\d+)/);
        if (match) return parseInt(match[1]);

        // Try /discussion/{id} format
        match = url.match(/\/discussion\/(\d+)/);
        if (match) return parseInt(match[1]);

        // Try discussionID query param
        const urlObj = new URL(url, window.location.origin);
        const discussionID = urlObj.searchParams.get('discussionID');
        if (discussionID) return parseInt(discussionID);

        return null;
    }

    /**
     * Run the lottery
     */
    async runLottery(params) {
        this.showLoading();

        try {
            // 1. Fetch discussion and comments
            const { discussion, comments } = await this.fetchDiscussionData(params.post);

            // 2. Filter valid comments
            const validComments = this.filterComments(comments, params);

            // 3. Check if draw time has passed
            const now = Math.floor(Date.now() / 1000);
            if (now < params.time) {
                // Show waiting state with countdown
                this.showWaiting(discussion, params, validComments);
                return;
            }

            // 4. Get drand beacon
            const beacon = await this.getDrandBeacon(params.time);

            // 5. Get random sequence
            const { sequence, source } = await this.getRandomSequence(validComments.length, beacon.randomness);

            // 6. Select winners
            const winners = this.selectWinners(validComments, sequence, params.count);

            // 7. Show results
            this.showResults(discussion, params, validComments, winners, beacon, source);

        } catch (error) {
            console.error('Lottery error:', error);
            this.showError(error.message || '抽奖过程出错，请稍后重试');
        }
    }

    /**
     * Fetch discussion and comments from custom API
     * Uses custom endpoint to bypass broken Vanilla API v2 (PHP 8.x compatibility issues)
     */
    async fetchDiscussionData(postId) {
        // Use custom lottery API endpoint
        const res = await fetch(`/lottery/api/${postId}`);
        if (!res.ok) {
            if (res.status === 404) {
                throw new Error('帖子不存在或已被删除');
            }
            throw new Error('获取帖子信息失败');
        }

        const data = await res.json();

        // Check for error in response
        if (data.error) {
            throw new Error(data.error);
        }

        return {
            discussion: data.discussion,
            comments: data.comments
        };
    }

    /**
     * Filter comments based on params
     */
    filterComments(comments, params) {
        const drawTime = params.time * 1000; // Convert to milliseconds

        // Add floor numbers and filter
        let filtered = comments
            .map((comment, index) => ({
                ...comment,
                floor: index + 1 // Floor starts from 1
            }))
            .filter(comment => {
                // Check floor number
                if (comment.floor < params.start) return false;

                // Check time (must be before draw time)
                const commentTime = new Date(comment.dateInserted).getTime();
                if (commentTime >= drawTime) return false;

                return true;
            });

        // Remove duplicates if unique is enabled
        if (params.unique) {
            const seen = new Set();
            filtered = filtered.filter(comment => {
                const userId = comment.insertUserID;
                if (seen.has(userId)) return false;
                seen.add(userId);
                return true;
            });
        }

        return filtered;
    }

    /**
     * Get drand random beacon for given timestamp
     */
    async getDrandBeacon(timestamp) {
        const round = Math.floor((timestamp - this.DRAND_GENESIS) / this.DRAND_PERIOD);

        const response = await fetch(`https://api.drand.sh/${this.DRAND_CHAIN}/public/${round}`);
        if (!response.ok) {
            if (response.status === 404) {
                throw new Error('随机信标尚未生成，请等待开奖时间');
            }
            throw new Error('获取随机信标失败');
        }

        const data = await response.json();
        return {
            round: data.round,
            randomness: data.randomness
        };
    }

    /**
     * Get random sequence using random.org or fallback to local PRNG
     */
    async getRandomSequence(maxFloor, randomness) {
        if (maxFloor === 0) {
            return { sequence: [], source: 'none' };
        }

        // Try random.org first
        try {
            const url = `https://www.random.org/sequences/?min=1&max=${maxFloor}&col=1&format=plain&rnd=id.${randomness}`;
            const response = await fetch(url);

            if (response.ok) {
                const text = await response.text();
                const sequence = text.trim().split('\n').map(Number);
                return { sequence, source: 'random.org' };
            }
        } catch (e) {
            console.warn('random.org failed, using local PRNG:', e);
        }

        // Fallback: local seeded shuffle
        const indices = Array.from({ length: maxFloor }, (_, i) => i + 1);
        const sequence = this.seededShuffle(indices, randomness);
        return { sequence, source: 'local PRNG (seeded)' };
    }

    /**
     * Seeded shuffle using mulberry32 PRNG
     */
    seededShuffle(array, seed) {
        // mulberry32 PRNG
        function mulberry32(a) {
            return function() {
                let t = a += 0x6D2B79F5;
                t = Math.imul(t ^ t >>> 15, t | 1);
                t ^= t + Math.imul(t ^ t >>> 7, t | 61);
                return ((t ^ t >>> 14) >>> 0) / 4294967296;
            };
        }

        // Convert hex seed to number (use first 8 chars)
        const seedNum = parseInt(seed.slice(0, 8), 16);
        const random = mulberry32(seedNum);

        // Fisher-Yates shuffle
        const result = [...array];
        for (let i = result.length - 1; i > 0; i--) {
            const j = Math.floor(random() * (i + 1));
            [result[i], result[j]] = [result[j], result[i]];
        }
        return result;
    }

    /**
     * Select winners from sequence
     */
    selectWinners(validComments, sequence, count) {
        const winners = [];
        const targetCount = Math.min(count, validComments.length);

        for (const idx of sequence) {
            if (winners.length >= targetCount) break;

            // sequence is 1-indexed, array is 0-indexed
            const comment = validComments[idx - 1];
            if (comment) {
                winners.push(comment);
            }
        }

        // Sort by floor number
        winners.sort((a, b) => a.floor - b.floor);
        return winners;
    }

    /**
     * Show waiting state with countdown
     */
    showWaiting(discussion, params, validComments) {
        this.hideAllSections();

        // Set info
        this.elements.waitingPostTitle.textContent = discussion.name;
        this.elements.waitingDrawTime.textContent = this.formatDateTime(params.time * 1000);

        // Set participation stats
        const uniqueUserIds = new Set(validComments.map(c => c.insertUserID));
        this.elements.validCommentsCount.textContent = validComments.length;
        this.elements.uniqueUsersCount.textContent = uniqueUserIds.size;

        // Start countdown
        this.startCountdown(params.time);

        this.elements.waitingSection.classList.remove('hidden');
    }

    /**
     * Start countdown timer
     */
    startCountdown(targetTimestamp) {
        if (this.countdownTimer) {
            clearInterval(this.countdownTimer);
        }

        const updateCountdown = () => {
            const now = Math.floor(Date.now() / 1000);
            const diff = targetTimestamp - now;

            if (diff <= 0) {
                clearInterval(this.countdownTimer);
                // Reload to run lottery
                window.location.reload();
                return;
            }

            const days = Math.floor(diff / 86400);
            const hours = Math.floor((diff % 86400) / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;

            this.elements.countdownDays.textContent = String(days).padStart(2, '0');
            this.elements.countdownHours.textContent = String(hours).padStart(2, '0');
            this.elements.countdownMinutes.textContent = String(minutes).padStart(2, '0');
            this.elements.countdownSeconds.textContent = String(seconds).padStart(2, '0');
        };

        updateCountdown();
        this.countdownTimer = setInterval(updateCountdown, 1000);
    }

    /**
     * Show lottery results
     */
    showResults(discussion, params, validComments, winners, beacon, source) {
        this.hideAllSections();

        // Post info
        this.elements.resultPostTitle.textContent = discussion.name;
        this.elements.resultPostTitle.href = `/post-${params.post}`;
        this.elements.resultDrawTime.textContent = this.formatDateTime(params.time * 1000);
        this.elements.resultWinnerCount.textContent = `${winners.length} / ${params.count}`;
        this.elements.resultValidCount.textContent = validComments.length;
        this.elements.resultUnique.textContent = params.unique ? '是' : '否';

        // Winners list
        this.elements.winnersList.innerHTML = winners.map(winner => `
            <li class="winner-item">
                <span class="winner-floor">#${winner.floor}</span>
                <span class="winner-avatar">
                    <img src="${winner.insertUser?.photoUrl || '/applications/dashboard/design/images/defaulticon.png'}" alt="">
                </span>
                <span class="winner-name">${this.escapeHtml(winner.insertUser?.name || 'Unknown')}</span>
                <a href="/post-${params.post}#${winner.floor}" class="winner-link" target="_blank">
                    查看评论 →
                </a>
            </li>
        `).join('');

        // @ Message
        const usernames = winners.map(w => `@${w.insertUser?.name || 'Unknown'}`);
        this.elements.atMessage.value = usernames.join(' ') + ' 恭喜中奖！';

        // Verification info
        this.elements.verifyTimestamp.textContent = params.time;
        this.elements.verifyRound.textContent = beacon.round;
        this.elements.verifyRandomness.textContent = beacon.randomness;
        this.elements.verifySource.textContent = source;

        this.elements.resultsSection.classList.remove('hidden');
    }

    /**
     * Format date time
     */
    formatDateTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleString('zh-CN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    /**
     * Escape HTML
     */
    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Copy to clipboard
     */
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
        } catch (e) {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
    }

    /**
     * Show copy success feedback
     */
    showCopySuccess(btn) {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<svg class="iconpark-icon" width="16" height="16"><use href="#check-one"></use></svg>';
        btn.classList.add('copied');

        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('copied');
        }, 2000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.lotteryTool = new LotteryTool();
});
