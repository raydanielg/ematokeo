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
    <Head title="Pending Schools" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                    Pending / Incomplete Schools
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Schools that haven't completed their setup yet
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Alert -->
            <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4">
                <p class="text-sm text-yellow-800">
                    <span class="font-semibold">{{ schools.total || 0 }} schools</span> are pending completion of their profile information.
                </p>
            </div>

            <!-- Pending Schools Table -->
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Pending Schools
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-100 bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-gray-700">School ID</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Created Date</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Users</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!schools.data || schools.data.length === 0">
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    No pending schools
                                </td>
                            </tr>
                            <tr v-for="school in schools.data" :key="school.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono text-gray-900">
                                    #{{ school.id }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ new Date(school.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ school.users_count || 0 }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                                        Awaiting Setup
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
