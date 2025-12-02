<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    guardians: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Hostel Parents & Guardians" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Parents & Guardians
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Overview of parents and guardians for hostel students, with key contact details.
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-600">
                <div class="flex items-center gap-2">
                    <label class="text-[11px] font-medium text-gray-600">Search</label>
                    <input
                        type="text"
                        class="w-56 rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="Type name, phone or student"
                    />
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <table class="min-w-full border-collapse text-left text-[11px]">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">#</th>
                            <th class="px-3 py-2">Guardian</th>
                            <th class="px-3 py-2">Student</th>
                            <th class="px-3 py-2">Relationship</th>
                            <th class="px-3 py-2">Phone</th>
                            <th class="px-3 py-2">Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!guardians.length">
                            <td colspan="6" class="px-3 py-6 text-center text-[11px] text-gray-400">
                                No parents or guardians have been added yet.
                            </td>
                        </tr>
                        <tr
                            v-for="(guardian, index) in guardians"
                            :key="guardian.id || index"
                            class="border-b border-gray-100 text-[11px] text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top">{{ index + 1 }}</td>
                            <td class="px-3 py-2 align-top">
                                <div class="font-medium text-gray-900">{{ guardian.name || '-' }}</div>
                            </td>
                            <td class="px-3 py-2 align-top">{{ guardian.student_name || '-' }}</td>
                            <td class="px-3 py-2 align-top">{{ guardian.relationship || '-' }}</td>
                            <td class="px-3 py-2 align-top">{{ guardian.phone || '-' }}</td>
                            <td class="px-3 py-2 align-top">{{ guardian.address || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
