<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Hostel Reports" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Hostel Reports
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        High-level overview of hostel occupancy, payments and allocations.
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div class="grid gap-3 md:grid-cols-3">
                <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
                    <p class="text-[11px] font-medium text-gray-500">Total Hostel Students</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ summary.students ?? 0 }}</p>
                </div>
                <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
                    <p class="text-[11px] font-medium text-gray-500">Total Rooms</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ summary.rooms ?? 0 }}</p>
                </div>
                <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
                    <p class="text-[11px] font-medium text-gray-500">Occupancy Rate</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ summary.occupancy_rate ?? 0 }}%</p>
                </div>
            </div>

            <div class="rounded-xl bg-white p-4 text-[11px] text-gray-600 shadow-sm ring-1 ring-gray-100">
                <p class="mb-1 text-[11px] font-semibold text-gray-700">Reports area</p>
                <p class="text-[11px] text-gray-500">
                    Detailed hostel reports (by hostel, class, gender, payment status, etc.) will appear here once processing and
                    export features are implemented.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
