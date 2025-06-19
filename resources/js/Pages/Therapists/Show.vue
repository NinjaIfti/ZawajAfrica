<template>
    <Head :title="`${therapist.name} - Therapist Profile`" />
    
    <div class="flex h-screen bg-gray-50">
        <!-- Mobile Sidebar Overlay -->
        <div v-if="mobileMenuOpen" class="fixed inset-0 z-50 lg:hidden">
            <div class="absolute inset-0 bg-black bg-opacity-50" @click="mobileMenuOpen = false"></div>
            <div class="fixed top-0 left-0 h-full w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out">
                <Sidebar :user="$page.props.auth.user" />
            </div>
        </div>

        <!-- Left Sidebar - Hidden on mobile/tablet -->
        <div class="hidden lg:block">
            <Sidebar :user="$page.props.auth.user" />
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header with AppHeader component -->
            <div class=" border-b border-gray-200 px-4 lg:px-6 py-4">
                <AppHeader :user="$page.props.auth.user">
                    <template #title>
                        <div class="flex items-center">
                            <!-- Mobile Menu Button -->
                            <button 
                                @click="mobileMenuOpen = true"
                                class="lg:hidden mr-3 p-2 rounded-lg text-gray-600 hover:bg-gray-100"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            
                            <!-- Back Button -->
                            <button @click="goBack" class="mr-2 lg:mr-3 p-1 hover:bg-gray-100 rounded-full">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <h1 class="text-lg lg:text-2xl font-bold text-gray-900">Therapist Profile</h1>
                        </div>
                    </template>
                </AppHeader>
            </div>
            
            <!-- Main Content Area -->
            <div class="flex-1 overflow-y-auto p-4 lg:p-10">
                <div class="max-w-4xl mx-auto">
                    <!-- Therapist Profile Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="flex flex-col md:flex-row">
                            <!-- Therapist Photo -->
                            <div class="md:w-1/3 bg-white p-4 lg:p-6 flex items-center justify-center">
                                <div class="w-full max-w-xs">
                                    <div class="w-full h-40 md:h-48 lg:h-56 rounded-lg overflow-hidden bg-gray-200 shadow-lg">
                                        <img 
                                            v-if="therapist.photo_url" 
                                            :src="therapist.photo_url" 
                                            :alt="therapist.name"
                                            class="w-full h-full object-cover object-center"
                                        />
                                        <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center">
                                            <svg class="w-12 h-12 lg:w-16 lg:h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Therapist Details -->
                            <div class="md:w-2/3 p-4 lg:p-6">
                                <div class="mb-4 lg:mb-6">
                                    <h2 class="text-xl lg:text-2xl font-bold text-gray-900 mb-2">{{ therapist.name }}</h2>
                                    <p class="text-sm lg:text-base text-gray-600 mb-4">
                                        {{ Array.isArray(therapist.specializations) 
                                            ? therapist.specializations.join(', ') 
                                            : therapist.specializations }}
                                    </p>
                                    
                                    <!-- Stats Row -->
                                    <div class="grid grid-cols-3 gap-2 lg:gap-4 mb-4 lg:mb-6">
                                        <div class="text-center bg-gray-100 rounded-lg p-2 lg:p-3">
                                            <span class="block text-sm lg:text-lg font-semibold text-purple-600">{{ therapist.years_of_experience }} years</span>
                                            <span class="text-xs lg:text-sm text-gray-500">Experience</span>
                                        </div>
                                        <div class="text-center bg-gray-100 rounded-lg p-2 lg:p-3">
                                            <span class="block text-sm lg:text-lg font-semibold text-purple-600">{{ therapist.session_duration || '90 min' }}</span>
                                            <span class="text-xs lg:text-sm text-grey-500">Session</span>
                                        </div>
                                        <div class="text-center bg-gray-100 rounded-lg p-2 lg:p-3">
                                            <div class="flex items-center justify-center mb-1">
                                                <span class="text-sm lg:text-lg font-semibold text-gray-900 mr-1">{{ therapist.avg_rating?.toFixed(1) || '4.5' }}</span>
                                                <div class="flex">
                                                    <svg v-for="star in 5" :key="star" class="w-3 h-3 lg:w-4 lg:h-4" 
                                                         :class="star <= Math.floor(therapist.avg_rating || 4.5) ? 'text-yellow-400' : 'text-gray-300'" 
                                                         fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <span class="text-xs lg:text-sm text-gray-500">Reviews</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Consultation Fee -->
                                    <div class="text-center mb-4 lg:mb-6 bg-gray-100 rounded-lg p-2 lg:p-3">
                                        <span class="text-xs lg:text-sm text-gray-600">Online Consultation Fee:</span>
                                        <p class="text-lg lg:text-2xl font-bold text-gray-900">${{ Number(therapist.hourly_rate).toLocaleString() }}</p>
                                    </div>
                                    <!-- Book Appointment Button -->
                                    <button 
                                        @click="showBookingSlider = true"
                                        class="w-full bg-violet-600 hover:bg-violet-700 text-white font-medium py-2.5 lg:py-3 px-4 rounded-xl transition-colors duration-200 text-sm lg:text-base"
                                    >
                                        Book Appointment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Available Hours Section -->
                    <div class="mt-4 lg:mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-4 lg:p-6">
                            <button 
                                @click="showAvailableHours = !showAvailableHours"
                                class="w-full flex items-center justify-between text-left"
                            >
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                    <h3 class="text-base lg:text-lg font-semibold text-gray-900">Available Hours</h3>
                                    <span class="ml-2 text-xs lg:text-sm text-green-600">Available Now</span>
                                </div>
                                <svg 
                                    class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                    :class="{ 'rotate-180': showAvailableHours }"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <!-- Available Hours Content -->
                            <div v-if="showAvailableHours" class="mt-4 border-t pt-4">
                                <div v-if="therapist.schedule_by_day && Object.keys(therapist.schedule_by_day).length > 0" 
                                     class="space-y-3">
                                    <div v-for="(slots, day) in therapist.schedule_by_day" :key="day" class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                        <span class="font-medium text-gray-900 capitalize text-sm lg:text-base">{{ day }}</span>
                                        <div class="flex flex-wrap gap-2">
                                            <span v-for="slot in slots" :key="slot.start + slot.end" 
                                                  class="text-xs lg:text-sm bg-gray-100 text-gray-700 px-2 py-1 rounded">
                                                {{ slot.start }} - {{ slot.end }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-gray-500 text-center py-4">
                                    <p class="text-sm lg:text-base">{{ therapist.working_hours || 'Schedule information not available' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bio Section (if available) -->
                    <div v-if="therapist.bio" class="mt-4 lg:mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
                        <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-3">About</h3>
                        <p class="text-sm lg:text-base text-gray-600 leading-relaxed">{{ therapist.bio }}</p>
                    </div>
                    
                    <!-- Additional Info (if available) -->
                    <div v-if="therapist.additional_info" class="mt-4 lg:mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
                        <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-3">Additional Information</h3>
                        <p class="text-sm lg:text-base text-gray-600 leading-relaxed">{{ therapist.additional_info }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Time Slot Slider -->
        <div v-if="showBookingSlider" class="fixed inset-0 z-50 overflow-hidden">
            <div class="absolute inset-0 bg-black bg-opacity-50" @click="closeBookingSlider"></div>
            <div class="fixed top-0 left-0 h-full w-full sm:w-96 bg-white shadow-xl transform transition-transform duration-300 overflow-y-auto"
                 :class="showBookingSlider ? 'translate-x-0' : '-translate-x-full'">
                
                <!-- Slider Header -->
                <div class="flex items-center justify-between p-4 lg:p-6 border-b border-gray-200">
                    <h3 class="text-base lg:text-lg font-semibold text-gray-900">Select a time slot</h3>
                    <button @click="closeBookingSlider" class="p-2 hover:bg-gray-100 rounded-full">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-4 lg:p-6">

                <!-- Therapist Info -->
                <div class="flex items-center mb-4 p-3 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-full overflow-hidden bg-gray-200 mr-3 relative">
                        <img 
                            v-if="therapist.photo_url" 
                            :src="therapist.photo_url" 
                            :alt="therapist.name"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center">
                            <svg class="w-5 h-5 lg:w-6 lg:h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-3 h-3 lg:w-4 lg:h-4 bg-green-500 rounded-full border-2 border-white"></div>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm lg:text-base font-semibold text-gray-900">{{ therapist.name }}</h4>
                        <p class="text-xs lg:text-sm text-gray-600">{{ Array.isArray(therapist.specializations) ? therapist.specializations[0] : therapist.specializations }}</p>
                    </div>
                </div>

                <!-- Consultation Fee -->
                <div class="text-center mb-4 lg:mb-6 p-3 bg-gray-50 rounded-lg">
                    <span class="text-xs lg:text-sm text-gray-600">Online Consultation Fee:</span>
                    <p class="text-base lg:text-lg font-bold text-purple-600">${{ therapist.hourly_rate }}</p>
                </div>

                <!-- Platform Selection -->
                <div class="mb-4 lg:mb-6">
                    <h4 class="text-xs lg:text-sm font-medium text-gray-700 mb-3">Select Platform</h4>
                    <div class="space-y-2 lg:space-y-3">
                        <label class="flex items-center justify-between p-2 lg:p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                               :class="selectedPlatform === 'google_meet' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'">
                            <div class="flex items-center">
                                <div class="w-6 h-6 lg:w-8 lg:h-8 bg-red-500 rounded-lg flex items-center justify-center mr-2 lg:mr-3">
                                    <svg class="w-3 h-3 lg:w-5 lg:h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </div>
                                <span class="text-sm lg:text-base font-medium text-gray-900">Google Meet</span>
                            </div>
                            <input type="radio" v-model="selectedPlatform" value="google_meet" class="text-purple-600">
                        </label>

                        <label class="flex items-center justify-between p-2 lg:p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                               :class="selectedPlatform === 'whatsapp' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'">
                            <div class="flex items-center">
                                <div class="w-6 h-6 lg:w-8 lg:h-8 bg-green-500 rounded-lg flex items-center justify-center mr-2 lg:mr-3">
                                    <svg class="w-3 h-3 lg:w-5 lg:h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                    </svg>
                                </div>
                                <span class="text-sm lg:text-base font-medium text-gray-900">Whatsapp</span>
                            </div>
                            <input type="radio" v-model="selectedPlatform" value="whatsapp" class="text-purple-600">
                        </label>

                        <label class="flex items-center justify-between p-2 lg:p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                               :class="selectedPlatform === 'zoom' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'">
                            <div class="flex items-center">
                                <div class="w-6 h-6 lg:w-8 lg:h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-2 lg:mr-3">
                                    <svg class="w-3 h-3 lg:w-5 lg:h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12c0 6.627-5.373 12-12 12S0 18.627 0 12 5.373 0 12 0s12 5.373 12 12zm-6.343-3.343a1.5 1.5 0 010 2.121L14.12 14.12a1.5 1.5 0 01-2.12 0l-3.538-3.537a1.5 1.5 0 010-2.121l3.537-3.538a1.5 1.5 0 012.121 0l3.537 3.538z"/>
                                    </svg>
                                </div>
                                <span class="text-sm lg:text-base font-medium text-gray-900">Zoom</span>
                            </div>
                            <input type="radio" v-model="selectedPlatform" value="zoom" class="text-purple-600">
                        </label>
                    </div>
                </div>

                <!-- Date Selection -->
                <div class="mb-4 lg:mb-6">
                    <h4 class="text-xs lg:text-sm font-medium text-gray-700 mb-3">Select date and time</h4>
                    <div class="flex space-x-2 mb-4 overflow-x-auto">
                        <button v-for="date in availableDates" :key="date.value" 
                                @click="selectedDate = date.value"
                                class="flex-shrink-0 px-3 py-2 lg:px-4 lg:py-3 rounded-lg text-center min-w-[50px] lg:min-w-[60px] transition-colors"
                                :class="selectedDate === date.value ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                            <div class="text-xs">{{ date.day }}</div>
                            <div class="text-sm lg:text-base font-semibold">{{ date.date }}</div>
                        </button>
                    </div>
                </div>

                <!-- Time Slots -->
                <div class="mb-4 lg:mb-6" v-if="selectedDate">
                    <div v-if="getSlotsForSelectedDate.length === 0" class="text-center py-6 lg:py-8">
                        <p class="text-gray-500 text-sm lg:text-base">No available slots for this date</p>
                        <p class="text-xs lg:text-sm text-gray-400 mt-1">Please select a different date</p>
                    </div>
                    
                    <div v-else>
                        <div v-if="afternoonSlots.length > 0" class="mb-4">
                            <h5 class="text-xs lg:text-sm font-medium text-gray-700 mb-3">Afternoon Slots</h5>
                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-2">
                                <button v-for="slot in afternoonSlots" :key="slot"
                                        @click="selectedTimeSlot = slot"
                                        class="py-2 px-2 lg:px-3 text-xs lg:text-sm rounded-lg border transition-colors"
                                        :class="selectedTimeSlot === slot ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-300'">
                                    {{ slot }}
                                </button>
                            </div>
                        </div>

                        <div v-if="eveningSlots.length > 0">
                            <h5 class="text-xs lg:text-sm font-medium text-gray-700 mb-3">Evening Slots</h5>
                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-2">
                                <button v-for="slot in eveningSlots" :key="slot"
                                        @click="selectedTimeSlot = slot"
                                        class="py-2 px-2 lg:px-3 text-xs lg:text-sm rounded-lg border transition-colors"
                                        :class="selectedTimeSlot === slot ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-300'">
                                    {{ slot }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-else class="mb-4 lg:mb-6 text-center py-6 lg:py-8">
                    <p class="text-gray-500 text-sm lg:text-base">Please select a date to see available time slots</p>
                </div>

                <!-- Confirm Button -->
                <button @click="proceedToPayment" 
                        :disabled="!selectedPlatform || !selectedDate || !selectedTimeSlot"
                        class="w-full bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 text-white font-medium py-3 lg:py-4 rounded-xl transition-colors text-sm lg:text-base">
                    Confirm Booking
                </button>
                </div>
            </div>
        </div>

        <!-- Payment Process Slider -->
        <div v-if="showPaymentSlider" class="fixed inset-0 z-50 overflow-hidden">
            <div class="absolute inset-0 bg-black bg-opacity-50" @click="closePaymentSlider"></div>
            <div class="fixed top-0 left-0 h-full w-full sm:w-96 bg-white shadow-xl transform transition-transform duration-300 overflow-y-auto"
                 :class="showPaymentSlider ? 'translate-x-0' : '-translate-x-full'">
                
                <!-- Slider Header -->
                <div class="flex items-center justify-between p-4 lg:p-6 border-b border-gray-200">
                    <h3 class="text-base lg:text-lg font-semibold text-gray-900">Select Payment Process</h3>
                    <button @click="closePaymentSlider" class="p-2 hover:bg-gray-100 rounded-full">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-4 lg:p-6">

                <!-- Payment Options -->
                <div class="space-y-3 lg:space-y-4 mb-6 lg:mb-8">
                    <div class="p-3 lg:p-4 border border-gray-200 rounded-lg flex items-center justify-between hover:bg-gray-50 cursor-pointer">
                        <div class="flex items-center">
                            <div class="w-8 h-8 lg:w-10 lg:h-10 bg-orange-500 rounded-lg flex items-center justify-center mr-3 lg:mr-4">
                                <span class="text-white font-bold text-xs lg:text-sm">GT</span>
                            </div>
                            <div>
                                <h4 class="text-sm lg:text-base font-medium text-gray-900">GTpay</h4>
                                <p class="text-xs lg:text-sm text-gray-500">Payments within Nigeria</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 lg:w-5 lg:h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>

                    <div @click="proceedWithPaystackPayment" 
                         :class="{'pointer-events-none opacity-50': isProcessingPayment}"
                         class="p-3 lg:p-4 border border-gray-200 rounded-lg flex items-center justify-between hover:bg-gray-50 cursor-pointer transition-colors">
                        <div class="flex items-center">
                            <div class="w-8 h-8 lg:w-10 lg:h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3 lg:mr-4">
                                <svg v-if="!isProcessingPayment" class="w-4 h-4 lg:w-6 lg:h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <svg v-else class="animate-spin w-4 h-4 lg:w-6 lg:h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm lg:text-base font-medium text-gray-900">
                                    {{ isProcessingPayment ? 'Processing...' : 'Paystack' }}
                                </h4>
                                <p class="text-xs lg:text-sm text-gray-500">Secure card payment</p>
                            </div>
                        </div>
                        <svg v-if="!isProcessingPayment" class="w-4 h-4 lg:w-5 lg:h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>

                <!-- Booking Summary -->
                <div class="border-t pt-4 lg:pt-6 mb-4 lg:mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm lg:text-base font-medium text-gray-900">{{ therapist.name }}</span>
                        <span class="text-xs lg:text-sm text-gray-600">Fee:</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs lg:text-sm text-gray-600">{{ selectedDate }} {{ selectedTimeSlot }}</span>
                        <span class="text-base lg:text-lg font-bold text-purple-600">${{ therapist.hourly_rate }}</span>
                    </div>
                </div>

                <!-- Back Button -->
                <button @click="backToTimeSelection" 
                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 lg:py-4 rounded-xl transition-colors text-sm lg:text-base">
                    Back
                </button>
                </div>
            </div>
        </div>

        <!-- Payment Success Modal -->
        <PaymentSuccessModal 
            :show="showPaymentSuccessModal" 
            :payment-type="page.props.flash?.payment_type || 'therapist_booking'"
            @close="closePaymentSuccessModal" 
        />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { router, Head, usePage } from '@inertiajs/vue3'
import Sidebar from '@/Components/Sidebar.vue'
import AppHeader from '@/Components/AppHeader.vue'
import PaymentSuccessModal from '@/Components/PaymentSuccessModal.vue'
import axios from 'axios'

const page = usePage()
const showPaymentSuccessModal = ref(false)

const props = defineProps({
    therapist: Object,
    userBookings: Array,
})

const showAvailableHours = ref(false)
const showBookingSlider = ref(false)
const showPaymentSlider = ref(false)
const selectedPlatform = ref('')
const selectedDate = ref('')
const selectedTimeSlot = ref('')
const mobileMenuOpen = ref(false)
const isProcessingPayment = ref(false)

// Generate available dates for the next 7 days
const availableDates = ref([])
const generateDates = () => {
    const dates = []
    const today = new Date()
    
    for (let i = 0; i < 7; i++) {
        const date = new Date(today)
        date.setDate(today.getDate() + i)
        
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
        const day = i === 0 ? 'Today' : dayNames[date.getDay()]
        const dateNum = date.getDate()
        const fullDayName = date.toLocaleDateString('en-US', { weekday: 'long' })
        
        dates.push({
            day: day,
            date: dateNum,
            fullDayName: fullDayName,
            value: date.toLocaleDateString('en-GB', { 
                weekday: 'short', 
                day: 'numeric', 
                month: 'short' 
            })
        })
    }
    
    availableDates.value = dates
}

// Initialize dates when component loads
generateDates()

// Reset selected time slot when date changes
watch(selectedDate, () => {
    selectedTimeSlot.value = ''
})

// Get time slots for the selected date from real therapist availability
const getSlotsForSelectedDate = computed(() => {
    if (!selectedDate.value || !props.therapist.slots_by_day) {
        return []
    }
    
    // Find the selected date's full day name
    const selectedDateObj = availableDates.value.find(date => date.value === selectedDate.value)
    if (!selectedDateObj) {
        return []
    }
    
    // Get slots for this day from therapist's real availability
    const daySlots = props.therapist.slots_by_day[selectedDateObj.fullDayName] || []
    
    // Convert to display format and sort
    return daySlots.map(slot => {
        // Convert 24-hour format to 12-hour format
        const time = new Date(`2000-01-01T${slot}:00`)
        return time.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        })
    }).sort((a, b) => {
        // Sort by time
        const timeA = new Date(`2000-01-01 ${a}`)
        const timeB = new Date(`2000-01-01 ${b}`)
        return timeA - timeB
    })
})

// Split slots into afternoon and evening based on time
const afternoonSlots = computed(() => {
    return getSlotsForSelectedDate.value.filter(slot => {
        const hour = parseInt(slot.split(':')[0])
        const isPM = slot.includes('PM')
        const hour24 = isPM && hour !== 12 ? hour + 12 : (hour === 12 && !isPM ? 0 : hour)
        return hour24 >= 12 && hour24 < 17 // 12 PM to 5 PM
    })
})

const eveningSlots = computed(() => {
    return getSlotsForSelectedDate.value.filter(slot => {
        const hour = parseInt(slot.split(':')[0])
        const isPM = slot.includes('PM')
        const hour24 = isPM && hour !== 12 ? hour + 12 : (hour === 12 && !isPM ? 0 : hour)
        return hour24 >= 17 || hour24 < 12 // 5 PM onwards or morning slots
    })
})

const goBack = () => {
    router.visit(route('therapists.index'))
}

const closeBookingSlider = () => {
    showBookingSlider.value = false
    // Reset selections
    selectedPlatform.value = ''
    selectedDate.value = ''
    selectedTimeSlot.value = ''
}

const closePaymentSlider = () => {
    showPaymentSlider.value = false
}

const proceedToPayment = () => {
    if (selectedPlatform.value && selectedDate.value && selectedTimeSlot.value) {
        showBookingSlider.value = false
        showPaymentSlider.value = true
    }
}

const backToTimeSelection = () => {
    showPaymentSlider.value = false
    showBookingSlider.value = true
}

// Function to handle Paystack payment
const proceedWithPaystackPayment = async () => {
    if (!selectedPlatform.value || !selectedDate.value || !selectedTimeSlot.value) {
        alert('Please select all required fields before proceeding.');
        return;
    }

    isProcessingPayment.value = true;

    try {
        // Convert selected date to proper format
        const bookingDate = convertToBookingDate(selectedDate.value);
        
        // Initialize payment with backend
        const response = await axios.post(route('payment.therapist.initialize'), {
            therapist_id: props.therapist.id,
            booking_date: bookingDate,
            booking_time: selectedTimeSlot.value,
            platform: selectedPlatform.value,
            notes: `Session via ${selectedPlatform.value}`
        });

        if (response.data.status) {
            // Redirect to Paystack payment page
            window.location.href = response.data.authorization_url;
        } else {
            alert('Payment initialization failed: ' + response.data.message);
        }
    } catch (error) {
        console.error('Payment error:', error);
        alert('An error occurred while initializing payment. Please try again.');
    } finally {
        isProcessingPayment.value = false;
    }
};

// Convert display date to booking date format
const convertToBookingDate = (displayDate) => {
    // Find the corresponding date object
    const dateObj = availableDates.value.find(date => date.value === displayDate);
    if (!dateObj) return '';
    
    // Create a proper date string
    const today = new Date();
    const targetDate = new Date(today);
    
    // Find the day index
    const dayIndex = availableDates.value.findIndex(date => date.value === displayDate);
    targetDate.setDate(today.getDate() + dayIndex);
    
    return targetDate.toISOString().split('T')[0]; // Format: YYYY-MM-DD
};

// Check for payment success on page load
onMounted(() => {
    if (page.props.flash?.payment_success) {
        showPaymentSuccessModal.value = true;
    }
});

const closePaymentSuccessModal = () => {
    showPaymentSuccessModal.value = false;
};
</script> 