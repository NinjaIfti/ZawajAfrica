<template>
    <div class="adsterra-manager">
        <!-- GDPR Consent Banner -->
        <div v-if="showConsentBanner" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 z-50">
            <div class="container mx-auto flex justify-between items-center">
                <p class="text-sm">We use cookies for ads. Choose your preferences:</p>
                <div class="flex gap-2">
                    <button @click="acceptConsent" class="bg-blue-600 px-4 py-2 rounded text-sm">Accept</button>
                    <button @click="rejectConsent" class="bg-red-600 px-4 py-2 rounded text-sm">Reject</button>
                </div>
            </div>
        </div>

        <!-- Error Display -->
        <div v-if="error && adsterraConfig.debug" class="fixed top-4 left-4 bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded text-xs z-40">
            Adsterra Error: {{ error }}
        </div>

        <!-- Debug Information -->
        <div v-if="adsterraConfig.debug && adsterraConfig.enabled && !error" class="fixed top-4 right-4 bg-blue-100 border border-blue-400 text-blue-700 px-3 py-2 rounded text-xs z-40">
            Adsterra Active ({{ adsterraConfig.test_mode ? 'Test' : 'Live' }} Mode)
            <div class="text-xs mt-1">
                Load Time: {{ loadTime }}ms | Errors: {{ errorCount }}
            </div>
        </div>

        <!-- Fallback Content -->
        <div v-if="shouldShowFallback" class="adsterra-fallback text-center text-gray-500 text-sm py-2">
            {{ adsterraConfig.fallback?.fallback_message || 'Advertisement unavailable' }}
        </div>
    </div>
</template>

<script>
import { ref, onMounted, watch, computed, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'

export default {
    name: 'AdsterraManager',
    props: {
        adsterraConfig: {
            type: Object,
            required: true
        },
        showOnPage: {
            type: Boolean,
            default: true
        },
        consentData: {
            type: Object,
            default: () => ({})
        }
    },
    setup(props) {
        const page = usePage()
        const adsterraConfig = ref(props.adsterraConfig)
        const adsterraLoaded = ref(false)
        const error = ref(null)
        const loadTime = ref(0)
        const errorCount = ref(0)
        const showConsentBanner = ref(false)
        const retryCount = ref(0)

        const shouldLoadAds = computed(() => {
            return adsterraConfig.value.enabled &&
                   props.showOnPage &&
                   !error.value
        })

        const shouldShowFallback = computed(() => {
            return error.value &&
                   adsterraConfig.value.fallback?.enabled &&
                   adsterraConfig.value.fallback?.show_fallback
        })

        const needsConsent = computed(() => {
            return adsterraConfig.value.privacy?.gdpr_enabled &&
                   !props.consentData.cookie_consent
        })

        // Initialize Adsterra
        const initializeAdsterra = () => {
            if (!adsterraConfig.value.enabled || !props.showOnPage) {
                return
            }

            // Check consent requirements
            if (needsConsent.value) {
                showConsentBanner.value = true
                return
            }

            // Load Adsterra script
            loadAdsterra()
        }

        // Load Adsterra script with error handling and retries
        const loadAdsterra = async () => {
            if (adsterraLoaded.value) return

            const scriptUrl = adsterraConfig.value.script_url

            if (!scriptUrl) {
                handleError('Adsterra Script URL not configured')
                return
            }

            try {
                const startTime = performance.now()
                
                await loadScript(scriptUrl)
                
                const endTime = performance.now()
                loadTime.value = Math.round(endTime - startTime)
                
                adsterraLoaded.value = true
                error.value = null
                
                // Log success
                if (adsterraConfig.value.debug) {
                    console.log('Adsterra: Script loaded successfully', {
                        loadTime: loadTime.value,
                        retryCount: retryCount.value
                    })
                }
                
                // Report success to backend
                reportAdEvent('script_loaded', { load_time: loadTime.value })
                
            } catch (err) {
                handleError(`Failed to load Adsterra script: ${err.message}`)
                
                // Retry logic
                if (retryCount.value < (adsterraConfig.value.performance?.retry_attempts || 3)) {
                    retryCount.value++
                    const delay = adsterraConfig.value.performance?.retry_delay || 1000
                    
                    setTimeout(() => {
                        loadAdsterra()
                    }, delay)
                }
            }
        }

        // Load script with Promise wrapper
        const loadScript = (url) => {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script')
                script.type = 'text/javascript'
                script.src = url
                script.async = adsterraConfig.value.performance?.async_loading !== false
                script.defer = true
                script.setAttribute('data-adsterra-publisher', adsterraConfig.value.publisher_id)
                script.setAttribute('data-adsterra-timestamp', Date.now())

                // Add security attributes
                if (adsterraConfig.value.security?.csp_enabled) {
                    script.crossOrigin = 'anonymous'
                }

                script.onload = () => {
                    resolve()
                }

                script.onerror = () => {
                    reject(new Error('Script loading failed'))
                }

                // Set timeout
                const timeout = adsterraConfig.value.performance?.timeout || 5000
                const timeoutId = setTimeout(() => {
                    reject(new Error('Script loading timeout'))
                }, timeout)

                script.onload = () => {
                    clearTimeout(timeoutId)
                    resolve()
                }

                document.head.appendChild(script)
            })
        }

        // Handle errors
        const handleError = (errorMessage) => {
            error.value = errorMessage
            errorCount.value++
            
            if (adsterraConfig.value.debug) {
                console.error('Adsterra:', errorMessage)
            }
            
            // Report error to backend
            reportAdEvent('error', { error: errorMessage, retry_count: retryCount.value })
        }

        // Report events to backend
        const reportAdEvent = async (eventType, data = {}) => {
            try {
                await fetch('/api/adsterra/track', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({
                        event_type: eventType,
                        data: data,
                        timestamp: Date.now()
                    })
                })
            } catch (err) {
                if (adsterraConfig.value.debug) {
                    console.warn('Failed to report ad event:', err)
                }
            }
        }

        // Consent handling
        const acceptConsent = () => {
            showConsentBanner.value = false
            
            // Set consent cookie
            document.cookie = `adsterra_consent=true; path=/; max-age=${365 * 24 * 60 * 60}` // 1 year
            
            // Load ads
            loadAdsterra()
        }

        const rejectConsent = () => {
            showConsentBanner.value = false
            
            // Set rejection cookie
            document.cookie = `adsterra_consent=false; path=/; max-age=${365 * 24 * 60 * 60}` // 1 year
        }

        // Watch for config changes
        watch(() => props.adsterraConfig, (newConfig) => {
            adsterraConfig.value = newConfig
            if (newConfig.enabled && props.showOnPage) {
                initializeAdsterra()
            }
        }, { immediate: true })

        // Initialize on mount
        onMounted(() => {
            initializeAdsterra()
        })

        // Clean up on unmount
        onMounted(() => {
            // Clean up any timers or event listeners if needed
            return () => {
                // Cleanup logic here
            }
        })

        return {
            adsterraConfig,
            shouldLoadAds,
            shouldShowFallback,
            adsterraLoaded,
            error,
            loadTime,
            errorCount,
            showConsentBanner,
            acceptConsent,
            rejectConsent,
        }
    }
}
</script>

<style scoped>
.adsterra-manager {
    /* Base styles for Adsterra manager */
    position: relative;
    z-index: 1;
}

.adsterra-fallback {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 1rem;
    margin: 0.5rem 0;
}

/* Consent banner styling */
.fixed {
    box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Debug info styling */
.debug-info {
    font-family: monospace;
    font-size: 0.75rem;
    line-height: 1.2;
}

/* Error display styling */
.error-display {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Responsive design */
@media (max-width: 768px) {
    .fixed {
        padding: 0.75rem;
    }
    
    .fixed .container {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .fixed .text-sm {
        font-size: 0.875rem;
    }
}
</style> 