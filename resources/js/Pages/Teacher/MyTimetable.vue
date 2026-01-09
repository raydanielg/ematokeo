<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    timetables: {
        type: Array,
        default: () => [],
    },
    assignedClasses: {
        type: Array,
        default: () => [],
    },
});

const count = computed(() => (Array.isArray(props.timetables) ? props.timetables.length : 0));

const preview = (id) => {
    router.get(route('timetables.show', id));
};

const myPeriods = (id) => {
    router.get(route('teacher.timetables.periods', id));
};
</script>

<template>
    <Head title="My Timetable" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">My Timetable</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Open a saved timetable and view only your own periods automatically.
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="text-sm font-semibold text-gray-800">Assigned classes</div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span
                                v-for="c in assignedClasses"
                                :key="c"
                                class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100"
                            >
                                {{ c }}
                            </span>
                            <span v-if="!assignedClasses || assignedClasses.length === 0" class="text-sm text-gray-500">—</span>
                        </div>
                    </div>

                    <div class="text-xs text-gray-500">
                        Showing <span class="font-semibold text-gray-800">{{ count }}</span> timetable(s)
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Title</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Class</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Year / Term</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!timetables || timetables.length === 0">
                                <td colspan="4" class="px-5 py-6 text-center text-sm text-gray-500">
                                    No saved timetables found yet. Ask the admin to generate and save a timetable.
                                </td>
                            </tr>

                            <tr v-for="t in timetables" :key="t.id" class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ t.title }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ t.class_name || '—' }}</td>
                                <td class="px-5 py-3 text-gray-700">
                                    <span v-if="t.academic_year">{{ t.academic_year }}</span>
                                    <span v-if="t.term"> • {{ t.term }}</span>
                                    <span v-if="!t.academic_year && !t.term">—</span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="rounded-md bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100"
                                            @click="preview(t.id)"
                                        >
                                            Preview
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-md bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                                            @click="myPeriods(t.id)"
                                        >
                                            My Periods
                                        </button>
                                        <button
                                            type="button"
                                            :disabled="!t.file_path"
                                            :class="[
                                                'rounded-md px-3 py-1.5 text-xs font-semibold ring-1',
                                                t.file_path
                                                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-100 hover:bg-emerald-100'
                                                    : 'bg-gray-50 text-gray-400 ring-gray-100 cursor-not-allowed',
                                            ]"
                                            @click="t.file_path && window.open(route('timetables.download', t.id), '_blank')"
                                        >
                                            Download
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
