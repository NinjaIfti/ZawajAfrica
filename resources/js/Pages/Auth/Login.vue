<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const socialLogin = (provider) => {
    // Implement social login logic here
    console.log(`Login with ${provider}`);
};
</script>

<template>
    <Head title="Log in" />

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

        <!-- Right side - Login form -->
        <div class="flex min-h-screen w-full flex-1 items-center justify-center bg-white">
            <div class="flex items-center justify-center h-full w-full py-8">
                <div class="w-full max-w-[600px] px-8 md:px-[80px]">
                    <!-- Form header -->
                    <div class="mb-[50px]">
                        <h2 class="text-5xl font-bold font-display text-[#04060A]">Log in</h2>
                    </div>

                    <div v-if="status" class="mb-6 text-sm font-medium text-green-600">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="mb-[40px] space-y-[30px]">
                        <!-- Email input -->
                        <div>
                            <label for="email" class="mb-3 block text-lg text-[#41465a]">Email</label>
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
                                    placeholder="Enter Email"
                                />
                            </div>
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <!-- Password input -->
                        <div>
                            <label for="password" class="mb-3 block text-lg text-[#41465a]">Enter Password</label>
                            <div class="flex items-center rounded-lg bg-[#F5F5F5] px-5 py-5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-3 h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <input
                                    id="password"
                                    type="password"
                                    class="w-full bg-transparent text-lg text-gray-700 focus:outline-none"
                                    v-model="form.password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter Password"
                                />
                            </div>
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <!-- Forgot password link -->
                        <div class="text-right">
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-lg text-[#654396] hover:underline"
                            >
                                Forgot Password?
                            </Link>
                        </div>

                        <!-- Login button -->
                        <div class="pt-[10px]">
                            <button
                                type="submit"
                                class="w-full rounded-lg bg-[#654396] py-5 text-center text-lg text-white font-medium"
                                :class="{ 'opacity-75': form.processing }"
                                :disabled="form.processing"
                            >
                                Sign in
                            </button>
                        </div>

                        <!-- Signup link -->
                        <div class="text-center text-lg">
                            <span>Don't have account? </span>
                            <Link :href="route('register')" class="text-[#654396] font-medium hover:underline">
                                Join Now!
                            </Link>
                        </div>
                    </form>

                    <!-- Social login buttons -->
                    <div class="mt-8 flex flex-row gap-4">
                        <button 
                            @click="socialLogin('google')"
                            class="flex w-1/2 items-center justify-center rounded-lg border border-[#E5E5E5] bg-white py-4 px-4 text-black shadow-sm hover:bg-gray-50"
                        >
                            <svg class="mr-2 h-6 w-6" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span>Login with Google</span>
                        </button>
                        
                        <button 
                            @click="socialLogin('apple')"
                            class="flex w-1/2 items-center justify-center rounded-lg bg-[#040A04] py-4 px-4 text-white hover:bg-black"
                        >
                            <svg class="mr-2 h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.084 1.013 1.455 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09z"/>
                                <path d="M12.1 3.818c.845-1.027 1.403-2.455 1.247-3.818-1.207.052-2.662.805-3.532 1.818-.78.896-1.455 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.714z"/>
                            </svg>
                            <span>Login with Apple</span>  
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
