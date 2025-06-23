<script setup>
    import { ref, computed, watch } from 'vue';

    const props = defineProps({
        show: Boolean,
        userTier: {
            type: String,
            default: 'free',
        },
        currentFilters: {
            type: Object,
            default: () => ({}),
        },
    });

    const emit = defineEmits(['close', 'apply-filters', 'clear-filters']);

    // Filter state
    const filters = ref({
        age_min: props.currentFilters.age_min || 18,
        age_max: props.currentFilters.age_max || 50,
        location: props.currentFilters.location || '',
        marital_status: props.currentFilters.marital_status || '',
        religion: props.currentFilters.religion || '',
        education_level: props.currentFilters.education_level || '',
        occupation: props.currentFilters.occupation || '',
        income_range: props.currentFilters.income_range || '',
        height_min: props.currentFilters.height_min || 150,
        height_max: props.currentFilters.height_max || 190,
        ethnicity: props.currentFilters.ethnicity || '',
        smoking: props.currentFilters.smoking || '',
        drinking: props.currentFilters.drinking || '',
        elite_only: props.currentFilters.elite_only || false,
    });

    // Advanced filter sections state
    const appearanceExpanded = ref(false);
    const lifestyleExpanded = ref(false);
    const backgroundExpanded = ref(false);

    // Check if user can access advanced filters
    const canAccessAdvancedFilters = computed(() => {
        return ['gold', 'platinum'].includes(props.userTier);
    });

    const canAccessPlatinumFilters = computed(() => {
        return props.userTier === 'platinum';
    });

    // Apply filters
    const applyFilters = () => {
        const activeFilters = {};

        // Only include non-empty filters
        Object.keys(filters.value).forEach(key => {
            const value = filters.value[key];
            if (value !== '' && value !== null && value !== undefined) {
                if (typeof value === 'boolean' && value) {
                    activeFilters[key] = value;
                } else if (typeof value !== 'boolean') {
                    activeFilters[key] = value;
                }
            }
        });

        emit('apply-filters', activeFilters);
        emit('close');
    };

    // Clear all filters
    const clearAll = () => {
        filters.value = {
            age_min: 18,
            age_max: 50,
            location: '',
            marital_status: '',
            religion: '',
            education_level: '',
            occupation: '',
            income_range: '',
            height_min: 150,
            height_max: 190,
            ethnicity: '',
            smoking: '',
            drinking: '',
            elite_only: false,
        };
        emit('clear-filters');
    };

    // Close modal
    const closeModal = () => {
        emit('close');
    };

    // Watch for prop changes
    watch(
        () => props.currentFilters,
        newFilters => {
            Object.assign(filters.value, newFilters);
        },
        { deep: true }
    );
</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg max-w-md w-full max-h-[80vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Filters</h3>
                <button @click="closeModal" class="p-1 hover:bg-gray-100 rounded">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        ></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-4 space-y-6">
                <!-- Basic Info Section -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Basic Info</h4>

                    <!-- Age Range -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">Age</span>
                            <span class="text-sm text-gray-800">{{ filters.age_min }}-{{ filters.age_max }}</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <input
                                v-model="filters.age_min"
                                type="range"
                                min="18"
                                max="80"
                                class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                            />
                            <input
                                v-model="filters.age_max"
                                type="range"
                                min="18"
                                max="80"
                                class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                            />
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">Living in</span>
                        </div>
                        <input
                            v-model="filters.location"
                            type="text"
                            placeholder="Any location"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                        />
                    </div>

                    <!-- Marital Status -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">Marital Status</span>
                        </div>
                        <select
                            v-model="filters.marital_status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                        >
                            <option value="">Any</option>
                            <option value="single">Single</option>
                            <option value="divorced">Divorced</option>
                            <option value="widowed">Widowed</option>
                        </select>
                    </div>

                    <!-- Religion -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">Religion</span>
                        </div>
                        <select
                            v-model="filters.religion"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                        >
                            <option value="">Any</option>
                            <option value="islam">Islam</option>
                            <option value="christianity">Christianity</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Advanced Filters Section -->
                <div v-if="canAccessAdvancedFilters">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Advanced Filters</h4>

                    <!-- Background/Cultural Values -->
                    <div class="mb-4">
                        <button
                            @click="backgroundExpanded = !backgroundExpanded"
                            class="w-full flex items-center justify-between py-2 text-left"
                        >
                            <span class="text-sm text-gray-600">Their Background/Cultural Values</span>
                            <svg
                                class="w-4 h-4 transition-transform"
                                :class="backgroundExpanded ? 'rotate-180' : ''"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                ></path>
                            </svg>
                        </button>

                        <div v-if="backgroundExpanded" class="mt-2 space-y-3">
                            <!-- Education Level -->
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Education Level</label>
                                <select
                                    v-model="filters.education_level"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                >
                                    <option value="">Any</option>
                                    <option value="high_school">High School</option>
                                    <option value="diploma">Diploma</option>
                                    <option value="bachelor">Bachelor's Degree</option>
                                    <option value="master">Master's Degree</option>
                                    <option value="phd">PhD</option>
                                </select>
                            </div>

                            <!-- Occupation -->
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Occupation</label>
                                <input
                                    v-model="filters.occupation"
                                    type="text"
                                    placeholder="Any occupation"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                />
                            </div>

                            <!-- Income Range -->
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Income Range</label>
                                <select
                                    v-model="filters.income_range"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                >
                                    <option value="">Any</option>
                                    <option value="under_50k">Under ₦50,000</option>
                                    <option value="50k_100k">₦50,000 - ₦100,000</option>
                                    <option value="100k_200k">₦100,000 - ₦200,000</option>
                                    <option value="over_200k">Over ₦200,000</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Appearance (Platinum Only) -->
                    <div v-if="canAccessPlatinumFilters" class="mb-4">
                        <button
                            @click="appearanceExpanded = !appearanceExpanded"
                            class="w-full flex items-center justify-between py-2 text-left"
                        >
                            <span class="text-sm text-gray-600">Their Appearance</span>
                            <svg
                                class="w-4 h-4 transition-transform"
                                :class="appearanceExpanded ? 'rotate-180' : ''"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                ></path>
                            </svg>
                        </button>

                        <div v-if="appearanceExpanded" class="mt-2 space-y-3">
                            <!-- Height Range -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="text-xs text-gray-500">Height (cm)</label>
                                    <span class="text-xs text-gray-800">
                                        {{ filters.height_min }}-{{ filters.height_max }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input
                                        v-model="filters.height_min"
                                        type="range"
                                        min="140"
                                        max="220"
                                        class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                    />
                                    <input
                                        v-model="filters.height_max"
                                        type="range"
                                        min="140"
                                        max="220"
                                        class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                    />
                                </div>
                            </div>

                            <!-- Ethnicity -->
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Ethnicity</label>
                                <select
                                    v-model="filters.ethnicity"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                >
                                    <option value="">Any</option>
                                    <option value="african">African</option>
                                    <option value="arab">Arab</option>
                                    <option value="asian">Asian</option>
                                    <option value="european">European</option>
                                    <option value="mixed">Mixed</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Lifestyle (Platinum Only) -->
                    <div v-if="canAccessPlatinumFilters" class="mb-4">
                        <button
                            @click="lifestyleExpanded = !lifestyleExpanded"
                            class="w-full flex items-center justify-between py-2 text-left"
                        >
                            <span class="text-sm text-gray-600">Their Lifestyle</span>
                            <svg
                                class="w-4 h-4 transition-transform"
                                :class="lifestyleExpanded ? 'rotate-180' : ''"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                ></path>
                            </svg>
                        </button>

                        <div v-if="lifestyleExpanded" class="mt-2 space-y-3">
                            <!-- Smoking -->
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Smoking</label>
                                <select
                                    v-model="filters.smoking"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                >
                                    <option value="">Any</option>
                                    <option value="never">Never</option>
                                    <option value="occasionally">Occasionally</option>
                                    <option value="regularly">Regularly</option>
                                </select>
                            </div>

                            <!-- Drinking -->
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Drinking</label>
                                <select
                                    v-model="filters.drinking"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                >
                                    <option value="">Any</option>
                                    <option value="never">Never</option>
                                    <option value="occasionally">Occasionally</option>
                                    <option value="socially">Socially</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Elite Only (Platinum Only) -->
                    <div v-if="canAccessPlatinumFilters" class="mb-4">
                        <label class="flex items-center">
                            <input
                                v-model="filters.elite_only"
                                type="checkbox"
                                class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                            />
                            <span class="ml-2 text-sm text-gray-600">Platinum Members Only</span>
                        </label>
                    </div>
                </div>

                <!-- Upgrade prompt for free/basic users -->
                <div v-if="!canAccessAdvancedFilters" class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h5 class="text-sm font-medium text-purple-800 mb-1">Unlock Advanced Filters</h5>
                    <p class="text-xs text-purple-600 mb-2">
                        Upgrade to Gold or Platinum to access more detailed search options.
                    </p>
                    <button class="text-xs text-purple-700 underline">Upgrade Now</button>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t border-gray-200 flex space-x-3">
                <button
                    @click="clearAll"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500"
                >
                    Clear All
                </button>
                <button
                    @click="applyFilters"
                    class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
                >
                    Apply Filters
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
    /* Custom range slider styling */
    input[type='range']::-webkit-slider-thumb {
        appearance: none;
        height: 16px;
        width: 16px;
        border-radius: 50%;
        background: #9333ea;
        cursor: pointer;
    }

    input[type='range']::-moz-range-thumb {
        height: 16px;
        width: 16px;
        border-radius: 50%;
        background: #9333ea;
        cursor: pointer;
        border: none;
    }
</style>
