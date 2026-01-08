<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    student: Object,
    school: Object,
    exam: Object,
    subjects: Array,
    summary: Object,
    year: [String, Number],
    behaviours: Array,
    class_teacher_name: [String, null],
    head_teacher_name: [String, null],
});

const hasTestExam = computed(() => {
    return (props.subjects || []).some((s) => s?.test_mark !== null && s?.test_mark !== undefined);
});

const printCard = () => {
    window.print();
};

const downloadPdf = () => {
    const url = new URL(window.location.href);
    url.searchParams.set('download', 'pdf');
    window.location.href = url.toString();
};

const printDate = new Date().toLocaleDateString('en-GB');
</script>

<template>
    <Head :title="`Report Card - ${student.full_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Student Report Card
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Printable progress report for {{ student.full_name }}.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center gap-1 rounded-md bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-sky-700"
                        @click="downloadPdf"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" x2="12" y1="15" y2="3" />
                        </svg>
                        Download
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                        @click="printCard"
                    >
                        Print
                    </button>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-5xl bg-white p-4 shadow-sm ring-1 ring-gray-100" id="student-report-card">
            <div class="mb-2 text-center">
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
                        Simu: {{ school?.phone ?? '........' }}, Email: {{ school?.email ?? '........' }}
                    </div>
                </div>
                <div class="mt-2 text-[11px] font-semibold uppercase tracking-wide text-gray-800">
                    TAARIFA YA MAENDELEO YA MWANAFUNZI (MWAKA WA {{ year }})
                </div>
            </div>

            <div class="mb-1 grid grid-cols-2 gap-2 text-[11px]">
                <div class="border border-gray-400 px-2 py-1">
                    <div>JINA: <span class="font-semibold uppercase">{{ student.full_name }}</span></div>
                </div>
                <div class="border border-gray-400 px-2 py-1">
                    <div>KIDATO: <span class="font-semibold uppercase">{{ student.class_level }}</span></div>
                </div>
            </div>

            <div class="mb-2 grid grid-cols-3 gap-2 text-[11px]">
                <div class="border border-gray-400 px-2 py-1">
                    <div>NUSU MUHULA / MUHULA:</div>
                    <div class="font-semibold uppercase">{{ exam?.name || '........' }}</div>
                </div>
                <div class="border border-gray-400 px-2 py-1">
                    <div>MWAKA:</div>
                    <div class="font-semibold">{{ exam?.academic_year || year }}</div>
                </div>
                <div class="border border-gray-400 px-2 py-1">
                    <div>TAREHE:</div>
                    <div class="font-semibold">{{ new Date().toLocaleDateString() }}</div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 text-[11px]">
                <!-- A. Matokeo ya mitihani -->
                <div class="col-span-2 border border-gray-400">
                    <div class="border-b border-gray-400 bg-gray-100 px-2 py-1 font-semibold">
                        A. MATOKEO YAKE YA MITIHANI
                    </div>
                    <table class="w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="border border-gray-400 px-1 py-0.5 text-left">MASOMO</th>
                                <th class="border border-gray-400 px-1 py-0.5 text-center">ALAMA</th>
                                <th class="border border-gray-400 px-1 py-0.5 text-center">WASTANI</th>
                                <th class="border border-gray-400 px-1 py-0.5 text-center">GREDI</th>
                                <th class="border border-gray-400 px-1 py-0.5 text-left">COMMENT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!subjects || !subjects.length">
                                <td colspan="5" class="border border-gray-400 px-2 py-3 text-center text-[11px] text-gray-400">
                                    Hakuna matokeo ya kuonyesha kwa mtihani huu.
                                </td>
                            </tr>
                            <tr
                                v-for="(sub, index) in subjects"
                                :key="index"
                            >
                                <td class="border border-gray-400 px-1 py-0.5">
                                    {{ sub.name }}
                                </td>
                                <td class="border border-gray-400 px-1 py-0.5 text-center">
                                    {{ sub.marks ?? '-' }}
                                </td>
                                <td class="border border-gray-400 px-1 py-0.5 text-center">
                                    {{ sub.average ?? '-' }}
                                </td>
                                <td class="border border-gray-400 px-1 py-0.5 text-center">
                                    {{ sub.grade ?? '-' }}
                                </td>
                                <td class="border border-gray-400 px-1 py-0.5">
                                    {{ sub.comment ?? '-' }}
                                </td>
                            </tr>
                            <tr class="font-semibold">
                                <td class="border border-gray-400 px-1 py-0.5 text-right">JUMLA</td>
                                <td class="border border-gray-400 px-1 py-0.5 text-center">
                                    {{ summary?.total ?? '-' }}
                                </td>
                                <td class="border border-gray-400 px-1 py-0.5 text-center">
                                    {{ summary?.average ?? '-' }}
                                </td>
                                <td class="border border-gray-400 px-1 py-0.5 text-center">
                                    {{ summary?.division ?? '-' }}
                                </td>
                                <td class="border border-gray-400 px-1 py-0.5">
                                    Points: {{ summary?.points ?? '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="px-2 py-1 text-[9px] text-gray-600">
                        NB: INC - Hakukamilisha; ABS - Hakufanya mtihani.
                    </div>
                </div>

                <!-- B. Tabia na maendeleo -->
                <div class="border border-gray-400">
                    <div class="border-b border-gray-400 bg-gray-100 px-2 py-1 font-semibold">
                        B. UPIMAJI WA TABIA NA MAENDELEO
                    </div>
                    <table class="w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="border border-gray-400 px-1 py-0.5 text-left">TABIA</th>
                                <th class="border border-gray-400 px-1 py-0.5 text-center">GREDI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="beh in behaviours || []"
                                :key="beh.code"
                            >
                                <td class="border border-gray-400 px-1 py-0.5">
                                    {{ beh.code }}: {{ beh.label }}
                                </td>
                                <td class="border border-gray-400 px-1 py-0.5 text-center">
                                    {{ beh.grade || '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-2 text-[10px] text-gray-700">
                Maelezo ya viwango vya ufaulu: A=75-100, B=65-74, C=45-64, D=30-44, F=0-29.
            </div>

            <div class="mt-2 border border-gray-400 px-2 py-1 text-[11px] text-gray-800">
                Amekuwa wa <span class="font-semibold">{{ summary?.position ?? '—' }}</span>
                kati ya wanafunzi <span class="font-semibold">{{ summary?.out_of ?? '—' }}</span>
                wa darasa lake.
                Pointi: <span class="font-semibold">{{ summary?.points ?? '—' }}</span>.
                Division: <span class="font-semibold">{{ summary?.division ?? '—' }}</span>.
            </div>

            <div class="mt-3 grid grid-cols-2 gap-3 text-[11px]">
                <div class="space-y-2">
                    <div>
                        Maoni ya Mwalimu wa Darasa:
                        <div class="handwriting min-h-[3rem] border border-gray-400 px-2 py-1 text-[12px]">
                            {{ summary?.teacher_comment ?? '____________________________________________' }}
                        </div>
                    </div>
                    <div>
                        Jina/Sahihi ya Mwalimu wa Darasa:
                        <span class="font-semibold">{{ class_teacher_name ?? '____________________' }}</span>
                        <span class="ml-2 handwriting font-semibold">{{ class_teacher_name ?? '' }}</span>
                        Tarehe: <span class="font-semibold">{{ printDate }}</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <div>
                        Maoni ya Mkuu wa shule:
                        <div class="handwriting min-h-[3rem] border border-gray-400 px-2 py-1 text-[12px]">
                            {{ summary?.headteacher_comment ?? '____________________________________________' }}
                        </div>
                    </div>
                    <div>
                        Jina/Sahihi/Muhuri wa Mkuu wa Shule:
                        <span class="font-semibold">{{ head_teacher_name ?? '____________________' }}</span>
                        <span class="ml-2 handwriting font-semibold">{{ head_teacher_name ?? '' }}</span>
                        Tarehe: <span class="font-semibold">{{ printDate }}</span>
                    </div>
                </div>
            </div>

            <div class="report-extra mt-3 border-t border-dashed border-gray-400 pt-2 text-[11px]">
                <div class="mb-1 font-semibold">C: KUFUNGA/KUFUNGUA SHULE</div>
                <div>
                    Shule imefungwa leo Tarehe __________________ na itafunguliwa rasmi Tarehe __________________
                </div>
                <div class="mt-2 text-[10px] text-gray-600">
                    Kata hapa rudisha maoni yako shule siku ya kufungua shule
                </div>
            </div>

            <div class="report-extra mt-3 text-[11px]">
                <div class="mb-1">
                    Maoni ya mzazi wa: ________________________________________________
                </div>
                <div class="handwriting min-h-[5rem] border border-gray-400 px-2 py-1 text-[12px]">
                    ______________________________________________________________________________________
                </div>
                <div class="mt-2 flex justify-between text-[11px]">
                    <span>JINA LA MZAZI/MLEZI ______________________</span>
                    <span>SAINI ______________________</span>
                    <span>TAREHE ______________________</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.handwriting {
    font-family: "Segoe Script", "Lucida Handwriting", "Brush Script MT", cursive;
    line-height: 1.6;
    color: #111827;
    background-image: repeating-linear-gradient(
        to bottom,
        transparent,
        transparent 20px,
        rgba(156, 163, 175, 0.6) 21px
    );
    background-size: 100% 21px;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 8mm;
    }

    body * {
        visibility: hidden;
    }

    #student-report-card,
    #student-report-card * {
        visibility: visible;
    }

    #student-report-card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        border: none;
        box-shadow: none;
        padding: 0;
    }

    #student-report-card {
        font-size: 10px;
    }

    #student-report-card .report-extra {
        display: none;
    }

    table,
    tr,
    td,
    th,
    .border {
        page-break-inside: avoid;
    }
}
</style>
