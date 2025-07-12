<template>
  <div class="mobile-login min-h-screen w-full flex flex-col justify-end relative">
    <!-- Background Image -->
    <img src="/images/mobile.png" alt="Background" class="absolute inset-0 w-full h-full object-cover z-0" />
    <div class="absolute inset-0 bg-black bg-opacity-30 z-0"></div>

   

    <!-- Main Content -->
    <div class="relative z-10 flex flex-col items-center justify-end w-full px-6 pb-12">
      <div class="flex flex-col items-center">
        <h1 class="text-3xl font-bold text-black-500 mb-2">ZawajAfrica</h1>
        <p class="text-white text-base font-medium text-center mb-6">The First Halal Matchmaking App<br />for People of Color!</p>
      </div>
      <div class="w-full max-w-xs flex flex-col gap-4">
        <button class="w-full py-3 rounded-lg bg-[#E6A157] text-white text-lg font-semibold shadow" @click="goToRegister">Create Account</button>
        <button class="w-full py-3 rounded-lg border border-white text-white text-lg font-semibold bg-transparent" @click="showLogin = true">Sign in</button>
      </div>
      <div class="mt-6 flex flex-wrap justify-center gap-4 text-sm">
        <button class="text-white underline hover:text-gray-200" @click="openAboutModal">About Us</button>
        <button class="text-white underline hover:text-gray-200" @click="openTermsModal">Terms & Conditions</button>
        <button class="text-white underline hover:text-gray-200" @click="openPrivacyModal">Privacy Policy</button>
        <button class="text-white underline hover:text-gray-200" @click="openContactModal">Contact Us</button>
      </div>
    </div>

    <!-- Slide-up Login Form -->
    <transition name="slide-up">
      <div v-if="showLogin" class="fixed inset-0 z-20 flex items-end justify-center bg-black bg-opacity-60">
        <div class="w-full max-w-md bg-white rounded-t-2xl p-6 pb-10 shadow-lg animate-slideInUp relative">
          <button class="absolute top-3 right-4 text-gray-400 text-2xl" @click="showLogin = false">&times;</button>
          <h2 class="text-2xl font-bold mb-6 text-center text-[#04060A]">Sign in</h2>
          <form @submit.prevent="submit" class="space-y-5">
            <div>
              <label for="email" class="block text-gray-700 mb-2">Email</label>
              <input id="email" v-model="form.email" type="email" class="w-full border rounded-lg px-4 py-3 text-lg focus:outline-none focus:ring-2 focus:ring-[#E6A157]" required autocomplete="username" placeholder="Enter Email" />
              <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div>
              <label for="password" class="block text-gray-700 mb-2">Password</label>
              <input id="password" v-model="form.password" type="password" class="w-full border rounded-lg px-4 py-3 text-lg focus:outline-none focus:ring-2 focus:ring-[#E6A157]" required autocomplete="current-password" placeholder="Enter Password" />
              <InputError class="mt-2" :message="form.errors.password" />
            </div>
            <div class="flex justify-end">
              <Link v-if="canResetPassword" :href="route('password.request')" class="text-[#E6A157] text-sm underline">Forgot Password?</Link>
            </div>
            <button type="submit" class="w-full py-3 rounded-lg bg-[#E6A157] text-white text-lg font-semibold" :class="{ 'opacity-75': form.processing }" :disabled="form.processing">Sign in</button>
          </form>
        </div>
      </div>
    </transition>

    <TermsAndConditionsModal :show="showTermsModal" @close="closeTermsModal" />
    <PrivacyPolicyModal :show="showPrivacyModal" @close="closePrivacyModal" />
    <AboutUsModal :show="showAboutModal" @close="closeAboutModal" />
    <ContactUsModal :show="showContactModal" @close="closeContactModal" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import TermsAndConditionsModal from '@/Components/TermsAndConditionsModal.vue';
import PrivacyPolicyModal from '@/Components/PrivacyPolicyModal.vue';
import AboutUsModal from '@/Components/AboutUsModal.vue';
import ContactUsModal from '@/Components/ContactUsModal.vue';

const showLogin = ref(false);
const showTermsModal = ref(false);
const showPrivacyModal = ref(false);
const showAboutModal = ref(false);
const showContactModal = ref(false);

const props = defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  window.refreshCSRFToken();
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
    onSuccess: () => {
      setTimeout(() => {
        window.refreshCSRFToken();
        window.location.reload();
      }, 200);
    },
    onError: () => {
      window.refreshCSRFToken();
    }
  });
};

const goToRegister = () => {
  window.location.href = route('register');
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

const openAboutModal = () => {
  showAboutModal.value = true;
};
const closeAboutModal = () => {
  showAboutModal.value = false;
};

const openContactModal = () => {
  showContactModal.value = true;
};
const closeContactModal = () => {
  showContactModal.value = false;
};
</script>

<style scoped>
.mobile-login {
  background: #204D33;
  min-height: 100vh;
  position: relative;
}
.slide-up-enter-active, .slide-up-leave-active {
  transition: all 0.3s cubic-bezier(.4,2,.6,1);
}
.slide-up-enter-from, .slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}
.slide-up-enter-to, .slide-up-leave-from {
  transform: translateY(0);
  opacity: 1;
}
</style> 