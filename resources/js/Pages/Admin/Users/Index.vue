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
    <Head title="All Users" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                    All Users
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manage all users in the system
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid gap-6 md:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Total Users</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">
                        {{ users.total || 0 }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Active</p>
                    <p class="mt-2 text-3xl font-bold text-blue-600">
                        {{ users.data?.filter(u => u.is_active).length || 0 }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Admins</p>
                    <p class="mt-2 text-3xl font-bold text-orange-600">
                        {{ users.data?.filter(u => u.role === 'admin').length || 0 }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Inactive</p>
                    <p class="mt-2 text-3xl font-bold text-red-600">
                        {{ users.data?.filter(u => !u.is_active).length || 0 }}
                    </p>
                </div>
            </div>

            <!-- Users Table -->
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Users List
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-100 bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-gray-700">Name</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Email</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Role</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">School</th>
                                <th class="px-6 py-3 font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!users.data || users.data.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No users found
                                </td>
                            </tr>
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ user.name }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ user.email }}
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="user.role === 'admin'" class="inline-flex items-center rounded-full bg-orange-100 px-3 py-1 text-xs font-medium text-orange-700">
                                        Admin
                                    </span>
                                    <span v-else class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                                        User
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ user.school?.name || '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="user.is_active" class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">
                                        Active
                                    </span>
                                    <span v-else class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                                        Inactive
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
