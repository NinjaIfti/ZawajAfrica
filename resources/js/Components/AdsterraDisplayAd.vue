<template>
    <div v-if="shouldShow" class="adsterra-display-ad my-4" :class="className">
        <!-- Adsterra Banner Ad -->
        <div class="bg-gray-100 border border-gray-200 rounded-lg p-4 text-center relative">
            <div class="text-xs text-gray-500 mb-2">Advertisement</div>
            
            <!-- Loading State -->
            <div v-if="isLoading" class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-600"></div>
                <span class="ml-2 text-sm text-gray-600">Loading ad...</span>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="py-8 text-center">
                <div class="text-red-500 text-sm mb-2">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    {{ error }}
                </div>
                <button 
                    v-if="canRetry" 
                    @click="retryLoad" 
                    class="text-blue-500 hover:text-blue-700 text-sm underline"
                >
                    Try again
                </button>
            </div>

            <!-- Ad Container -->
            <div v-else ref="adContainer" :id="adElementId" class="adsterra-ad-container min-h-[100px]">
                <!-- Adsterra ad will be loaded here -->
            </div>

            <!-- Fallback Content -->
            <div v-if="shouldShowFallback" class="py-8 text-center">
                <div class="text-gray-500 text-sm">
                    {{ fallbackMessage }}
                </div>
            </div>

            <!-- Debug Info -->
            <div v-if="debug && adsterraConfig?.debug" class="absolute top-1 right-1 text-xs text-gray-400">
                {{ zoneName }} | {{ loadTime }}ms
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, computed, onUnmounted, watch, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'

export default {
    name: 'AdsterraDisplayAd',
    props: {
        zoneName: {
            type: String,
            default: 'banner'
        },
        className: {
            type: String,
            default: ''
        },
        placement: {
            type: String,
            default: 'content'
        },
        lazy: {
            type: Boolean,
            default: true
        },
        debug: {
            type: Boolean,
            default: false
        }
    },
    setup(props) {
        const page = usePage()
        const adLoaded = ref(false)
        const isLoading = ref(false)
        const error = ref(null)
        const loadTime = ref(0)
        const retryCount = ref(0)
        const adContainer = ref(null)
        const intersectionObserver = ref(null)
        const adElementId = `adsterra-${props.zoneName}-${Math.random().toString(36).substr(2, 9)}`

        const adsterraConfig = computed(() => page.props.adsterra?.config || {})
        const maxRetries = computed(() => adsterraConfig.value.performance?.retry_attempts || 3)
        const canRetry = computed(() => retryCount.value < maxRetries.value)
        const fallbackMessage = computed(() => adsterraConfig.value.fallback?.fallback_message || 'Advertisement unavailable')

        const shouldShow = computed(() => {
            // Check global Adsterra config first
            if (!adsterraConfig.value?.enabled) {
                return false
            }

            // Check if ads should show on this page
            const showOnPage = page.props.adsterra?.show_on_page
            if (!showOnPage) {
                return false
            }

            // Check if this placement is enabled
            if (!isPlacementEnabled.value) {
                return false
            }

            return true
        })

        const isPlacementEnabled = computed(() => {
            const display = adsterraConfig.value.display || {}
            
            switch (props.placement) {
                case 'sidebar':
                    return display.sidebar_ads_enabled !== false
                case 'dashboard_feed':
                    return display.dashboard_feed_enabled !== false
                case 'mobile':
                    return display.show_on_mobile !== false
                default:
                    return true
            }
        })

        const shouldShowFallback = computed(() => {
            return error.value && 
                   !canRetry.value && 
                   adsterraConfig.value.fallback?.enabled && 
                   adsterraConfig.value.fallback?.show_fallback
        })

        const loadAd = async () => {
            if (adLoaded.value || isLoading.value || !shouldShow.value) return

            isLoading.value = true
            error.value = null
            const startTime = performance.now()

            try {
                // Get zone configuration
                const zoneId = adsterraConfig.value.ad_zones?.[props.zoneName]
                if (!zoneId) {
                    throw new Error(`Ad zone '${props.zoneName}' not configured`)
                }

                // Wait for next tick to ensure DOM is ready
                await nextTick()

                // Initialize ad container
                const container = document.getElementById(adElementId)
                if (!container) {
                    throw new Error('Ad container not found')
                }

                // Create unique container ID for this ad
                const adContainerId = `adsterra-container-${zoneId}-${Date.now()}`
                
                // Set up the ad container
                container.innerHTML = `<div class="adsterra-ad" data-zone="${zoneId}" data-placement="${props.placement}" id="${adContainerId}"></div>`
                
                // Dynamically create and inject Adsterra script
                const script = document.createElement('script')
                script.type = 'text/javascript'
                script.async = true
                
                // Configure ad options based on zone type
                let adConfig = {
                    key: zoneId,
                    format: 'iframe',
                    params: {}
                }
                
                if (props.zoneName === 'banner' || props.zoneName === 'sidebar' || props.zoneName === 'feed') {
                    adConfig.height = 250
                    adConfig.width = 300
                } else if (props.zoneName === 'native') {
                    adConfig.height = 300
                    adConfig.width = 160
                } else {
                    // Default dimensions
                    adConfig.height = 200
                    adConfig.width = 300
                }
                
                // Set global options for Adsterra
                window.atOptions = adConfig
                
                // Load the Adsterra script
                script.src = `//www.highcpmrevenuegate.com/${zoneId}/invoke.js`
                
                // Add error handling
                script.onerror = () => {
                    console.warn(`Failed to load Adsterra script for zone ${props.zoneName}`)
                    // Show fallback content
                    const fallbackContainer = document.getElementById(adContainerId)
                    if (fallbackContainer) {
                        fallbackContainer.innerHTML = `
                            <div class="bg-gray-100 p-4 text-center rounded">
                                <div class="text-sm text-gray-600">Advertisement</div>
                                <div class="text-xs text-gray-500 mt-1">Zone: ${props.zoneName}</div>
                            </div>
                        `
                    }
                }
                
                document.head.appendChild(script)

                // Wait for ad to load
                await new Promise((resolve, reject) => {
                    const timeout = setTimeout(() => {
                        // Don't reject immediately, just resolve as ads might still load
                        console.warn(`Adsterra ad load timeout for zone ${props.zoneName}`)
                        resolve()
                    }, adsterraConfig.value.performance?.timeout || 8000)

                    // For now, just resolve after a short delay since we're using document.write
                    setTimeout(() => {
                        clearTimeout(timeout)
                        resolve()
                    }, 2000)
                })

                const endTime = performance.now()
                loadTime.value = Math.round(endTime - startTime)

                adLoaded.value = true
                
                // Report success
                await reportAdEvent('ad_loaded', {
                    zone_name: props.zoneName,
                    placement: props.placement,
                    load_time: loadTime.value
                })

            } catch (err) {
                error.value = err.message
                
                // Report error
                await reportAdEvent('ad_error', {
                    zone_name: props.zoneName,
                    placement: props.placement,
                    error: err.message,
                    retry_count: retryCount.value
                })

                console.error('Adsterra ad load failed:', err)
            } finally {
                isLoading.value = false
            }
        }

        const retryLoad = () => {
            retryCount.value++
            adLoaded.value = false
            loadAd()
        }

        const reportAdEvent = async (eventType, data = {}) => {
            try {
                await fetch('/api/adsterra/track', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
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

        const setupIntersectionObserver = () => {
            if (!props.lazy || !('IntersectionObserver' in window)) {
                loadAd()
                return
            }

            intersectionObserver.value = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting && !adLoaded.value) {
                            loadAd()
                            intersectionObserver.value?.disconnect()
                        }
                    })
                },
                { threshold: 0.1 }
            )

            if (adContainer.value) {
                intersectionObserver.value.observe(adContainer.value)
            }
        }

        // Watch for config changes
        watch(() => adsterraConfig.value, (newConfig) => {
            if (newConfig.enabled && shouldShow.value && !adLoaded.value) {
                setupIntersectionObserver()
            }
        }, { immediate: true })

        onMounted(() => {
            if (shouldShow.value) {
                nextTick(() => {
                    setupIntersectionObserver()
                })
            }
        })

        onUnmounted(() => {
            if (intersectionObserver.value) {
                intersectionObserver.value.disconnect()
            }
        })

        return {
            shouldShow,
            shouldShowFallback,
            adLoaded,
            isLoading,
            error,
            loadTime,
            canRetry,
            fallbackMessage,
            adsterraConfig,
            adContainer,
            adElementId,
            retryLoad,
            debug: props.debug
        }
    }
}
</script>

<style scoped>
.adsterra-display-ad {
    min-height: 120px;
    position: relative;
}

.adsterra-ad-container {
    min-height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

/* Loading animation */
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Error and retry styles */
.text-red-500 {
    color: #ef4444;
}

.text-blue-500 {
    color: #3b82f6;
}

.text-blue-700 {
    color: #1d4ed8;
}

.text-gray-500 {
    color: #6b7280;
}

.text-gray-600 {
    color: #4b5563;
}

/* Responsive design */
@media (max-width: 768px) {
    .adsterra-display-ad {
        min-height: 100px;
    }
    
    .adsterra-ad-container {
        min-height: 80px;
    }
}

/* Hover effects */
.adsterra-display-ad:hover {
    transform: translateY(-1px);
    transition: transform 0.2s ease;
}

/* Focus states for accessibility */
button:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Loading state animation */
.py-8 {
    padding-top: 2rem;
    padding-bottom: 2rem;
}

/* Fallback content styling */
.adsterra-fallback {
    background-color: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 0.5rem;
    padding: 1rem;
    text-align: center;
}

/* Debug info styling */
.absolute {
    position: absolute;
}

.top-1 {
    top: 0.25rem;
}

.right-1 {
    right: 0.25rem;
}

.text-xs {
    font-size: 0.75rem;
    line-height: 1rem;
}

.text-gray-400 {
    color: #9ca3af;
}
</style> 