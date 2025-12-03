<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    schools: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="All Schools" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        All Schools
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage all registered schools in the system
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid gap-6 md:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Total Schools</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">
                        {{ schools.total || 0 }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Active</p>
                    <p class="mt-2 text-3xl font-bold text-blue-600">
                        {{ schools.data?.filter(s => s.name).length || 0 }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Pending</p>
                    <p class="mt-2 text-3xl font-bold text-amber-600">
                        {{ schools.data?.filter(s => !s.name).length || 0 }}
                    </p>
                </div>
            </div>

            <!-- Schools Table -->
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        All Schools List
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-100 bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-gray-700">School Name</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Code</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Level</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Users</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!schools.data || schools.data.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No schools found
                                </td>
                            </tr>
                            <tr v-for="school in schools.data" :key="school.id" class="hover:bg-gray-50">
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
                                    {{ school.users_count || 0 }}
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
