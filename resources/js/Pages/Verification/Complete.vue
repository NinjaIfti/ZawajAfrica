<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

</script>

<template>
    <Head title="Verification Completed" />

    <div class="flex min-h-screen items-center justify-center bg-white">
        <div class="w-full max-w-[600px] rounded-xl bg-white p-8 text-center">
            <!-- Purple Checkmark Icon -->
            <div class="mb-6 flex justify-center">
                <div class="h-20 w-20 rounded-full bg-purple-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-purple-700" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>

            <h1 class="text-xl font-bold text-black mb-4">Your identification verification is completed.</h1>
            
            <p class="mx-auto max-w-md text-center text-gray-800 mb-8">
                Your identification verification is completed successfully. You can now start using all features of the app.
            </p>

            <Link 
                :href="route('dashboard')" 
                class="block w-full rounded-lg bg-purple-700 py-3 text-white transition duration-150 hover:bg-purple-800"
            >
                Find your Match
            </Link>
        </div>
    </div>
</template> 