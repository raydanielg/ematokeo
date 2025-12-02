<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({}),
    },
});

const form = reactive({
    email_from_name: props.settings.email_from_name || '',
    email_from_address: props.settings.email_from_address || '',
    smtp_host: props.settings.smtp_host || '',
    smtp_port: props.settings.smtp_port || '',
    smtp_encryption: props.settings.smtp_encryption || 'tls',
    smtp_username: props.settings.smtp_username || '',
    smtp_password: props.settings.smtp_password || '',
    sms_provider_name: props.settings.sms_provider_name || '',
    sms_api_url: props.settings.sms_api_url || '',
    sms_sender_id: props.settings.sms_sender_id || '',
    sms_api_key: props.settings.sms_api_key || '',
    sms_callback_url: props.settings.sms_callback_url || '',
});

const saveSettings = () => {
    router.post(route('settings.email-sms.save'), form, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Email & SMS Settings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Email & SMS Settings
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Configure how the system sends email and SMS notifications to parents, students and staff.
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <form
                class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100"
                @submit.prevent="saveSettings"
            >
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Email Configuration
                </h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2 text-xs text-gray-700">
                        <div>
                            <label class="mb-1 block font-medium">From Name</label>
                            <input
                                v-model="form.email_from_name"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="e.g. Mlimani Secondary School"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">From Email</label>
                            <input
                                v-model="form.email_from_address"
                                type="email"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="e.g. noreply@school.ac.tz"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">SMTP Host</label>
                            <input
                                v-model="form.smtp_host"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="smtp.mailhost.com"
                            />
                        </div>
                    </div>
                    <div class="space-y-2 text-xs text-gray-700">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="mb-1 block font-medium">SMTP Port</label>
                                <input
                                    v-model.number="form.smtp_port"
                                    type="number"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="587"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Encryption</label>
                                <select
                                    v-model="form.smtp_encryption"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                >
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">SMTP Username</label>
                            <input
                                v-model="form.smtp_username"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">SMTP Password</label>
                            <input
                                v-model="form.smtp_password"
                                type="password"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            />
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                    >
                        Save Email Settings
                    </button>
                </div>

                <div class="mt-6 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        SMS Gateway Configuration
                    </h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2 text-xs text-gray-700">
                            <div>
                                <label class="mb-1 block font-medium">Provider Name</label>
                                <input
                                    v-model="form.sms_provider_name"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="e.g. SMS Provider"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">API URL</label>
                                <input
                                    v-model="form.sms_api_url"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="https://api.smsprovider.com/send"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Sender ID</label>
                                <input
                                    v-model="form.sms_sender_id"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="e.g. SCHOOLNAME"
                                />
                            </div>
                        </div>
                        <div class="space-y-2 text-xs text-gray-700">
                            <div>
                                <label class="mb-1 block font-medium">API Key / Token</label>
                                <input
                                    type="password"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Callback URL (optional)</label>
                                <input
                                    v-model="form.sms_callback_url"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="For delivery reports"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-[11px] text-gray-500">
                        <p>
                            Make sure you keep your API keys secret. We will wire these settings to real SMS providers later.
                        </p>
                        <button
                            type="submit"
                            class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                        >
                            Save SMS Settings
                        </button>
                    </div>
                </div>
            </form>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Test Notifications (Preview Only)
                </h3>
                <div class="grid gap-4 md:grid-cols-2 text-xs text-gray-700">
                    <div class="space-y-2">
                        <label class="mb-1 block font-medium">Test Email Address</label>
                        <input
                            type="email"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="parent@example.com"
                        />
                        <button
                            type="button"
                            class="mt-1 rounded-md bg-blue-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-blue-700"
                        >
                            Send Test Email (coming soon)
                        </button>
                    </div>
                    <div class="space-y-2">
                        <label class="mb-1 block font-medium">Test Phone Number</label>
                        <input
                            type="tel"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="07XXXXXXXX"
                        />
                        <button
                            type="button"
                            class="mt-1 rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                        >
                            Send Test SMS (coming soon)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
