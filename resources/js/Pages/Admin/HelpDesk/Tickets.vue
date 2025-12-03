<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Support Tickets" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-semibold leading-tight text-gray-800">Support Tickets</h2>
            <p class="mt-1 text-sm text-gray-500">Manage customer support tickets</p>
        </template>
        <div class="space-y-6">
            <div class="grid gap-6 md:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Open</p>
                    <p class="mt-2 text-3xl font-bold text-blue-600">0</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">In Progress</p>
                    <p class="mt-2 text-3xl font-bold text-orange-600">0</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Resolved</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">0</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Closed</p>
                    <p class="mt-2 text-3xl font-bold text-gray-600">0</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600">Support tickets list - Coming soon</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
