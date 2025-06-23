/**
 * CSRF Token Management Utility
 * Helps prevent 419 "Page Expired" errors
 */

export const csrfUtils = {
    /**
     * Get the current CSRF token from meta tag
     */
    getToken() {
        const metaTag = document.head.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : null;
    },

    /**
     * Update CSRF token in meta tag and axios headers
     */
    updateToken(newToken) {
        if (!newToken) return false;

        // Update meta tag
        const metaTag = document.head.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', newToken);
        }

        // Update axios headers if available
        if (window.axios) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
        }

        // Store globally
        window.csrfToken = newToken;
        
        return true;
    },

    /**
     * Fetch a fresh CSRF token from the server
     */
    async refresh() {
        try {
            const response = await fetch('/api/csrf-token', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.csrf_token) {
                    this.updateToken(data.csrf_token);
                    console.log('CSRF token refreshed successfully');
                    return data.csrf_token;
                }
            }
        } catch (error) {
            console.error('Failed to refresh CSRF token:', error);
        }
        return null;
    },

    /**
     * Create headers object with CSRF token for fetch requests
     */
    getHeaders(additionalHeaders = {}) {
        const token = this.getToken();
        return {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            ...additionalHeaders
        };
    },

    /**
     * Make a POST request with proper CSRF token handling
     */
    async post(url, data = {}, options = {}) {
        const token = this.getToken();
        
        if (!token) {
            console.warn('No CSRF token found, refreshing...');
            await this.refresh();
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: this.getHeaders(options.headers),
                body: JSON.stringify(data),
                ...options
            });

            // Handle 419 error by refreshing token and retrying
            if (response.status === 419) {
                console.log('CSRF token expired, refreshing and retrying...');
                const newToken = await this.refresh();
                
                if (newToken) {
                    // Retry the request with new token
                    return fetch(url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: this.getHeaders(options.headers),
                        body: JSON.stringify(data),
                        ...options
                    });
                }
            }

            return response;
        } catch (error) {
            console.error('CSRF POST request failed:', error);
            throw error;
        }
    },

    /**
     * Initialize CSRF token handling for the page
     */
    init() {
        // Refresh token on page focus
        window.addEventListener('focus', () => {
            this.refresh();
        });

        // Refresh token periodically (every 30 minutes)
        setInterval(() => {
            this.refresh();
        }, 30 * 60 * 1000);

        console.log('CSRF utility initialized');
    }
};

// Auto-initialize when module is loaded
if (typeof window !== 'undefined') {
    csrfUtils.init();
}

export default csrfUtils; 