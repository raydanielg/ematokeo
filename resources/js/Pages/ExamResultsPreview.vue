<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    exam: {
        type: Object,
        default: () => ({}),
    },
    classes: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ class: null }),
    },
    students: {
        type: Array,
        default: () => [],
    },
    centreSummary: {
        type: Object,
        default: () => null,
    },
    subjectSummary: {
        type: Array,
        default: () => [],
    },
    subjectCodes: {
        type: Array,
        default: () => [],
    },
    schoolName: {
        type: String,
        default: null,
    },
});

const printDocument = () => {
    const prevTitle = document.title;
    const styleId = 'exam-results-print-style';
    const existing = document.getElementById(styleId);
    if (existing) {
        existing.remove();
    }

    const isWide = selectedView.value === 'wide';
    const style = document.createElement('style');
    style.id = styleId;
    style.media = 'print';
    style.textContent = `
@page {
  size: A4 landscape;
  margin: 5mm;
}
`;
    document.head.appendChild(style);

    const school = props.schoolName || 'School';
    const examName = props.exam?.name || 'Exam';
    document.title = `${school} - ${examName} Results`;

    document.documentElement.style.setProperty('--print-doc-width', isWide ? '270mm' : '250mm');
    document.documentElement.style.setProperty('--print-doc-padding', isWide ? '6mm' : '4mm');
    document.documentElement.style.setProperty('--print-font-size', isWide ? '8px' : '9px');
    document.documentElement.style.setProperty('--print-cell-padding-x', isWide ? '2px' : '3px');
    document.documentElement.style.setProperty('--print-cell-padding-y', '1px');
    document.documentElement.style.setProperty('--print-table-layout', isWide ? 'fixed' : 'auto');

    const cleanup = () => {
        document.documentElement.style.removeProperty('--print-doc-width');
        document.documentElement.style.removeProperty('--print-doc-padding');
        document.documentElement.style.removeProperty('--print-font-size');
        document.documentElement.style.removeProperty('--print-cell-padding-x');
        document.documentElement.style.removeProperty('--print-cell-padding-y');
        document.documentElement.style.removeProperty('--print-table-layout');
        document.title = prevTitle;
        style.remove();
        window.removeEventListener('afterprint', cleanup);
    };

    window.addEventListener('afterprint', cleanup);
    window.print();
    setTimeout(cleanup, 1500);
};

const selectedClassId = computed(() => String(props.filters?.class || ''));
const selectedView = computed(() => String(props.filters?.view || 'compact'));

const onClassChange = (event) => {
    const value = event.target.value || '';
    router.get(
        route('exams.results', props.exam.id),
        { class: value || null, view: selectedView.value || 'compact' },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const onViewChange = (event) => {
    const value = event.target.value || 'compact';
    router.get(
        route('exams.results', props.exam.id),
        { class: selectedClassId.value || null, view: value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const subjectByCode = (row, code) => {
    const arr = row?.subjects || [];
    return arr.find((s) => s.code === code) || null;
};

const detailsOpen = ref(false);
const selectedStudent = ref(null);

const openDetails = (row) => {
    selectedStudent.value = row;
    detailsOpen.value = true;
};

const closeDetails = () => {
    detailsOpen.value = false;
    selectedStudent.value = null;
};
</script>

<template>
    <Head :title="`Results - ${exam.name || 'Exam'}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Exam Results Preview
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Read-only preview of results layout for this exam. Marks and divisions will be filled once results are processed.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <div v-if="classes && classes.length" class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Class</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-2 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            :value="selectedClassId"
                            @change="onClassChange"
                        >
                            <option value="">All</option>
                            <option
                                v-for="cls in classes"
                                :key="cls.id"
                                :value="String(cls.id)"
                            >
                                {{ cls.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">View</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-2 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            :value="selectedView"
                            @change="onViewChange"
                        >
                            <option value="compact">By Marks</option>
                            <option value="wide">By Grades</option>
                        </select>
                    </div>
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                        @click="printDocument"
                    >
                        Print / Export
                    </button>
                </div>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mx-auto max-w-7xl border border-gray-200 bg-[#e3f2ff] p-6 text-xs text-gray-800" id="exam-results-document">
                <!-- Header with emblem and government text -->
                <div class="mb-4 text-center print-header">
                    <div class="mb-1 flex justify-center">
                        <img
                            src="/images/emblem.png"
                            alt="National Emblem"
                            class="h-10 w-10 object-contain"
                        />
                    </div>
                    <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-800">
                        THE PRESIDENT'S OFFICE
                    </div>
                    <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-800">
                        REGIONAL ADMINISTRATION AND LOCAL GOVERNMENT
                    </div>
                    <div class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-gray-800">
                        EXAMINATION CENTRE RESULTS
                    </div>
                    <div class="text-[10px] text-gray-700">
                        {{ exam.name || 'Examination' }}
                        <span v-if="exam.academic_year"> - {{ exam.academic_year }}</span>
                        <span v-if="exam.term"> ({{ exam.term }})</span>
                        <span v-if="exam.type"> [{{ exam.type }}]</span>
                    </div>
                </div>

                <!-- Division performance summary -->
                <div class="mb-4 flex justify-center print-division-summary">
                    <div class="inline-block bg-[#f6f4d2] text-[10px] text-gray-800">
                        <table class="border-collapse border border-gray-500 text-center">
                        <thead>
                            <tr class="bg-[#f0e8a0]">
                                <th class="border border-gray-500 px-2 py-1" colspan="7">DIVISION PERFORMANCE SUMMARY</th>
                            </tr>
                            <tr class="bg-[#f0e8a0]">
                                <th class="border border-gray-500 px-2 py-1">SEX</th>
                                <th class="border border-gray-500 px-2 py-1">I</th>
                                <th class="border border-gray-500 px-2 py-1">II</th>
                                <th class="border border-gray-500 px-2 py-1">III</th>
                                <th class="border border-gray-500 px-2 py-1">IV</th>
                                <th class="border border-gray-500 px-2 py-1">0</th>
                                <th class="border border-gray-500 px-2 py-1">TOT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sex in ['F','M','T']" :key="sex" :class="sex === 'T' ? 'font-semibold' : ''">
                                <td class="border border-gray-500 px-2 py-0.5">{{ sex }}</td>
                                <td class="border border-gray-500 px-2 py-0.5">{{ centreSummary?.division_summary?.[sex]?.I ?? 0 }}</td>
                                <td class="border border-gray-500 px-2 py-0.5">{{ centreSummary?.division_summary?.[sex]?.II ?? 0 }}</td>
                                <td class="border border-gray-500 px-2 py-0.5">{{ centreSummary?.division_summary?.[sex]?.III ?? 0 }}</td>
                                <td class="border border-gray-500 px-2 py-0.5">{{ centreSummary?.division_summary?.[sex]?.IV ?? 0 }}</td>
                                <td class="border border-gray-500 px-2 py-0.5">{{ centreSummary?.division_summary?.[sex]?.['0'] ?? 0 }}</td>
                                <td class="border border-gray-500 px-2 py-0.5">
                                    {{
                                        (centreSummary?.division_summary?.[sex]?.I ?? 0) +
                                        (centreSummary?.division_summary?.[sex]?.II ?? 0) +
                                        (centreSummary?.division_summary?.[sex]?.III ?? 0) +
                                        (centreSummary?.division_summary?.[sex]?.IV ?? 0) +
                                        (centreSummary?.division_summary?.[sex]?.['0'] ?? 0)
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- Students results table (Compact: by marks) -->
                <div v-if="selectedView !== 'wide'" class="mb-4 overflow-x-auto bg-[#fff9d7] print-results-table">
                    <table class="min-w-full border-collapse border border-gray-700 text-[10px]">
                        <thead>
                            <tr class="bg-[#f0e8a0] text-gray-800">
                                <th class="border border-gray-700 px-2 py-1 text-left">CNO</th>
                                <th class="border border-gray-700 px-2 py-1 text-left">FULL NAME</th>
                                <th class="border border-gray-700 px-2 py-1 text-center">SEX</th>
                                <th class="border border-gray-700 px-2 py-1 text-left">DETAILED SUBJECTS &amp; MARKS</th>
                                <th class="border border-gray-700 px-2 py-1 text-center">TOTAL</th>
                                <th class="border border-gray-700 px-2 py-1 text-center">GRD</th>
                                <th class="border border-gray-700 px-2 py-1 text-center">AVG</th>
                                <th class="border border-gray-700 px-2 py-1 text-center">DIV</th>
                                <th class="border border-gray-700 px-2 py-1 text-center">PTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!students.length" class="text-gray-500">
                                <td colspan="9" class="border border-gray-700 px-2 py-3 text-center text-[10px]">
                                    No marks have been captured yet for this exam. Once you save marks from the Enter Marks page, they will appear here.
                                </td>
                            </tr>
                            <tr
                                v-for="(row, index) in students"
                                :key="row.exam_number || index"
                                class="cursor-pointer text-gray-700 hover:bg-amber-50"
                                @click="openDetails(row)"
                            >
                                <td class="border border-gray-700 px-2 py-0.5 align-top font-mono">{{ row.exam_number }}</td>
                                <td class="border border-gray-700 px-2 py-0.5 align-top">{{ row.full_name ?? '—' }}</td>
                                <td class="border border-gray-700 px-2 py-0.5 text-center align-top">{{ row.gender ?? '—' }}</td>
                                <td class="border border-gray-700 px-2 py-0.5 align-top">
                                    <div class="flex flex-nowrap gap-x-2 gap-y-0.5 overflow-hidden font-mono" style="text-overflow: ellipsis;">
                                        <span
                                            v-for="subject in (row.subjects || [])"
                                            :key="subject.code"
                                            class="whitespace-nowrap"
                                        >
                                            {{ subject.code }} {{ subject.marks ?? '-' }}{{ subject.grade ? `-${String(subject.grade).toUpperCase()}` : '' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="border border-gray-700 px-2 py-0.5 text-center align-top">{{ row.total ?? '—' }}</td>
                                <td class="border border-gray-700 px-2 py-0.5 text-center align-top font-semibold">{{ row.grade ?? '—' }}</td>
                                <td class="border border-gray-700 px-2 py-0.5 text-center align-top">{{ row.average ?? '—' }}</td>
                                <td class="border border-gray-700 px-2 py-0.5 text-center align-top font-semibold">{{ row.division ?? '—' }}</td>
                                <td class="border border-gray-700 px-2 py-0.5 text-center align-top font-semibold">{{ row.points ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Students results table (Wide: by grades) -->
                <div v-else class="mb-4 overflow-x-auto bg-[#fff9d7] print-results-table">
                    <table class="min-w-full border-collapse border border-gray-700 text-[10px]">
                        <thead>
                            <tr class="bg-[#f0e8a0] text-gray-800">
                                <th class="border border-gray-700 px-2 py-1 text-left" rowspan="2">CNO</th>
                                <th class="border border-gray-700 px-2 py-1 text-left" rowspan="2">FULL NAME</th>
                                <th class="border border-gray-700 px-2 py-1 text-center" rowspan="2">SEX</th>
                                <th
                                    v-for="code in (subjectCodes || [])"
                                    :key="code"
                                    class="border border-gray-700 px-2 py-1 text-center"
                                    colspan="2"
                                >
                                    {{ code }}
                                </th>
                                <th class="border border-gray-700 px-2 py-1 text-center" rowspan="2">TOTAL</th>
                                <th class="border border-gray-700 px-2 py-1 text-center" rowspan="2">GRD</th>
                                <th class="border border-gray-700 px-2 py-1 text-center" rowspan="2">AVG</th>
                                <th class="border border-gray-700 px-2 py-1 text-center" rowspan="2">DIV</th>
                                <th class="border border-gray-700 px-2 py-1 text-center" rowspan="2">PTS</th>
                            </tr>
                            <tr class="bg-[#f0e8a0] text-gray-800">
                                <template v-for="code in (subjectCodes || [])" :key="`${code}-sub`">
                                    <th class="border border-gray-700 px-2 py-1 text-center">M</th>
                                    <th class="border border-gray-700 px-2 py-1 text-center">G</th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!students.length" class="text-gray-500">
                                <td
                                    :colspan="3 + ((subjectCodes || []).length * 2) + 5"
                                    class="border border-gray-700 px-2 py-3 text-center text-[10px]"
                                >
                                    No marks have been captured yet for this exam. Once you save marks from the Enter Marks page, they will appear here.
                                </td>
                            </tr>
                            <tr
                                v-for="(row, index) in students"
                                :key="row.exam_number || index"
                                class="cursor-pointer text-gray-700 hover:bg-amber-50"
                                @click="openDetails(row)"
                            >
                                <td class="border border-gray-700 px-2 py-0.5 align-top font-mono">{{ row.exam_number }}</td>
                                <td class="border border-gray-700 px-2 py-0.5 align-top">{{ row.full_name ?? '—' }}</td>
                                <td class="border border-gray-700 px-2 py-0.5 text-center align-top">{{ row.gender ?? '—' }}</td>

                                <template v-for="code in (subjectCodes || [])" :key="`${row.exam_number}-${code}`">
                                    <td class="border border-gray-700 px-2 py-0.5 text-center align-top font-mono">
                                        {{ subjectByCode(row, code)?.marks ?? '—' }}
                                    </td>
                                    <td class="border border-gray-700 px-2 py-0.5 text-center align-top font-semibold">
                                        {{ subjectByCode(row, code)?.grade ? String(subjectByCode(row, code)?.grade).toUpperCase() : '—' }}
                                    </td>
                                </template>

                                <td class="border border-gray-700 px-2 py-0.5 text-center align-top">{{ row.total ?? '—' }}</td>
                                <td class="border border-gray-700 px-2 py-0.5 text-center align-top font-semibold">{{ row.grade ?? '—' }}</td>
                                <td class="border border-gray-700 px-2 py-0.5 text-center align-top">{{ row.average ?? '—' }}</td>
                                <td class="border border-gray-700 px-2 py-0.5 text-center align-top font-semibold">{{ row.division ?? '—' }}</td>
                                <td class="border border-gray-700 px-2 py-0.5 text-center align-top font-semibold">{{ row.points ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="detailsOpen" class="fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/40" @click="closeDetails"></div>
                    <div class="relative w-full max-w-3xl rounded-lg bg-white shadow-lg ring-1 ring-black/10">
                        <div class="flex items-start justify-between border-b border-gray-200 px-4 py-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-800">Student Details</div>
                                <div class="mt-0.5 text-xs text-gray-600">
                                    <span class="font-mono">{{ selectedStudent?.exam_number }}</span>
                                    <span class="mx-1">-</span>
                                    <span>{{ selectedStudent?.full_name }}</span>
                                    <span class="mx-1">|</span>
                                    <span>Sex: {{ selectedStudent?.gender ?? '—' }}</span>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="rounded-md px-2 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100"
                                @click="closeDetails"
                            >
                                Close
                            </button>
                        </div>

                        <div class="p-4">
                            <div class="grid grid-cols-5 gap-2 text-xs">
                                <div class="col-span-5 overflow-x-auto">
                                    <table class="min-w-full border-collapse border border-gray-200">
                                        <thead>
                                            <tr class="bg-gray-50 text-gray-700">
                                                <th class="border border-gray-200 px-2 py-1 text-left">SUBJECT</th>
                                                <th class="border border-gray-200 px-2 py-1 text-center">MARKS</th>
                                                <th class="border border-gray-200 px-2 py-1 text-center">GRADE</th>
                                                <th class="border border-gray-200 px-2 py-1 text-center">POINTS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="sub in (selectedStudent?.subjects || [])"
                                                :key="sub.code"
                                                class="text-gray-800"
                                            >
                                                <td class="border border-gray-200 px-2 py-1 font-medium">{{ sub.code }}</td>
                                                <td class="border border-gray-200 px-2 py-1 text-center font-mono">{{ sub.marks ?? '—' }}</td>
                                                <td class="border border-gray-200 px-2 py-1 text-center font-semibold">{{ sub.grade ? String(sub.grade).toUpperCase() : '—' }}</td>
                                                <td class="border border-gray-200 px-2 py-1 text-center font-mono">{{ sub.points ?? '—' }}</td>
                                            </tr>
                                            <tr v-if="!(selectedStudent?.subjects || []).length">
                                                <td colspan="4" class="border border-gray-200 px-2 py-3 text-center text-xs text-gray-500">
                                                    No subjects found.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="col-span-5 mt-3 grid grid-cols-5 gap-2 rounded-md bg-gray-50 p-3 text-xs text-gray-700">
                                    <div class="col-span-1">
                                        <div class="text-[10px] uppercase text-gray-500">Total</div>
                                        <div class="mt-0.5 font-semibold">{{ selectedStudent?.total ?? '—' }}</div>
                                    </div>
                                    <div class="col-span-1">
                                        <div class="text-[10px] uppercase text-gray-500">Grade</div>
                                        <div class="mt-0.5 font-semibold">{{ selectedStudent?.grade ?? '—' }}</div>
                                    </div>
                                    <div class="col-span-1">
                                        <div class="text-[10px] uppercase text-gray-500">Average</div>
                                        <div class="mt-0.5 font-semibold">{{ selectedStudent?.average ?? '—' }}</div>
                                    </div>
                                    <div class="col-span-1">
                                        <div class="text-[10px] uppercase text-gray-500">Div</div>
                                        <div class="mt-0.5 font-semibold">{{ selectedStudent?.division ?? '—' }}</div>
                                    </div>
                                    <div class="col-span-1">
                                        <div class="text-[10px] uppercase text-gray-500">Pts</div>
                                        <div class="mt-0.5 font-semibold">{{ selectedStudent?.points ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Centre performance summary (sample blocks) -->
                <div class="space-y-3 text-[10px] text-gray-800">
                    <div class="overflow-hidden bg-[#e4f2d0]">
                        <table class="w-full border-collapse border border-gray-500">
                            <thead>
                                <tr class="bg-[#d6e8be] text-gray-800">
                                    <th colspan="4" class="border border-gray-500 px-2 py-1 text-left">
                                        EXAMINATION CENTRE OVERALL PERFORMANCE
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-gray-500 px-2 py-0.5">TOTAL CANDIDATES</td>
                                    <td class="border border-gray-500 px-2 py-0.5">{{ centreSummary?.total_candidates ?? '—' }}</td>
                                    <td class="border border-gray-500 px-2 py-0.5">EXAM CENTRE AVERAGE</td>
                                    <td class="border border-gray-500 px-2 py-0.5">{{ centreSummary?.centre_average ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-500 px-2 py-0.5">TOTAL PASSED (DIV I-IV)</td>
                                    <td class="border border-gray-500 px-2 py-0.5">{{ centreSummary?.passed_candidates ?? '—' }}</td>
                                    <td class="border border-gray-500 px-2 py-0.5">CENTRE GPA</td>
                                    <td class="border border-gray-500 px-2 py-0.5">{{ centreSummary?.centre_gpa ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-500 px-2 py-0.5">TOTAL FAILED (DIV 0)</td>
                                    <td class="border border-gray-500 px-2 py-0.5">{{ centreSummary?.failed_candidates ?? '—' }}</td>
                                    <td class="border border-gray-500 px-2 py-0.5">CENTRE RANK (REGION)</td>
                                    <td class="border border-gray-500 px-2 py-0.5">—</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="overflow-x-auto bg-[#f5f8ff]">
                        <table class="min-w-full border-collapse border border-gray-500 text-[10px]">
                            <thead>
                                <tr class="bg-[#dbe4ff] text-gray-800">
                                    <th class="border border-gray-500 px-2 py-1 text-left">SUBJECT CODE</th>
                                    <th class="border border-gray-500 px-2 py-1 text-left">SUBJECT NAME</th>
                                    <th class="border border-gray-500 px-2 py-1 text-center">SAT</th>
                                    <th class="border border-gray-500 px-2 py-1 text-center">PASS (A-D)</th>
                                    <th class="border border-gray-500 px-2 py-1 text-center">FAILED (F)</th>
                                    <th class="border border-gray-500 px-2 py-1 text-center">GPA</th>
                                    <th class="border border-gray-500 px-2 py-1 text-center">COMPETENCY</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!subjectSummary.length">
                                    <td colspan="7" class="border border-gray-500 px-2 py-2 text-center text-[10px] text-gray-500">
                                        Subject performance summary will appear here once marks have been captured.
                                    </td>
                                </tr>
                                <tr
                                    v-for="subject in subjectSummary"
                                    :key="subject.code"
                                >
                                    <td class="border border-gray-500 px-2 py-0.5">{{ subject.code }}</td>
                                    <td class="border border-gray-500 px-2 py-0.5">{{ subject.name }}</td>
                                    <td class="border border-gray-500 px-2 py-0.5 text-center">{{ subject.sat }}</td>
                                    <td class="border border-gray-500 px-2 py-0.5 text-center">{{ subject.pass }}</td>
                                    <td class="border border-gray-500 px-2 py-0.5 text-center">{{ subject.fail }}</td>
                                    <td class="border border-gray-500 px-2 py-0.5 text-center">{{ subject.gpa ?? '—' }}</td>
                                    <td class="border border-gray-500 px-2 py-0.5 text-center">{{ subject.competency ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="mt-2 text-[10px] text-gray-600">
                        Hii ni muhtasari wa mfano wa matokeo ya kituo cha mtihani. Katika mfumo kamili, takwimu za juu zitajazwa kwa kutumia alama halisi
                        na sheria za ufaulu.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    html,
    body {
        background: #e3f2ff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    body * {
        visibility: hidden;
    }

    #exam-results-document,
    #exam-results-document * {
        visibility: visible;
    }

    #exam-results-document {
        width: var(--print-doc-width, 277mm) !important;
        max-width: var(--print-doc-width, 277mm) !important;
        margin: 0 !important;
        padding: var(--print-doc-padding, 6mm) !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        background: #e3f2ff !important;
        box-sizing: border-box;
    }

    #exam-results-document .print-header {
        margin-bottom: 4mm !important;
    }

    #exam-results-document .print-division-summary {
        margin-bottom: 4mm !important;
        transform: scale(0.92);
        transform-origin: top center;
    }

    #exam-results-document .print-division-summary table th,
    #exam-results-document .print-division-summary table td {
        padding: 1px 4px !important;
        font-size: 8px !important;
    }

    #exam-results-document .print-results-table table {
        font-size: var(--print-font-size, 8px) !important;
    }

    #exam-results-document .print-results-table th,
    #exam-results-document .print-results-table td {
        padding: var(--print-cell-padding-y, 1px) var(--print-cell-padding-x, 2px) !important;
    }

    #exam-results-document .print-results-table th:nth-child(2),
    #exam-results-document .print-results-table td:nth-child(2) {
        white-space: nowrap !important;
        word-break: normal !important;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #exam-results-document .print-results-table th:nth-child(1),
    #exam-results-document .print-results-table td:nth-child(1) {
        width: 18mm;
    }

    #exam-results-document .print-results-table th:nth-child(2),
    #exam-results-document .print-results-table td:nth-child(2) {
        width: 52mm;
        max-width: 52mm;
    }

    /* Compact view: keep detailed subjects column on one line in print */
    #exam-results-document .print-results-table td:nth-child(4) {
        white-space: nowrap !important;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #exam-results-document .print-results-table th:nth-child(3),
    #exam-results-document .print-results-table td:nth-child(3) {
        width: 9mm;
    }

    #exam-results-document .print-results-table th:nth-last-child(5),
    #exam-results-document .print-results-table td:nth-last-child(5) {
        width: 14mm;
    }

    #exam-results-document .print-results-table th:nth-last-child(4),
    #exam-results-document .print-results-table td:nth-last-child(4) {
        width: 11mm;
    }

    #exam-results-document .print-results-table th:nth-last-child(3),
    #exam-results-document .print-results-table td:nth-last-child(3) {
        width: 14mm;
    }

    #exam-results-document .print-results-table th:nth-last-child(2),
    #exam-results-document .print-results-table td:nth-last-child(2) {
        width: 9mm;
    }

    #exam-results-document .print-results-table th:nth-last-child(1),
    #exam-results-document .print-results-table td:nth-last-child(1) {
        width: 11mm;
    }

    #exam-results-document table {
        width: 100% !important;
        table-layout: var(--print-table-layout, fixed);
        border-collapse: collapse;
    }

    #exam-results-document thead {
        display: table-header-group;
    }

    #exam-results-document tfoot {
        display: table-footer-group;
    }

    #exam-results-document th,
    #exam-results-document td {
        white-space: normal !important;
        word-break: break-word;
        overflow-wrap: anywhere;
        line-height: 1.15;
    }

    #exam-results-document tr {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .fixed {
        display: none !important;
    }
}
</style>
