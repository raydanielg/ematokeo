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
    ranking: {
        // array of { exam_number, full_name, class_level, total, average, division, points, position }
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ year: null, exam: null }),
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
        route('results.ranking'),
        { year, exam: null },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const onExamChange = (event) => {
    const exam = event.target.value || null;
    router.get(
        route('results.ranking'),
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

const closeStudentModal = () => {
    showStudentModal.value = false;
    studentDetails.value = null;
    studentError.value = null;
    studentLoading.value = false;
    showAllStudentSubjects.value = false;
};

const openStudentModal = async (row) => {
    if (!row?.student_id || !props.filters?.exam) {
        return;
    }

    showStudentModal.value = true;
    studentLoading.value = true;
    studentError.value = null;
    studentDetails.value = null;
    showAllStudentSubjects.value = false;

    try {
        const url = route('results.student-details', { exam: props.filters.exam, student: row.student_id });
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
    window.print();
};
</script>

<template>
    <Head title="Ranking" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Students Ranking
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Overall ranking of students based on exam performance. Use filters to select the exam you want to analyse.
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
                <p class="mb-1 font-medium">Ranking Summary</p>
                <p class="text-[11px] text-gray-500">
                    The list below shows students ordered by their average marks and points for the selected exam.
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
                Please select an exam to view ranking.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600">
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Pos</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Exam No</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Student Name</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Class</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Total</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Average</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Division</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filters.exam && ranking.length === 0">
                            <td colspan="8" class="px-3 py-4 text-center text-[11px] text-gray-400">
                                No ranking data found for this exam yet.
                            </td>
                        </tr>
                        <tr
                            v-for="row in ranking"
                            :key="row.exam_number"
                            class="border-b border-gray-100 text-gray-700 odd:bg-white even:bg-gray-50"
                        >
                            <td class="px-3 py-1.5 align-top text-sm font-medium text-gray-900">
                                {{ row.position }}
                            </td>
                            <td class="px-3 py-1.5 align-top font-mono">
                                {{ row.exam_number }}
                            </td>
                            <td class="px-3 py-1.5 align-top">
                                <button
                                    type="button"
                                    class="text-left font-semibold text-emerald-700 hover:underline"
                                    @click="openStudentModal(row)"
                                >
                                    {{ row.full_name }}
                                </button>
                            </td>
                            <td class="px-3 py-1.5 align-top">
                                {{ row.class_level || '—' }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-center">
                                {{ row.total ?? '—' }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-center">
                                {{ row.average ?? '—' }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-center">
                                {{ row.division || '—' }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-center">
                                {{ row.points ?? '—' }}
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
                                Ripoti Ya Orodha Ya Wanafunzi (Ranking)
                            </h3>
                            <p class="mt-0.5 text-[11px] text-gray-500">
                                Huu ni waraka wa kuchapishwa unaoonyesha orodha ya wanafunzi kwa mpangilio wa ufaulu kwa mtihani uliouchagua.
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

                    <div class="border border-gray-200 bg-white p-4" id="ranking-document">
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
                                Orodha Ya Wanafunzi Kwa Mpangilio Wa Ufaulu
                            </div>
                            <div class="text-[10px] text-gray-500">
                                Exam: <span class="font-medium">{{ exams.find((e) => String(e.id) === String(filters.exam))?.name || 'Selected Exam' }}</span>
                                <span v-if="filters.year"> • Year: {{ filters.year }}</span>
                            </div>
                        </div>

                        <table class="w-full border-collapse text-[11px]">
                            <thead>
                                <tr class="bg-emerald-50 text-emerald-800">
                                    <th class="border border-emerald-100 px-2 py-1 text-left">Pos</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-left">Exam No</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-left">Student Name</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-left">Class</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-center">Total</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-center">Average</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-center">Division</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-center">Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!ranking.length">
                                    <td
                                        colspan="8"
                                        class="border border-gray-200 px-2 py-3 text-center text-[11px] text-gray-400"
                                    >
                                        No ranking data available yet for this exam.
                                    </td>
                                </tr>
                                <tr
                                    v-for="row in ranking"
                                    :key="row.exam_number"
                                    class="text-gray-700"
                                >
                                    <td class="border border-emerald-100 px-2 py-1 bg-white">
                                        {{ row.position }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 bg-white font-mono">
                                        {{ row.exam_number }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 bg-white">
                                        <button
                                            type="button"
                                            class="text-left font-semibold text-emerald-700 hover:underline"
                                            @click="openStudentModal(row)"
                                        >
                                            {{ row.full_name }}
                                        </button>
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 bg-white">
                                        {{ row.class_level || '—' }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 text-center bg-white">
                                        {{ row.total ?? '—' }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 text-center bg-white">
                                        {{ row.average ?? '—' }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 text-center bg-white">
                                        {{ row.division || '—' }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 text-center bg-white">
                                        {{ row.points ?? '—' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                            <div class="text-[11px] font-semibold text-gray-700">Subjects</div>
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
    body * {
        visibility: hidden;
    }

    #ranking-document,
    #ranking-document * {
        visibility: visible;
    }

    #ranking-document {
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
