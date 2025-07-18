<script setup>
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import { ref, onMounted } from 'vue';
    import InputError from '@/Components/InputError.vue';
    import TermsAndConditionsModal from '@/Components/TermsAndConditionsModal.vue';
    import PrivacyPolicyModal from '@/Components/PrivacyPolicyModal.vue';

    defineProps({
        canResetPassword: {
            type: Boolean,
        },
        status: {
            type: String,
        },
    });

    // Auto-redirect mobile users to /mobile-login
    onMounted(() => {
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        if (isMobile && window.location.pathname !== '/mobile-login') {
            window.location.href = '/mobile-login';
        }
    });

    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const showTermsModal = ref(false);
    const showPrivacyModal = ref(false);

    const submit = () => {
        // Refresh CSRF token before login attempt
        window.refreshCSRFToken();

        form.post(route('login'), {
            onFinish: () => form.reset('password'),
            onSuccess: () => {
                // Force refresh CSRF token after successful login with a delay
                setTimeout(() => {
                    window.refreshCSRFToken();
                    // Force page reload to ensure fresh session state
                    window.location.reload();
                }, 200);
            },
            onError: () => {
                // Refresh token on error as well
                window.refreshCSRFToken();
            }
        });
    };

  

    const openTermsModal = () => {
        showTermsModal.value = true;
    };

    const closeTermsModal = () => {
        showTermsModal.value = false;
    };

    const openPrivacyModal = () => {
        showPrivacyModal.value = true;
    };

    const closePrivacyModal = () => {
        showPrivacyModal.value = false;
    };

    // Social login function
    const socialLogin = (provider) => {
        if (provider === 'google') {
            window.location.href = route('auth.google');
        }
    };
</script>

<template>
    <Head title="Log in" />

    <div class="flex min-h-screen w-full flex-col md:flex-row">
        <!-- Left side - Background image and branding -->
        <div class="relative hidden h-screen w-full md:block md:max-w-[800px] md:flex-shrink-0">
            <!-- Base background color -->
            <div class="absolute inset-0 bg-[#204D33]"></div>

            <!-- Background with overlay -->
            <div class="absolute inset-0">
                <img src="/images/login.png" alt="Background" class="h-full w-full object-cover" />
                <!-- Purple overlay -->
                <div class="absolute inset-0 bg-[#654396] opacity-30"></div>

                <!-- Gradient overlay at bottom -->
                <div
                    class="absolute bottom-0 left-0 right-0 h-[40%] bg-gradient-to-b from-[#65439600] to-[#654396f2]"
                ></div>
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
                <button @click="openTermsModal" class="text-white underline hover:text-gray-200 cursor-pointer mr-4">Terms & Conditions</button>
                <button @click="openPrivacyModal" class="text-white underline hover:text-gray-200 cursor-pointer">Privacy Policy</button>
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
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="mr-3 h-6 w-6 text-gray-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    />
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
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="mr-3 h-6 w-6 text-gray-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                    />
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
                            <span>Don't have account?</span>
                            <Link :href="route('register')" class="text-[#654396] font-medium hover:underline">
                                Sign Up!
                            </Link>
                        </div>
                    </form>

                    <!-- Social login buttons -->
                    <div class="mt-8 flex justify-center">
                        <button
                            @click="socialLogin('google')"
                            class="flex w-full sm:w-1/2 items-center justify-center rounded-lg border border-[#E5E5E5] bg-white py-4 px-4 text-black shadow-sm hover:bg-gray-50 text-sm sm:text-base whitespace-nowrap"
                        >
                            <svg class="mr-2 h-5 w-5 sm:h-6 sm:w-6 flex-shrink-0" viewBox="0 0 24 24">
                                <path
                                    fill="#4285F4"
                                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                />
                                <path
                                    fill="#34A853"
                                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                />
                                <path
                                    fill="#FBBC05"
                                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                                />
                                <path
                                    fill="#EA4335"
                                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                />
                            </svg>
                            <span>Login With Google</span>
                        </button>

                    </div>
                </div>
            </div>
        </div>

        <!-- Terms and Conditions Modal -->
        <TermsAndConditionsModal 
            :show="showTermsModal" 
            @close="closeTermsModal" 
        />
        
        <!-- Privacy Policy Modal -->
        <PrivacyPolicyModal 
            :show="showPrivacyModal" 
            @close="closePrivacyModal" 
        />
    </div>
</template>
