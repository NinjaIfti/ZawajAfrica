import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import axios from 'axios';
import csrfUtils from './utils/csrf.js';
import './utils/activityTracker.js'; // Initialize activity tracking

const appName = import.meta.env.VITE_APP_NAME || 'ZawajAfrica';

// Set up axios CSRF handling
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Function to refresh CSRF token (keeping for backwards compatibility)
const refreshCSRFToken = () => {
    return csrfUtils.getToken();
};

// Make utilities globally available
window.refreshCSRFToken = refreshCSRFToken;
window.csrfUtils = csrfUtils;

// Initial CSRF token setup
csrfUtils.refresh();

// Add axios interceptor to handle 419 errors (CSRF token mismatch)
axios.interceptors.response.use(
    response => response,
    async error => {
        if (error.response && error.response.status === 419) {
            console.log('CSRF token mismatch detected, attempting to refresh...');
            
            // Try to get a fresh token using our utility
            const newToken = await csrfUtils.refresh();
            
            if (newToken) {
                // Retry the original request with new token
                const originalRequest = error.config;
                originalRequest.headers['X-CSRF-TOKEN'] = newToken;
                return axios(originalRequest);
            } else {
                // If we can't get a fresh token, reload the page
                console.log('Could not refresh CSRF token, reloading page...');
                window.location.reload();
            }
        }
        return Promise.reject(error);
    }
);

createInertiaApp({
    title: title => title ? `${title} - ${appName}` : appName,
    resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Handle Inertia errors
router.on('error', errors => {
    console.error('Inertia error:', errors);
});

// Handle Inertia navigation events
router.on('navigate', event => {
    // Refresh CSRF token on each navigation
    csrfUtils.refresh();
});

// Refresh CSRF token after login success or any successful navigation
router.on('success', event => {
    // Small delay to ensure token is updated on server side
    setTimeout(() => {
        csrfUtils.refresh();
    }, 100);
});

// Handle authentication errors
router.on('error', errors => {
    if (errors.response && errors.response.status === 401) {
        // Unauthorized, redirect to login
        window.location.href = '/login';
    } else if (errors.response && errors.response.status === 403) {
        // Forbidden, redirect to dashboard
        window.location.href = '/dashboard';
    }
});
