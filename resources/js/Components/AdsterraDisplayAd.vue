<template>
    <!-- Debug info (only when debug prop is true) -->
    <div v-if="debug" class="text-xs bg-yellow-100 p-2 mb-2 rounded">
        <strong>Ad Debug ({{ zoneName }}):</strong>
        shouldShow: {{ shouldShow }}, 
        isLoading: {{ isLoading }}, 
        error: {{ error }}, 
        adLoaded: {{ adLoaded }},
        elementId: {{ adElementId }}
    </div>

    <div v-if="shouldShow && (isLoading || hasExternalContent)" class="adsterra-display-ad my-4" :class="className">
        <!-- Adsterra Banner Ad -->
        <div class="bg-gray-100 border border-gray-200 rounded-lg p-4 text-center relative">
            <div class="flex justify-between items-center mb-2">
                <div class="text-xs text-gray-500">Advertisement</div>
                <button 
                    @click="closeAd" 
                    class="text-gray-400 hover:text-gray-600 text-xs px-2 py-1 rounded hover:bg-gray-200 transition-colors"
                    title="Close ad"
                >
                    ✕
                </button>
            </div>
            
            <!-- Ad Container - Completely isolated from Vue reactivity -->
            <!-- Use v-show instead of v-if to avoid DOM removal issues -->
            <div v-show="!hasExternalContent" class="vue-managed-placeholder">
            <div v-if="isLoading" class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-600"></div>
                <span class="ml-2 text-sm text-gray-600">Loading ad...</span>
            </div>
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
                <div v-else class="text-xs text-gray-400 p-2">
                    Ready for ad...
                </div>
            </div>

            <!-- External ad container - completely separate from Vue DOM -->
            <div ref="adContainer" :id="adElementId" class="adsterra-ad-container min-h-[100px]" 
                 v-show="hasExternalContent" style="display: none;">
                <!-- External ad script will populate this -->
            </div>

            <!-- Debug: Show container content after load (outside ad container) -->
            <div v-if="debug" class="text-xs bg-blue-100 p-2 mt-2 rounded border">
                <div v-if="adLoaded">
                    <strong>Container Content:</strong>
                    <div class="mt-1 text-gray-600">{{ containerContent }}</div>
                    <div class="mt-1">
                        <strong>Child Elements:</strong> {{ childElementsCount }}
                    </div>
                </div>
                <div class="mt-2">
                    <button @click="retryLoad" class="text-xs bg-green-500 text-white px-2 py-1 rounded">
                        Retry Load
                    </button>
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
import { ref, onMounted, computed, onUnmounted, nextTick } from 'vue'
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
            default: false
        },
        debug: {
            type: Boolean,
            default: false
        }
    },
    setup(props) {
        const page = usePage()
        const adContainer = ref(null)
        const adLoaded = ref(false)
        const isLoading = ref(false)
        const error = ref(null)
        const loadTime = ref(0)
        const retryCount = ref(0)
        const adElementId = `adsterra-${props.zoneName}-${Math.random().toString(36).substr(2, 9)}`
        const containerContent = ref('')
        const childElementsCount = ref(0)
        const hasExternalContent = ref(false)
        let loadingTimeout = null
        let mutationObserver = null

        const adsterraConfig = computed(() => page.props.adsterra?.config || {})
        const maxRetries = computed(() => adsterraConfig.value.performance?.retry_attempts || 3)
        const canRetry = computed(() => retryCount.value < maxRetries.value)
        const fallbackMessage = computed(() => adsterraConfig.value.fallback?.fallback_message || 'Advertisement unavailable')

        const shouldShow = computed(() => {
            try {
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

                // Check if zone is configured
                const zoneId = adsterraConfig.value.ad_zones?.[props.zoneName]
                if (!zoneId) {
                    if (adsterraConfig.value.debug) {
                        console.warn(`Ad zone '${props.zoneName}' not configured`)
                    }
                    return false
                }

            return true
            } catch (err) {
                console.error('Error in shouldShow computed:', err)
                return false
            }
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



        const loadAd = async () => {
            if (adLoaded.value || isLoading.value || !shouldShow.value) {
                console.log('AdsterraDisplayAd: Skipping load', {
                    adLoaded: adLoaded.value,
                    isLoading: isLoading.value,
                    shouldShow: shouldShow.value,
                    zoneName: props.zoneName
                })
                return
            }

            isLoading.value = true
            error.value = null
            hasExternalContent.value = false
            const startTime = performance.now()
            
            // Clear any existing timeouts
            if (loadingTimeout) {
                clearTimeout(loadingTimeout)
            }
            
            // Set a timeout for ad loading
            loadingTimeout = setTimeout(() => {
                console.log(`AdsterraDisplayAd: Timeout fired for zone ${props.zoneName}`)
                console.log(`AdsterraDisplayAd: isLoading=${isLoading.value}, hasExternalContent=${hasExternalContent.value}`)
                
                if (isLoading.value && !hasExternalContent.value) {
                    console.warn(`AdsterraDisplayAd: No Adsterra ad loaded after timeout for zone ${props.zoneName} - hiding component`)
                    isLoading.value = false
                    adLoaded.value = false // Mark as failed, not loaded
                    // Don't show anything - just hide the component
                } else {
                    console.log(`AdsterraDisplayAd: Timeout fired but conditions not met for ${props.zoneName}`)
                }
            }, 8000) // 8 second timeout

            try {
                // Get zone configuration
                const zoneId = adsterraConfig.value.ad_zones?.[props.zoneName]
                if (!zoneId) {
                    throw new Error(`Ad zone '${props.zoneName}' not configured`)
                }

                // Debug logging
                console.log(`AdsterraDisplayAd: Loading ad for zone: ${props.zoneName}, ID: ${zoneId}`)

                // Wait for Vue to finish rendering
                await nextTick()

                // Use Vue ref to get container
                const container = adContainer.value
                if (!container) {
                    throw new Error('Ad container ref not available')
                }

                console.log('AdsterraDisplayAd: Container found via ref', container)

                // Set up the container for external ad script
                container.innerHTML = ''
                container.style.minHeight = '200px'
                container.style.textAlign = 'center'
                container.style.padding = '10px'
                container.id = zoneId // Set the container itself as the ad div
                
                console.log(`AdsterraDisplayAd: Prepared container with ID: ${zoneId}`)
                
                // Set up mutation observer to detect external content
                setupMutationObserver(container)
                
                // Configure ad options for Adsterra - this must be set before loading the script
                const adConfig = {
                    'key': zoneId,
                    'format': 'iframe',
                    'height': 250,
                    'width': 300,
                    'params': {}
                }
                
                // Set different dimensions based on zone type
                if (props.zoneName === 'sidebar') {
                    adConfig.height = 600
                    adConfig.width = 160
                } else if (props.zoneName === 'native') {
                    adConfig.height = 300
                    adConfig.width = 250
                } else if (props.zoneName === 'banner' || props.zoneName === 'feed') {
                    adConfig.height = 250
                    adConfig.width = 728
                }
                
                // Set global options for Adsterra BEFORE loading script
                window.atOptions = adConfig
                
                console.log('AdsterraDisplayAd: Set atOptions:', adConfig)
                console.log(`AdsterraDisplayAd: Starting to load script for zone ${props.zoneName}`)
                console.log(`AdsterraDisplayAd: Timeout set for 8000ms`)
                
                // Create and inject Adsterra script
                const script = document.createElement('script')
                script.type = 'text/javascript'
                script.async = true
                
                // Use the script URL from config or fallback to the zone-specific URL
                const baseScriptUrl = adsterraConfig.value.script_url || `//www.highcpmrevenuegate.com/${zoneId}/invoke.js`
                script.src = baseScriptUrl.includes(zoneId) ? baseScriptUrl : `//www.highcpmrevenuegate.com/${zoneId}/invoke.js`
                
                console.log(`AdsterraDisplayAd: Loading script from: ${script.src}`)
                
                // Add error handling
                script.onerror = (e) => {
                    console.error(`Failed to load Adsterra script for zone ${props.zoneName}:`, e)
                    console.error(`Script URL was: ${script.src}`)
                    if (container) {
                container.innerHTML = `
                            <div style="background: #fef2f2; padding: 16px; text-align: center; border-radius: 8px; border: 1px solid #fecaca;">
                                <div style="color: #dc2626; font-size: 14px;">Advertisement</div>
                                <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">Script failed to load</div>
                                <div style="color: #6b7280; font-size: 10px; margin-top: 4px;">${script.src}</div>
                    </div>
                `
                    }
                }
                
                                script.onload = () => {
                    console.log(`AdsterraDisplayAd: Script loaded successfully for zone ${props.zoneName}`)
                    console.log(`AdsterraDisplayAd: Script src was: ${script.src}`)
                }
                
                script.onerror = (e) => {
                    console.error(`AdsterraDisplayAd: Script failed to load for zone ${props.zoneName}:`, e)
                    console.error(`AdsterraDisplayAd: Failed script URL: ${script.src}`)
                    // Don't show fallback here, let the timeout handle it
                }
                
                // Remove any existing scripts with the same src to avoid duplicates
                const existingScript = document.querySelector(`script[src="${script.src}"]`)
                if (existingScript) {
                    console.log(`AdsterraDisplayAd: Removing existing script for ${props.zoneName}`)
                    existingScript.remove()
                }
                
                console.log(`AdsterraDisplayAd: Appending script to head for zone ${props.zoneName}`)
                document.head.appendChild(script)

                // Wait a short time for the script to execute
                await new Promise(resolve => setTimeout(resolve, 1500))

                const endTime = performance.now()
                loadTime.value = Math.round(endTime - startTime)

                // Check if external content was loaded by mutation observer
                if (!hasExternalContent.value) {
                    console.warn(`AdsterraDisplayAd: No external content detected after script load for zone ${props.zoneName}`)
                    // The timeout will handle showing fallback
                } else {
                    isLoading.value = false
                adLoaded.value = true
                    console.log(`AdsterraDisplayAd: External content detected for zone ${props.zoneName}`)
                }
                
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
                if (loadingTimeout) {
                    clearTimeout(loadingTimeout)
                    loadingTimeout = null
                }
                // Don't set isLoading to false here - let timeout or success handle it
            }
        }

        const setupMutationObserver = (container) => {
            if (mutationObserver) {
                mutationObserver.disconnect()
            }
            
            mutationObserver = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        // Check if meaningful content was added
                        const hasContent = container.innerHTML.length > 50 && 
                                         !container.innerHTML.includes('Advertisement Loading...')
                        
                        if (hasContent) {
                            console.log(`AdsterraDisplayAd: External content detected via mutation observer for zone ${props.zoneName}`)
                            console.log(`AdsterraDisplayAd: Content preview:`, container.innerHTML.substring(0, 100))
                            
                            // Clear loading state
                            isLoading.value = false
                            hasExternalContent.value = true
                            adLoaded.value = true
                            
                            // Clear any pending timeouts
                            if (loadingTimeout) {
                                clearTimeout(loadingTimeout)
                                loadingTimeout = null
                            }
                            
                            // Show the external container
                            container.style.display = 'block'
                            
                            console.log(`AdsterraDisplayAd: States after external content - isLoading: ${isLoading.value}, adLoaded: ${adLoaded.value}`)
                            
                            // Update debug info
                            containerContent.value = container.innerHTML.substring(0, 200) + (container.innerHTML.length > 200 ? '...' : '')
                            childElementsCount.value = container.children.length
                            
                            // Disconnect observer
                            mutationObserver.disconnect()
                            mutationObserver = null
                        }
                    }
                })
            })
            
            mutationObserver.observe(container, {
                childList: true,
                subtree: true,
                characterData: true
            })
        }



        const retryLoad = () => {
            retryCount.value++
            adLoaded.value = false
            hasExternalContent.value = false
            error.value = null
            if (mutationObserver) {
                mutationObserver.disconnect()
                mutationObserver = null
            }
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

        const closeAd = async () => {
            try {
                // Record that user closed the ad (triggers 2.5 minute delay)
                const response = await fetch('/api/adsterra/close', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        ad_type: 'general',
                        zone_name: props.zoneName
                    })
                })

                if (response.ok) {
                    const result = await response.json()
                    console.log(`Ad closed. Next ad available in ${result.delay_minutes} minutes`)
                    
                    // Hide the current ad
                    adLoaded.value = false
                    hasExternalContent.value = false
                    isLoading.value = false
                }
            } catch (err) {
                console.warn('Failed to record ad close:', err)
                // Still hide the ad even if API call fails
                adLoaded.value = false
                hasExternalContent.value = false
                isLoading.value = false
            }
        }

        onMounted(() => {
            if (shouldShow.value) {
                if (props.lazy) {
                    // For lazy loading, wait a bit more
                    setTimeout(() => loadAd(), 1000)
                } else {
                    // Load immediately after DOM is ready
                    setTimeout(() => loadAd(), 500)
                }
            }
        })

        onUnmounted(() => {
            // Cleanup
            if (loadingTimeout) {
                clearTimeout(loadingTimeout)
            }
            if (mutationObserver) {
                mutationObserver.disconnect()
            }
        })

        return {
            shouldShow,
            adContainer,
            adLoaded,
            isLoading,
            error,
            loadTime,
            canRetry,
            fallbackMessage,
            adsterraConfig,
            adElementId,
            containerContent,
            childElementsCount,
            hasExternalContent,
            retryLoad,
            closeAd,
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
    flex-direction: column;
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

/* Ad content styling */
.adsterra-ad-content {
    width: 100%;
    min-height: 90px;
}
</style> 