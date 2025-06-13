<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Verification Pending" />

    <div class="flex min-h-screen items-center justify-center bg-white">
        <div class="w-full max-w-[600px] rounded-xl bg-white p-8 text-center">
            <!-- Pending Icon -->
            <div class="mb-6 flex justify-center">
                <div class="h-20 w-20 rounded-full bg-amber-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <h1 class="text-xl font-bold text-black mb-4">Your verification is pending approval</h1>
            
            <p class="mx-auto max-w-md text-center text-gray-800 mb-8">
                Your documents have been submitted successfully and are currently being reviewed by our team. This process typically takes 24-48 hours. You'll be notified once your verification is approved.
            </p>

            <div class="flex flex-col space-y-4">
                <Link 
                    :href="route('verification.intro')" 
                    class="block w-full rounded-lg bg-purple-700 py-3 text-white transition duration-150 hover:bg-purple-800"
                >
                    Submit Different Documents
                </Link>
                
                <Link 
                    :href="route('logout')" 
                    method="post" 
                    as="button"
                    class="text-purple-700 text-center font-medium hover:underline"
                >
                    Logout
                </Link>
            </div>
        </div>
    </div>
</template> 