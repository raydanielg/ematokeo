<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    years: {
        type: Array,
        default: () => [],
    },
    exams: {
        type: Array,
        default: () => [],
    },
    publishedCount: {
        type: Number,
        default: 0,
    },
});

const selectedYear = ref('');

const filteredExams = computed(() => {
    if (!selectedYear.value) {
        return props.exams || [];
    }
    return (props.exams || []).filter((e) => String(e?.academic_year || '') === String(selectedYear.value));
});

const onYearChange = (event) => {
    const year = event.target.value || '';
    selectedYear.value = year;
};

const openClassPerformance = (examId) => {
    if (!examId) return;
    router.get(
        route('teacher.results.class'),
        { year: selectedYear.value || null, exam: examId, class: null, stream: null },
        { preserveScroll: true, preserveState: true },
    );
};

const openSubjectPerformance = (examId) => {
    if (!examId) return;
    router.get(
        route('teacher.results.subject'),
        { year: selectedYear.value || null, exam: examId },
        { preserveScroll: true, preserveState: true },
    );
};
</script>

<template>
    <Head title="Results" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">Results</h2>
                    <p class="mt-1 text-sm text-gray-500">Quick access to class and subject performance for your exams.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Year</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            @change="onYearChange"
                        >
                            <option value="">All Years</option>
                            <option v-for="year in years" :key="year" :value="year">
                                {{ year }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Exams</p>
                <p class="mt-2 text-3xl font-semibold text-gray-800">{{ filteredExams.length }}</p>
                <p class="mt-1 text-xs text-gray-500">Available in your school</p>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Published</p>
                <p class="mt-2 text-3xl font-semibold text-gray-800">{{ publishedCount }}</p>
                <p class="mt-1 text-xs text-gray-500">Exams marked as published</p>
            </div>

            <div class="rounded-xl bg-emerald-50 p-5 shadow-sm ring-1 ring-emerald-100">
                <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Tip</p>
                <p class="mt-2 text-sm text-emerald-800">Choose an exam below to open Class or Subject performance instantly.</p>
            </div>
        </div>

        <div class="mt-6 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-gray-800">All Exams</p>
                    <p class="mt-0.5 text-xs text-gray-500">Open performance pages for the selected exam.</p>
                </div>
            </div>

            <div v-if="filteredExams.length === 0" class="py-10 text-center text-xs text-gray-400">
                No exams found for the selected year.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600">
                            <th class="border-b border-gray-200 px-3 py-2 text-left">Exam</th>
                            <th class="border-b border-gray-200 px-3 py-2 text-center">Type</th>
                            <th class="border-b border-gray-200 px-3 py-2 text-center">Year</th>
                            <th class="border-b border-gray-200 px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="exam in filteredExams" :key="exam.id" class="hover:bg-gray-50">
                            <td class="border-b border-gray-100 px-3 py-2">
                                <div class="font-medium text-gray-800">{{ exam.name }}</div>
                                <div class="text-[10px] text-gray-500">#{{ exam.id }}</div>
                            </td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">
                                {{ exam.type || '—' }}
                            </td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">
                                {{ exam.academic_year || '—' }}
                            </td>
                            <td class="border-b border-gray-100 px-3 py-2 text-right">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                                        @click="openClassPerformance(exam.id)"
                                    >
                                        Class Performance
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-[11px] font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                                        @click="openSubjectPerformance(exam.id)"
                                    >
                                        Subject Performance
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
