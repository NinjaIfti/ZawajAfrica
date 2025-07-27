<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import AppHeader from '@/Components/AppHeader.vue';

const props = defineProps({
    user: Object,
    therapist: Object,
    bookingData: Object,
});

const page = usePage();
const isMobileMenuOpen = ref(false);

// Get booking data from URL params if not passed as props
const bookingDetails = ref({
    therapist_id: null,
    therapist_name: '',
    appointment_datetime: '',
    session_type: 'online',
    platform: '',
    amount: 0,
    selected_date: '',
    selected_time: ''
});

// Initialize booking details from URL params or props
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    
    bookingDetails.value = {
        therapist_id: urlParams.get('therapist_id') || props.bookingData?.therapist_id,
        therapist_name: urlParams.get('therapist_name') || props.therapist?.name || props.bookingData?.therapist_name,
        appointment_datetime: urlParams.get('appointment_datetime') || props.bookingData?.appointment_datetime,
        session_type: urlParams.get('session_type') || props.bookingData?.session_type || 'online',
        platform: urlParams.get('platform') || props.bookingData?.platform,
        amount: parseFloat(urlParams.get('amount')) || props.therapist?.hourly_rate || props.bookingData?.amount || 0,
        selected_date: urlParams.get('selected_date') || props.bookingData?.selected_date,
        selected_time: urlParams.get('selected_time') || props.bookingData?.selected_time
    };
});

// Toggle mobile menu
const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
    if (isMobileMenuOpen.value) {
        document.body.classList.add('overflow-hidden');
    } else {
        document.body.classList.remove('overflow-hidden');
    }
};

// Format currency amounts
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
};

const formatUsdAmount = (nairaAmount) => {
    // Convert Naira to USD (approximate rate: 1 USD = 750 NGN)
    const usdAmount = Math.ceil(nairaAmount / 750);
    return usdAmount;
};

const sessionAmount = computed(() => {
    return bookingDetails.value.amount || 0;
});

const usdAmount = computed(() => {
    return formatUsdAmount(sessionAmount.value);
});

const whatsappMessage = computed(() => {
    const username = props.user?.email || 'N/A';
    const therapistName = bookingDetails.value.therapist_name || 'Unknown Therapist';
    const sessionDate = bookingDetails.value.selected_date || 'N/A';
    const sessionTime = bookingDetails.value.selected_time || 'N/A';
    const amount = formatCurrency(sessionAmount.value);
    
    return `Hello ZawajAfrica, I have made my payment for therapy session with ${therapistName}. 

Session Details:
- Date: ${sessionDate}
- Time: ${sessionTime}
- Amount: ${amount}
- My username: ${username}

Please confirm my booking and provide session details.`;
});

const whatsappUrl = computed(() => {
    const message = encodeURIComponent(whatsappMessage.value);
    return `https://wa.me/2347037727643?text=${message}`;
});

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    });
};
</script>

<template>
    <Head title="Manual Payment - Therapy Session" />

    <div class="min-h-screen bg-gray-50">
        <!-- Mobile menu backdrop -->
        <div
            v-if="isMobileMenuOpen"
            class="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden"
            @click="toggleMobileMenu"
        ></div>

        <!-- Sidebar -->
        <div
            class="fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out md:translate-x-0"
            :class="{ 'translate-x-0': isMobileMenuOpen, '-translate-x-full': !isMobileMenuOpen }"
        >
            <Sidebar :user="user" />
        </div>

        <!-- Main content -->
        <div class="flex-1 md:ml-64">
            <!-- Header -->
            <AppHeader :user="user" @toggle-mobile-menu="toggleMobileMenu" />

            <!-- Page content -->
            <main class="p-4 md:p-8">
                <div class="max-w-4xl mx-auto">
                    <!-- Back button -->
                    <div class="mb-6">
                        <Link
                            v-if="bookingDetails.therapist_id"
                            :href="route('therapists.show', { id: bookingDetails.therapist_id })"
                            class="inline-flex items-center text-purple-600 hover:text-purple-800 font-medium"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Therapist
                        </Link>
                        <Link
                            v-else
                            :href="route('therapists.index')"
                            class="inline-flex items-center text-purple-600 hover:text-purple-800 font-medium"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Therapists
                        </Link>
                    </div>

                    <!-- Header -->
                    <div class="text-center mb-8">
                        <h1 class="text-4xl font-bold text-gray-900 mb-4">
                            🧠 Therapy Session Payment
                        </h1>
                        <p class="text-xl text-gray-600">Manual Payment Options</p>
                        <div class="mt-4 p-4 bg-purple-50 rounded-lg">
                            <p class="text-lg font-semibold text-purple-800">
                                Session with: {{ bookingDetails.therapist_name }}
                            </p>
                            <p class="text-purple-600">
                                Date: {{ bookingDetails.selected_date }} at {{ bookingDetails.selected_time }}
                            </p>
                            <p class="text-purple-600">
                                Amount: {{ formatCurrency(sessionAmount) }} / ${{ usdAmount }}
                            </p>
                        </div>
                    </div>

                    <!-- Payment Instructions -->
                    <div class="grid md:grid-cols-2 gap-8">
                        <!-- Nigerian Users Section -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h2 class="text-2xl font-bold text-green-600 mb-4 flex items-center">
                                🇳🇬 For Nigerian Users
                                <span class="text-sm font-normal text-gray-500 ml-2">(Local Payment)</span>
                            </h2>
                            
                            <div class="space-y-4">
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <h3 class="font-semibold text-green-800 mb-2">Bank Transfer Details:</h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="font-medium">Amount:</span>
                                            <span class="font-bold">{{ formatCurrency(sessionAmount) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Account Name:</span>
                                            <span class="font-bold">SHE ELGANCE NIG LTD</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Bank Name:</span>
                                            <span class="font-bold">Access Bank Nig PLC</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium">Account Number:</span>
                                            <div class="flex items-center">
                                                <span class="font-bold mr-2">0781388373</span>
                                                <button
                                                    @click="copyToClipboard('0781388373')"
                                                    class="text-green-600 hover:text-green-800 text-xs"
                                                >
                                                    Copy
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <h3 class="font-semibold text-blue-800 mb-2">✅ After Payment:</h3>
                                    <p class="text-sm text-blue-700">
                                        Send your payment proof (screenshot or receipt) via WhatsApp to confirm your therapy session.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- International Users Section -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h2 class="text-2xl font-bold text-blue-600 mb-4 flex items-center">
                                🌍 For International Users
                                <span class="text-sm font-normal text-gray-500 ml-2">(Diaspora)</span>
                            </h2>
                            
                            <div class="space-y-4">
                                <!-- PayPal Option -->
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <h3 class="font-semibold text-blue-800 mb-2">Option A - PayPal</h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="font-medium">Amount:</span>
                                            <span class="font-bold">${{ usdAmount }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium">PayPal Email:</span>
                                            <div class="flex items-center">
                                                <span class="font-bold mr-2">zawajafrica@gmail.com</span>
                                                <button
                                                    @click="copyToClipboard('zawajafrica@gmail.com')"
                                                    class="text-blue-600 hover:text-blue-800 text-xs"
                                                >
                                                    Copy
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- USD Bank Transfer Option -->
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <h3 class="font-semibold text-purple-800 mb-2">Option B - USD Bank Transfer</h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="font-medium">Amount:</span>
                                            <span class="font-bold">${{ usdAmount }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Account Name:</span>
                                            <span class="font-bold">SHE ELGANCE NIG LTD</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Bank Name:</span>
                                            <span class="font-bold">Access Bank</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium">USD Account:</span>
                                            <div class="flex items-center">
                                                <span class="font-bold mr-2">1419791282</span>
                                                <button
                                                    @click="copyToClipboard('1419791282')"
                                                    class="text-purple-600 hover:text-purple-800 text-xs"
                                                >
                                                    Copy
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium">SWIFT Code:</span>
                                            <div class="flex items-center">
                                                <span class="font-bold mr-2">ABNGNGLA</span>
                                                <button
                                                    @click="copyToClipboard('ABNGNGLA')"
                                                    class="text-purple-600 hover:text-purple-800 text-xs"
                                                >
                                                    Copy
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Country:</span>
                                            <span class="font-bold">Nigeria</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PayPal Instructions -->
                    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">
                            💳 PayPal Payment Steps
                        </h2>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <div class="flex items-start">
                                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-1">1</span>
                                    <span>Log into your PayPal account</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-1">2</span>
                                    <span>Choose "Send Money"</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-1">3</span>
                                    <span>Enter email: zawajafrica@gmail.com</span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-start">
                                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-1">4</span>
                                    <span>Enter amount: ${{ usdAmount }}.00</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-1">5</span>
                                    <span>Click "Send"</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-1">6</span>
                                    <span>Send us the confirmation screenshot</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- WhatsApp Contact Section -->
                    <div class="mt-8 bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-md p-6 text-white">
                        <h2 class="text-2xl font-bold mb-4 flex items-center">
                            📱 Send Payment Proof
                        </h2>
                        <p class="text-lg mb-4">
                            After making your payment, send the proof via WhatsApp to confirm your therapy session:
                        </p>
                        <div class="flex flex-col md:flex-row gap-4 items-center">
                            <div class="flex items-center bg-white bg-opacity-20 rounded-lg px-4 py-2">
                                <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.570-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                </svg>
                                <span class="font-bold">+234 703 772 7643</span>
                            </div>
                            <a
                                :href="whatsappUrl"
                                target="_blank"
                                class="bg-white text-green-600 hover:bg-gray-100 px-6 py-3 rounded-lg font-bold transition-colors duration-200 flex items-center"
                            >
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.570-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                </svg>
                                Send Payment Proof
                            </a>
                        </div>
                    </div>

                    <!-- Important Notes -->
                    <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-yellow-800 mb-3">⚠️ Important Notes:</h3>
                        <ul class="space-y-2 text-yellow-700">
                            <li>• Your therapy session will be confirmed within 24 hours of payment verification</li>
                            <li>• Keep your payment receipt/screenshot for your records</li>
                            <li>• Session link will be provided via WhatsApp after payment confirmation</li>
                            <li>• Contact our support team if you don't receive confirmation within 24 hours</li>
                            <li>• Payment covers a 1-hour therapy session with your selected therapist</li>
                        </ul>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Mobile menu transitions */
.translate-x-0 {
    transform: translateX(0);
}

.-translate-x-full {
    transform: translateX(-100%);
}

@media (min-width: 768px) {
    .md\:translate-x-0 {
        transform: translateX(0) !important;
    }
}
</style> 