<script setup>
    import { Head, Link } from '@inertiajs/vue3';
    import { ref, onMounted, onUnmounted } from 'vue';
    import { router } from '@inertiajs/vue3';
    import Sidebar from '@/Components/Sidebar.vue';
    import ProfileHeader from '@/Components/ProfileHeader.vue';

    const props = defineProps({
        auth: Object,
        user: Object,
        faqs: Object, // FAQs data passed from the backend
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

    // Close mobile menu when clicking outside
    const closeMobileMenu = e => {
        if (isMobileMenuOpen.value && !e.target.closest('.mobile-menu') && !e.target.closest('.mobile-menu-toggle')) {
            isMobileMenuOpen.value = false;
            document.body.classList.remove('overflow-hidden');
        }
    };

    // Add click event listener when component is mounted
    onMounted(() => {
        document.addEventListener('click', closeMobileMenu);
    });

    // Remove event listener when component is unmounted
    onUnmounted(() => {
        document.removeEventListener('click', closeMobileMenu);
        document.body.classList.remove('overflow-hidden');
    });

    // Initialize FAQ categories from props or use defaults
    const generalFaqs = ref(
        props.faqs?.general || [
            {
                id: 1,
                question: 'What is ZawajAfrica?',
                answer: 'ZawajAfrica is the first halal matchmaking app designed for people of color, combining cultural sensitivity, exclusivity, and modern features to help you find meaningful connections.',
            },
            {
                id: 2,
                question: 'Who can join ZawajAfrica?',
                answer: 'ZawajAfrica is open to men, women, and elders who are serious about finding meaningful, halal relationships. Some features are tailored for specific user groups, such as privacy add-ons for young women and sponsor options for elder women.',
            },
        ]
    );

    const subscriptionFaqs = ref(
        props.faqs?.subscription || [
            {
                id: 1,
                question: 'What are the subscription plans available?',
                answer: 'We offer monthly, quarterly, and yearly subscription plans with discounts for longer commitments. Plans are tailored for men, elder women, and young women (privacy add-ons).',
            },
            {
                id: 2,
                question: 'How can I pay for a subscription?',
                answer: 'You can pay with any acceptable credit card.',
            },
        ]
    );

    // Function to request or refresh FAQ data
    function refreshFaqs() {
        router.get(
            route('me.faqs.index'),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                only: ['faqs'],
                onSuccess: page => {
                    if (page.props.faqs) {
                        if (page.props.faqs.general) {
                            generalFaqs.value = page.props.faqs.general;
                        }
                        if (page.props.faqs.subscription) {
                            subscriptionFaqs.value = page.props.faqs.subscription;
                        }
                    }
                },
            }
        );
    }
</script>

<template>
    <Head title="My FAQs" />

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100 relative">
        <!-- Mobile header with hamburger menu - Only visible on mobile -->
        <div class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md p-4 flex items-center md:hidden">
            <button @click="toggleMobileMenu" class="mobile-menu-toggle p-1 mr-3" aria-label="Toggle menu">
                <svg
                    class="h-6 w-6 text-gray-700"
                    :class="{ hidden: isMobileMenuOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg
                    class="h-6 w-6 text-gray-700"
                    :class="{ hidden: !isMobileMenuOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Page title on mobile -->
            <h1 class="text-lg font-bold">FAQs</h1>
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
            :class="{ 'translate-x-0': isMobileMenuOpen, '-translate-x-full': !isMobileMenuOpen }"
        >
            <Sidebar :user="$page.props.auth.user" />
        </aside>

        <!-- Main Content -->
        <div class="flex-1 px-4 py-4 md:p-8 mt-16 md:mt-0">
            <div class="container mx-auto max-w-6xl">
                <!-- Profile Header Component - Only visible on desktop -->
                <ProfileHeader :user="$page.props.auth.user" activeTab="faqs" class="hidden md:block" />

                <!-- Mobile Profile Navigation - Only visible on mobile -->
                <div class="md:hidden mb-4 overflow-x-auto">
                    <div class="flex rounded-lg shadow-sm">
                        <Link
                            :href="route('me.profile')"
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium text-gray-700"
                        >
                            <svg
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
                            Profile
                        </Link>

                        <Link
                            :href="route('me.photos')"
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium text-gray-700"
                        >
                            <svg
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
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                            Photos
                        </Link>

                        <Link
                            :href="route('me.hobbies')"
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium text-gray-700"
                        >
                            <svg
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
                                    d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            Hobbies
                        </Link>

                        <Link
                            :href="route('me.personality')"
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium text-gray-700"
                        >
                            <svg
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
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                                />
                            </svg>
                            Personality
                        </Link>

                        <Link
                            :href="route('me.faqs')"
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium bg-primary-dark text-white"
                        >
                            <svg
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
                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            FAQs
                        </Link>
                    </div>
                </div>

                <!-- FAQs Content -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold">FAQs</h2>

                        <!-- Refresh button -->
                        <button
                            @click="refreshFaqs"
                            class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 text-sm flex items-center"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 mr-1"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                />
                            </svg>
                            Refresh FAQs
                        </button>
                    </div>

                    <!-- General Questions Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-xl font-semibold mb-6">General Questions</h3>

                        <div class="space-y-6">
                            <div v-for="faq in generalFaqs" :key="faq.id" class="space-y-2">
                                <div class="font-medium">Q: {{ faq.question }}</div>
                                <div class="text-gray-600">A: {{ faq.answer }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription and Payment Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-xl font-semibold mb-6">Subscription and Payment</h3>

                        <div class="space-y-6">
                            <div v-for="faq in subscriptionFaqs" :key="faq.id" class="space-y-2">
                                <div class="font-medium">Q: {{ faq.question }}</div>
                                <div class="text-gray-600">A: {{ faq.answer }}</div>
                            </div>
                        </div>
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

    button {
        transition: all 0.2s;
    }
</style>
