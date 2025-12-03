<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    users: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Admin Users" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                    Admin Users
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    System administrators with full access
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Alert -->
            <div class="rounded-lg bg-orange-50 border border-orange-200 p-4">
                <p class="text-sm text-orange-800">
                    <span class="font-semibold">{{ users.total || 0 }} admin users</span> have full system access.
                </p>
            </div>

            <!-- Admin Users Table -->
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Admin Users List
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-100 bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-gray-700">Name</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Email</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Username</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Status</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!users.data || users.data.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No admin users found
                                </td>
                            </tr>
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ user.name }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ user.email }}
                                </td>
                                <td class="px-6 py-4 font-mono text-gray-600">
                                    {{ user.username }}
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="user.is_active" class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">
                                        Active
                                    </span>
                                    <span v-else class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                                        Inactive
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
