import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Function to refresh the CSRF token from the meta tag
 */
window.refreshCSRFToken = function () {
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

// Add a response interceptor to handle authentication errors
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response) {
            // Handle 401 (Unauthorized)
            if (error.response.status === 401) {
                window.location.href = '/login';
                return Promise.reject(error);
            }

            // Handle 403 (Forbidden)
            if (error.response.status === 403) {
                window.location.href = '/dashboard';
                return Promise.reject(error);
            }

            // Handle 419 (CSRF Token Mismatch)
            if (error.response.status === 419) {
                window.location.reload();
                return Promise.reject(error);
            }
        }

        return Promise.reject(error);
    }
);
