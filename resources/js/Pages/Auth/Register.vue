<script setup>
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import { ref, computed, onMounted } from 'vue';
    import InputError from '@/Components/InputError.vue';

    const currentStep = ref(1);
    const totalSteps = 5;

    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        gender: '',
        interested_in: '',
        dob_day: '10',
        dob_month: 'Feb',
        dob_year: '1994',
        country: 'Nigeria',
        state: '',
        city: '',
        // Additional fields can be added here
    });

    // Date of birth refs
    const dayListRef = ref(null);
    const monthListRef = ref(null);
    const yearListRef = ref(null);

    // Generate days array (1-31)
    const days = Array.from({ length: 31 }, (_, i) => {
        const num = i + 1;
        return num < 10 ? `0${num}` : `${num}`;
    });

    // Create repeated days for circular scrolling
    const repeatedDays = [...days, ...days.slice(0, 5)];

    // Months array
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    // Create repeated months for circular scrolling
    const repeatedMonths = [...months, ...months.slice(0, 3)];

    // Generate years array (current year - 80 to current year - 18)
    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 63 }, (_, i) => `${currentYear - 18 - i}`);

    // Calculate age based on selected date
    const calculateAge = computed(() => {
        if (!form.dob_day || !form.dob_month || !form.dob_year) return null;

        const birthDate = new Date(form.dob_year, getMonthIndex(form.dob_month), form.dob_day);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        return age;
    });

    // Helper function to get month index from name
    function getMonthIndex(monthName) {
        return months.indexOf(monthName);
    }

    // Function to handle day scroll with circular behavior
    const handleDayScroll = event => {
        if (!dayListRef.value) return;

        // After scrolling ends, snap to nearest item
        clearTimeout(dayListRef.value._scrollTimeout);
        dayListRef.value._scrollTimeout = setTimeout(() => {
            const scrollTop = event.target.scrollTop;
            const itemHeight = 40; // Height of each item
            let selectedIndex = Math.round(scrollTop / itemHeight);

            // Handle looping logic
            if (selectedIndex >= days.length) {
                // We're in the repeated section, so map back to the original set
                const originalIndex = selectedIndex % days.length;

                // Update form value from the original set
                form.dob_day = days[originalIndex];

                // Snap to the equivalent position in the original set
                if (originalIndex === 0) {
                    // If we're at the first item in the repeated section, smoothly snap
                    // to the first item in the original section
                    event.target.scrollTop = 0;
                } else {
                    // Otherwise snap to the corresponding position
                    event.target.scrollTo({
                        top: originalIndex * itemHeight,
                        behavior: 'smooth',
                    });
                }
            } else if (selectedIndex < 0) {
                // If we're before the first item, loop to the end
                const originalIndex = days.length - 1;
                form.dob_day = days[originalIndex];

                event.target.scrollTo({
                    top: originalIndex * itemHeight,
                    behavior: 'smooth',
                });
            } else {
                // We're within the normal range
                form.dob_day = days[selectedIndex];

                // Smooth scroll to the selected item
                event.target.scrollTo({
                    top: selectedIndex * itemHeight,
                    behavior: 'smooth',
                });
            }
        }, 100);
    };

    // Function to handle month scroll with circular behavior
    const handleMonthScroll = event => {
        if (!monthListRef.value) return;

        // After scrolling ends, snap to nearest item
        clearTimeout(monthListRef.value._scrollTimeout);
        monthListRef.value._scrollTimeout = setTimeout(() => {
            const scrollTop = event.target.scrollTop;
            const itemHeight = 40; // Height of each item
            let selectedIndex = Math.round(scrollTop / itemHeight);

            // Handle looping logic
            if (selectedIndex >= months.length) {
                // We're in the repeated section, so map back to the original set
                const originalIndex = selectedIndex % months.length;

                // Update form value from the original set
                form.dob_month = months[originalIndex];

                // Snap to the equivalent position in the original set
                if (originalIndex === 0) {
                    // If we're at the first item in the repeated section, smoothly snap
                    // to the first item in the original section
                    event.target.scrollTop = 0;
                } else {
                    // Otherwise snap to the corresponding position
                    event.target.scrollTo({
                        top: originalIndex * itemHeight,
                        behavior: 'smooth',
                    });
                }
            } else if (selectedIndex < 0) {
                // If we're before the first item, loop to the end
                const originalIndex = months.length - 1;
                form.dob_month = months[originalIndex];

                event.target.scrollTo({
                    top: originalIndex * itemHeight,
                    behavior: 'smooth',
                });
            } else {
                // We're within the normal range
                form.dob_month = months[selectedIndex];

                // Smooth scroll to the selected item
                event.target.scrollTo({
                    top: selectedIndex * itemHeight,
                    behavior: 'smooth',
                });
            }
        }, 100);
    };

    // Function to handle year scroll
    const handleYearScroll = event => {
        if (!yearListRef.value) return;

        // After scrolling ends, snap to nearest item
        clearTimeout(yearListRef.value._scrollTimeout);
        yearListRef.value._scrollTimeout = setTimeout(() => {
            const scrollTop = event.target.scrollTop;
            const itemHeight = 40; // Height of each item
            let selectedIndex = Math.round(scrollTop / itemHeight);

            // Prevent scrolling past the end
            const maxIndex = years.length - 1;
            selectedIndex = Math.min(Math.max(0, selectedIndex), maxIndex);

            // Smooth scroll to the selected item
            event.target.scrollTo({
                top: selectedIndex * itemHeight,
                behavior: 'smooth',
            });

            // Update form value
            form.dob_year = years[selectedIndex];
        }, 100);
    };

    // Initialize scroll positions
    onMounted(() => {
        // Short delay to ensure refs are mounted
        setTimeout(() => {
            if (dayListRef.value) {
                const dayIndex = days.indexOf(form.dob_day);
                if (dayIndex >= 0) {
                    dayListRef.value.scrollTop = dayIndex * 40;
                }
            }

            if (monthListRef.value) {
                const monthIndex = months.indexOf(form.dob_month);
                if (monthIndex >= 0) {
                    monthListRef.value.scrollTop = monthIndex * 40;
                }
            }

            if (yearListRef.value) {
                const yearIndex = years.indexOf(form.dob_year);
                if (yearIndex >= 0) {
                    yearListRef.value.scrollTop = yearIndex * 40;
                }
            }
        }, 300);
    });

    const nextStep = () => {
        if (currentStep.value < totalSteps) {
            currentStep.value++;
        } else {
            // Submit form when all steps are completed
            submit();
        }
    };

    const prevStep = () => {
        if (currentStep.value > 1) {
            currentStep.value--;
        }
    };

    const submit = () => {
        form.post(route('register'), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };

  

    // Function to select gender
    const selectGender = gender => {
        form.gender = gender;
    };

    // Function to select gender interest
    const selectInterest = gender => {
        form.interested_in = gender;
    };

    // Function to handle day click with circular behavior
    const handleDayClick = event => {
        const clickedElement = event.target.closest('[data-value]');
        if (!clickedElement || !dayListRef.value) return;

        const day = clickedElement.getAttribute('data-value');
        form.dob_day = day;

        // Calculate scroll position
        const itemHeight = 40;
        const index = days.indexOf(day);
        if (index >= 0) {
            dayListRef.value.scrollTo({
                top: index * itemHeight,
                behavior: 'smooth',
            });
        }
    };

    // Function to handle month click with circular behavior
    const handleMonthClick = event => {
        const clickedElement = event.target.closest('[data-value]');
        if (!clickedElement || !monthListRef.value) return;

        const month = clickedElement.getAttribute('data-value');
        form.dob_month = month;

        // Calculate scroll position
        const itemHeight = 40;
        const index = months.indexOf(month);
        if (index >= 0) {
            monthListRef.value.scrollTo({
                top: index * itemHeight,
                behavior: 'smooth',
            });
        }
    };

    // Function to handle year click
    const handleYearClick = event => {
        const clickedElement = event.target.closest('[data-value]');
        if (!clickedElement || !yearListRef.value) return;

        const year = clickedElement.getAttribute('data-value');
        form.dob_year = year;

        // Calculate scroll position
        const itemHeight = 40;
        const index = years.indexOf(year);
        if (index >= 0) {
            yearListRef.value.scrollTo({
                top: index * itemHeight,
                behavior: 'smooth',
            });
        }
    };

    // Add Nigeria states and cities data
    const nigerianStates = [
        'Abia',
        'Adamawa',
        'Akwa Ibom',
        'Anambra',
        'Bauchi',
        'Bayelsa',
        'Benue',
        'Borno',
        'Cross River',
        'Delta',
        'Ebonyi',
        'Edo',
        'Ekiti',
        'Enugu',
        'FCT (Abuja)',
        'Gombe',
        'Imo',
        'Jigawa',
        'Kaduna',
        'Kano',
        'Katsina',
        'Kebbi',
        'Kogi',
        'Kwara',
        'Lagos',
        'Nasarawa',
        'Niger',
        'Ogun',
        'Ondo',
        'Osun',
        'Oyo',
        'Plateau',
        'Rivers',
        'Sokoto',
        'Taraba',
        'Yobe',
        'Zamfara',
    ];

    // Nigerian cities by state
    const nigerianCities = {
        Abia: ['Aba', 'Arochukwu', 'Umuahia', 'Ohafia', 'Isuikwuato'],
        Adamawa: ['Yola', 'Mubi', 'Jimeta', 'Numan', 'Ganye'],
        'Akwa Ibom': ['Uyo', 'Eket', 'Ikot Ekpene', 'Oron', 'Abak'],
        Anambra: ['Awka', 'Onitsha', 'Nnewi', 'Ekwulobia', 'Aguata'],
        Bauchi: ['Bauchi', 'Azare', 'Misau', "Jama'are", 'Katagum'],
        Bayelsa: ['Yenagoa', 'Brass', 'Nembe', 'Ogbia', 'Sagbama'],
        Benue: ['Makurdi', 'Gboko', 'Otukpo', 'Katsina-Ala', 'Vandeikya'],
        Borno: ['Maiduguri', 'Bama', 'Gwoza', 'Dikwa', 'Monguno'],
        'Cross River': ['Calabar', 'Ogoja', 'Ugep', 'Obudu', 'Ikom'],
        Delta: ['Asaba', 'Warri', 'Ughelli', 'Sapele', 'Agbor'],
        Ebonyi: ['Abakaliki', 'Afikpo', 'Onueke', 'Ishieke', 'Uburu'],
        Edo: ['Benin City', 'Auchi', 'Ekpoma', 'Uromi', 'Sabongida-Ora'],
        Ekiti: ['Ado Ekiti', 'Ikere', 'Ikole', 'Ijero', 'Oye'],
        Enugu: ['Enugu', 'Nsukka', 'Oji River', 'Awgu', 'Udi'],
        'FCT (Abuja)': ['Abuja', 'Gwagwalada', 'Kuje', 'Bwari', 'Kwali'],
        Gombe: ['Gombe', 'Billiri', 'Dukku', 'Kaltungo', 'Bajoga'],
        Imo: ['Owerri', 'Orlu', 'Okigwe', 'Mbaise', 'Oguta'],
        Jigawa: ['Dutse', 'Hadejia', 'Gumel', 'Kazaure', 'Ringim'],
        Kaduna: ['Kaduna', 'Zaria', 'Kafanchan', 'Kagoro', 'Birnin Gwari'],
        Kano: ['Kano', 'Dala', 'Nassarawa', 'Ungogo', 'Rano'],
        Katsina: ['Katsina', 'Funtua', 'Daura', 'Jibia', 'Dutsin-Ma'],
        Kebbi: ['Birnin Kebbi', 'Argungu', 'Yauri', 'Zuru', 'Jega'],
        Kogi: ['Lokoja', 'Okene', 'Kabba', 'Idah', 'Ankpa'],
        Kwara: ['Ilorin', 'Offa', 'Omu-Aran', 'Pategi', 'Lafiagi'],
        Lagos: [
            'Lagos Island',
            'Ikeja',
            'Lekki',
            'Badagry',
            'Epe',
            'Ikorodu',
            'Mushin',
            'Surulere',
            'Alimosho',
            'Agege',
        ],
        Nasarawa: ['Lafia', 'Keffi', 'Akwanga', 'Nasarawa', 'Karu'],
        Niger: ['Minna', 'Bida', 'Kontagora', 'Suleja', 'Lapai'],
        Ogun: ['Abeokuta', 'Ijebu Ode', 'Sagamu', 'Ilaro', 'Ota'],
        Ondo: ['Akure', 'Ondo', 'Owo', 'Ikare', 'Okitipupa'],
        Osun: ['Osogbo', 'Ife', 'Ilesa', 'Ede', 'Iwo'],
        Oyo: ['Ibadan', 'Ogbomosho', 'Oyo', 'Iseyin', 'Saki'],
        Plateau: ['Jos', 'Bukuru', 'Pankshin', 'Shendam', 'Langtang'],
        Rivers: ['Port Harcourt', 'Bonny', 'Buguma', 'Degema', 'Eleme'],
        Sokoto: ['Sokoto', 'Tambuwal', 'Wurno', 'Gwadabawa', 'Illela'],
        Taraba: ['Jalingo', 'Wukari', 'Bali', 'Gembu', 'Ibi'],
        Yobe: ['Damaturu', 'Potiskum', 'Gashua', 'Nguru', 'Geidam'],
        Zamfara: ['Gusau', 'Kaura Namoda', 'Anka', 'Talata Mafara', 'Tsafe'],
    };

    // Computed property to get cities based on selected state
    const availableCities = computed(() => {
        return form.state ? nigerianCities[form.state] || [] : [];
    });
</script>

<template>
    <Head title="Create Account" />

    <div class="flex min-h-screen w-full flex-col md:flex-row">
        <!-- Left side - Background image and branding -->
        <div class="relative hidden h-screen w-full md:block md:max-w-[600px] md:flex-shrink-0">
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

            <!-- Language selector -->
            <div
                class="absolute left-[5%] top-[5%] z-10 flex items-center rounded-full bg-[#654396] bg-opacity-70 px-4 py-2"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="mr-2 h-5 w-5 text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1"
                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"
                    />
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

        <!-- Right side - Registration form -->
        <div class="flex min-h-screen w-full flex-1 items-center justify-center bg-white">
            <div class="flex items-center justify-center h-full w-full py-8">
                <div class="w-full max-w-[600px] px-8 md:px-[90px]">
                    <!-- Progress bar -->
                    <div class="mb-[40px] w-full">
                        <div class="flex w-full space-x-2">
                            <div
                                v-for="step in totalSteps"
                                :key="step"
                                :class="[
                                    'h-[6px] flex-1 rounded-full',
                                    step <= currentStep ? 'bg-[#E6A157]' : 'bg-[#E4E5E4]',
                                ]"
                            ></div>
                        </div>
                    </div>

                    <!-- Step 1: Name -->
                    <div v-if="currentStep === 1">
                        <div class="mb-[40px]">
                            <h2 class="text-5xl font-bold font-display text-[#04060A]">What is your name?</h2>
                        </div>

                        <div class="mb-[30px]">
                            <label for="name" class="mb-3 block text-lg text-[#41465a]">Full Name</label>
                            <div class="flex items-center rounded-lg bg-[#F5F5F5] px-5 py-5">
                                <input
                                    id="name"
                                    type="text"
                                    class="w-full bg-transparent text-lg text-gray-700 focus:outline-none"
                                    v-model="form.name"
                                    required
                                    autofocus
                                    autocomplete="name"
                                    placeholder="Enter your full name"
                                />
                            </div>
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                    </div>

                    <!-- Step 2: Gender -->
                    <div v-if="currentStep === 2">
                        <div class="mb-[40px]">
                            <h2 class="text-5xl font-bold font-display text-[#04060A]">What is your Gender?</h2>
                        </div>

                        <div class="mb-8">
                            <p class="mb-4 text-lg text-[#41465a]">I am</p>
                            <div class="flex gap-4">
                                <!-- Male option -->
                                <div
                                    @click="selectGender('male')"
                                    class="flex-1 cursor-pointer rounded-xl border transition-all duration-200"
                                    :class="[
                                        form.gender === 'male'
                                            ? 'border-blue-500 bg-blue-50'
                                            : 'border-gray-200 bg-[#F5F5F5] hover:bg-blue-50/30',
                                    ]"
                                >
                                    <div class="flex flex-col items-center py-6 px-4">
                                        <!-- Male image -->
                                        <div class="mb-2">
                                            <img src="/images/male.png" alt="Male" class="h-16 w-16" />
                                        </div>
                                        <span
                                            class="text-lg font-medium"
                                            :class="form.gender === 'male' ? 'text-blue-600' : 'text-gray-700'"
                                        >
                                            Male
                                        </span>
                                    </div>
                                </div>

                                <!-- Female option -->
                                <div
                                    @click="selectGender('female')"
                                    class="flex-1 cursor-pointer rounded-xl border transition-all duration-200"
                                    :class="[
                                        form.gender === 'female'
                                            ? 'border-pink-500 bg-pink-50'
                                            : 'border-gray-200 bg-[#F5F5F5] hover:bg-pink-50/30',
                                    ]"
                                >
                                    <div class="flex flex-col items-center py-6 px-4">
                                        <!-- Female image -->
                                        <div class="mb-2">
                                            <img src="/images/woman.png" alt="Female" class="h-16 w-16" />
                                        </div>
                                        <span
                                            class="text-lg font-medium"
                                            :class="form.gender === 'female' ? 'text-pink-600' : 'text-gray-700'"
                                        >
                                            Female
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.gender" />
                        </div>

                        <div class="mb-4">
                            <p class="mb-4 text-lg text-[#41465a]">I am interested in</p>
                            <div class="flex gap-4">
                                <!-- Interested in Male -->
                                <div
                                    @click="selectInterest('male')"
                                    class="flex-1 cursor-pointer rounded-xl border transition-all duration-200"
                                    :class="[
                                        form.interested_in === 'male'
                                            ? 'border-blue-500 bg-blue-50'
                                            : 'border-gray-200 bg-[#F5F5F5] hover:bg-blue-50/30',
                                    ]"
                                >
                                    <div class="flex flex-col items-center py-6 px-4">
                                        <!-- Male image -->
                                        <div class="mb-2">
                                            <img src="/images/male.png" alt="Male" class="h-16 w-16" />
                                        </div>
                                        <span
                                            class="text-lg font-medium"
                                            :class="form.interested_in === 'male' ? 'text-blue-600' : 'text-gray-700'"
                                        >
                                            Male
                                        </span>
                                    </div>
                                </div>

                                <!-- Interested in Female -->
                                <div
                                    @click="selectInterest('female')"
                                    class="flex-1 cursor-pointer rounded-xl border transition-all duration-200"
                                    :class="[
                                        form.interested_in === 'female'
                                            ? 'border-pink-500 bg-pink-50'
                                            : 'border-gray-200 bg-[#F5F5F5] hover:bg-pink-50/30',
                                    ]"
                                >
                                    <div class="flex flex-col items-center py-6 px-4">
                                        <!-- Female image -->
                                        <div class="mb-2">
                                            <img src="/images/woman.png" alt="Female" class="h-16 w-16" />
                                        </div>
                                        <span
                                            class="text-lg font-medium"
                                            :class="form.interested_in === 'female' ? 'text-pink-600' : 'text-gray-700'"
                                        >
                                            Female
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.interested_in" />
                        </div>
                    </div>

                    <!-- Step 3: Date of Birth -->
                    <div v-if="currentStep === 3">
                        <div class="mb-[40px]">
                            <h2 class="text-5xl font-bold font-display text-[#04060A]">What is your DOB?</h2>
                        </div>

                        <div class="mb-[30px]">
                            <div class="flex flex-col items-center">
                                <div class="w-full max-w-[280px] mb-8">
                                    <!-- Date selector labels -->
                                    <div class="flex justify-center gap-6 mb-2">
                                        <div class="w-[60px] text-center">
                                            <span class="text-sm font-medium text-gray-500">Day</span>
                                        </div>
                                        <div class="w-[60px] text-center">
                                            <span class="text-sm font-medium text-gray-500">Month</span>
                                        </div>
                                        <div class="w-[60px] text-center">
                                            <span class="text-sm font-medium text-gray-500">Year</span>
                                        </div>
                                    </div>

                                    <!-- Date selector -->
                                    <div class="flex justify-center gap-6 mb-6 relative">
                                        <!-- Highlight selection area -->
                                        <div
                                            class="absolute left-0 right-0 top-[78px] h-[40px] bg-[#F5F5F5] rounded-md z-0"
                                        ></div>

                                        <!-- Day selector -->
                                        <div class="relative w-[60px] h-[184px] z-10">
                                            <div
                                                ref="dayListRef"
                                                @scroll="handleDayScroll"
                                                @click="handleDayClick"
                                                class="absolute inset-0 overflow-y-auto no-scrollbar scroll-smooth cursor-pointer"
                                                style="scroll-snap-type: y mandatory; -webkit-overflow-scrolling: touch"
                                            >
                                                <!-- Empty space at top for padding -->
                                                <div class="h-[72px]"></div>

                                                <!-- Days with repeated items at the end for circular effect -->
                                                <div
                                                    v-for="day in repeatedDays"
                                                    :key="`day-${day}-${repeatedDays.indexOf(day)}`"
                                                    :data-value="day"
                                                    class="h-[40px] flex items-center justify-center text-center text-xl transition-all duration-150 select-none"
                                                    :class="
                                                        form.dob_day === day
                                                            ? 'font-semibold text-[#654396]'
                                                            : 'text-gray-400 hover:text-gray-600'
                                                    "
                                                >
                                                    {{ day }}
                                                </div>

                                                <!-- Empty space at bottom for padding -->
                                                <div class="h-[72px]"></div>
                                            </div>
                                        </div>

                                        <!-- Month selector -->
                                        <div class="relative w-[60px] h-[184px] z-10">
                                            <div
                                                ref="monthListRef"
                                                @scroll="handleMonthScroll"
                                                @click="handleMonthClick"
                                                class="absolute inset-0 overflow-y-auto no-scrollbar scroll-smooth cursor-pointer"
                                                style="scroll-snap-type: y mandatory; -webkit-overflow-scrolling: touch"
                                            >
                                                <!-- Empty space at top for padding -->
                                                <div class="h-[72px]"></div>

                                                <!-- Months with repeated items at the end for circular effect -->
                                                <div
                                                    v-for="month in repeatedMonths"
                                                    :key="`month-${month}-${repeatedMonths.indexOf(month)}`"
                                                    :data-value="month"
                                                    class="h-[40px] flex items-center justify-center text-center text-xl transition-all duration-150 select-none"
                                                    :class="
                                                        form.dob_month === month
                                                            ? 'font-semibold text-[#654396]'
                                                            : 'text-gray-400 hover:text-gray-600'
                                                    "
                                                >
                                                    {{ month }}
                                                </div>

                                                <!-- Empty space at bottom for padding -->
                                                <div class="h-[72px]"></div>
                                            </div>
                                        </div>

                                        <!-- Year selector -->
                                        <div class="relative w-[60px] h-[184px] z-10">
                                            <div
                                                ref="yearListRef"
                                                @scroll="handleYearScroll"
                                                @click="handleYearClick"
                                                class="absolute inset-0 overflow-y-auto no-scrollbar scroll-smooth cursor-pointer"
                                                style="scroll-snap-type: y mandatory; -webkit-overflow-scrolling: touch"
                                            >
                                                <!-- Empty space at top for padding -->
                                                <div class="h-[72px]"></div>

                                                <!-- Years - ensure visibility -->
                                                <div
                                                    v-for="year in years"
                                                    :key="year"
                                                    :data-value="year"
                                                    class="h-[40px] flex items-center justify-center text-center text-xl transition-all duration-150 select-none"
                                                    :class="
                                                        form.dob_year === year
                                                            ? 'font-semibold text-[#654396]'
                                                            : 'text-gray-400 hover:text-gray-600'
                                                    "
                                                >
                                                    {{ year }}
                                                </div>

                                                <!-- Empty space at bottom for padding -->
                                                <div class="h-[72px]"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Selection area indicators -->
                                    <div class="relative w-full h-0">
                                        <!-- Top and bottom selection lines -->
                                        <div
                                            class="absolute left-0 right-0 top-[-130px] border-b border-[#654396] w-full"
                                        ></div>
                                        <div
                                            class="absolute left-0 right-0 top-[-90px] border-b border-[#654396] w-full"
                                        ></div>
                                    </div>

                                    <!-- Age display - ensure visibility -->
                                    <div
                                        v-if="calculateAge !== null"
                                        class="text-center text-lg font-medium text-[#654396] mt-6 p-3 bg-[#F5F5F5] rounded-lg"
                                    >
                                        Your age is {{ calculateAge }}
                                    </div>

                                    <!-- Scrolling hint -->
                                    <div class="text-center text-xs text-gray-500 mt-3">Scroll or tap to select</div>
                                </div>
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.dob_day || form.errors.dob_month || form.errors.dob_year"
                            />
                        </div>
                    </div>

                    <!-- Step 4: Where do you live? -->
                    <div v-if="currentStep === 4">
                        <div class="mb-[40px]">
                            <h2 class="text-5xl font-bold font-display text-[#04060A]">Where do you live?</h2>
                        </div>

                        <div class="mb-[30px]">
                            <div class="space-y-6">
                                <!-- Country input (Fixed to Nigeria) -->
                                <div>
                                    <label for="country" class="mb-3 block text-lg text-[#41465a]">Country</label>
                                    <div class="flex items-center rounded-lg bg-[#F5F5F5] px-5 py-5">
                                        <div class="mr-3 h-5 w-5 flex-shrink-0">
                                            <!-- Nigeria flag icon -->
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 36 36">
                                                <path
                                                    fill="#186648"
                                                    d="M36 27c0 2.209-1.791 4-4 4H4c-2.209 0-4-1.791-4-4V9c0-2.209 1.791-4 4-4h28c2.209 0 4 1.791 4 4v18z"
                                                ></path>
                                                <path
                                                    fill="#FFFFFF"
                                                    d="M4 5h8v26H4c-2.209 0-4-1.791-4-4V9c0-2.209 1.791-4 4-4zm20 0h8c2.209 0 4 1.791 4 4v18c0 2.209-1.791 4-4 4h-8V5z"
                                                ></path>
                                            </svg>
                                        </div>
                                        <input
                                            id="country"
                                            type="text"
                                            class="w-full bg-transparent text-lg text-gray-700 focus:outline-none cursor-default"
                                            v-model="form.country"
                                            readonly
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.country" />
                                </div>

                                <!-- State dropdown -->
                                <div>
                                    <label for="state" class="mb-3 block text-lg text-[#41465a]">State</label>
                                    <div class="relative">
                                        <select
                                            id="state"
                                            v-model="form.state"
                                            class="w-full appearance-none rounded-lg bg-[#F5F5F5] px-5 py-5 text-lg text-gray-700 focus:outline-none custom-select"
                                        >
                                            <option value="" disabled selected>Select your state</option>
                                            <option v-for="state in nigerianStates" :key="state" :value="state">
                                                {{ state }}
                                            </option>
                                        </select>
                                        <div
                                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-5"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5 text-gray-500"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.state" />
                                </div>

                                <!-- City dropdown (dependent on state selection) -->
                                <div>
                                    <label for="city" class="mb-3 block text-lg text-[#41465a]">City</label>
                                    <div class="relative">
                                        <select
                                            id="city"
                                            v-model="form.city"
                                            class="w-full appearance-none rounded-lg bg-[#F5F5F5] px-5 py-5 text-lg text-gray-700 focus:outline-none custom-select"
                                            :disabled="!form.state"
                                        >
                                            <option value="" disabled selected>
                                                {{ form.state ? 'Select your city' : 'Please select a state first' }}
                                            </option>
                                            <option v-for="city in availableCities" :key="city" :value="city">
                                                {{ city }}
                                            </option>
                                        </select>
                                        <div
                                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-5"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5 text-gray-500"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.city" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Email and Password (combined) - Final Step -->
                    <div v-if="currentStep === 5">
                        <div class="mb-[40px]">
                            <h2 class="text-5xl font-bold font-display text-[#04060A]">Create your credentials</h2>
                        </div>

                        <div class="space-y-6">
                            <!-- Email field -->
                            <div>
                                <label for="email" class="mb-3 block text-lg text-[#41465a]">Email Address</label>
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
                                        autocomplete="username"
                                        placeholder="Enter your email address"
                                    />
                                </div>
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <!-- Password field -->
                            <div>
                                <label for="password" class="mb-3 block text-lg text-[#41465a]">Password</label>
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
                                        autocomplete="new-password"
                                        placeholder="Create a strong password"
                                    />
                                </div>
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <!-- Confirm Password field -->
                            <div>
                                <label for="password_confirmation" class="mb-3 block text-lg text-[#41465a]">
                                    Confirm Password
                                </label>
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
                                        id="password_confirmation"
                                        type="password"
                                        class="w-full bg-transparent text-lg text-gray-700 focus:outline-none"
                                        v-model="form.password_confirmation"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Confirm your password"
                                    />
                                </div>
                                <InputError class="mt-2" :message="form.errors.password_confirmation" />
                            </div>
                        </div>
                    </div>

                    <!-- Navigation buttons -->
                    <div class="mt-[40px] space-y-5">
                        <button
                            @click="currentStep === totalSteps ? submit() : nextStep()"
                            type="button"
                            :disabled="
                                (currentStep === 2 && (!form.gender || !form.interested_in)) ||
                                (currentStep === 3 && (!form.dob_day || !form.dob_month || !form.dob_year)) ||
                                (currentStep === 5 && (!form.email || !form.password || !form.password_confirmation))
                            "
                            class="w-full rounded-lg bg-[#654396] py-4 text-center text-lg text-white font-medium disabled:opacity-50"
                        >
                            {{ currentStep === totalSteps ? 'Create Account' : 'Continue' }}
                        </button>

                        <div v-if="currentStep > 1" class="text-center">
                            <button @click="prevStep" type="button" class="text-[#654396] font-medium hover:underline">
                                Back
                            </button>
                        </div>

                        <div v-if="currentStep < 4" class="text-center text-lg">
                            <span>Already have an account?</span>
                            <Link :href="route('login')" class="text-[#654396] font-medium hover:underline">
                                Log in!
                            </Link>
                        </div>
                    </div>

                    <!-- Social signup buttons (shown only on first step) -->
                    <div v-if="currentStep === 1" class="mt-8 flex justify-center ">
                        <button
                            @click="socialLogin('google')"
                            class="flex w-1/2 items-center justify-center rounded-lg border border-[#E5E5E5] bg-white py-4 px-4 text-black shadow-sm hover:bg-gray-50"
                        >
                            <svg class="mr-2 h-6 w-6" viewBox="0 0 24 24">
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
                            <span>Google</span>
                        </button>

                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Prevent text selection on date picker items */
    .cursor-pointer div {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }

    /* Force hardware acceleration for smoother scrolling */
    .scroll-smooth {
        -webkit-transform: translateZ(0);
        -moz-transform: translateZ(0);
        -ms-transform: translateZ(0);
        -o-transform: translateZ(0);
        transform: translateZ(0);
        -webkit-backface-visibility: hidden;
        -moz-backface-visibility: hidden;
        -ms-backface-visibility: hidden;
        backface-visibility: hidden;
    }

    /* Remove default dropdown arrow */
    .custom-select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }
    .custom-select::-ms-expand {
        display: none;
    }
</style>
