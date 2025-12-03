<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Announcements" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Announcements
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage system announcements
                    </p>
                </div>
                <Link :href="route('admin.announcements.create')" class="px-6 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-colors">
                    + New Announcement
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <div class="grid gap-6 md:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Total Announcements</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">0</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Active</p>
                    <p class="mt-2 text-3xl font-bold text-blue-600">0</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Scheduled</p>
                    <p class="mt-2 text-3xl font-bold text-orange-600">0</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600">Announcements list - Coming soon</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
