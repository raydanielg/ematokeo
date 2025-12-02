<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    school: Object,
    years: Array,
    exams: Array,
    classPerformance: Array,
    schoolSummary: Object,
    examSummaries: Array,
    subjectPerformance: Array,
    topSubjectsBest: Array,
    topSubjectsWorst: Array,
    yearTopStudents: Array,
    yearTrends: Array,
    filters: Object,
});

const onFilterChange = (key, value) => {
    const params = {
        ...props.filters,
        [key]: value || null,
    };

    router.get(route('reports.school'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="School Report" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        School Performance Report
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Analytical report for the whole school by year and exam, including class performance and exam trends.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Year</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            :value="filters.year || ''"
                            @change="(e) => onFilterChange('year', e.target.value)"
                        >
                            <option value="">Select Year</option>
                            <option
                                v-for="year in years"
                                :key="year"
                                :value="year"
                            >
                                {{ year }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Exam</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            :value="filters.exam || ''"
                            @change="(e) => onFilterChange('exam', e.target.value)"
                        >
                            <option value="">All Exams (Year Overview)</option>
                            <option
                                v-for="exam in exams"
                                :key="exam.id"
                                :value="exam.id"
                            >
                                {{ exam.name }} ({{ exam.academic_year }})
                            </option>
                        </select>
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                        @click="printReport"
                    >
                        Print Full Report
                    </button>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-6xl bg-white p-4 shadow-sm ring-1 ring-gray-100" id="school-report-document">
            <!-- Header -->
            <div class="mb-4 text-center">
                <div class="mb-1 flex justify-center">
                    <img
                        src="/images/emblem.png"
                        alt="National Emblem"
                        class="h-10 w-10 object-contain"
                    />
                </div>
                <div class="leading-tight">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-700">
                        {{ school?.council_name || 'HALMASHAURI YA MANISPAA' }}
                    </div>
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-700">
                        {{ school?.name || 'SHULE YA SEKONDARI' }}
                    </div>
                    <div class="text-[10px] text-gray-700">
                        {{ school?.address_line ?? '' }}
                    </div>
                    <div class="text-[10px] text-gray-700">
                        SIMU: {{ school?.phone ?? '........' }}, EMAIL: {{ school?.email ?? '........' }}
                    </div>
                </div>
                <div class="mt-2 text-[11px] font-semibold uppercase tracking-wide text-gray-800">
                    TAARIFA YA UFAULU WA SHULE KWA MWAKA {{ filters.year || '____' }}
                </div>
                <div class="text-[10px] text-gray-600">
                    Mtihani: <span class="font-semibold">{{ filters.exam ? (exams.find((e) => e.id === Number(filters.exam))?.name || 'Mtihani Maalum') : 'Mitihani Yote ya Mwaka' }}</span>
                </div>
            </div>

            <!-- Section 1: School Summary -->
            <div class="mb-4 border-b border-dashed border-gray-300 pb-3 text-[11px]">
                <h3 class="mb-2 text-[12px] font-semibold uppercase tracking-wide text-gray-800">
                    1. Muhtasari wa Ufaulu wa Shule
                </h3>
                <div v-if="schoolSummary" class="grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div class="rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2">
                        <div class="text-[10px] font-medium text-emerald-700">Jumla ya Watahimiwa</div>
                        <div class="text-lg font-semibold text-emerald-900">
                            {{ schoolSummary.candidates }}
                        </div>
                    </div>
                    <div class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2">
                        <div class="text-[10px] font-medium text-blue-700">Wastani wa Alama za Shule</div>
                        <div class="text-lg font-semibold text-blue-900">
                            {{ schoolSummary.average_mark ?? '-' }}
                        </div>
                    </div>
                    <div class="rounded-lg border border-amber-100 bg-amber-50 px-3 py-2">
                        <div class="text-[10px] font-medium text-amber-700">Idadi ya Waliofaulu (Div I-IV)</div>
                        <div class="text-lg font-semibold text-amber-900">
                            {{ (schoolSummary.divisions.I || 0) + (schoolSummary.divisions.II || 0) + (schoolSummary.divisions.III || 0) + (schoolSummary.divisions.IV || 0) }}
                        </div>
                    </div>
                    <div class="rounded-lg border border-purple-100 bg-purple-50 px-3 py-2">
                        <div class="text-[10px] font-medium text-purple-700">Asilimia ya Ufaulu wa Shule</div>
                        <div class="text-lg font-semibold text-purple-900">
                            {{ schoolSummary.pass_rate }}<span class="text-sm">%</span>
                        </div>
                    </div>
                </div>
                <p v-else class="text-[11px] text-gray-500">
                    Chagua mwaka na mtihani ili kuona muhtasari wa ufaulu wa shule.
                </p>
            </div>

            <!-- Section 2: Class Performance (bar chart style) -->
            <div class="mb-4 border-b border-dashed border-gray-300 pb-3 text-[11px]">
                <h3 class="mb-2 text-[12px] font-semibold uppercase tracking-wide text-gray-800">
                    2. Ufaulu kwa Kila Darasa (Kwa Mtihani Ulioteuliwa)
                </h3>
                <p class="mb-2 text-[10px] text-gray-600">
                    Jedwali hili linaonyesha idadi ya watahiniwa, wastani wa alama, mgawanyo wa division na asilimia ya ufaulu kwa kila darasa.
                </p>
                <div v-if="classPerformance && classPerformance.length" class="space-y-2">
                    <table class="w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600">
                                <th class="border border-gray-200 px-1 py-0.5 text-left">Darasa</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">Watahimiwa</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">Wastani</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">Div I</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">Div II</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">Div III</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">Div IV</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">Div 0</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">% Ufaulu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in classPerformance"
                                :key="row.class_level"
                                class="text-gray-700"
                            >
                                <td class="border border-gray-200 px-1 py-0.5">
                                    {{ row.class_level }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ row.candidates }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ row.average_mark ?? '-' }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ row.divisions.I || 0 }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ row.divisions.II || 0 }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ row.divisions.III || 0 }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ row.divisions.IV || 0 }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ row.divisions['0'] || 0 }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ row.pass_rate }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Simple horizontal bar chart using CSS -->
                    <div class="mt-3 grid gap-2 md:grid-cols-2">
                        <div
                            v-for="row in classPerformance"
                            :key="row.class_level + '-bar'"
                            class="space-y-1"
                        >
                            <div class="flex justify-between text-[10px] text-gray-600">
                                <span>{{ row.class_level }}</span>
                                <span>Wastani: {{ row.average_mark ?? '-' }}</span>
                            </div>
                            <div class="h-3 w-full overflow-hidden rounded-full bg-gray-100">
                                <div
                                    class="h-full rounded-full bg-emerald-500"
                                    :style="{ width: (row.average_mark ? Math.min(row.average_mark, 100) : 0) + '%' }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-[11px] text-gray-500">
                    Chagua mwaka na mtihani ili kuona ufaulu wa kila darasa.
                </p>
            </div>

            <!-- Section 3: Exam overview for the year -->
            <div class="mb-4 border-b border-dashed border-gray-300 pb-3 text-[11px]">
                <h3 class="mb-2 text-[12px] font-semibold uppercase tracking-wide text-gray-800">
                    3. Muhtasari wa Mitihani Yote ya Mwaka
                </h3>
                <p class="mb-2 text-[10px] text-gray-600">
                    Jedwali hili linaonyesha idadi ya watahiniwa na wastani wa ufaulu kwa kila mtihani uliofanyika katika mwaka husika.
                </p>
                <div v-if="examSummaries && examSummaries.length" class="space-y-3">
                    <table class="w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600">
                                <th class="border border-gray-200 px-1 py-0.5 text-left">Mtihani</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-left">Aina</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">Watahimiwa</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">Wastani wa Alama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="ex in examSummaries"
                                :key="ex.id"
                                class="text-gray-700"
                            >
                                <td class="border border-gray-200 px-1 py-0.5">
                                    {{ ex.name }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5">
                                    {{ ex.type || '-' }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ ex.candidates }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ ex.average_mark ?? '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Bar chart by exam average -->
                    <div class="mt-3 space-y-2">
                        <div
                            v-for="ex in examSummaries"
                            :key="ex.id + '-bar'"
                            class="space-y-1"
                        >
                            <div class="flex justify-between text-[10px] text-gray-600">
                                <span>{{ ex.name }}</span>
                                <span>Wastani: {{ ex.average_mark ?? '-' }}</span>
                            </div>
                            <div class="h-3 w-full overflow-hidden rounded-full bg-gray-100">
                                <div
                                    class="h-full rounded-full bg-blue-500"
                                    :style="{ width: (ex.average_mark ? Math.min(ex.average_mark, 100) : 0) + '%' }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-[11px] text-gray-500">
                    Chagua mwaka ili kuona muhtasari wa mitihani yote ya shule.
                </p>
            </div>
            <!-- Section 4: Subject performance across the school -->
            <div class="mb-4 border-b border-dashed border-gray-300 pb-3 text-[11px]">
                <h3 class="mb-2 text-[12px] font-semibold uppercase tracking-wide text-gray-800">
                    4. Ufaulu kwa Masomo ya Shule Nzima (Mwaka Ulioteuliwa)
                </h3>
                <p class="mb-2 text-[10px] text-gray-600">
                    Jedwali hili linaonyesha masomo bora na yenye changamoto kwa kuangalia wastani wa ufaulu wa wanafunzi wote katika mitihani yote ya mwaka uliochaguliwa.
                </p>
                <div v-if="subjectPerformance && subjectPerformance.length" class="space-y-3">
                    <table class="w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600">
                                <th class="border border-gray-200 px-1 py-0.5 text-left">Somu</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">Waliofanya</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">Wastani wa Alama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="sub in subjectPerformance"
                                :key="sub.code"
                                class="text-gray-700"
                            >
                                <td class="border border-gray-200 px-1 py-0.5">
                                    {{ sub.code }} - {{ sub.name }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ sub.sat }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ sub.average_mark ?? '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Bar chart avg per subject -->
                    <div class="mt-3 space-y-2">
                        <div
                            v-for="sub in subjectPerformance"
                            :key="sub.code + '-bar'"
                            class="space-y-1"
                        >
                            <div class="flex justify-between text-[10px] text-gray-600">
                                <span>{{ sub.code }}</span>
                                <span>Wastani: {{ sub.average_mark ?? '-' }}</span>
                            </div>
                            <div class="h-3 w-full overflow-hidden rounded-full bg-gray-100">
                                <div
                                    class="h-full rounded-full bg-amber-500"
                                    :style="{ width: (sub.average_mark ? Math.min(sub.average_mark, 100) : 0) + '%' }"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Masomo 5 Bora / 5 Duni -->
                <div class="mt-4 grid gap-4 md:grid-cols-2" v-if="(topSubjectsBest && topSubjectsBest.length) || (topSubjectsWorst && topSubjectsWorst.length)">
                    <div>
                        <h4 class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Masomo 5 Bora</h4>
                        <ul class="space-y-0.5 text-[11px] text-gray-700">
                            <li
                                v-for="sub in topSubjectsBest"
                                :key="'best-' + sub.code"
                                class="flex justify-between"
                            >
                                <span>{{ sub.code }} - {{ sub.name }}</span>
                                <span class="font-semibold">{{ sub.average_mark ?? '-' }}</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-red-700">Masomo 5 Duni</h4>
                        <ul class="space-y-0.5 text-[11px] text-gray-700">
                            <li
                                v-for="sub in topSubjectsWorst"
                                :key="'worst-' + sub.code"
                                class="flex justify-between"
                            >
                                <span>{{ sub.code }} - {{ sub.name }}</span>
                                <span class="font-semibold">{{ sub.average_mark ?? '-' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <p v-else class="text-[11px] text-gray-500">
                    Chagua mwaka ili kuona ufaulu wa masomo yote ya shule.
                </p>
            </div>

            <!-- Section 5: Top 10 students of the year -->
            <div class="mb-4 border-b border-dashed border-gray-300 pb-3 text-[11px]">
                <h3 class="mb-2 text-[12px] font-semibold uppercase tracking-wide text-gray-800">
                    5. Wanafunzi 10 Bora wa Shule kwa Mwaka Ulioteuliwa
                </h3>
                <p class="mb-2 text-[10px] text-gray-600">
                    Jedwali hili linaonyesha wanafunzi 10 bora waliopata wastani mkubwa zaidi katika mitihani yote ya mwaka uliochaguliwa.
                </p>
                <div v-if="yearTopStudents && yearTopStudents.length" class="space-y-2">
                    <table class="w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600">
                                <th class="border border-gray-200 px-1 py-0.5 text-center">NAFASI</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-left">NO. MTIHANI</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-left">JINA LA MWANAFUNZI</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-left">DARASA</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">WASTANI</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">DIVISION</th>
                                <th class="border border-gray-200 px-1 py-0.5 text-center">POINTI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(stu, index) in yearTopStudents"
                                :key="stu.exam_number + '-' + index"
                                class="text-gray-700"
                            >
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ index + 1 }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5">
                                    {{ stu.exam_number || '-' }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5">
                                    {{ stu.full_name }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5">
                                    {{ stu.class_level }}<span v-if="stu.stream"> - {{ stu.stream }}</span>
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ stu.average ?? '-' }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ stu.division || '-' }}
                                </td>
                                <td class="border border-gray-200 px-1 py-0.5 text-center">
                                    {{ stu.points ?? '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-[11px] text-gray-500">
                    Chagua mwaka ili kuona wanafunzi 10 bora wa shule.
                </p>
            </div>

            <!-- Section 6: Multi-year trend -->
            <div class="mb-4 border-b border-dashed border-gray-300 pb-3 text-[11px]">
                <h3 class="mb-2 text-[12px] font-semibold uppercase tracking-wide text-gray-800">
                    6. Mwenendo wa Ufaulu kwa Miaka Mingi
                </h3>
                <p class="mb-2 text-[10px] text-gray-600">
                    Mchoro huu unaonyesha mabadiliko ya wastani wa ufaulu wa shule kwa miaka mbalimbali ili kusaidia uongozi kutathmini maendeleo ya muda mrefu.
                </p>
                <div v-if="yearTrends && yearTrends.length" class="space-y-2">
                    <div
                        v-for="yt in yearTrends"
                        :key="yt.year"
                        class="space-y-1"
                    >
                        <div class="flex justify-between text-[10px] text-gray-600">
                            <span>Mwaka {{ yt.year }}</span>
                            <span>Wastani: {{ yt.average_mark ?? '-' }} (Watahimiwa: {{ yt.candidates }})</span>
                        </div>
                        <div class="h-3 w-full overflow-hidden rounded-full bg-gray-100">
                            <div
                                class="h-full rounded-full bg-indigo-500"
                                :style="{ width: (yt.average_mark ? Math.min(yt.average_mark, 100) : 0) + '%' }"
                            />
                        </div>
                    </div>
                </div>
                <p v-else class="text-[11px] text-gray-500">
                    Hakuna takwimu za miaka mingi zilizopatikana bado.
                </p>
            </div>

            <!-- Section 7: Maoni ya Kitaalamu -->
            <div class="text-[11px]">
                <h3 class="mb-2 text-[12px] font-semibold uppercase tracking-wide text-gray-800">
                    7. Maoni ya Kitaalamu Kuhusu Ufaulu wa Shule
                </h3>
                <p class="mb-1 text-[11px] text-gray-700">
                    Sehemu hii inaweza kujazwa na uongozi wa shule au afisa elimu kwa kutoa maoni juu ya mwenendo wa ufaulu wa shule, mafanikio makubwa, changamoto zilizojitokeza na mikakati ya kuboresha matokeo kwa mwaka unaofuata.
                </p>
                <div class="min-h-[6rem] border border-gray-300 px-2 py-1 text-[10px]">
                    ____________________________________________________________________________________________________
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

    #school-report-document,
    #school-report-document * {
        visibility: visible;
    }

    #school-report-document {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        border: none;
        box-shadow: none;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
