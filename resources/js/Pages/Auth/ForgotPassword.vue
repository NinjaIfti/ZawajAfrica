<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />

    <div class="flex min-h-screen w-full flex-col md:flex-row">
        <!-- Left side - Background image and branding -->
        <div class="relative hidden h-screen w-full md:block md:max-w-[600px] md:flex-shrink-0">
            <!-- Base background color -->
            <div class="absolute inset-0 bg-[#204D33]"></div>

            <!-- Background with overlay -->
            <div class="absolute inset-0">
                <img 
                    src="/images/login.png" 
                    alt="Background" 
                    class="h-full w-full object-cover"
                />
                <!-- Purple overlay -->
                <div class="absolute inset-0 bg-[#654396] opacity-30"></div>
                
                <!-- Gradient overlay at bottom -->
                <div class="absolute bottom-0 left-0 right-0 h-[40%] bg-gradient-to-b from-[#65439600] to-[#654396f2]"></div>
            </div>

            <!-- Language selector -->
            <div class="absolute left-[5%] top-[5%] z-10 flex items-center rounded-full bg-[#654396] bg-opacity-70 px-4 py-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                </svg>
                <span class="text-white">English</span>
            </div>

            <!-- App name and tagline -->
            <div class="absolute bottom-[20%] left-1/2 w-full max-w-[400px] -translate-x-1/2 text-center z-10">
                <h1 class="mb-5 text-4xl md:text-5xl lg:text-6xl font-bold font-display text-[#E6A157]">ZawajAfrica</h1>
                <div class="text-center text-white">
                    <p class="text-lg md:text-xl font-medium">The First Halal Matchmaking App</p>
                    <p class="text-lg md:text-xl font-medium">for People of Color!</p>
                </div>
            </div>

            <!-- Terms & Conditions -->
            <div class="absolute bottom-[5%] left-1/2 -translate-x-1/2 text-center text-white z-10">
                <a href="#" class="text-white underline">Terms & Conditions</a>
            </div>
        </div>

        <!-- Right side - Forgot Password form -->
        <div class="flex min-h-screen w-full flex-1 items-center justify-center bg-white">
            <div class="flex items-center justify-center h-full w-full py-8">
                <div class="w-full max-w-[600px] px-8 md:px-[90px]">
                    <!-- Back button -->
                    <div class="mb-8">
                        <Link 
                            :href="route('login')" 
                            class="inline-flex items-center text-gray-700 hover:text-[#654396] transition-colors"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Back to Login</span>
                        </Link>
                    </div>

                    <!-- Heading -->
                    <div class="mb-[40px]">
                        <h2 class="text-5xl font-bold font-display text-[#04060A]">Forgot Password</h2>
                    </div>

                    <!-- Success message -->
                    <div
                        v-if="status"
                        class="mb-6 p-4 rounded-lg bg-green-50 text-green-700 text-lg"
                    >
                        {{ status }}
                    </div>

                    <!-- Instructions -->
                    <div class="mb-6 text-lg text-gray-600">
                        Please enter your email address and we'll send you a link to reset your password.
                    </div>

                    <!-- Email input -->
                    <div class="mb-[30px]">
                        <label for="email" class="mb-3 block text-lg text-[#41465a]">Email Address</label>
                        <div class="flex items-center rounded-lg bg-[#F5F5F5] px-5 py-5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-3 h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <input
                                id="email"
                                type="email"
                                class="w-full bg-transparent text-lg text-gray-700 focus:outline-none"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Enter your email address"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Submit button -->
                    <div class="mb-6">
                        <button
                            @click="submit"
                            type="button"
                            :disabled="form.processing || !form.email"
                            class="w-full rounded-lg bg-[#654396] py-4 text-center text-lg text-white font-medium disabled:opacity-50"
                        >
                            Send Reset Link
                        </button>
                    </div>

                    <!-- Login link -->
                    <div class="text-center text-lg">
                        <span>Remember your password? </span>
                        <Link :href="route('login')" class="text-[#654396] font-medium hover:underline">
                            Log in!
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add any custom styles here if needed */
</style>
