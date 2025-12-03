<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    totalSchools: {
        type: Number,
        default: 0,
    },
    activeSchools: {
        type: Number,
        default: 0,
    },
    totalUsers: {
        type: Number,
        default: 0,
    },
    adminUsers: {
        type: Number,
        default: 0,
    },
    recentSchools: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                    Admin Dashboard
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    System overview and management
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Schools -->
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Schools</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-600">
                                {{ totalSchools }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Active Schools -->
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Active Schools</p>
                            <p class="mt-2 text-3xl font-bold text-blue-600">
                                {{ activeSchools }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Users</p>
                            <p class="mt-2 text-3xl font-bold text-purple-600">
                                {{ totalUsers }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Admin Users -->
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Admin Users</p>
                            <p class="mt-2 text-3xl font-bold text-orange-600">
                                {{ adminUsers }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Schools -->
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Recently Registered Schools
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-100 bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-gray-700">School Name</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">School Code</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Level</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Registered Date</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="recentSchools.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No schools registered yet
                                </td>
                            </tr>
                            <tr v-for="school in recentSchools" :key="school.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ school.name || '—' }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ school.school_code || '—' }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ school.level || '—' }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ new Date(school.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="school.name" class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">
                                        Active
                                    </span>
                                    <span v-else class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                                        Pending
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
