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
    <Modal :show="show" @close="closeModal" max-width="4xl">
        <div class="bg-white rounded-lg max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-[#654396] text-white">
                <h2 class="text-2xl font-bold">Privacy Policy</h2>
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
                        <h3 class="text-xl font-bold mb-3">🔒 Your Privacy Matters</h3>
                        <p class="text-lg leading-relaxed">
                            ZawajAfrica is committed to protecting your privacy and ensuring your personal information is handled with the utmost care and respect.
                        </p>
                        <p class="text-sm mt-2 opacity-90">
                            <strong>Effective Date:</strong> October 7, 2025<br>
                            <strong>Website:</strong> https://zawajafrica.online
                        </p>
                    </div>
                </div>

                <!-- Privacy Policy Content -->
                <div class="prose prose-lg max-w-none">
                    <div class="space-y-6">
                        <!-- Introduction -->
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">1. Introduction</h4>
                            <p class="text-gray-700 mb-4">
                                ZawajAfrica is committed to protecting your privacy. This Privacy Policy explains how we collect, use, 
                                disclose, and safeguard your information when you visit our platform or use our services through the 
                                website or mobile app.
                            </p>
                            <p class="text-gray-700">
                                By using ZawajAfrica, you agree to the terms outlined in this Privacy Policy.
                            </p>
                        </div>

                        <!-- Information We Collect -->
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">2. Information We Collect</h4>
                            <p class="text-gray-700 mb-4">We may collect the following types of personal data:</p>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>Name, email address, phone number</li>
                                <li>Profile details (e.g., age, gender, religion, preferences)</li>
                                <li>Uploaded photos and documents</li>
                                <li>Payment details (only proof of payment if manual, otherwise through secured gateways)</li>
                                <li>Device and usage data for improving our services</li>
                            </ul>
                        </div>

                        <!-- How We Use Your Information -->
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">3. How We Use Your Information</h4>
                            <p class="text-gray-700 mb-4">We use your data to:</p>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>Create and manage user accounts</li>
                                <li>Connect you with potential matches</li>
                                <li>Provide customer support</li>
                                <li>Improve our services and user experience</li>
                                <li>Communicate with you (via email, WhatsApp, or phone)</li>
                                <li>Comply with legal requirements</li>
                            </ul>
                        </div>

                        <!-- Third-Party Services -->
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">4. Third-Party Services</h4>
                            <p class="text-gray-700 mb-4">We may integrate trusted third-party services for:</p>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>Payment processing (e.g., Paystack, Monnify – when active)</li>
                                <li>Analytics and app performance</li>
                                <li>Identity or KYC verification (when applicable)</li>
                            </ul>
                            <p class="text-gray-700 mt-4">
                                These providers have their own privacy policies and security practices.
                            </p>
                        </div>

                        <!-- Manual Payments & WhatsApp -->
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">5. Manual Payments & WhatsApp</h4>
                            <p class="text-gray-700">
                                For users making manual payments to our bank account, you will be asked to submit proof of payment 
                                via WhatsApp. This communication is private and secure.
                            </p>
                        </div>

                        <!-- Account Deletion -->
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">6. Account Deletion</h4>
                            <p class="text-gray-700 mb-4">You may request to delete your account at any time by:</p>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>Using the "Delete My Account" option in your profile</li>
                                <li>Or sending an email to <a href="mailto:support@zawajafrica.online" class="text-[#654396] hover:underline">support@zawajafrica.online</a> with your registered phone number or email</li>
                            </ul>
                            <p class="text-gray-700 mt-4">
                                Once deleted, your data will be permanently removed from our platform within 7 working days, except 
                                for legally required information.
                            </p>
                        </div>

                        <!-- Data Security -->
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">7. Data Security</h4>
                            <p class="text-gray-700">
                                We implement appropriate technical and organizational measures to protect your personal data. 
                                However, no system is 100% secure. We advise users to keep their login details confidential.
                            </p>
                        </div>

                        <!-- Children's Privacy -->
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">8. Children's Privacy</h4>
                            <p class="text-gray-700">
                                ZawajAfrica is strictly for users aged 18 years and above. We do not knowingly collect data from 
                                individuals under 18.
                            </p>
                        </div>

                        <!-- Changes to This Policy -->
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">9. Changes to This Policy</h4>
                            <p class="text-gray-700">
                                We may update this Privacy Policy from time to time. All changes will be posted on this page with a 
                                new effective date.
                            </p>
                        </div>

                        <!-- Contact Us -->
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">10. Contact Us</h4>
                            <p class="text-gray-700 mb-4">
                                If you have any questions or concerns about your privacy, please contact:
                            </p>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="space-y-2">
                                    <p class="text-gray-700">
                                        <strong>📧 Email:</strong> 
                                        <a href="mailto:support@zawajafrica.com.ng" class="text-[#654396] hover:underline">
                                            support@zawajafrica.com.ng
                                        </a>
                                        <span class="mx-2">|</span>
                                        <a href="mailto:info@zawajafrica.online" class="text-[#654396] hover:underline">
                                            info@zawajafrica.online
                                        </a>
                                    </p>
                                    <p class="text-gray-700">
                                        <strong>📱 Call/WhatsApp:</strong> 
                                        <a href="https://wa.me/2347037727643" target="_blank" class="text-[#654396] hover:underline">
                                            +234 703 772 7643
                                        </a>
                                    </p>
                                    <p class="text-gray-700">
                                        <strong>🏢 Address:</strong> 
                                        <span>No 378 Gadangami plaza, BUK road, Kano, Nigeria</span>
                                    </p>
                                    <p class="text-gray-700">
                                        <strong>🌐 Website:</strong> 
                                        <a href="https://zawajafrica.online" target="_blank" class="text-[#654396] hover:underline">
                                            https://zawajafrica.online
                                        </a>
                                    </p>
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