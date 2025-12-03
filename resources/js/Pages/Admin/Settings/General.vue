<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="General Settings" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                    General Settings
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Configure general system settings
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Settings Form -->
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">System Configuration</h3>
                
                <div class="space-y-6">
                    <!-- Application Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Application Name
                        </label>
                        <input
                            type="text"
                            value="Ematokeo"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        />
                    </div>

                    <!-- System Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            System Email
                        </label>
                        <input
                            type="email"
                            placeholder="noreply@ematokeo.local"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        />
                    </div>

                    <!-- Maintenance Mode -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">Maintenance Mode</p>
                            <p class="text-sm text-gray-600">Put system in maintenance mode</p>
                        </div>
                        <input type="checkbox" class="rounded border-gray-300" />
                    </div>

                    <!-- Save Button -->
                    <div class="flex gap-3">
                        <button class="px-6 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-colors">
                            Save Settings
                        </button>
                        <button class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
