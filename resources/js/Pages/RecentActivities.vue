<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    activities: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Recent Activities" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                    Recent Activities
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Session history for your account, including login and logout events.
                </p>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 flex items-center justify-between">
                <p class="text-sm font-medium text-gray-700">
                    Activity log (latest 100 entries)
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">#</th>
                            <th class="px-3 py-2">Action</th>
                            <th class="px-3 py-2">IP Address</th>
                            <th class="px-3 py-2">Browser / Device</th>
                            <th class="px-3 py-2">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="activities.length === 0">
                            <td colspan="5" class="px-3 py-4 text-center text-xs text-gray-500">
                                No activity has been recorded yet.
                            </td>
                        </tr>
                        <tr
                            v-for="(activity, index) in activities"
                            :key="activity.id"
                            class="border-b border-gray-100 text-xs text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top">
                                {{ index + 1 }}
                            </td>
                            <td class="px-3 py-2 align-top capitalize">
                                {{ activity.action }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                {{ activity.ip_address || '—' }}
                            </td>
                            <td class="px-3 py-2 align-top max-w-xs truncate">
                                {{ activity.user_agent || '—' }}
                            </td>
                            <td class="px-3 py-2 align-top whitespace-nowrap">
                                {{ activity.occurred_at }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
