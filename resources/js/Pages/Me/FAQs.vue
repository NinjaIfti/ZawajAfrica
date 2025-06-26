<script setup>
    import { Head, Link } from '@inertiajs/vue3';
    import { ref, onMounted, onUnmounted } from 'vue';
    import Sidebar from '@/Components/Sidebar.vue';
    import ProfileHeader from '@/Components/ProfileHeader.vue';

    const props = defineProps({
        auth: Object,
        user: Object,
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

    // Hardcoded FAQ data
    const generalFaqs = ref([
        {
            id: 1,
            question: 'What is ZawajAfrica?',
            answer: 'ZawajAfrica is a premium, faith-based matchmaking platform designed exclusively for African Muslims in Africa and the diaspora. Our purpose is to help you meet compatible individuals for halal, purpose-driven marriages, connecting users based on shared values and clear intentions.',
        },
        {
            id: 2,
            question: 'What is ZawajAfrica\'s vision and mission?',
            answer: 'Our Vision is to be the most trusted, faith-based, Africa-centric matchmaking platform globally. Our Mission is to guide African Muslims into meaningful unions through safe, verified, value-oriented processes rooted in Islamic principles.',
        },
        {
            id: 3,
            question: 'Is ZawajAfrica only for people living in Africa?',
            answer: 'No, ZawajAfrica caters to African Muslims both in Africa and in the diaspora, helping to connect our community globally.',
        },
        {
            id: 4,
            question: 'How does ZawajAfrica ensure a halal matchmaking process?',
            answer: 'We\'re committed to Islamic principles by focusing on compatibility, shared values, and clear intentions for marriage. Our platform encourages respectful communication and provides features that support a wholesome and guided search for a spouse.',
        },
    ]);

    const accountFaqs = ref([
        {
            id: 5,
            question: 'What are the different user tiers available on ZawajAfrica?',
            answer: 'We offer four user tiers to suit various needs: Free Tier (₦0 / $0): Limited profile views, ads, no messaging unless contacted by a paid user. Basic Tier (₦8,000 / $10): Increased profile views and messaging limits. Gold Tier (₦15,000 / $15): Unlimited profile views and higher messaging limits. Platinum Tier (₦25,000 / $25): Unlimited messaging, full access to all users (including elites), and advanced match filters.',
        },
        {
            id: 6,
            question: 'What benefits do I get with the Free Tier?',
            answer: 'With the Free Tier, you can view up to 50 profiles per day. Please note that messaging is only available if a paid user contacts you first, and you cannot upload or display contact information. Free-to-Free messaging isn\'t supported and will prompt an upgrade.',
        },
        {
            id: 7,
            question: 'How can I send messages to other users?',
            answer: 'Messaging capabilities depend on your user tier. Paid users (Basic, Gold, Platinum) have messaging allowances. Free Tier users can only message if contacted by a paid user.',
        },
        {
            id: 8,
            question: 'What is the \'Profile Assistant\' feature?',
            answer: 'Our Profile Assistant is available for paid users. If you\'re finding it difficult to fill out your profile, the assistant will ask you questions in your preferred language to help you structure and complete your profile effectively. Our advanced AI bot can even assist in answering profile questions in almost all Native African languages, making the process smoother for you.',
        },
        {
            id: 9,
            question: 'How do I know if a user is verified?',
            answer: 'Users who have completed our ID verification process (via Monnify in Nigeria) will display a ✅ badge on their profile, indicating they\'re verified.',
        },
        {
            id: 10,
            question: 'Are there any special offers for new users?',
            answer: 'Yes! The first 70 users to sign up for ZawajAfrica will automatically receive a Platinum Tier membership for one month as an early access bonus. You\'ll be notified clearly about this offer upon registration.',
        },
    ]);

    const paymentFaqs = ref([
        {
            id: 11,
            question: 'What payment methods are accepted on ZawajAfrica?',
            answer: 'We accept all major debit and credit cards. We also accept all currencies via Paystack for international payments and Monnify for Nigerian payments.',
        },
        {
            id: 12,
            question: 'Is my personal information and payment data secure?',
            answer: 'Yes, we prioritize your security. We use reputable payment gateways (Paystack, Monnify) to handle transactions, and your personal information is managed with strict privacy protocols.',
        },
        {
            id: 13,
            question: 'How does ZawajAfrica verify user identities?',
            answer: 'For users in Nigeria, we utilize Monnify for ID verification, which includes BVN, NIN, International Passports, or Driver\'s Licenses. This adds an extra layer of trust to our community.',
        },
    ]);

    const supportFaqs = ref([
        {
            id: 14,
            question: 'What is the \'Therapy Section\' and how can I book a session?',
            answer: 'Our Therapy Section offers professional guidance with Dr. Aisha (Marriage Counselor) and Dr. Ummie (Life Coach/Personal Growth). Each session costs ₦50,000 / $50. You can book a session directly through our platform, powered by Google Calendar.',
        },
        {
            id: 15,
            question: 'When should I consider booking a therapy session?',
            answer: 'We encourage you to consider therapy if you feel confused, sad, lost, or simply wish to gain more clarity and support in your personal growth or relationship journey. It\'s open to all users.',
        },
        {
            id: 16,
            question: 'What happens if I encounter a technical issue with the app?',
            answer: 'If you experience any technical issues, please report them through the app. Our tech support team will log the details and address your request promptly.',
        },
        {
            id: 17,
            question: 'How does ZawajAfrica handle inappropriate behavior or language?',
            answer: 'We have strict moderation rules. Users are warned for sexual, abusive, or disrespectful language. Red-flag conversations may be flagged for admin review. Repeated violations can lead to account suspension.',
        },
    ]);

    const otherFaqs = ref([
        {
            id: 18,
            question: 'Does ZawajAfrica offer gift suggestions or a gift shop?',
            answer: 'Yes! We have TheJannahKabeer Store, which offers a variety of thoughtful gifts, including clothing, perfumes, veils, bags, jewelry, and bridal items, with global delivery. We might suggest gifts for special occasions like birthdays. You can visit the store at www.thejannahkabeer.com.ng.',
        },
        {
            id: 19,
            question: 'How will ZawajAfrica communicate important updates or promotions with me?',
            answer: 'We use ZohoMail to send various communications, including welcome emails, OTPs, birthday reminders, upgrade confirmations, promotions, and seasonal campaigns. Please ensure your email address is up-to-date.',
        },
        {
            id: 20,
            question: 'How can I reach out for any problems, comments, or observations?',
            answer: 'You can reach us via email at: support@zawajafrica.com.ng or admin@zawajafrica.com.ng',
        },
        {
            id: 21,
            question: 'Can your AI bot answer questions beyond the FAQs?',
            answer: 'Absolutely! Our AI bot is ready to answer all the FAQs and more. If you have any further questions or need additional assistance, our intelligent assistant is here to help guide you.',
        },
    ]);
</script>

<template>
    <Head title="My FAQs" />

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100 relative">
        <!-- Mobile header with hamburger menu - Only visible on mobile -->
        <div class="fixed top-0 left-0 right-0 z-50 bg-purple-600 shadow-md p-4 flex items-center md:hidden">
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
            <h1 class="text-lg text-white font-bold">FAQs</h1>
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
            <Sidebar :user="$page.props.auth.user" />
        </aside>

        <!-- Main Content - Add left margin on desktop to account for fixed sidebar -->
        <div class="flex-1 px-4 py-4 md:p-8 mt-16 md:mt-0 md:ml-64">
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
                    <!-- Header -->
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold mb-2">Frequently Asked Questions (FAQs) for ZawajAfrica</h2>
                        <p class="text-gray-600">Welcome to ZawajAfrica! We're here to help you navigate your journey to a meaningful and halal union. Below are answers to some common questions you might have about our platform.</p>
                    </div>

                    <!-- General Questions Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-xl font-semibold mb-6">General Questions</h3>
                        <div class="space-y-6">
                            <div v-for="faq in generalFaqs" :key="faq.id" class="space-y-2">
                                <div class="font-medium text-gray-800">Q{{ faq.id }}: {{ faq.question }}</div>
                                <div class="text-gray-600">A{{ faq.id }}: {{ faq.answer }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Account & Features Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-xl font-semibold mb-6">Account & Features</h3>
                        <div class="space-y-6">
                            <div v-for="faq in accountFaqs" :key="faq.id" class="space-y-2">
                                <div class="font-medium text-gray-800">Q{{ faq.id }}: {{ faq.question }}</div>
                                <div class="text-gray-600">A{{ faq.id }}: {{ faq.answer }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Payments & Security Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-xl font-semibold mb-6">Payments & Security</h3>
                        <div class="space-y-6">
                            <div v-for="faq in paymentFaqs" :key="faq.id" class="space-y-2">
                                <div class="font-medium text-gray-800">Q{{ faq.id }}: {{ faq.question }}</div>
                                <div class="text-gray-600">A{{ faq.id }}: {{ faq.answer }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Support & Well-being Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-xl font-semibold mb-6">Support & Well-being</h3>
                        <div class="space-y-6">
                            <div v-for="faq in supportFaqs" :key="faq.id" class="space-y-2">
                                <div class="font-medium text-gray-800">Q{{ faq.id }}: {{ faq.question }}</div>
                                <div class="text-gray-600">A{{ faq.id }}: {{ faq.answer }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Other Questions Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-xl font-semibold mb-6">Other Questions</h3>
                        <div class="space-y-6">
                            <div v-for="faq in otherFaqs" :key="faq.id" class="space-y-2">
                                <div class="font-medium text-gray-800">Q{{ faq.id }}: {{ faq.question }}</div>
                                <div class="text-gray-600">A{{ faq.id }}: {{ faq.answer }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Message -->
                    <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-6 text-center">
                        <p class="text-gray-700 font-medium">If you have any other questions not covered here, please don't hesitate to reach out to us.</p>
                        <p class="text-gray-600 mt-2">Salam alaikum!</p>
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
