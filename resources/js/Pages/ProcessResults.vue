<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    years: {
        type: Array,
        default: () => [],
    },
    exams: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ year: null, exam: null }),
    },
});

const onYearChange = (event) => {
    const year = event.target.value || null;
    router.get(
        route('results.process'),
        { year, exam: null },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const onExamChange = (event) => {
    const exam = event.target.value || null;
    router.get(
        route('results.process'),
        { year: props.filters.year, exam },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};
</script>

<template>
    <Head title="Process Results" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Process Results
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Choose academic year and exam to review processing status and open detailed results preview.
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Year</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            @change="onYearChange"
                        >
                            <option value="">
                                All Years
                            </option>
                            <option
                                v-for="year in years"
                                :key="year"
                                :value="year"
                                :selected="filters.year === year"
                            >
                                {{ year }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Exam</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            @change="onExamChange"
                        >
                            <option value="">
                                All Exams
                            </option>
                            <option
                                v-for="exam in exams"
                                :key="exam.id"
                                :value="exam.id"
                                :selected="String(filters.exam) === String(exam.id)"
                            >
                                {{ exam.name }} ({{ exam.academic_year || '—' }})
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 text-xs text-gray-600">
                <p class="mb-1 font-medium">Exams Overview</p>
                <p class="text-[11px] text-gray-500">
                    Below is a list of exams for the selected filters. Use the preview action to open the detailed exam results document.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600">
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Exam Name</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Year</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Type</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Class Levels</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Status</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="exams.length === 0">
                            <td colspan="6" class="px-3 py-4 text-center text-[11px] text-gray-400">
                                No exams found for the selected filters.
                            </td>
                        </tr>
                        <tr
                            v-for="exam in exams"
                            :key="exam.id"
                            class="border-b border-gray-100 text-gray-700 odd:bg-white even:bg-gray-50"
                        >
                            <td class="px-3 py-1.5 align-top text-sm font-medium text-gray-900">
                                {{ exam.name }}
                            </td>
                            <td class="px-3 py-1.5 align-top">
                                {{ exam.academic_year || '—' }}
                            </td>
                            <td class="px-3 py-1.5 align-top">
                                {{ exam.type || '—' }}
                            </td>
                            <td class="px-3 py-1.5 align-top">
                                <span v-if="exam.levels && exam.levels.length" class="inline-flex flex-wrap gap-1">
                                    <span
                                        v-for="level in exam.levels"
                                        :key="level"
                                        class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-emerald-100"
                                    >
                                        {{ level }}
                                    </span>
                                </span>
                                <span v-else class="text-gray-400">—</span>
                            </td>
                            <td class="px-3 py-1.5 align-top text-center">
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700 ring-1 ring-blue-100">
                                    Ready
                                </span>
                            </td>
                            <td class="px-3 py-1.5 align-top text-right">
                                <a
                                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[10px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                                    :href="route('exams.results', exam.id)"
                                >
                                    View Preview
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
