<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Blog Comments" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-semibold leading-tight text-gray-800">Blog Comments</h2>
        </template>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600">Blog comments moderation - Coming soon</p>
        </div>
    </AuthenticatedLayout>
</template>
