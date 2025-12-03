<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="School Details" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                    School Details & Analytics
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Detailed information and analytics for individual schools
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Search & Filter -->
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-100 p-6">
                <div class="flex gap-4">
                    <input
                        type="text"
                        placeholder="Search school by name or code..."
                        class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    />
                    <button class="px-6 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-colors">
                        Search
                    </button>
                </div>
            </div>

            <!-- School Info Cards -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Total Students</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">—</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Total Teachers</p>
                    <p class="mt-2 text-3xl font-bold text-blue-600">—</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Total Classes</p>
                    <p class="mt-2 text-3xl font-bold text-purple-600">—</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Active Users</p>
                    <p class="mt-2 text-3xl font-bold text-orange-600">—</p>
                </div>
            </div>

            <!-- Placeholder -->
            <div class="rounded-lg bg-blue-50 border border-blue-200 p-6">
                <p class="text-sm text-blue-800">
                    <span class="font-semibold">Note:</span> Select a school to view detailed analytics and information.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
