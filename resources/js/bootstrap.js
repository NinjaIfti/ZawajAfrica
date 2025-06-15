import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Function to refresh the CSRF token from the meta tag
 */
window.refreshCSRFToken = function() {
    const token = document.head.querySelector('meta[name="csrf-token"]');
    
    if (token) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        
        // Also set it as a cookie for non-axios requests
        document.cookie = `XSRF-TOKEN=${token.content}; path=/`;
    } else {
        console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
    }
};

// Initialize CSRF token
window.refreshCSRFToken();

// Set up interceptor to handle 419 (CSRF token mismatch) errors
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 419) {
            // Token expired, refresh the page to get a new token
            window.location.reload();
        }
        return Promise.reject(error);
    }
);
