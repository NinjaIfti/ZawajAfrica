<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import AppHeader from '@/Components/AppHeader.vue';

const props = defineProps({
    user: Object,
    plans: Object,
    userGender: String
});

// Mobile menu state
const isMobileMenuOpen = ref(false);

// Toggle mobile menu
const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
    
    // Prevent body scrolling when menu is open
    if (isMobileMenuOpen.value) {
        document.body.classList.add('overflow-hidden');
    } else {
        document.body.classList.remove('overflow-hidden');
    }
};

// Form for subscription purchase
const form = useForm({
    plan: '',
    agreed_to_terms: false
});

// Selected gender for plans
const selectedGender = ref(props.userGender || 'male');

// Get plans for the selected gender
const genderPlans = computed(() => {
    return props.plans[selectedGender.value] || props.plans['male'];
});

// Function to select a plan
const selectPlan = (planName) => {
    form.plan = planName;
};

// Function to submit the form
const submit = () => {
    form.post(route('subscription.purchase'), {
        onSuccess: () => {
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Subscription Plans" />

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100 relative">
        <!-- Mobile header with hamburger menu - Only visible on mobile -->
        <div class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md p-4 flex items-center md:hidden">
            <button 
                @click="toggleMobileMenu" 
                class="mobile-menu-toggle p-1 mr-3"
                aria-label="Toggle menu"
            >
                <svg 
                    class="h-6 w-6 text-gray-700" 
                    :class="{ 'hidden': isMobileMenuOpen }"
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg 
                    class="h-6 w-6 text-gray-700" 
                    :class="{ 'hidden': !isMobileMenuOpen }"
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            <h1 class="text-lg font-bold">Subscription Plans</h1>
        </div>

        <!-- Mobile Menu Overlay -->
        <div 
            v-if="isMobileMenuOpen" 
            class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
            @click="toggleMobileMenu"
        ></div>

        <!-- Left Sidebar Component - Slides in from left on mobile -->
        <aside 
            class="mobile-menu fixed inset-y-0 left-0 w-64 transform transition-transform duration-300 ease-in-out z-50 md:relative md:z-0 md:translate-x-0"
            :class="{'translate-x-0': isMobileMenuOpen, '-translate-x-full': !isMobileMenuOpen}"
        >
            <Sidebar :user="user" />
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 px-4 py-4 md:p-8 mt-16 md:mt-0">
            <!-- Header with language selector and profile dropdown -->
            <div class="hidden md:block mb-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold">Subscription Plans</h1>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-gray-700">English</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-gray-300 overflow-hidden">
                            <img :src="user.profile_photo || '/images/placeholder.jpg'" alt="Profile" class="h-full w-full object-cover" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab for gender selection -->
            <div class="bg-gray-100 rounded-lg p-2 inline-flex mb-6">
                <button 
                    @click="selectedGender = 'male'" 
                    class="px-4 py-2 rounded-md text-sm font-medium"
                    :class="selectedGender === 'male' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-600 hover:bg-gray-200'"
                >
                    Packages for Males
                </button>
                <button 
                    @click="selectedGender = 'female'" 
                    class="px-4 py-2 rounded-md text-sm font-medium"
                    :class="selectedGender === 'female' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-600 hover:bg-gray-200'"
                >
                    Packages for Females
                </button>
            </div>

            <!-- Subscription Plans Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div 
                    v-for="(plan, index) in genderPlans" 
                    :key="index" 
                    class="bg-white rounded-lg shadow-md overflow-hidden"
                >
                    <!-- Plan Name -->
                    <div class="p-6">
                        <h2 class="text-xl font-medium text-purple-600">{{ plan.name }}</h2>
                        
                        <!-- Price -->
                        <div class="mt-4">
                            <p class="text-3xl font-bold">${{ plan.price_usd }} / {{ plan.price_naira }} naira</p>
                            <p class="text-gray-600">Per Month</p>
                        </div>
                        
                        <!-- Features -->
                        <div class="mt-6">
                            <h3 class="font-medium mb-4">Features Included:</h3>
                            <ul class="space-y-3">
                                <li 
                                    v-for="(feature, featureIndex) in plan.features" 
                                    :key="featureIndex"
                                    class="flex items-center"
                                >
                                    <!-- Feature icon (placeholder) -->
                                    <span class="mr-3 text-amber-500">
                                        <svg v-if="featureIndex === 0" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                        <svg v-else-if="featureIndex === 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        <svg v-else-if="featureIndex === 2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                                    
                                    <!-- Feature text -->
                                    <span>{{ feature }}</span>
                                    
                                    <!-- Checkmark icon -->
                                    <svg class="ml-auto h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Terms and Conditions -->
                        <div class="mt-6 flex items-start">
                            <input 
                                type="checkbox" 
                                :id="`terms-${index}`" 
                                v-model="form.agreed_to_terms" 
                                class="mt-1 h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                            >
                            <label :for="`terms-${index}`" class="ml-2 block text-sm text-gray-700">
                                Yes, I agree to the 
                                <Link href="#" class="text-purple-600 underline">Terms of Use</Link>
                                and
                                <Link href="#" class="text-purple-600 underline">Privacy Statement</Link>
                            </label>
                        </div>
                        
                        <!-- Subscribe button -->
                        <button 
                            @click="selectPlan(plan.name)"
                            class="mt-6 w-full bg-purple-600 py-3 px-4 rounded-md text-white font-medium hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2"
                        >
                            {{ plan.name }} for ${{ plan.price_usd }} / {{ plan.price_naira }} naira
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Ensure proper stacking on mobile */
@media (max-width: 768px) {
    .min-h-screen {
        padding-top: 1rem;
    }
}

/* Prevent scrolling when mobile menu is open */
:global(.overflow-hidden) {
    overflow: hidden;
}

/* Transition for mobile menu */
.translate-x-0 {
    transform: translateX(0);
}

.-translate-x-full {
    transform: translateX(-100%);
}

@media (min-width: 768px) {
    .md\:translate-x-0 {
        transform: translateX(0) !important;
    }
}
</style> 