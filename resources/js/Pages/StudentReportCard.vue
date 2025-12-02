<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    student: Object,
    school: Object,
    exam: Object,
    subjects: Array,
    summary: Object,
    year: [String, Number],
    behaviours: Array,
});

const printCard = () => {
    window.print();
};
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
                        SIMU: {{ school?.phone ?? '........' }}, EMAIL: {{ school?.email ?? '........' }}
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
                                <th class="border border-gray-400 px-1 py-0.5 text-center">JARIBIO</th>
                                <th class="border border-gray-400 px-1 py-0.5 text-center">MTIHANI</th>
                                <th class="border border-gray-400 px-1 py-0.5 text-center">JUMLA</th>
                                <th class="border border-gray-400 px-1 py-0.5 text-center">WASTANI</th>
                                <th class="border border-gray-400 px-1 py-0.5 text-center">GREDI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!subjects || !subjects.length">
                                <td colspan="6" class="border border-gray-400 px-2 py-3 text-center text-[11px] text-gray-400">
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
                                    {{ sub.test_mark ?? '-' }}
                                </td>
                                <td class="border border-gray-400 px-1 py-0.5 text-center">
                                    {{ sub.exam_mark ?? '-' }}
                                </td>
                                <td class="border border-gray-400 px-1 py-0.5 text-center">
                                    {{ sub.total ?? '-' }}
                                </td>
                                <td class="border border-gray-400 px-1 py-0.5 text-center">
                                    {{ sub.average ?? '-' }}
                                </td>
                                <td class="border border-gray-400 px-1 py-0.5 text-center">
                                    {{ sub.grade ?? '-' }}
                                </td>
                            </tr>
                            <tr class="font-semibold">
                                <td class="border border-gray-400 px-1 py-0.5 text-right">JUMLA</td>
                                <td class="border border-gray-400 px-1 py-0.5"></td>
                                <td class="border border-gray-400 px-1 py-0.5"></td>
                                <td class="border border-gray-400 px-1 py-0.5 text-center">
                                    {{ summary?.total ?? '-' }}
                                </td>
                                <td class="border border-gray-400 px-1 py-0.5 text-center">
                                    {{ summary?.average ?? '-' }}
                                </td>
                                <td class="border border-gray-400 px-1 py-0.5 text-center">
                                    {{ summary?.division ?? '-' }}
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

            <div class="mt-2 text-[11px]">
                <div>
                    Wastani wa shule ni 50% (mfano).
                </div>
                <div>
                    Amekuwa wa: {{ summary?.position || '____' }} kati ya wanafunzi: {{ summary?.out_of || '____' }}. Division: {{ summary?.division || '____' }} Pointi: {{ summary?.points || '____' }}.
                </div>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-3 text-[11px]">
                <div class="space-y-2">
                    <div>
                        Maoni ya Mwalimu wa Darasa:
                        <div class="min-h-[3rem] border border-gray-400 px-2 py-1 text-[10px]">
                            ____________________________________________
                        </div>
                    </div>
                    <div>
                        Jina/Sahihi ya Mwalimu wa Darasa: ____________________ Tarehe: ____________________
                    </div>
                </div>
                <div class="space-y-2">
                    <div>
                        Maoni ya Mkuu wa shule:
                        <div class="min-h-[3rem] border border-gray-400 px-2 py-1 text-[10px]">
                            ____________________________________________
                        </div>
                    </div>
                    <div>
                        Jina/Sahihi/Muhuri wa Mkuu wa Shule: ____________________ Tarehe: ____________________
                    </div>
                </div>
            </div>

            <div class="mt-3 border-t border-dashed border-gray-400 pt-2 text-[11px]">
                <div class="mb-1 font-semibold">C: KUFUNGA/KUFUNGUA SHULE</div>
                <div>
                    Shule imefungwa leo Tarehe __________________ na itafunguliwa rasmi Tarehe __________________
                </div>
                <div class="mt-2 text-[10px] text-gray-600">
                    Kata hapa rudisha maoni yako shule siku ya kufungua shule
                </div>
            </div>

            <div class="mt-3 text-[11px]">
                <div class="mb-1">
                    Maoni ya mzazi wa: ________________________________________________
                </div>
                <div class="min-h-[5rem] border border-gray-400 px-2 py-1 text-[10px]">
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
@media print {
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
    }
}
</style>
