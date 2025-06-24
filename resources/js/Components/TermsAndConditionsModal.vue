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
                <h2 class="text-2xl font-bold">Terms & Conditions</h2>
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
                <!-- Vision & Mission Section -->
                <div class="bg-gradient-to-r from-[#654396] to-[#E6A157] text-white p-6 rounded-lg">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold mb-3">🌟 Our Vision</h3>
                        <p class="text-lg leading-relaxed">
                            To be the most trusted halal matchmaking platform for African Muslims — connecting hearts across continents through love, faith, and purpose.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-xl font-bold mb-3">🤲 Our Mission</h3>
                        <p class="text-lg leading-relaxed">
                            Zawaj Africa is committed to creating a safe, respectful, and spiritually guided space where African Muslims can find meaningful, marriage-focused connections.
                            We honor Islamic values, celebrate African heritage, and support our members with personalized tools and sincere guidance on the path to love and companionship.
                        </p>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="prose prose-lg max-w-none">
                    <h3 class="text-2xl font-bold text-[#654396] mb-4">Terms and Conditions for Zawaj Africa</h3>
                    <p class="text-gray-600 mb-6">Last updated: [Insert Date]</p>
                    
                    <p class="mb-6 text-gray-700">
                        Welcome to Zawaj Africa! These Terms and Conditions ("Terms") govern your use of our website, mobile app, and services. 
                        By registering or using our platform, you agree to abide by these Terms.
                    </p>

                    <div class="space-y-6">
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">1. Eligibility</h4>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>You must be at least 18 years old and a Muslim (by birth or by conversion) to join Zawaj Africa.</li>
                                <li>By using this platform, you confirm that you are legally allowed to enter into this agreement under your local laws.</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">2. User Responsibilities</h4>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>You agree to provide accurate, current, and complete information during registration and profile setup.</li>
                                <li>You are solely responsible for your interactions with other users.</li>
                                <li>Any use of offensive language, harassment, fraud, or misrepresentation will result in immediate suspension or ban.</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">3. Account Security</h4>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>Keep your login details confidential. You are responsible for all activity under your account.</li>
                                <li>Zawaj Africa is not liable for any loss or damage from unauthorized use of your account.</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">4. Subscriptions and Payments</h4>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>Zawaj Africa offers Free, Basic, Gold, and Platinum plans.</li>
                                <li>All payments are securely processed through trusted third-party providers.</li>
                                <li>Subscription fees are non-refundable unless otherwise stated.</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">5. Content and Conduct</h4>
                            <p class="text-gray-700 mb-2">You may not post or share:</p>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>Pornographic or explicit material.</li>
                                <li>False, misleading, or defamatory content.</li>
                                <li>Content that promotes hate, violence, or discrimination.</li>
                            </ul>
                            <p class="text-gray-700 mt-2">Zawaj Africa reserves the right to remove content or suspend users violating our values or Islamic ethics.</p>
                        </div>

                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">6. Privacy</h4>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>Please review our Privacy Policy to understand how we handle your data.</li>
                                <li>We do not share your personal data with third parties except as necessary to provide services or comply with the law.</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">7. Matchmaking Disclaimer</h4>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>Zawaj Africa does not guarantee successful matchmaking, marriage, or compatibility.</li>
                                <li>All decisions regarding communication, meetings, or marriage are entirely your responsibility.</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">8. Termination</h4>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>You may delete your account at any time.</li>
                                <li>We reserve the right to terminate or suspend any account without prior notice for breach of these Terms or harmful conduct.</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">9. Intellectual Property</h4>
                            <p class="text-gray-700">All content on Zawaj Africa (logo, design, texts, features) is our property and may not be used without permission.</p>
                        </div>

                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">10. Governing Law</h4>
                            <p class="text-gray-700">These Terms shall be governed in accordance with the laws of the Federal Republic of Nigeria.</p>
                        </div>

                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">11. Changes to Terms</h4>
                            <p class="text-gray-700">We may update these Terms occasionally. Continued use of the platform implies your acceptance of any changes.</p>
                        </div>

                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">12. Contact Us</h4>
                            <p class="text-gray-700 mb-2">For questions or concerns, contact us at:</p>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>Email: <a href="mailto:Admin@zawajafrica.com.ng" class="text-[#654396] hover:underline">Admin@zawajafrica.com.ng</a></li>
                                <li>Support: <a href="mailto:Support@zawajafrica.online" class="text-[#654396] hover:underline">Support@zawajafrica.online</a></li>
                                <li>Website: <a href="https://www.zawajafrica.online" class="text-[#654396] hover:underline" target="_blank">www.zawajafrica.online</a></li>
                            </ul>
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