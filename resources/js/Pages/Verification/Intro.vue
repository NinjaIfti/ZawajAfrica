<script setup>
    import { Head, Link, router } from '@inertiajs/vue3';
    import { ref } from 'vue';

    const skipVerification = () => {
        router.visit(route('dashboard'));
    };
</script>

<template>
    <Head title="Verify Your Identity" />

    <div class="flex min-h-screen items-center justify-center bg-white px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-[600px] rounded-xl bg-white p-4 sm:p-6 lg:p-8">
            <div
                class="mb-8 sm:mb-10 lg:mb-12 flex flex-col items-center justify-center space-y-4 sm:space-y-5 lg:space-y-6"
            >
                <!-- ID Card Icon -->
                <div class="mb-2">
                    <svg
                        class="w-20 h-20 sm:w-24 sm:h-24 lg:w-[120px] lg:h-[120px]"
                        viewBox="0 0 120 120"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M90 20H30C25.8579 20 22.5 23.3579 22.5 27.5V92.5C22.5 96.6421 25.8579 100 30 100H90C94.1421 100 97.5 96.6421 97.5 92.5V27.5C97.5 23.3579 94.1421 20 90 20Z"
                            fill="#E6A157"
                        />
                        <circle cx="45" cy="50" r="12.5" fill="white" />
                        <rect x="65" y="37.5" width="20" height="5" rx="2.5" fill="white" />
                        <rect x="65" y="50" width="20" height="5" rx="2.5" fill="white" />
                        <rect x="65" y="62.5" width="20" height="5" rx="2.5" fill="white" />
                        <rect x="35" y="75" width="50" height="5" rx="2.5" fill="white" />
                        <rect x="35" y="87.5" width="35" height="5" rx="2.5" fill="white" />
                    </svg>
                </div>

                <h1 class="text-xl sm:text-2xl lg:text-2xl font-bold text-black text-center">Verify Your Identity</h1>

                <p class="mx-auto max-w-md text-center text-gray-800 text-sm sm:text-base lg:text-base px-2 sm:px-0">
                    You can verify your identity with a valid government-issued ID for enhanced trust and credibility on your profile.
                </p>

               

                <p class="text-center text-xs sm:text-sm lg:text-sm text-red-600 px-2 sm:px-0">
                    All your data will not be shared with anyone. It's just for verification purpose.
                </p>
            </div>

            <div class="flex flex-col space-y-3 sm:space-y-4 lg:space-y-4">
                <button
                    @click="skipVerification"
                    class="flex w-full items-center justify-center rounded-lg bg-purple-700 py-2.5 sm:py-3 lg:py-3 text-white transition duration-150 hover:bg-purple-800 text-sm sm:text-base lg:text-base font-medium"
                >
                    Continue to Dashboard
                </button>

                <Link
                    :href="route('verification.document-type')"
                    class="flex w-full items-center justify-center rounded-lg border border-purple-700 py-2.5 sm:py-3 lg:py-3 text-purple-700 transition duration-150 hover:bg-purple-50 text-sm sm:text-base lg:text-base font-medium"
                >
                    Verify My Identity 
                </Link>
            </div>
        </div>
    </div>
</template>
