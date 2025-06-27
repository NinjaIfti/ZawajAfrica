<script setup>
    import { Link, router } from '@inertiajs/vue3';
    import { ref, computed } from 'vue';
    import TierBadge from '@/Components/TierBadge.vue';

    const props = defineProps({
        user: Object,
    });

    const showTherapistDropdown = ref(false);

    // Compute user tier
    const userTier = computed(() => {
        const user = props.user;
        if (!user) return 'free';

        // Check if user has an active subscription
        if (user.subscription_status === 'active' && user.subscription_plan) {
            // Check if subscription hasn't expired
            if (!user.subscription_expires_at || new Date(user.subscription_expires_at) > new Date()) {
                return user.subscription_plan.toLowerCase();
            }
        }

        return 'free';
    });

    // Custom logout function
    const logout = () => {
        router.post(
            route('logout'),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Refresh CSRF token after logout
                    window.refreshCSRFToken();
                    // Redirect to login page
                    window.location.href = route('login');
                },
            }
        );
    };

    const toggleTherapistDropdown = () => {
        showTherapistDropdown.value = !showTherapistDropdown.value;
    };
</script>

<template>
    <!-- Left Sidebar -->
    <div class="fixed left-0 top-0 w-64 bg-white shadow-md h-screen overflow-y-auto flex flex-col z-40">
        <!-- Logo -->
        <div class="p-4 mb-6">
            <div>
                <img src="/images/dash.png" alt="ZawajAfrica" class="h-10" />
            </div>
        </div>

        <!-- Navigation -->
        <nav class="space-y-2 px-3 flex-grow">
            <Link
                :href="route('dashboard')"
                class="flex items-center rounded-lg px-4 py-3 text-base font-medium"
                :class="route().current('dashboard') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'"
            >
                <svg
                    class="mr-3 h-6 w-6"
                    :class="route().current('dashboard') ? 'text-white' : 'text-gray-500'"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                    />
                </svg>
                <span>Dashboard</span>
            </Link>

            <!-- Therapist Section with Dropdown -->
            <div class="relative">
                <button
                    @click="toggleTherapistDropdown"
                    class="w-full flex items-center justify-between rounded-lg px-4 py-3 text-base font-medium"
                    :class="
                        route().current('therapists.*') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'
                    "
                >
                    <div class="flex items-center">
                        <svg
                            class="mr-3 h-6 w-6"
                            :class="route().current('therapists.*') ? 'text-white' : 'text-gray-500'"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span>Therapists</span>
                    </div>
                    <svg
                        class="h-4 w-4 transition-transform duration-200"
                        :class="showTherapistDropdown ? 'rotate-180' : ''"
                        :style="route().current('therapists.*') ? 'color: white' : 'color: #6b7280'"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div v-show="showTherapistDropdown" class="mt-1 ml-6 space-y-1">
                    <Link
                        :href="route('therapists.index')"
                        class="flex items-center rounded-lg px-4 py-2 text-sm font-medium"
                        :class="
                            route().current('therapists.index')
                                ? 'bg-purple-100 text-purple-600'
                                : 'text-gray-600 hover:bg-gray-50'
                        "
                    >
                        <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <span>Find Therapist</span>
                    </Link>

                    <Link
                        :href="route('therapists.bookings')"
                        class="flex items-center rounded-lg px-4 py-2 text-sm font-medium"
                        :class="
                            route().current('therapists.bookings')
                                ? 'bg-purple-100 text-purple-600'
                                : 'text-gray-600 hover:bg-gray-50'
                        "
                    >
                        <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                            />
                        </svg>
                        <span>Booking Details</span>
                    </Link>
                </div>
            </div>

            <Link
                :href="route('messages')"
                class="flex items-center rounded-lg px-4 py-3 text-base font-medium"
                :class="route().current('messages') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'"
            >
                <svg
                    class="mr-3 h-6 w-6"
                    :class="route().current('messages') ? 'text-white' : 'text-gray-500'"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
                    />
                </svg>
                <span>Messages</span>
            </Link>

            <Link
                :href="route('chatbot.index')"
                class="flex items-center rounded-lg px-4 py-3 text-base font-medium"
                :class="route().current('chatbot.*') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'"
            >
                <svg
                    class="mr-3 h-6 w-6"
                    :class="route().current('chatbot.*') ? 'text-white' : 'text-gray-500'"
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
                <span>AI Chatbot</span>
            </Link>

            <Link
                :href="route('kyc.index')"
                class="flex items-center rounded-lg px-4 py-3 text-base font-medium"
                :class="route().current('kyc.*') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'"
            >
                <svg
                    class="mr-3 h-6 w-6"
                    :class="route().current('kyc.*') ? 'text-white' : 'text-gray-500'"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                    />
                </svg>
                <span>KYC Verification</span>
                <!-- Add a small badge if KYC is not verified -->
                <span v-if="user && user.kyc_status !== 'verified'" class="ml-auto">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        !
                    </span>
                </span>
            </Link>

            <Link
                :href="route('notifications.index')"
                class="flex items-center rounded-lg px-4 py-3 text-base font-medium"
                :class="
                    route().current('notifications.*') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'
                "
            >
                <svg
                    class="mr-3 h-6 w-6"
                    :class="route().current('notifications.*') ? 'text-white' : 'text-gray-500'"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                    />
                </svg>
                <span>Notifications</span>
            </Link>
            <br />
            <hr class="border-gray-400" />
            <Link
                :href="route('me.profile')"
                class="flex items-center justify-between rounded-lg px-4 py-3 text-base font-medium"
                :class="route().current('me.*') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'"
            >
                <div class="flex items-center">
                    <svg
                        class="mr-3 h-6 w-6"
                        :class="route().current('me.*') ? 'text-white' : 'text-gray-500'"
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
                    <span>My Profile</span>
                </div>
                <TierBadge :tier="userTier" size="xs" />
            </Link>

            <Link
                :href="route('subscription.index')"
                class="flex items-center rounded-lg px-4 py-3 text-base font-medium"
                :class="
                    route().current('subscription.index')
                        ? 'bg-purple-600 text-white'
                        : 'text-gray-700 hover:bg-gray-50'
                "
            >
                <svg
                    class="mr-3 h-6 w-6"
                    :class="route().current('subscription.index') ? 'text-white' : 'text-gray-500'"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                    />
                </svg>
                <span>Subscription Plans</span>
            </Link>

            <button
                @click="logout"
                class="w-full flex items-center rounded-lg px-4 py-3 text-base font-medium text-red-600 hover:bg-gray-50"
            >
                <svg class="mr-3 h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                    />
                </svg>
                <span>Logout</span>
            </button>
        </nav>

        <!-- ZawajAfrica Social Media Links -->
        <div class="p-4 border-t border-gray-200">
            <p class="text-sm text-gray-600 mb-3 font-medium">Follow ZawajAfrica</p>
            <div class="flex justify-center space-x-4">
                <!-- WhatsApp -->
                <a 
                    href="https://wa.me/qr/DQN37GVLFCV6J1" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    class="text-green-600 hover:text-green-800 transition-colors"
                    title="WhatsApp"
                >
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.787"/>
                    </svg>
                </a>

                <!-- Instagram -->
                <a 
                    href="https://www.instagram.com/zawajafrica?igsh=anQydWcxMTAzbGNi" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    class="text-pink-600 hover:text-pink-800 transition-colors"
                    title="Instagram"
                >
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987c6.62 0 11.987-5.367 11.987-11.987C24.004 5.367 18.637.001 12.017.001zM8.449 16.988c-1.297 0-2.348-1.051-2.348-2.348s1.051-2.348 2.348-2.348c1.297 0 2.348 1.051 2.348 2.348S9.746 16.988 8.449 16.988zM12.017 7.129c-2.79 0-5.043 2.254-5.043 5.043s2.254 5.043 5.043 5.043s5.043-2.254 5.043-5.043S14.807 7.129 12.017 7.129zM18.408 6.034c-.424 0-.766-.343-.766-.766s.343-.766.766-.766s.766.343.766.766S18.831 6.034 18.408 6.034z"/>
                        <path d="M12 7.377a4.623 4.623 0 100 9.246 4.623 4.623 0 000-9.246zm0 7.627a3.004 3.004 0 110-6.008 3.004 3.004 0 010 6.008z"/>
                        <path d="M17.884 6.136a.766.766 0 11-1.532 0 .766.766 0 011.532 0zM12 4.056c-1.297 0-1.458.006-1.967.038-.509.033-.856.147-1.159.314a2.34 2.34 0 00-.847.551c-.24.24-.416.516-.551.847-.167.303-.281.65-.314 1.159-.032.509-.038.67-.038 1.967v.068c0 1.297.006 1.458.038 1.967.033.509.147.856.314 1.159.135.331.311.607.551.847.24.24.516.416.847.551.303.167.65.281 1.159.314.509.032.67.038 1.967.038s1.458-.006 1.967-.038c.509-.033.856-.147 1.159-.314.331-.135.607-.311.847-.551.24-.24.416-.516.551-.847.167-.303.281-.65.314-1.159.032-.509.038-.67.038-1.967s-.006-1.458-.038-1.967c-.033-.509-.147-.856-.314-1.159a2.34 2.34 0 00-.551-.847 2.34 2.34 0 00-.847-.551c-.303-.167-.65-.281-1.159-.314-.509-.032-.67-.038-1.967-.038H12zm0 1.441c1.234 0 1.381.005 1.867.034.451.021.696.095.858.158.216.084.37.184.532.346.162.162.262.316.346.532.063.162.137.407.158.858.029.486.034.633.034 1.867s-.005 1.381-.034 1.867c-.021.451-.095.696-.158.858-.084.216-.184.37-.346.532a1.435 1.435 0 01-.532.346c-.162.063-.407.137-.858.158-.486.029-.633.034-1.867.034s-1.381-.005-1.867-.034c-.451-.021-.696-.095-.858-.158a1.435 1.435 0 01-.532-.346 1.435 1.435 0 01-.346-.532c-.063-.162-.137-.407-.158-.858-.029-.486-.034-.633-.034-1.867s.005-1.381.034-1.867c.021-.451.095-.696.158-.858.084-.216.184-.37.346-.532.162-.162.316-.262.532-.346.162-.063.407-.137.858-.158.486-.029.633-.034 1.867-.034z"/>
                    </svg>
                </a>

                <!-- YouTube -->
                <a 
                    href="https://youtube.com/@zawajafrica?si=NhmdSvd0p1ClpEQ9" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    class="text-red-600 hover:text-red-800 transition-colors"
                    title="YouTube"
                >
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Upgrade Membership - Only show if user doesn't have active subscription -->
        <div v-if="!user.subscription_status || user.subscription_status !== 'active'" class="p-4 w-full">
            <Link :href="route('subscription.index')" class="block">
                <div
                    class="rounded-lg bg-purple-700 p-4 text-center text-white relative overflow-hidden cursor-pointer hover:bg-purple-800 transition-colors"
                >
                    <!-- Diagonal gradient strips -->
                    <div
                        class="absolute -top-8 right-10 w-6 h-48 bg-gradient-to-b from-purple-700 via-purple-500 to-purple-700 transform rotate-45"
                    ></div>
                    <div
                        class="absolute -top-16 -right-2 w-6 h-48 bg-gradient-to-b from-purple-700 via-purple-500 to-purple-700 transform rotate-45"
                    ></div>
                    <div
                        class="absolute -top-12 right-24 w-6 h-48 bg-gradient-to-b from-purple-700 via-purple-500 to-purple-700 transform rotate-45"
                    ></div>

                    <!-- Side strip images -->
                    <img src="/images/member/side1.png" alt="Side Decoration" class="absolute left-0 top-0 h-full" />
                    <img src="/images/member/side2.png" alt="Side Decoration" class="absolute right-0 top-0 h-full" />

                    <!-- Circle element -->
                    <div class="absolute bottom-4 right-8 w-5 h-5 border border-green-800 rounded-full"></div>

                    <!-- Content -->
                    <div class="relative z-10 flex flex-col items-center">
                        <!-- Icon in orange circle -->
                        <div class="mb-3 w-9 h-9 rounded-xl flex items-center justify-center">
                            <img src="/images/member/lock.png" alt="Membership Lock" class="w-8 h-8" />
                        </div>

                        <p class="mt-1 text-s font-bold text-gray-200">Upgrade Membership</p>

                        <!-- Button -->
                        <div class="mt-6 flex items-center justify-center">
                            <span class="font-medium">Upgrade Now</span>
                            <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </Link>
        </div>
    </div>
</template>

<style scoped>
    /* Add some transitions for smooth opening/closing */
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
