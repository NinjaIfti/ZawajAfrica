<script setup>
    import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Verification Submitted" />

    <div class="flex min-h-screen items-center justify-center bg-white px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-[600px] rounded-xl bg-white p-4 sm:p-6 lg:p-8 text-center">
            <!-- Success Icon -->
            <div class="mb-6 flex justify-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <h1 class="mb-4 text-2xl font-bold text-gray-900">Verification Documents Submitted!</h1>
            
            <p class="mb-6 text-gray-600">
                Thank you for submitting your verification documents. Our admin team will review them and get back to you.
            </p>

            <div class="mb-6 rounded-lg bg-blue-50 border border-blue-200 p-4">
                <p class="text-sm text-blue-700">
                    <strong>Important:</strong> You can continue using all features of our platform while your verification is being reviewed. 
                    Verification is optional and doesn't restrict your access to the site.
                </p>
            </div>

            <div class="space-y-3">
                <Link
                    :href="route('dashboard')"
                    class="block w-full rounded-lg bg-purple-700 py-3 text-white font-medium hover:bg-purple-800 transition"
                >
                    Go to Dashboard
                </Link>
                
                <Link
                    :href="route('verification.pending')"
                    class="block w-full rounded-lg border border-gray-300 py-3 text-gray-700 font-medium hover:bg-gray-50 transition"
                >
                    Check Verification Status
                </Link>
            </div>
        </div>
    </div>
</template>
