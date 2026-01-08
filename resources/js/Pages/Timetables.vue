<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    timetables: {
        type: Array,
        default: () => [],
    },
});

const previewTimetable = (id) => {
    router.get(route('timetables.show', id));
};

const deleteTimetable = (id) => {
    if (!confirm('Delete this timetable?')) return;

    router.delete(route('timetables.destroy', id));
};

const formatDateTime = (value) => {
    if (!value) return '-';
    const dt = new Date(value);
    if (Number.isNaN(dt.getTime())) return String(value);
    return dt.toLocaleString();
};

const hasSchedule = (t) => {
    const s = t?.schedule_json;
    if (!s) return false;
    if (Array.isArray(s)) return s.length > 0;
    if (typeof s === 'object') return Object.keys(s).length > 0;
    return false;
};
</script>

<template>
    <Head title="Timetables" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Timetables
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        View and manage class timetables. You can preview, download or delete a timetable from the list below.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                    @click="router.get(route('timetables.create'))"
                >
                    Create Timetable
                </button>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-sm font-medium text-gray-700">
                    Timetables ({{ timetables.length }})
                </p>
                <p class="text-[11px] text-gray-500">
                    Hizi ni ratiba ulizohifadhi (kwa madarasa au shule). Unaweza ku-preview, ku-print au kufuta.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">Title</th>
                            <th class="px-3 py-2">Class</th>
                            <th class="px-3 py-2">Year / Term</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Created</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="timetables.length === 0">
                            <td colspan="6" class="px-3 py-4 text-center text-xs text-gray-500">
                                No timetables created yet.
                            </td>
                        </tr>
                        <tr
                            v-for="timetable in timetables"
                            :key="timetable.id"
                            class="border-b border-gray-100 text-xs text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top">
                                {{ timetable.title }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                {{ timetable.class_name || '-' }}
                            </td>
                            <td class="px-3 py-2 align-top text-xs text-gray-600">
                                <span v-if="timetable.academic_year">
                                    {{ timetable.academic_year }}
                                </span>
                                <span v-if="timetable.term">
                                    • {{ timetable.term }}
                                </span>
                                <span v-if="!timetable.academic_year && !timetable.term">-</span>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-wrap items-center gap-1">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1"
                                        :class="hasSchedule(timetable)
                                            ? 'bg-emerald-50 text-emerald-700 ring-emerald-100'
                                            : 'bg-amber-50 text-amber-800 ring-amber-100'"
                                    >
                                        {{ hasSchedule(timetable) ? 'Saved' : 'No schedule' }}
                                    </span>
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1"
                                        :class="timetable.file_path
                                            ? 'bg-blue-50 text-blue-700 ring-blue-100'
                                            : 'bg-gray-50 text-gray-500 ring-gray-100'"
                                    >
                                        {{ timetable.file_path ? 'PDF' : 'No PDF' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top text-xs text-gray-600">
                                {{ formatDateTime(timetable.created_at) }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="rounded-md bg-blue-50 px-2 py-1 text-[11px] font-medium text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100"
                                        @click="router.get(route('timetables.show', timetable.id))"
                                    >
                                        Preview
                                    </button>
                                    <button
                                        type="button"
                                        :disabled="!timetable.file_path"
                                        :class="[
                                            'rounded-md px-2 py-1 text-[11px] font-medium ring-1',
                                            timetable.file_path
                                                ? 'bg-emerald-50 text-emerald-700 ring-emerald-100 hover:bg-emerald-100'
                                                : 'bg-gray-50 text-gray-400 ring-gray-100 cursor-not-allowed',
                                        ]"
                                        @click="timetable.file_path && window.open(route('timetables.download', timetable.id), '_blank')"
                                    >
                                        Download
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-red-50 px-2 py-1 text-[11px] font-medium text-red-700 ring-1 ring-red-100 hover:bg-red-100"
                                        @click="deleteTimetable(timetable.id)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
