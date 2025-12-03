<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    laravelVersion: {
        type: String,
        default: 'N/A',
    },
    phpVersion: {
        type: String,
        default: 'N/A',
    },
    dbDriver: {
        type: String,
        default: 'N/A',
    },
    dbName: {
        type: String,
        default: 'N/A',
    },
    dbHost: {
        type: String,
        default: 'N/A',
    },
    dbSize: {
        type: Number,
        default: 0,
    },
    tables: {
        type: Array,
        default: () => [],
    },
    cacheDriver: {
        type: String,
        default: 'N/A',
    },
    cacheEnabled: {
        type: Boolean,
        default: false,
    },
});

const showClearConfirm = ref(false);
const clearForm = useForm({});

const formatBytes = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
};

const getTotalRecords = () => {
    return tables.reduce((sum, table) => sum + table.count, 0);
};

const clearCache = () => {
    clearForm.post(route('admin.cache-clear'), {
        onSuccess: () => {
            showClearConfirm.value = false;
        },
    });
};
</script>

<template>
    <Head title="System Statistics" />

    <AuthenticatedLayout>
        <template #header>
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 -mx-6 -mt-4 px-6 py-6 rounded-b-lg">
                <h2 class="text-3xl font-bold text-white">
                    System Statistics
                </h2>
                <p class="mt-2 text-emerald-100">
                    System information, database stats, and cache management
                </p>
            </div>
        </template>

        <div class="space-y-8 py-8">
            <!-- System Info Cards -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Laravel Version -->
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Laravel Version</p>
                            <p class="mt-2 text-2xl font-bold text-red-600">
                                {{ laravelVersion }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- PHP Version -->
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">PHP Version</p>
                            <p class="mt-2 text-2xl font-bold text-blue-600">
                                {{ phpVersion }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Database Driver -->
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Database Driver</p>
                            <p class="mt-2 text-2xl font-bold text-emerald-600 uppercase">
                                {{ dbDriver }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-6.586L9.172 4.172A2 2 0 007.586 4H4a2 2 0 00-2 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Cache Driver -->
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Cache Driver</p>
                            <p class="mt-2 text-2xl font-bold text-purple-600 uppercase">
                                {{ cacheDriver }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ cacheEnabled ? '✓ Enabled' : '✗ Disabled' }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Database Info -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Database Details -->
                <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Database Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between rounded-md bg-gray-50 px-4 py-3">
                            <span class="text-sm font-medium text-gray-700">Database Name</span>
                            <span class="text-sm text-gray-900 font-mono">{{ dbName }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-md bg-gray-50 px-4 py-3">
                            <span class="text-sm font-medium text-gray-700">Host</span>
                            <span class="text-sm text-gray-900 font-mono">{{ dbHost }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-md bg-gray-50 px-4 py-3">
                            <span class="text-sm font-medium text-gray-700">Database Size</span>
                            <span class="text-sm text-gray-900 font-mono font-bold text-emerald-600">
                                {{ formatBytes(dbSize) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between rounded-md bg-gray-50 px-4 py-3">
                            <span class="text-sm font-medium text-gray-700">Total Records</span>
                            <span class="text-sm text-gray-900 font-mono font-bold text-blue-600">
                                {{ getTotalRecords().toLocaleString() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between rounded-md bg-gray-50 px-4 py-3">
                            <span class="text-sm font-medium text-gray-700">Total Tables</span>
                            <span class="text-sm text-gray-900 font-mono font-bold text-purple-600">
                                {{ tables.length }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Cache Management -->
                <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Cache Management
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="rounded-md bg-blue-50 border border-blue-200 p-4">
                            <p class="text-sm text-blue-800">
                                <span class="font-semibold">Cache Driver:</span> {{ cacheDriver }}
                            </p>
                            <p class="text-xs text-blue-700 mt-1">
                                {{ cacheEnabled ? 'Cache is currently enabled and active.' : 'Cache is disabled. Set to null driver.' }}
                            </p>
                        </div>

                        <button
                            @click="showClearConfirm = true"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-orange-600 text-white font-semibold hover:bg-orange-700 transition-colors"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Clear Cache & Config
                        </button>

                        <div class="text-xs text-gray-500 bg-gray-50 rounded p-3">
                            <p class="font-semibold mb-1">This will clear:</p>
                            <ul class="list-disc list-inside space-y-0.5">
                                <li>Application cache</li>
                                <li>Configuration cache</li>
                                <li>View cache</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Database Tables -->
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Database Tables
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        All tables and their record counts
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-100 bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-gray-700">Table Name</th>
                                <th class="px-6 py-3 font-semibold text-gray-700 text-right">Records</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="tables.length === 0">
                                <td colspan="2" class="px-6 py-4 text-center text-gray-500">
                                    No tables found
                                </td>
                            </tr>
                            <tr v-for="table in tables" :key="table.name" class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ table.name }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        {{ table.count.toLocaleString() }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Clear Cache Confirmation Modal -->
        <div v-if="showClearConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 animate-in fade-in zoom-in-95 duration-300">
                <div class="flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 mx-auto">
                    <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M4.93 4.93a10 10 0 1114.14 14.14A10 10 0 014.93 4.93z" />
                    </svg>
                </div>

                <h3 class="mt-4 text-lg font-semibold text-gray-900 text-center">
                    Clear Cache & Config?
                </h3>

                <p class="mt-2 text-sm text-gray-600 text-center">
                    This will clear the application cache, configuration cache, and view cache. The system will regenerate these on next request.
                </p>

                <div class="mt-6 flex gap-3">
                    <button
                        @click="showClearConfirm = false"
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="clearCache"
                        :disabled="clearForm.processing"
                        class="flex-1 px-4 py-2 rounded-lg bg-orange-600 text-white font-semibold hover:bg-orange-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ clearForm.processing ? 'Clearing...' : 'Clear Cache' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
