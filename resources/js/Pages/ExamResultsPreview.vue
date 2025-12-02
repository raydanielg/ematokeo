<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    exam: {
        type: Object,
        default: () => ({}),
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
});

const printDocument = () => {
    window.print();
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
                <button
                    type="button"
                    class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                    @click="printDocument"
                >
                    Print / Export
                </button>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mx-auto max-w-5xl border border-gray-200 bg-[#e3f2ff] p-6 text-xs text-gray-800" id="exam-results-document">
                <!-- Header with emblem and government text -->
                <div class="mb-4 text-center">
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
                        EXAMINATION CENTRE RESULTS PREVIEW
                    </div>
                    <div class="text-[10px] text-gray-700">
                        {{ exam.name || 'Examination' }}
                        <span v-if="exam.academic_year"> - {{ exam.academic_year }}</span>
                        <span v-if="exam.term"> ({{ exam.term }})</span>
                        <span v-if="exam.type"> [{{ exam.type }}]</span>
                    </div>
                </div>

                <!-- Division performance summary -->
                <div class="mb-4 flex justify-center">
                    <div class="inline-block bg-[#f6f4d2] text-[10px] text-gray-800">
                        <table class="border-collapse border border-gray-500 text-center">
                        <thead>
                            <tr class="bg-[#f0e8a0]">
                                <th class="border border-gray-500 px-2 py-1" colspan="8">DIVISION PERFORMANCE SUMMARY</th>
                            </tr>
                            <tr class="bg-[#f0e8a0]">
                                <th class="border border-gray-500 px-2 py-1">SEX</th>
                                <th class="border border-gray-500 px-2 py-1">I</th>
                                <th class="border border-gray-500 px-2 py-1">II</th>
                                <th class="border border-gray-500 px-2 py-1">III</th>
                                <th class="border border-gray-500 px-2 py-1">IV</th>
                                <th class="border border-gray-500 px-2 py-1">0</th>
                                <th class="border border-gray-500 px-2 py-1">ABS</th>
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
                                <td class="border border-gray-500 px-2 py-0.5">—</td>
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

                <!-- Students results table -->
                <div class="mb-4 overflow-x-auto bg-[#fff9d7]">
                    <table class="min-w-full border-collapse border border-gray-500 text-[11px]">
                        <thead>
                            <tr class="bg-[#f0e8a0] text-gray-700">
                                <th class="border border-gray-500 px-2 py-1 text-left">#</th>
                                <th class="border border-gray-500 px-2 py-1 text-left">Exam No</th>
                                <th class="border border-gray-500 px-2 py-1 text-left">Student Name</th>
                                <th class="border border-gray-500 px-2 py-1 text-center">Subjects & Marks</th>
                                <th class="border border-gray-500 px-2 py-1 text-center">Total</th>
                                <th class="border border-gray-500 px-2 py-1 text-center">Average</th>
                                <th class="border border-gray-500 px-2 py-1 text-center">Division</th>
                                <th class="border border-gray-500 px-2 py-1 text-center">Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!students.length" class="text-gray-500">
                                <td colspan="8" class="border border-gray-500 px-2 py-3 text-center text-[10px]">
                                    No marks have been captured yet for this exam. Once you save marks from the Enter Marks page, they will appear here.
                                </td>
                            </tr>
                            <tr
                                v-for="(row, index) in students"
                                :key="row.exam_number || index"
                                class="text-gray-700"
                            >
                                <td class="border border-gray-500 px-2 py-1 align-top">{{ index + 1 }}</td>
                                <td class="border border-gray-500 px-2 py-1 align-top font-mono">{{ row.exam_number }}</td>
                                <td class="border border-gray-500 px-2 py-1 align-top">{{ row.full_name }}</td>
                                <td class="border border-gray-500 px-2 py-1 align-top">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="subject in row.subjects"
                                            :key="subject.code"
                                            class="rounded bg-gray-50 px-1.5 py-0.5"
                                        >
                                            {{ subject.code }}: {{ subject.marks ?? '—' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="border border-gray-500 px-2 py-1 text-center align-top">{{ row.total ?? '—' }}</td>
                                <td class="border border-gray-500 px-2 py-1 text-center align-top">{{ row.average ?? '—' }}</td>
                                <td class="border border-gray-500 px-2 py-1 text-center align-top">{{ row.division ?? '—' }}</td>
                                <td class="border border-gray-500 px-2 py-1 text-center align-top">{{ row.points ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Centre performance summary (sample blocks) -->
                <div class="space-y-3 text-[10px] text-gray-800">
                    <div class="overflow-hidden bg-[#e4f2d0]">
                        <table class="w-full border-collapse border border-gray-500">
                            <thead>
                                <tr class="bg-[#d6e8be] text-gray-800">
                                    <th colspan="4" class="border border-gray-500 px-2 py-1 text-left">
                                        EXAMINATION CENTRE OVERALL PERFORMANCE (SAMPLE)
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
                                    <td class="border border-gray-500 px-2 py-0.5">TOTAL PASSED (DIV I-III)</td>
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
                                        Subject performance summary will appear here once marks and grading are fully configured.
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
    body * {
        visibility: hidden;
    }

    #exam-results-document,
    #exam-results-document * {
        visibility: visible;
    }

    #exam-results-document {
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
