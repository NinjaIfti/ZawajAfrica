<template>
    <div class="space-y-6">
        <!-- Gateway Selection -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Select Payment Method</h3>
            
            <!-- Payment Gateway Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Paystack Option -->
                <div 
                    @click="selectGateway('paystack')"
                    :class="[
                        'border-2 rounded-lg p-4 cursor-pointer transition-all duration-200',
                        selectedGateway === 'paystack' 
                            ? 'border-green-500 bg-green-50' 
                            : 'border-gray-200 hover:border-gray-300'
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-8 bg-green-600 rounded flex items-center justify-center">
                                <span class="text-white text-xs font-bold">PAY</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Paystack</h4>
                                <p class="text-sm text-gray-600">Cards, Bank Transfer, USSD</p>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center">
                            <div 
                                v-if="selectedGateway === 'paystack'" 
                                class="w-3 h-3 bg-green-500 rounded-full"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Monnify Option -->
                <div 
                    @click="selectGateway('monnify')"
                    :class="[
                        'border-2 rounded-lg p-4 cursor-pointer transition-all duration-200',
                        selectedGateway === 'monnify' 
                            ? 'border-purple-500 bg-purple-50' 
                            : 'border-gray-200 hover:border-gray-300'
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-8 bg-purple-600 rounded flex items-center justify-center">
                                <span class="text-white text-xs font-bold">MN</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Monnify</h4>
                                <p class="text-sm text-gray-600">Payments within Nigeria</p>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center">
                            <div 
                                v-if="selectedGateway === 'monnify'" 
                                class="w-3 h-3 bg-purple-500 rounded-full"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monnify Info (when selected) -->
        <div v-if="selectedGateway === 'monnify'" class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-orange-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div>
                    <h4 class="font-medium text-orange-900">Monnify Payment</h4>
                    <p class="text-sm text-orange-700 mt-1">
                        You'll be redirected to Monnify's secure payment page where you can pay with cards, bank transfer, or USSD.
                    </p>
                    <div class="mt-3 text-lg font-semibold text-orange-900">
                        Amount: {{ formatAmount(amount) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Paystack Info (when selected) -->
        <div v-else-if="selectedGateway === 'paystack'" class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div>
                    <h4 class="font-medium text-green-900">Paystack Payment</h4>
                    <p class="text-sm text-green-700 mt-1">
                        You'll be redirected to Paystack's secure payment page where you can pay with cards, bank transfer, or USSD.
                    </p>
                    <div class="mt-3 text-lg font-semibold text-green-900">
                        Amount: {{ formatAmount(amount) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between">
            <button
                @click="$emit('back')"
                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors"
            >
                Back
            </button>
            <button
                @click="processPayment"
                :disabled="!canProceed"
                :class="[
                    'px-6 py-3 rounded-lg font-medium transition-colors',
                    canProceed 
                        ? 'bg-purple-600 text-white hover:bg-purple-700' 
                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                ]"
            >
                <span v-if="processing">Processing...</span>
                <span v-else>{{ selectedGateway === 'monnify' ? 'Pay with Monnify' : 'Pay with Paystack' }}</span>
            </button>
        </div>
    </div>
</template>

<script>
import { ref, computed } from 'vue'

export default {
    name: 'PaymentGatewaySelector',
    props: {
        amount: {
            type: Number,
            required: true
        },
        therapistId: {
            type: Number,
            required: true
        },
        bookingDate: {
            type: String,
            required: true
        },
        bookingTime: {
            type: String,
            required: true
        },
        notes: {
            type: String,
            default: ''
        }
    },
    emits: ['back', 'payment-initiated'],
    setup(props, { emit }) {
        const selectedGateway = ref('paystack')
        const selectedCardType = ref('mastercard')
        const processing = ref(false)
        
        const cardDetails = ref({
            holderName: '',
            number: '',
            expiry: '',
            cvv: ''
        })

        const cardTypes = ref([
            {
                id: 'mastercard',
                name: 'Mastercard',
                logo: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iI0VCMDAxQiIvPgo8Y2lyY2xlIGN4PSIxNSIgY3k9IjEyIiByPSI3IiBmaWxsPSIjRkY1RjAwIi8+CjxjaXJjbGUgY3g9IjI1IiBjeT0iMTIiIHI9IjciIGZpbGw9IiNGRkY1RjAiLz4KPC9zdmc+'
            },
            {
                id: 'visa',
                name: 'Visa',
                logo: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iIzFFNzNBQSIvPgo8dGV4dCB4PSI1IiB5PSIxNiIgZmlsbD0id2hpdGUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxMiIgZm9udC13ZWlnaHQ9ImJvbGQiPlZJU0E8L3RleHQ+Cjwvc3ZnPg=='
            },
            {
                id: 'verve',
                name: 'Verve Card',
                logo: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iIzAwNjUzOSIvPgo8dGV4dCB4PSI4IiB5PSIxNiIgZmlsbD0id2hpdGUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSI4IiBmb250LXdlaWdodD0iYm9sZCI+VkVSVkU8L3RleHQ+Cjwvc3ZnPg=='
            },
            {
                id: 'googlepay',
                name: 'G Pay',
                logo: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iIzRDODVGRiIvPgo8dGV4dCB4PSIxMCIgeT0iMTYiIGZpbGw9IndoaXRlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iOCIgZm9udC13ZWlnaHQ9ImJvbGQiPkdQYXk8L3RleHQ+Cjwvc3ZnPg=='
            }
        ])

        const canProceed = computed(() => {
            // Both Paystack and Monnify redirect to their hosted payment pages
            return selectedGateway.value === 'paystack' || selectedGateway.value === 'monnify'
        })

        const selectGateway = (gateway) => {
            selectedGateway.value = gateway
        }

        const selectCardType = (type) => {
            selectedCardType.value = type
        }

        const formatCardNumber = (event) => {
            let value = event.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '')
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value
            cardDetails.value.number = formattedValue
        }

        const formatExpiry = (event) => {
            let value = event.target.value.replace(/[^0-9]/g, '')
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4)
            }
            cardDetails.value.expiry = value
        }

        const formatAmount = (amount) => {
            return new Intl.NumberFormat('en-NG', {
                style: 'currency',
                currency: 'NGN'
            }).format(amount)
        }

        const processPayment = async () => {
            if (!canProceed.value) return
            
            processing.value = true

            try {
                const paymentData = {
                    therapist_id: props.therapistId,
                    booking_date: props.bookingDate,
                    booking_time: props.bookingTime,
                    notes: props.notes,
                    payment_gateway: selectedGateway.value
                }

                // Both gateways redirect to their hosted payment pages - no card details needed

                emit('payment-initiated', paymentData)
            } catch (error) {
                console.error('Payment processing error:', error)
            } finally {
                processing.value = false
            }
        }

        return {
            selectedGateway,
            selectedCardType,
            cardDetails,
            cardTypes,
            processing,
            canProceed,
            selectGateway,
            selectCardType,
            formatCardNumber,
            formatExpiry,
            formatAmount,
            processPayment
        }
    }
}
</script> 