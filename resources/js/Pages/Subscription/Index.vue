<script setup>
    import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
    import { ref, computed, onMounted } from 'vue';
    import Sidebar from '@/Components/Sidebar.vue';
    import AppHeader from '@/Components/AppHeader.vue';
    import PaymentSuccessModal from '@/Components/PaymentSuccessModal.vue';
    import axios from 'axios';

    const props = defineProps({
        user: Object,
        plans: Object,
        userGender: String,
        paystackPublicKey: String,
        currentSubscription: Object,
    });

    const page = usePage();
    const showPaymentSuccessModal = ref(false);

    // Mobile menu state
    const isMobileMenuOpen = ref(false);
    const isProcessingPayment = ref(false);

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
        agreed_to_terms: false,
    });

    // Terms agreement state for each plan
    const termsAgreed = ref({});

    // Selected gender for plans
    const selectedGender = ref(props.userGender || 'male');

    // Get plans for the selected gender
    const genderPlans = computed(() => {
        return props.plans[selectedGender.value] || props.plans['male'];
    });

    // Define plan hierarchy for upgrade/downgrade logic
    const planHierarchy = {
        Basic: 1,
        Gold: 2,
        Platinum: 3,
    };

    // Helper function to get plan status
    const getPlanStatus = planName => {
        const currentPlan = props.currentSubscription?.plan;
        const currentStatus = props.currentSubscription?.status;

        // If no active subscription, all plans are available for purchase
        if (!currentPlan || currentStatus !== 'active') {
            return 'purchase';
        }

        // If this is the current plan (case-insensitive comparison)
        if (currentPlan.toLowerCase() === planName.toLowerCase()) {
            return 'current';
        }

        // Compare plan levels for upgrade/downgrade (handle both cases)
        const currentLevel = planHierarchy[currentPlan] || planHierarchy[currentPlan.charAt(0).toUpperCase() + currentPlan.slice(1).toLowerCase()] || 0;
        const targetLevel = planHierarchy[planName] || 0;

        if (targetLevel > currentLevel) {
            return 'upgrade';
        } else {
            return 'downgrade';
        }
    };

    // Helper function to get button text
    const getButtonText = planName => {
        const status = getPlanStatus(planName);
        const plan = genderPlans.value.find(p => p.name === planName);

        switch (status) {
            case 'current':
                return 'Current Plan';
            case 'upgrade':
                return `Upgrade to ${planName}`;
            case 'downgrade':
                return `Switch to ${planName}`;
            default:
                return `${planName} for $${plan?.price_usd} / ₦${plan?.price_naira}`;
        }
    };

    // Helper function to check if button should be disabled
    const isButtonDisabled = planName => {
        const status = getPlanStatus(planName);
        return status === 'current' || isProcessingPayment.value || !termsAgreed.value[planName];
    };

    // Function to select a plan and initiate payment
    const selectPlan = async planName => {
        // Prevent purchasing the current plan
        if (getPlanStatus(planName) === 'current') {
            return;
        }

        if (!termsAgreed.value[planName]) {
            alert('Please agree to the Terms of Use and Privacy Statement before proceeding.');
            return;
        }

        form.plan = planName;
        form.agreed_to_terms = termsAgreed.value[planName];
        isProcessingPayment.value = true;

        try {
            // Initialize payment with backend
            const response = await axios.post(route('payment.subscription.initialize'), {
                plan: planName,
                agreed_to_terms: form.agreed_to_terms,
            });

            if (response.data.status) {
                // Store payment reference and timestamp in localStorage for fallback
                localStorage.setItem('zawaj_payment_reference', response.data.reference);
                localStorage.setItem('zawaj_payment_timestamp', Date.now().toString());
                localStorage.setItem('zawaj_payment_plan', planName);
                
                // Redirect to Paystack payment page
                window.location.href = response.data.authorization_url;
            } else {
                alert('Payment initialization failed: ' + response.data.message);
            }
        } catch (error) {
            console.error('Payment error:', error);
            if (error.response && error.response.status === 422) {
                alert('Please check that you\'ve agreed to the terms and try again.');
            } else {
                alert('An error occurred while initializing payment. Please try again.');
            }
        } finally {
            isProcessingPayment.value = false;
        }
    };

    // Check payment status when user returns
    const checkPaymentStatus = async () => {
        const paymentRef = localStorage.getItem('zawaj_payment_reference');
        const paymentTimestamp = localStorage.getItem('zawaj_payment_timestamp');
        
        if (paymentRef && paymentTimestamp) {
            const timeDiff = Date.now() - parseInt(paymentTimestamp);
            // If payment was initiated less than 30 minutes ago
            if (timeDiff < 30 * 60 * 1000) {
                try {
                    // Check if user's subscription was updated
                    const userResponse = await axios.get('/api/user');
                    if (userResponse.data.subscription_status === 'active') {
                        // Payment was successful
                        localStorage.removeItem('zawaj_payment_reference');
                        localStorage.removeItem('zawaj_payment_timestamp');
                        localStorage.removeItem('zawaj_payment_plan');
                        
                        showPaymentSuccessModal.value = true;
                        
                        // Refresh the page to show updated subscription
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                    }
                } catch (error) {
                    console.error('Error checking payment status:', error);
                }
            } else {
                // Clean up old payment references
                localStorage.removeItem('zawaj_payment_reference');
                localStorage.removeItem('zawaj_payment_timestamp');
                localStorage.removeItem('zawaj_payment_plan');
            }
        }
    };

    // Legacy function for non-Paystack flow (kept for fallback)
    const submit = () => {
        form.post(route('subscription.purchase'), {
            onSuccess: () => {
                form.reset();
            },
        });
    };

    // Function to refresh subscription status
    const refreshSubscriptionStatus = async () => {
        try {
            const response = await axios.get('/api/user/subscription');
            if (response.data) {
                // Update the subscription data and reload the page to reflect changes
                window.location.reload();
            }
        } catch (error) {
            console.error('Failed to refresh subscription status:', error);
        }
    };

    // Check for payment success on page load
    onMounted(() => {
        if (page.props.flash?.payment_success) {
            showPaymentSuccessModal.value = true;
            // Clean up payment references on successful payment
            localStorage.removeItem('zawaj_payment_reference');
            localStorage.removeItem('zawaj_payment_timestamp');
            localStorage.removeItem('zawaj_payment_plan');
        } else {
            // Check for pending payments when user returns
            setTimeout(checkPaymentStatus, 1000);
        }
    });

    const closePaymentSuccessModal = () => {
        showPaymentSuccessModal.value = false;
    };
</script>

<template>
    <Head title="Subscription Plans" />

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100 relative">
        <!-- Mobile header with hamburger menu - Only visible on mobile -->
        <div class="fixed top-0 left-0 right-0 z-50 bg-purple-600 shadow-md p-4 flex items-center justify-between md:hidden">
            <div class="flex items-center">
                <button @click="toggleMobileMenu" class="mobile-menu-toggle p-1 mr-3" aria-label="Toggle menu">
                    <svg
                        class="h-6 w-6 text-white"
                        :class="{ hidden: isMobileMenuOpen }"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg
                        class="h-6 w-6 text-white"
                        :class="{ hidden: !isMobileMenuOpen }"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <h1 class="text-lg text-white font-bold">Subscription Plans</h1>
            </div>
            
            <!-- Mobile Refresh Button -->
            <button
                @click="refreshSubscriptionStatus"
                class="px-2 py-1 text-xs bg-white/20 text-white rounded hover:bg-white/30 transition-colors"
                title="Refresh subscription status"
            >
                🔄
            </button>
        </div>

        <!-- Mobile Menu Overlay -->
        <div
            v-if="isMobileMenuOpen"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
            @click="toggleMobileMenu"
        ></div>

        <!-- Left Sidebar Component - Fixed position -->
        <aside
            class="mobile-menu fixed inset-y-0 left-0 w-64 transform transition-transform duration-300 ease-in-out z-50 md:translate-x-0"
            :class="{ 'translate-x-0': isMobileMenuOpen, '-translate-x-full': !isMobileMenuOpen }"
        >
            <Sidebar :user="user" />
        </aside>

        <!-- Main Content - Add left margin on desktop to account for fixed sidebar -->
        <div class="flex-1 px-4 py-4 md:p-8 mt-16 md:mt-0 md:ml-64">
            <!-- Header with language selector and profile dropdown -->
            <div class="hidden md:block mb-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold">Subscription Plans</h1>
                    <div class="flex items-center space-x-4">
                        <!-- Refresh Subscription Status Button -->
                        <button
                            @click="refreshSubscriptionStatus"
                            class="px-3 py-1 text-sm bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 transition-colors"
                            title="Refresh subscription status"
                        >
                            🔄 Refresh Status
                        </button>
                        
                        <div class="flex items-center space-x-2">
                            <span class="text-gray-700">English</span>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 text-gray-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-gray-300 overflow-hidden">
                            <img
                                :src="user.profile_photo || '/images/placeholder.jpg'"
                                alt="Profile"
                                class="h-full w-full object-cover"
                            />
                        </div>
                    </div>
                </div>
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
                                        <svg
                                            v-if="featureIndex === 0"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                            />
                                        </svg>
                                        <svg
                                            v-else-if="featureIndex === 1"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                        <svg
                                            v-else-if="featureIndex === 2"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            />
                                        </svg>
                                        <svg
                                            v-else
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </span>

                                    <!-- Feature text -->
                                    <span>{{ feature }}</span>

                                    <!-- Checkmark icon -->
                                    <svg
                                        class="ml-auto h-5 w-5 text-green-500"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                </li>
                            </ul>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mt-6 flex items-start">
                            <input
                                type="checkbox"
                                :id="`terms-${plan.name}-${index}`"
                                v-model="termsAgreed[plan.name]"
                                class="mt-1 h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                            />
                            <label :for="`terms-${plan.name}-${index}`" class="ml-2 block text-sm text-gray-700">
                                Yes, I agree to the
                                <Link href="#" class="text-purple-600 underline">Terms of Use</Link>
                                and
                                <Link href="#" class="text-purple-600 underline">Privacy Statement</Link>
                            </label>
                        </div>

                        <!-- Subscribe button -->
                        <button
                            @click="selectPlan(plan.name)"
                            :disabled="isButtonDisabled(plan.name)"
                            class="mt-6 w-full py-3 px-4 rounded-md font-medium focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors duration-200"
                            :class="{
                                'bg-green-600 text-white cursor-default': getPlanStatus(plan.name) === 'current',
                                'bg-purple-600 hover:bg-purple-700 text-white':
                                    getPlanStatus(plan.name) !== 'current' &&
                                    !isProcessingPayment &&
                                    termsAgreed[plan.name],
                                'bg-gray-400 cursor-not-allowed text-white':
                                    isProcessingPayment ||
                                    (!termsAgreed[plan.name] && getPlanStatus(plan.name) !== 'current'),
                            }"
                        >
                            <span v-if="isProcessingPayment" class="flex items-center justify-center">
                                <svg
                                    class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Processing...
                            </span>
                            <span
                                v-else-if="getPlanStatus(plan.name) === 'current'"
                                class="flex items-center justify-center"
                            >
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Current Plan
                            </span>
                            <span v-else>
                                {{ getButtonText(plan.name) }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Success Modal -->
        <PaymentSuccessModal
            :show="showPaymentSuccessModal"
            :payment-type="page.props.flash?.payment_type || 'subscription'"
            @close="closePaymentSuccessModal"
        />
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
