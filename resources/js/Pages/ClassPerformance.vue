<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    schoolName: {
        type: String,
        default: null,
    },
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
    assignedClasses: {
        type: Array,
        default: () => [],
    },
    streams: {
        type: Array,
        default: () => [],
    },
    preview: {
        type: Object,
        default: () => null,
    },
});

const showPreview = ref(false);

const showStudentModal = ref(false);
const studentLoading = ref(false);
const studentError = ref(null);
const studentDetails = ref(null);
const showAllStudentSubjects = ref(false);

const enteredSubjectsCount = computed(() => {
    const subs = studentDetails.value?.subjects || [];
    return subs.filter((s) => s?.entered).length;
});

const totalSubjectsCount = computed(() => {
    const subs = studentDetails.value?.subjects || [];
    return subs.length;
});

const visibleStudentSubjects = computed(() => {
    const subs = studentDetails.value?.subjects || [];
    if (showAllStudentSubjects.value) {
        return subs;
    }
    return subs.filter((s) => s?.entered);
});

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
        { year: props.filters.year, exam, class: null, stream: null },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const selectedClassId = computed(() => String(props.filters?.class || ''));
const selectedStreamId = computed(() => String(props.filters?.stream || ''));

const onClassChange = (event) => {
    const cls = event.target.value || null;
    router.get(
        route('results.class'),
        { year: props.filters.year, exam: props.filters.exam, class: cls, stream: null },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const onStreamChange = (event) => {
    const stream = event.target.value || null;
    router.get(
        route('results.class'),
        { year: props.filters.year, exam: props.filters.exam, class: props.filters.class, stream },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const openPreviewForClassName = (className) => {
    const match = (props.assignedClasses || []).find((c) => c.name === className);
    if (!match) {
        return;
    }

    router.get(
        route('results.class'),
        { year: props.filters.year, exam: props.filters.exam, class: match.id, stream: null },
        {
            preserveScroll: true,
            replace: true,
            onSuccess: () => {
                showPreview.value = true;
            },
        },
    );
};

const openPreview = () => {
    showPreview.value = true;
};

const closePreview = () => {
    showPreview.value = false;
};

const closeStudentModal = () => {
    showStudentModal.value = false;
    studentDetails.value = null;
    studentError.value = null;
    studentLoading.value = false;
    showAllStudentSubjects.value = false;
};

const openStudentModal = async (stu) => {
    if (!stu?.student_id || !props.filters?.exam) {
        return;
    }

    showStudentModal.value = true;
    studentLoading.value = true;
    studentError.value = null;
    studentDetails.value = null;
    showAllStudentSubjects.value = false;

    try {
        const url = route('results.student-details', { exam: props.filters.exam, student: stu.student_id });
        const res = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const data = await res.json().catch(() => null);
        if (!res.ok) {
            studentError.value = data?.message || 'Failed to load student results.';
            return;
        }

        studentDetails.value = data;
    } catch (e) {
        studentError.value = 'Failed to load student results.';
    } finally {
        studentLoading.value = false;
    }
};

const printPreview = () => {
    const prevTitle = document.title;
    const school = props.schoolName || 'School';
    const clsName = props.preview?.class_name || 'Class';
    document.title = `${school} - ${clsName} Performance`;
    window.print();
    setTimeout(() => {
        document.title = prevTitle;
    }, 1000);
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
                    <div v-if="filters.exam" class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Class</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            :value="selectedClassId"
                            @change="onClassChange"
                        >
                            <option value="">Select Class</option>
                            <option
                                v-for="cls in assignedClasses"
                                :key="cls.id"
                                :value="String(cls.id)"
                            >
                                {{ cls.name }}
                            </option>
                        </select>
                    </div>
                    <div v-if="filters.exam && streams && streams.length" class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Stream</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            :value="selectedStreamId"
                            @change="onStreamChange"
                        >
                            <option value="">All</option>
                            <option
                                v-for="s in streams"
                                :key="s.id"
                                :value="String(s.id)"
                            >
                                {{ s.name }}
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
                    :disabled="!filters.class"
                >
                    Preview Document
                </button>
            </div>

            <div v-if="filters.exam && !filters.class" class="mb-3 rounded-md bg-amber-50 px-3 py-2 text-[11px] text-amber-700 ring-1 ring-amber-100">
                Select a class to enable preview.
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
                            <th class="border-b border-gray-200 px-3 py-1.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filters.exam && performance.length === 0">
                            <td colspan="10" class="px-3 py-4 text-center text-[11px] text-gray-400">
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
                            <td class="px-3 py-1.5 align-top text-right">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-emerald-50 px-3 py-1 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                                    @click="openPreviewForClassName(row.class_level)"
                                >
                                    Preview
                                </button>
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
                <div class="max-h-full w-full max-w-5xl overflow-y-auto rounded-xl bg-white p-6 text-xs text-gray-800 shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-800">
                                Class Performance Preview
                            </h3>
                            <p class="mt-0.5 text-[11px] text-gray-500">
                                Printable preview of class performance for the selected exam.
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

                    <div class="mx-auto border border-gray-200 bg-[#e3f2ff] p-6 text-xs text-gray-800" id="class-performance-document">
                        <div class="mb-4 text-center">
                            <div class="mb-1 flex justify-center">
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
                                            <th class="border border-gray-200 px-2 py-1 text-center">Total</th>
                                            <th class="border border-gray-200 px-2 py-1 text-center">Average</th>
                                            <th class="border border-gray-200 px-2 py-1 text-center">Division</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!topStudents.length">
                                            <td colspan="7" class="border border-gray-200 px-2 py-2 text-center text-[11px] text-gray-400">
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
                                                <button
                                                    type="button"
                                                    class="text-left font-semibold text-emerald-700 hover:underline"
                                                    @click="openStudentModal(stu)"
                                                >
                                                    {{ stu.full_name }}
                                                </button>
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1">
                                                {{ stu.exam_number }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1 text-center">
                                                {{ stu.class_level || '—' }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1 text-center">
                                                {{ stu.total ?? '—' }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1 text-center">
                                                {{ stu.average ?? '—' }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1 text-center">
                                                {{ stu.division ?? '—' }}
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
                                            <th class="border border-gray-200 px-2 py-1 text-center">Total</th>
                                            <th class="border border-gray-200 px-2 py-1 text-center">Average</th>
                                            <th class="border border-gray-200 px-2 py-1 text-center">Division</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!bottomStudents.length">
                                            <td colspan="7" class="border border-gray-200 px-2 py-2 text-center text-[11px] text-gray-400">
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
                                                <button
                                                    type="button"
                                                    class="text-left font-semibold text-emerald-700 hover:underline"
                                                    @click="openStudentModal(stu)"
                                                >
                                                    {{ stu.full_name }}
                                                </button>
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1">
                                                {{ stu.exam_number }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1 text-center">
                                                {{ stu.class_level || '—' }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1 text-center">
                                                {{ stu.total ?? '—' }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1 text-center">
                                                {{ stu.average ?? '—' }}
                                            </td>
                                            <td class="border border-gray-200 px-2 py-1 text-center">
                                                {{ stu.division ?? '—' }}
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

        <transition name="fade">
            <div
                v-if="showStudentModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-8"
            >
                <div class="max-h-full w-full max-w-3xl overflow-y-auto rounded-xl bg-white p-6 text-xs text-gray-800 shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-800">
                                Student Results
                            </h3>
                            <p class="mt-0.5 text-[11px] text-gray-500">
                                Detailed subject results for the selected exam.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="rounded-md bg-gray-100 px-3 py-1.5 text-[11px] font-semibold text-gray-700 hover:bg-gray-200"
                            @click="closeStudentModal"
                        >
                            Close
                        </button>
                    </div>

                    <div v-if="studentLoading" class="py-10 text-center text-[11px] text-gray-500">
                        Loading student results...
                    </div>

                    <div v-else-if="studentError" class="rounded-md bg-red-50 px-3 py-2 text-[11px] text-red-700 ring-1 ring-red-100">
                        {{ studentError }}
                    </div>

                    <div v-else-if="studentDetails" class="space-y-3">
                        <div class="rounded-md bg-gray-50 px-3 py-2 text-[11px] text-gray-700 ring-1 ring-gray-100">
                            <div class="font-semibold text-gray-900">
                                {{ studentDetails.student?.full_name }}
                            </div>
                            <div class="mt-0.5 text-gray-600">
                                Exam No: <span class="font-medium">{{ studentDetails.student?.exam_number }}</span>
                                <span class="mx-1">|</span>
                                Class: <span class="font-medium">{{ studentDetails.student?.class_level }}</span>
                                <span class="mx-1">|</span>
                                Sex: <span class="font-medium">{{ studentDetails.student?.gender ?? '—' }}</span>
                            </div>
                            <div class="mt-1 text-gray-600">
                                Total: <span class="font-semibold">{{ studentDetails.summary?.total ?? '—' }}</span>
                                <span class="mx-1">|</span>
                                Avg: <span class="font-semibold">{{ studentDetails.summary?.average ?? '—' }}</span>
                                <span class="mx-1">|</span>
                                Points: <span class="font-semibold">{{ studentDetails.summary?.points ?? '—' }}</span>
                                <span class="mx-1">|</span>
                                Div: <span class="font-semibold">{{ studentDetails.summary?.division ?? '—' }}</span>
                            </div>
                            <div class="mt-1 text-[10px] text-gray-500">
                                Entered: <span class="font-semibold">{{ enteredSubjectsCount }}</span>/<span class="font-semibold">{{ totalSubjectsCount }}</span> subjects
                            </div>
                            <div v-if="studentDetails.summary?.points === null" class="mt-1 text-[10px] text-gray-500">
                                Points/Division are based on available captured subjects. If fewer than 7 subjects have marks/points, points total is not shown.
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="text-[11px] font-semibold text-gray-700">
                                Subjects
                            </div>
                            <button
                                type="button"
                                class="rounded-md bg-gray-100 px-3 py-1.5 text-[11px] font-semibold text-gray-700 hover:bg-gray-200"
                                @click="showAllStudentSubjects = !showAllStudentSubjects"
                            >
                                {{ showAllStudentSubjects ? 'Show Entered Only' : 'Show All' }}
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-[11px]">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-600">
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-left">Code</th>
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-left">Subject</th>
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-center">Marks</th>
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-center">GRD</th>
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-center">PTS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!visibleStudentSubjects.length">
                                        <td colspan="5" class="px-3 py-4 text-center text-[11px] text-gray-400">
                                            No entered subject results found.
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(subj, idx) in visibleStudentSubjects"
                                        :key="subj.code || idx"
                                        class="border-b border-gray-100 text-gray-700 odd:bg-white even:bg-gray-50"
                                    >
                                        <td class="px-3 py-1.5 align-top font-mono">{{ subj.code }}</td>
                                        <td class="px-3 py-1.5 align-top">{{ subj.name }}</td>
                                        <td class="px-3 py-1.5 align-top text-center">
                                            <span v-if="subj.entered">{{ subj.marks ?? '—' }}</span>
                                            <span v-else class="text-gray-400">Not entered</span>
                                        </td>
                                        <td class="px-3 py-1.5 align-top text-center">
                                            <span class="font-semibold">{{ subj.grade ?? '—' }}</span>
                                            <span
                                                v-if="subj.entered && !subj?.saved?.grade && subj?.computed?.grade"
                                                class="ml-1 rounded bg-amber-50 px-1 py-0.5 text-[9px] font-semibold text-amber-700 ring-1 ring-amber-100"
                                            >
                                                calc
                                            </span>
                                        </td>
                                        <td class="px-3 py-1.5 align-top text-center">
                                            <span class="font-semibold">{{ subj.points ?? '—' }}</span>
                                            <span
                                                v-if="subj.entered && (subj?.saved?.points === null || subj?.saved?.points === undefined) && subj?.computed?.points !== null && subj?.computed?.points !== undefined"
                                                class="ml-1 rounded bg-amber-50 px-1 py-0.5 text-[9px] font-semibold text-amber-700 ring-1 ring-amber-100"
                                            >
                                                calc
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
