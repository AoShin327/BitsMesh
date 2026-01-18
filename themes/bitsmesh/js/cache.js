/**
 * BitsMesh Theme - Client-side Cache Manager
 * Handles caching of API responses in localStorage
 */

class DataCache {
    constructor(options = {}) {
        this.storage = localStorage;
        this.prefix = options.prefix || 'bits-cache-';
        this.defaultTTL = options.ttl || 5 * 60 * 1000; // 5 minutes default
    }

    /**
     * Generate cache key with prefix
     * @param {string} key - Original key
     * @returns {string} Prefixed key
     */
    _getKey(key) {
        return this.prefix + key;
    }

    /**
     * Set data in cache
     * @param {string} key - Cache key
     * @param {*} data - Data to cache
     * @param {number} ttl - Time to live in milliseconds (optional)
     */
    set(key, data, ttl = this.defaultTTL) {
        const cacheData = {
            data: data,
            timestamp: Date.now(),
            ttl: ttl
        };
        try {
            this.storage.setItem(this._getKey(key), JSON.stringify(cacheData));
        } catch (e) {
            // Handle quota exceeded
            if (e.name === 'QuotaExceededError') {
                this.clearExpired();
                try {
                    this.storage.setItem(this._getKey(key), JSON.stringify(cacheData));
                } catch (e2) {
                    console.warn('Cache storage full, unable to cache:', key);
                }
            }
        }
    }

    /**
     * Get data from cache
     * @param {string} key - Cache key
     * @returns {*} Cached data or null if expired/not found
     */
    get(key) {
        const cached = this.storage.getItem(this._getKey(key));
        if (!cached) return null;

        try {
            const { data, timestamp, ttl } = JSON.parse(cached);
            if (Date.now() - timestamp > ttl) {
                this.remove(key);
                return null;
            }
            return data;
        } catch (e) {
            this.remove(key);
            return null;
        }
    }

    /**
     * Check if key exists and is valid
     * @param {string} key - Cache key
     * @returns {boolean}
     */
    has(key) {
        return this.get(key) !== null;
    }

    /**
     * Remove item from cache
     * @param {string} key - Cache key
     */
    remove(key) {
        this.storage.removeItem(this._getKey(key));
    }

    /**
     * Clear all expired items
     */
    clearExpired() {
        const keys = Object.keys(this.storage);
        const now = Date.now();

        keys.forEach(key => {
            if (key.startsWith(this.prefix)) {
                try {
                    const { timestamp, ttl } = JSON.parse(this.storage.getItem(key));
                    if (now - timestamp > ttl) {
                        this.storage.removeItem(key);
                    }
                } catch (e) {
                    this.storage.removeItem(key);
                }
            }
        });
    }

    /**
     * Clear all cached data
     */
    clear() {
        const keys = Object.keys(this.storage);
        keys.forEach(key => {
            if (key.startsWith(this.prefix)) {
                this.storage.removeItem(key);
            }
        });
    }

    /**
     * Get or fetch data with caching
     * @param {string} key - Cache key
     * @param {Function} fetcher - Async function to fetch data if not cached
     * @param {number} ttl - Time to live (optional)
     * @returns {Promise<*>}
     */
    async getOrFetch(key, fetcher, ttl = this.defaultTTL) {
        const cached = this.get(key);
        if (cached !== null) {
            return cached;
        }

        const data = await fetcher();
        this.set(key, data, ttl);
        return data;
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.bitsTheme = window.bitsTheme || {};
    window.bitsTheme.cache = new DataCache();
});
