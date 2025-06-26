<script setup>
    import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Verification Pending" />

    <div class="flex min-h-screen items-center justify-center bg-white px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-[600px] rounded-xl bg-white p-4 sm:p-6 lg:p-8 text-center">
            <!-- Pending Icon -->
            <div class="mb-4 sm:mb-5 lg:mb-6 flex justify-center">
                <div
                    class="h-16 w-16 sm:h-18 sm:w-18 lg:h-20 lg:w-20 rounded-full bg-amber-100 flex items-center justify-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-8 w-8 sm:h-9 sm:w-9 lg:h-10 lg:w-10 text-amber-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>
            </div>

            <h1 class="text-lg sm:text-xl lg:text-xl font-bold text-black mb-3 sm:mb-4 lg:mb-4">
                Your verification is pending approval
            </h1>

            <p
                class="mx-auto max-w-md text-center text-gray-800 mb-4 text-sm sm:text-base lg:text-base px-2 sm:px-0"
            >
                Your documents have been submitted successfully and are currently being reviewed by our team. This
                process typically takes 24-48 hours. You'll be notified once your verification is approved.
            </p>

            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4">
                <p class="text-sm text-green-700">
                    <strong>Great news!</strong> You can continue using all features of our platform while your verification is being reviewed. 
                    No need to wait - start exploring now!
                </p>
            </div>

            <div class="flex flex-col space-y-3 sm:space-y-4 lg:space-y-4">
                <Link
                    :href="route('dashboard')"
                    class="block w-full rounded-lg bg-purple-700 py-2.5 sm:py-3 lg:py-3 text-white transition duration-150 hover:bg-purple-800 text-sm sm:text-base lg:text-base font-medium"
                >
                    Continue to Dashboard
                </Link>

                <Link
                    :href="route('verification.intro')"
                    class="block w-full rounded-lg border border-gray-300 py-2.5 sm:py-3 lg:py-3 text-gray-700 transition duration-150 hover:bg-gray-50 text-sm sm:text-base lg:text-base font-medium"
                >
                    Submit Different Documents
                </Link>
            </div>
        </div>
    </div>
</template>
