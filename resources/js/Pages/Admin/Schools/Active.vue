<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    schools: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Active Schools" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                    Active Schools
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Schools that have completed their setup and are actively using the system
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid gap-6 md:grid-cols-2">
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-6">
                    <p class="text-sm text-emerald-700 font-semibold">Total Active Schools</p>
                    <p class="mt-2 text-4xl font-bold text-emerald-600">
                        {{ schools.total || 0 }}
                    </p>
                </div>
                <div class="rounded-lg bg-blue-50 border border-blue-200 p-6">
                    <p class="text-sm text-blue-700 font-semibold">Average Users per School</p>
                    <p class="mt-2 text-4xl font-bold text-blue-600">
                        {{ schools.data ? Math.round(schools.data.reduce((sum, s) => sum + (s.users_count || 0), 0) / schools.data.length) : 0 }}
                    </p>
                </div>
            </div>

            <!-- Active Schools Table -->
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Active Schools List
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
                                <th class="px-6 py-3 font-semibold text-gray-700">Registered</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!schools.data || schools.data.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No active schools
                                </td>
                            </tr>
                            <tr v-for="school in schools.data" :key="school.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ school.name }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-mono">
                                    {{ school.school_code }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ school.level || '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                        {{ school.users_count || 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ new Date(school.created_at).toLocaleDateString() }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
