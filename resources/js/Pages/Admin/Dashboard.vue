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
    pendingSchools: {
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
    totalStudents: {
        type: Number,
        default: 0,
    },
    totalTeachers: {
        type: Number,
        default: 0,
    },
    schoolsWithStudents: {
        type: Number,
        default: 0,
    },
    schoolsByLevel: {
        type: Array,
        default: () => [],
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

        <div class="space-y-8">
            <!-- Top Stats Grid: Schools & Users -->
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

                <!-- Pending / Incomplete Schools -->
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Pending / Incomplete Schools</p>
                            <p class="mt-2 text-3xl font-bold text-amber-600">
                                {{ pendingSchools }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100">
                            <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93a10 10 0 1114.14 14.14A10 10 0 014.93 4.93z" />
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
            </div>

            <!-- Second Row: Students / Teachers / Admins / Coverage -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Students -->
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Students</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-700">
                                {{ totalStudents }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                            <svg class="h-6 w-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Teachers -->
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Teachers</p>
                            <p class="mt-2 text-3xl font-bold text-sky-700">
                                {{ totalTeachers }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-sky-100">
                            <svg class="h-6 w-6 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v7" />
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

                <!-- Schools With Students -->
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Schools With Students</p>
                            <p class="mt-2 text-3xl font-bold text-teal-700">
                                {{ schoolsWithStudents }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-100">
                            <svg class="h-6 w-6 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4zm0 6l9 4 9-4" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schools by Level & Recent Schools -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Schools by Level summary -->
                <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100 lg:col-span-1">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Schools by Level
                        </h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Distribution of all registered schools
                        </p>
                    </div>
                    <div class="p-6 space-y-3">
                        <div
                            v-if="schoolsByLevel.length === 0"
                            class="text-sm text-gray-500"
                        >
                            No schools data available yet.
                        </div>
                        <div
                            v-for="item in schoolsByLevel"
                            :key="item.level || 'unknown'"
                            class="flex items-center justify-between rounded-md bg-gray-50 px-3 py-2 text-sm"
                        >
                            <div>
                                <p class="font-medium text-gray-800">
                                    {{ item.level || 'Unknown Level' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500">Schools</span>
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                    {{ item.count }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Schools -->
                <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100 lg:col-span-2">
                    <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Recently Registered Schools
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                Last 10 schools created in the system
                            </p>
                        </div>
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
        </div>
    </AuthenticatedLayout>
</template>
