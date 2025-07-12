<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const closeModal = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="2xl">
        <div class="bg-white rounded-lg max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-[#654396] text-white">
                <h2 class="text-2xl font-bold">Contact Us</h2>
                <button
                    @click="closeModal"
                    class="text-white hover:text-gray-200 text-2xl font-bold"
                    type="button"
                >
                    ×
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[70vh] space-y-6">
                <!-- Header Info -->
                <div class="bg-gradient-to-r from-[#654396] to-[#E6A157] text-white p-6 rounded-lg">
                    <div class="text-center">
                        <h3 class="text-xl font-bold mb-3">💬 Get In Touch</h3>
                        <p class="text-lg leading-relaxed">
                            We're here to help you on your journey to finding love and meaningful connections. Reach out to us anytime!
                        </p>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="space-y-6">
                    <!-- Primary Contact -->
                    <div>
                        <h4 class="text-xl font-semibold text-[#654396] mb-4">📞 Primary Contact</h4>
                        <div class="bg-gray-50 p-6 rounded-lg space-y-4">
                            <div class="flex items-center">
                                <div class="text-2xl mr-4">📧</div>
                                <div>
                                    <h5 class="font-medium text-gray-800 mb-1">Email Support</h5>
                                    <div class="space-y-1">
                                        <p class="text-gray-600">
                                            <a href="mailto:support@zawajafrica.com.ng" class="text-[#654396] hover:underline">
                                                support@zawajafrica.com.ng
                                            </a>
                                        </p>
                                        <p class="text-gray-600">
                                            <a href="mailto:info@zawajafrica.online" class="text-[#654396] hover:underline">
                                                info@zawajafrica.online
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="text-2xl mr-4">📱</div>
                                <div>
                                    <h5 class="font-medium text-gray-800 mb-1">Call/WhatsApp</h5>
                                    <p class="text-gray-600">
                                        <a href="https://wa.me/2347037727643" target="_blank" class="text-[#654396] hover:underline">
                                            +234 703 772 7643
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Office Location -->
                    <div>
                        <h4 class="text-xl font-semibold text-[#654396] mb-4">🏢 Office Location</h4>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="flex items-start">
                                <div class="text-2xl mr-4">📍</div>
                                <div>
                                    <h5 class="font-medium text-gray-800 mb-2">Our Address</h5>
                                    <p class="text-gray-600">
                                        No 378 Gadangami Plaza<br>
                                        BUK Road, Kano<br>
                                        Nigeria
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Online Presence -->
                    <div>
                        <h4 class="text-xl font-semibold text-[#654396] mb-4">🌐 Online Presence</h4>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="flex items-center">
                                <div class="text-2xl mr-4">💻</div>
                                <div>
                                    <h5 class="font-medium text-gray-800 mb-1">Website</h5>
                                    <p class="text-gray-600">
                                        <a href="https://zawajafrica.online" target="_blank" class="text-[#654396] hover:underline">
                                            https://zawajafrica.online
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Support Hours -->
                    <div>
                        <h4 class="text-xl font-semibold text-[#654396] mb-4">⏰ Support Hours</h4>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Monday - Friday:</span>
                                    <span class="text-gray-800 font-medium">9:00 AM - 6:00 PM (WAT)</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Saturday:</span>
                                    <span class="text-gray-800 font-medium">10:00 AM - 4:00 PM (WAT)</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Sunday:</span>
                                    <span class="text-gray-800 font-medium">Closed</span>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                                <p class="text-sm text-blue-700">
                                    <strong>Emergency Support:</strong> For urgent matters, please use WhatsApp for faster response.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- What We Can Help With -->
                    <div>
                        <h4 class="text-xl font-semibold text-[#654396] mb-4">🤝 How We Can Help</h4>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="grid md:grid-cols-2 gap-4">
                                <div class="space-y-3">
                                    <div class="flex items-start">
                                        <div class="text-[#E6A157] mr-3 mt-1">💡</div>
                                        <div>
                                            <h5 class="font-medium text-gray-800">Account Support</h5>
                                            <p class="text-sm text-gray-600">Profile setup, verification, and account issues</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="text-[#E6A157] mr-3 mt-1">🔧</div>
                                        <div>
                                            <h5 class="font-medium text-gray-800">Technical Issues</h5>
                                            <p class="text-sm text-gray-600">App bugs, login problems, and feature questions</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="text-[#E6A157] mr-3 mt-1">💳</div>
                                        <div>
                                            <h5 class="font-medium text-gray-800">Billing & Payments</h5>
                                            <p class="text-sm text-gray-600">Subscription questions and payment support</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-start">
                                        <div class="text-[#E6A157] mr-3 mt-1">🛡️</div>
                                        <div>
                                            <h5 class="font-medium text-gray-800">Safety & Security</h5>
                                            <p class="text-sm text-gray-600">Report inappropriate behavior and safety concerns</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="text-[#E6A157] mr-3 mt-1">💬</div>
                                        <div>
                                            <h5 class="font-medium text-gray-800">General Feedback</h5>
                                            <p class="text-sm text-gray-600">Suggestions, complaints, and feature requests</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="text-[#E6A157] mr-3 mt-1">🗣️</div>
                                        <div>
                                            <h5 class="font-medium text-gray-800">Therapy Services</h5>
                                            <p class="text-sm text-gray-600">Counseling appointments and therapy support</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end p-6 border-t border-gray-200 bg-gray-50">
                <button
                    @click="closeModal"
                    class="px-6 py-2 bg-[#654396] text-white rounded-lg hover:bg-[#553082] font-medium"
                >
                    Close
                </button>
            </div>
        </div>
    </Modal>
</template> 