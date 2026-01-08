<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    years: {
        type: Array,
        default: () => [],
    },
    exams: {
        type: Array,
        default: () => [],
    },
    performance: {
        // array of { code, name, sat, pass, fail, gpa, competency }
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ year: null, exam: null }),
    },
});

const showPreview = ref(false);

const onYearChange = (event) => {
    const year = event.target.value || null;
    router.get(route('teacher.results.subject'), { year, exam: null }, { preserveState: true, preserveScroll: true, replace: true });
};

const onExamChange = (event) => {
    const exam = event.target.value || null;
    router.get(route('teacher.results.subject'), { year: props.filters.year, exam }, { preserveState: true, preserveScroll: true, replace: true });
};

const openPreview = () => {
    showPreview.value = true;
};

const closePreview = () => {
    showPreview.value = false;
};

const printPreview = () => {
    window.print();
};
</script>

<template>
    <Head title="Subject Performance" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">Subject Performance</h2>
                    <p class="mt-1 text-sm text-gray-500">Performance for your subjects only (restricted to your assignments).</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Year</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            @change="onYearChange"
                        >
                            <option value="">All Years</option>
                            <option v-for="year in years" :key="year" :value="year" :selected="filters.year === year">
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
                            <option value="">Select Exam</option>
                            <option v-for="exam in exams" :key="exam.id" :value="exam.id" :selected="String(filters.exam) === String(exam.id)">
                                {{ exam.name }} ({{ exam.academic_year || '—' }})
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 text-xs text-gray-600">
                <p class="mb-1 font-medium">Subject Summary</p>
                <p class="text-[11px] text-gray-500">Each row shows aggregated performance for a subject in the selected exam.</p>
            </div>

            <div class="mb-3 flex items-center justify-end">
                <button
                    type="button"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-emerald-300"
                    :disabled="!filters.exam"
                    @click="openPreview"
                >
                    Preview Document
                </button>
            </div>

            <div v-if="!filters.exam" class="py-10 text-center text-xs text-gray-400">Please select an exam to view subject performance.</div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600">
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Subject</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Code</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Sat</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Pass</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Fail</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">GPA</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Competency</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filters.exam && performance.length === 0">
                            <td colspan="7" class="px-3 py-4 text-center text-[11px] text-gray-400">
                                No subject performance data found for this exam yet.
                            </td>
                        </tr>
                        <tr v-for="row in performance" :key="row.code" class="hover:bg-gray-50">
                            <td class="border-b border-gray-100 px-3 py-2 font-medium text-gray-800">{{ row.name }}</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.code }}</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.sat ?? 0 }}</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.pass ?? 0 }}</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.fail ?? 0 }}</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.gpa ?? '—' }}</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.competency ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            v-if="showPreview"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/30 p-4 print:static print:bg-transparent"
        >
            <div class="mx-auto w-full max-w-5xl rounded-xl bg-white shadow-lg print:mx-0 print:max-w-none print:rounded-none print:shadow-none">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 print:hidden">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Preview</p>
                        <p class="text-xs text-gray-500">Printable subject performance summary</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-[11px] font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                            @click="printPreview"
                        >
                            Print
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-gray-900"
                            @click="closePreview"
                        >
                            Close
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-lg font-semibold text-gray-900">Subject Performance</p>
                        <p class="text-xs text-gray-500">Exam ID: {{ filters.exam }}</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-[11px]">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600">
                                    <th class="border-b border-gray-200 px-3 py-1.5 text-left">Subject</th>
                                    <th class="border-b border-gray-200 px-3 py-1.5 text-center">Code</th>
                                    <th class="border-b border-gray-200 px-3 py-1.5 text-center">Sat</th>
                                    <th class="border-b border-gray-200 px-3 py-1.5 text-center">Pass</th>
                                    <th class="border-b border-gray-200 px-3 py-1.5 text-center">Fail</th>
                                    <th class="border-b border-gray-200 px-3 py-1.5 text-center">GPA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="performance.length === 0">
                                    <td colspan="6" class="px-3 py-4 text-center text-[11px] text-gray-400">No data.</td>
                                </tr>
                                <tr v-for="row in performance" :key="row.code" class="hover:bg-gray-50">
                                    <td class="border-b border-gray-100 px-3 py-2 font-medium text-gray-800">{{ row.name }}</td>
                                    <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.code }}</td>
                                    <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.sat ?? 0 }}</td>
                                    <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.pass ?? 0 }}</td>
                                    <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.fail ?? 0 }}</td>
                                    <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.gpa ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
