<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const skipVerification = () => {
    router.visit(route('dashboard'));
};
</script>

<template>
    <Head title="Verify Your Identity" />

    <div class="flex min-h-screen items-center justify-center bg-white">
        <div class="w-full max-w-[600px] rounded-xl bg-white p-8">
            <div class="mb-12 flex flex-col items-center justify-center space-y-6">
                <!-- ID Card Icon -->
                <div class="mb-2">
                    <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M90 20H30C25.8579 20 22.5 23.3579 22.5 27.5V92.5C22.5 96.6421 25.8579 100 30 100H90C94.1421 100 97.5 96.6421 97.5 92.5V27.5C97.5 23.3579 94.1421 20 90 20Z" fill="#E6A157"/>
                        <circle cx="45" cy="50" r="12.5" fill="white"/>
                        <rect x="65" y="37.5" width="20" height="5" rx="2.5" fill="white"/>
                        <rect x="65" y="50" width="20" height="5" rx="2.5" fill="white"/>
                        <rect x="65" y="62.5" width="20" height="5" rx="2.5" fill="white"/>
                        <rect x="35" y="75" width="50" height="5" rx="2.5" fill="white"/>
                        <rect x="35" y="87.5" width="35" height="5" rx="2.5" fill="white"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-black text-center">Verify Your Identity</h1>
                
                <p class="mx-auto max-w-md text-center text-gray-800">
                    Confirm your identity with a valid government-issued ID to continue.
                </p>

                <p class="text-center text-sm text-red-600">
                    All your data will not be shared with anyone. It's just for verification purpose.
                </p>
            </div>

            <div class="flex flex-col space-y-4">
                <Link 
                    :href="route('verification.document-type')" 
                    class="flex w-full items-center justify-center rounded-lg bg-purple-700 py-3 text-white transition duration-150 hover:bg-purple-800"
                >
                    Start Verification
                </Link>

                <button
                    @click="skipVerification"
                    class="text-purple-700 text-center font-medium hover:underline"
                >
                    I will do it later, <span class="text-purple-700 font-bold">Skip Now</span>
                </button>
            </div>
        </div>
    </div>
</template> 