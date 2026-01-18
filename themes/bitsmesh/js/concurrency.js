/**
 * BitsMesh Theme - Concurrency Controller
 * Handles request queuing, optimistic updates, and debouncing
 */

class RequestQueue {
    constructor() {
        this.queue = [];
        this.processing = false;
        this.maxConcurrent = 3;
        this.activeRequests = 0;
    }

    /**
     * Add request to queue
     * @param {Function} request - Async function to execute
     * @returns {Promise}
     */
    async enqueue(request) {
        return new Promise((resolve, reject) => {
            this.queue.push({ request, resolve, reject });
            this.process();
        });
    }

    /**
     * Process queue
     */
    async process() {
        if (this.activeRequests >= this.maxConcurrent || this.queue.length === 0) {
            return;
        }

        const { request, resolve, reject } = this.queue.shift();
        this.activeRequests++;

        try {
            const result = await request();
            resolve(result);
        } catch (error) {
            reject(error);
        } finally {
            this.activeRequests--;
            this.process();
        }
    }

    /**
     * Optimistic update with version control
     * @param {string} endpoint - API endpoint
     * @param {Object} data - Data to send
     * @param {number} version - Current version number
     * @returns {Promise<Object>}
     */
    async optimisticUpdate(endpoint, data, version) {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ...data, _version: version })
        });

        if (response.status === 409) {
            // Version conflict
            return {
                conflict: true,
                newData: await response.json()
            };
        }

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return {
            success: true,
            data: await response.json()
        };
    }
}

/**
 * Debounce function
 * @param {Function} fn - Function to debounce
 * @param {number} delay - Delay in milliseconds
 * @returns {Function}
 */
function debounce(fn, delay = 300) {
    let timer = null;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

/**
 * Throttle function
 * @param {Function} fn - Function to throttle
 * @param {number} limit - Minimum time between calls in milliseconds
 * @returns {Function}
 */
function throttle(fn, limit = 300) {
    let inThrottle = false;
    return function(...args) {
        if (!inThrottle) {
            fn.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Retry wrapper for async functions
 * @param {Function} fn - Async function to retry
 * @param {number} maxRetries - Maximum number of retries
 * @param {number} delay - Delay between retries in milliseconds
 * @returns {Promise}
 */
async function retry(fn, maxRetries = 3, delay = 1000) {
    let lastError;
    for (let i = 0; i < maxRetries; i++) {
        try {
            return await fn();
        } catch (error) {
            lastError = error;
            if (i < maxRetries - 1) {
                await new Promise(resolve => setTimeout(resolve, delay * (i + 1)));
            }
        }
    }
    throw lastError;
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.bitsTheme = window.bitsTheme || {};
    window.bitsTheme.requestQueue = new RequestQueue();
    window.bitsTheme.debounce = debounce;
    window.bitsTheme.throttle = throttle;
    window.bitsTheme.retry = retry;
});
