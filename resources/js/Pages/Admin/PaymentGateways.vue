<script setup>
import { onMounted, reactive, ref } from 'vue'
import axios from 'axios'

const loading = ref(true)
const saving = ref(null)
const error = ref('')
const gateways = reactive({})

const load = async () => {
    try {
        const { data } = await axios.get('/api/v1/admin/payment-gateways')
        Object.assign(gateways, data.gateways)
    } catch (e) {
        error.value = 'Unable to load payment gateway settings.'
    } finally {
        loading.value = false
    }
}

const save = async (name) => {
    saving.value = name
    error.value = ''
    try {
        const { data } = await axios.put(`/api/v1/admin/payment-gateways/${name}`, gateways[name])
        gateways[name] = data.settings
    } catch (e) {
        error.value = e.response?.data?.message || 'Unable to save payment gateway settings.'
    } finally {
        saving.value = null
    }
}

onMounted(load)
</script>

<template>
    <div class="mx-auto max-w-5xl p-6">
        <h1 class="text-2xl font-semibold text-gray-900">Payment gateways</h1>
        <p class="mt-2 text-sm text-gray-600">Configure gateway availability and operating mode. Credentials remain server-side environment settings and are never editable here.</p>
        <p v-if="error" class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">{{ error }}</p>
        <div v-if="loading" class="mt-6 text-gray-600">Loading settings…</div>
        <div v-else class="mt-6 grid gap-5 md:grid-cols-3">
            <section v-for="(settings, name) in gateways" :key="name" class="rounded-lg border bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold capitalize text-gray-900">{{ name }}</h2>
                    <span class="text-xs" :class="settings.configured ? 'text-green-700' : 'text-amber-700'">
                        {{ settings.configured ? 'Credentials configured' : 'Credentials missing' }}
                    </span>
                </div>
                <label class="mt-5 flex items-center gap-2 text-sm text-gray-700">
                    <input v-model="settings.enabled" type="checkbox" class="rounded" /> Enabled
                </label>
                <label class="mt-4 block text-sm text-gray-700">
                    Mode
                    <select v-model="settings.mode" class="mt-1 w-full rounded border-gray-300">
                        <option value="sandbox">Sandbox</option>
                        <option value="live">Live</option>
                    </select>
                </label>
                <label class="mt-4 block text-sm text-gray-700">
                    Default currency
                    <input v-model="settings.default_currency" maxlength="3" class="mt-1 w-full rounded border-gray-300 uppercase" />
                </label>
                <button :disabled="saving === name" class="mt-5 rounded bg-purple-700 px-4 py-2 text-sm font-medium text-white disabled:opacity-50" @click="save(name)">
                    {{ saving === name ? 'Saving…' : 'Save settings' }}
                </button>
            </section>
        </div>
    </div>
</template>
