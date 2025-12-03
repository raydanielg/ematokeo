<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Send Notification" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-semibold leading-tight text-gray-800">Send Notification</h2>
        </template>
        <div class="rounded-lg bg-white shadow p-6">
            <form class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Recipients</label>
                    <select class="w-full rounded-lg border border-gray-300 px-4 py-2">
                        <option>All Users</option>
                        <option>All Schools</option>
                        <option>Specific Group</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea rows="6" placeholder="Enter notification message..." class="w-full rounded-lg border border-gray-300 px-4 py-2"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700">
                        Send Notification
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
