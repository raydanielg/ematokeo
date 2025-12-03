<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Help Desk Categories" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-semibold leading-tight text-gray-800">Ticket Categories</h2>
        </template>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600">Help desk categories - Coming soon</p>
        </div>
    </AuthenticatedLayout>
</template>
