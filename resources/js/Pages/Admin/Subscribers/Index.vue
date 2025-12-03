<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Subscribers" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-semibold leading-tight text-gray-800">All Subscribers</h2>
            <p class="mt-1 text-sm text-gray-500">Manage email subscribers</p>
        </template>
        <div class="space-y-6">
            <div class="grid gap-6 md:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Total Subscribers</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">0</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Active</p>
                    <p class="mt-2 text-3xl font-bold text-blue-600">0</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Unsubscribed</p>
                    <p class="mt-2 text-3xl font-bold text-red-600">0</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600">Subscribers list - Coming soon</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
