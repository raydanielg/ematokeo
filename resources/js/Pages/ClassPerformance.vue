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
        type: Array,
        default: () => [],
    },
    topStudents: {
        type: Array,
        default: () => [],
    },
    bottomStudents: {
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
    router.get(
        route('results.class'),
        { year, exam: null },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const onExamChange = (event) => {
    const exam = event.target.value || null;
    router.get(
        route('results.class'),
        { year: props.filters.year, exam },
        { preserveState: true, preserveScroll: true, replace: true },
    );
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
    <Head title="Class Performance" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Class Performance
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Overview of performance by class level for the selected exam, including averages and division distribution.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Year</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            @change="onYearChange"
                        >
                            <option value="">All Years</option>
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
                            <option value="">Select Exam</option>
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
                <p class="mb-1 font-medium">Class Level Summary</p>
                <p class="text-[11px] text-gray-500">
                    Each row shows aggregated performance for a class level in the selected exam, including number of candidates, average marks and division breakdown.
                </p>
            </div>

            <div class="mb-3 flex items-center justify-end">
                <button
                    type="button"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                    @click="openPreview"
                >
                    Preview Document
                </button>
            </div>

            <div v-if="!filters.exam" class="py-10 text-center text-xs text-gray-400">
                Please select an exam to view class performance.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600">
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Class Level</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Candidates</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Average Mark</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Div I</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Div II</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Div III</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Div IV</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Div 0</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Pass Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filters.exam && performance.length === 0">
                            <td colspan="9" class="px-3 py-4 text-center text-[11px] text-gray-400">
                                No performance data found for this exam yet.
                            </td>
                        </tr>
                        <tr
                            v-for="row in performance"
                            :key="row.class_level || 'unknown'"
                            class="border-b border-gray-100 text-gray-700 odd:bg-white even:bg-gray-50"
                        >
                            <td class="px-3 py-1.5 align-top text-sm font-medium text-gray-900">
                                {{ row.class_level || 'Unknown' }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-center">
                                {{ row.candidates }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-center">
                                {{ row.average_mark ?? '—' }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-center">
                                {{ row.divisions.I }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-center">
                                {{ row.divisions.II }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-center">
                                {{ row.divisions.III }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-center">
                                {{ row.divisions.IV }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-center">
                                {{ row.divisions['0'] }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-center">
                                {{ row.pass_rate }}%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <transition name="fade">
            <div
                v-if="showPreview"
                class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-8"
            >
                <div class="max-h-full w-full max-w-4xl overflow-y-auto rounded-xl bg-white p-6 text-xs text-gray-800 shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-800">
                                Muhtasari Wa Ufaulu Kwa Madarasa
                            </h3>
                            <p class="mt-0.5 text-[11px] text-gray-500">
                                Huu ni waraka wa kuchapishwa unaoonyesha ufaulu wa kila kidato kwa mtihani uliouchagua.
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="rounded-md bg-gray-100 px-3 py-1.5 text-[11px] font-semibold text-gray-700 hover:bg-gray-200"
                                @click="closePreview"
                            >
                                Close
                            </button>
                            <button
                                type="button"
                                class="rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                                @click="printPreview"
                            >
                                Print
                            </button>
                        </div>
                    </div>

                    <div class="border border-gray-200 bg-white p-4" id="class-performance-document">
                        <div class="mb-3 text-center">
                            <div class="mb-2 flex justify-center">
                                <img
                                    src="/images/emblem.png"
                                    alt="National Emblem"
                                    class="h-10 w-10 object-contain"
                                />
                            </div>
                            <div class="leading-tight">
                                <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-700">
                                    Jamhuri Ya Muungano Wa Tanzania
                                </div>
                                <div class="text-[10px] text-gray-700">
                                    Wizara Ya Elimu, Sayansi Na Teknolojia
                                </div>
                                <div class="mt-0.5 text-[10px] text-gray-500">
                                    Mfumo Wa Taarifa Za Mitihani Na Matokeo Ya Shule (E-Matokeo)
                                </div>
                            </div>
                            <div class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-800">
                                Ripoti Ya Ufaulu Kwa Madarasa
                            </div>
                            <div class="text-[10px] text-gray-500">
                                Exam: <span class="font-medium">{{ exams.find((e) => String(e.id) === String(filters.exam))?.name || 'Selected Exam' }}</span>
                                <span v-if="filters.year"> • Year: {{ filters.year }}</span>
                            </div>
                        </div>

                        <table class="w-full border-collapse text-[11px] mb-4">
                            <thead>
                                <tr class="bg-emerald-50 text-emerald-800">
                                    <th class="border border-emerald-100 px-2 py-1 text-left">Class Level</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-center">Candidates</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-center">Average Mark</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-center">Div I</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-center">Div II</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-center">Div III</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-center">Div IV</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-center">Div 0</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-center">Pass Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!performance.length">
                                    <td
                                        colspan="9"
                                        class="border border-gray-200 px-2 py-3 text-center text-[11px] text-gray-400"
                                    >
                                        No performance data available yet for this exam.
                                    </td>
                                </tr>
                                <tr
                                    v-for="row in performance"
                                    :key="row.class_level || 'unknown'"
                                    class="text-gray-700"
                                >
                                    <td class="border border-emerald-100 px-2 py-1 bg-white">
                                        {{ row.class_level || 'Unknown' }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 text-center bg-white">
                                        {{ row.candidates }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 text-center bg-white">
                                        {{ row.average_mark ?? '—' }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 text-center bg-white">
                                        {{ row.divisions.I }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 text-center bg-white">
                                        {{ row.divisions.II }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 text-center bg-white">
                                        {{ row.divisions.III }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 text-center bg-white">
                                        {{ row.divisions.IV }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 text-center bg-white">
                                        {{ row.divisions['0'] }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 text-center bg-white">
                                        {{ row.pass_rate }}%
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <div class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-700">
                                    Top 10 Bora (Kwa Wastani)
                                </div>
                                <table class="w-full border-collapse text-[11px]">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-600">
                                            <th class="border border-gray-200 px-2 py-1 text-left">#</th>
                                            <th class="border border-gray-200 px-2 py-1 text-left">Student</th>
                                            <th class="border border-gray-200 px-2 py-1 text-left">Exam No</th>
                                            <th class="border border-gray-200 px-2 py-1 text-center">Class</th>
                                            <th class="border border-gray-200 px-2 py-1 text-center">Average</th>
                                            <th class="border border-gray-200 px-2 py-1 text-center">Division</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!topStudents.length">
                                            <td colspan="6" class="border border-gray-200 px-2 py-2 text-center text-[11px] text-gray-400">
                                                Hakuna taarifa za wanafunzi 10 bora bado kwa mtihani huu.
                                            </td>
                                        </tr>
                                        <tr
                                            v-for="(stu, index) in topStudents"
                                            :key="stu.exam_number || index"
                                            class="text-gray-700"
                                        >
                                            <td class="border border-gray-200 px-2 py-1">
                                                {{ index + 1 }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1">
                                                {{ stu.full_name }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1">
                                                {{ stu.exam_number }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1 text-center">
                                                {{ stu.class_level || '—' }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1 text-center">
                                                {{ stu.average ?? '—' }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1 text-center">
                                                {{ stu.division || '—' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div>
                                <div class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-700">
                                    Wanafunzi 10 Wa Mwisho (Wasiofaulu)
                                </div>
                                <table class="w-full border-collapse text-[11px]">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-600">
                                            <th class="border border-gray-200 px-2 py-1 text-left">#</th>
                                            <th class="border border-gray-200 px-2 py-1 text-left">Student</th>
                                            <th class="border border-gray-200 px-2 py-1 text-left">Exam No</th>
                                            <th class="border border-gray-200 px-2 py-1 text-center">Class</th>
                                            <th class="border border-gray-200 px-2 py-1 text-center">Average</th>
                                            <th class="border border-gray-200 px-2 py-1 text-center">Division</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!bottomStudents.length">
                                            <td colspan="6" class="border border-gray-200 px-2 py-2 text-center text-[11px] text-gray-400">
                                                Hakuna taarifa za wanafunzi 10 wa mwisho bado kwa mtihani huu.
                                            </td>
                                        </tr>
                                        <tr
                                            v-for="(stu, index) in bottomStudents"
                                            :key="stu.exam_number || index"
                                            class="text-gray-700"
                                        >
                                            <td class="border border-gray-200 px-2 py-1">
                                                {{ index + 1 }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1">
                                                {{ stu.full_name }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1">
                                                {{ stu.exam_number }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1 text-center">
                                                {{ stu.class_level || '—' }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1 text-center">
                                                {{ stu.average ?? '—' }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1 text-center">
                                                {{ stu.division || '—' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    /* Hide everything by default */
    body * {
        visibility: hidden;
    }

    /* Only show the class performance document */
    #class-performance-document,
    #class-performance-document * {
        visibility: visible;
    }

    #class-performance-document {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        border: none;
        box-shadow: none;
    }
}
</style>
