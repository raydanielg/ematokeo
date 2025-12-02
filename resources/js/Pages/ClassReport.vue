<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    class: Object,
    school: Object,
    exam: Object,
    students: Array,
    summary: Object,
    year: [String, Number],
});

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head :title="exam ? `Class Report - ${props.class.level}${props.class.stream ? ' ' + props.class.stream : ''}` : 'Class Report'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Class Report
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Performance report for
                        <span class="font-semibold">{{ props.class.level }}<span v-if="props.class.stream"> - {{ props.class.stream }}</span></span>
                        {{ exam ? `(${exam.name})` : '' }}.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                        @click="printReport"
                    >
                        Print
                    </button>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-5xl bg-white p-4 shadow-sm ring-1 ring-gray-100" id="class-report-document">
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
                    TAARIFA YA UFAULU WA DARASA
                </div>
                <div class="text-[10px] text-gray-600">
                    KIDATO: <span class="font-semibold">{{ props.class.level }}</span>
                    <span v-if="props.class.stream"> STREAM: <span class="font-semibold">{{ props.class.stream }}</span></span>
                    &nbsp;·&nbsp;
                    MWAKA: <span class="font-semibold">{{ exam?.academic_year || year }}</span>
                </div>
                <div class="text-[10px] text-gray-600">
                    MTIHANI: <span class="font-semibold">{{ exam?.name || 'Hakujachaguliwa mtihani' }}</span>
                </div>
            </div>

            <div class="mt-2 text-[11px]">
                <div>
                    Idadi ya watahiniwa: <span class="font-semibold">{{ summary?.candidates || (students?.length || 0) }}</span>
                </div>
                <div>
                    Wastani wa ufaulu wa darasa: <span class="font-semibold">{{ summary?.average_mark ?? '-' }}</span>
                </div>
            </div>

            <div class="mt-3">
                <table class="w-full border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-emerald-50 text-emerald-800">
                            <th class="border border-emerald-100 px-1 py-0.5 text-center">NAFASI</th>
                            <th class="border border-emerald-100 px-1 py-0.5 text-left">NO. MTIHANI</th>
                            <th class="border border-emerald-100 px-1 py-0.5 text-left">JINA LA MWANAFUNZI</th>
                            <th class="border border-emerald-100 px-1 py-0.5 text-center">JINSIA</th>
                            <th class="border border-emerald-100 px-1 py-0.5 text-center">JUMLA</th>
                            <th class="border border-emerald-100 px-1 py-0.5 text-center">WASTANI</th>
                            <th class="border border-emerald-100 px-1 py-0.5 text-center">DIVISION</th>
                            <th class="border border-emerald-100 px-1 py-0.5 text-center">POINTI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!students || !students.length">
                            <td colspan="8" class="border border-gray-200 px-2 py-3 text-center text-[11px] text-gray-400">
                                Hakuna matokeo ya kuonyesha kwa darasa hili na mtihani huu.
                            </td>
                        </tr>
                        <tr
                            v-for="stu in students"
                            :key="stu.exam_number + '-' + stu.full_name"
                            class="text-gray-700"
                        >
                            <td class="border border-emerald-100 px-1 py-0.5 text-center">
                                {{ stu.position || '-' }}
                            </td>
                            <td class="border border-emerald-100 px-1 py-0.5">
                                {{ stu.exam_number || '-' }}
                            </td>
                            <td class="border border-emerald-100 px-1 py-0.5">
                                {{ stu.full_name }}
                            </td>
                            <td class="border border-emerald-100 px-1 py-0.5 text-center uppercase">
                                {{ stu.gender || '-' }}
                            </td>
                            <td class="border border-emerald-100 px-1 py-0.5 text-center">
                                {{ stu.total ?? '-' }}
                            </td>
                            <td class="border border-emerald-100 px-1 py-0.5 text-center">
                                {{ stu.average ?? '-' }}
                            </td>
                            <td class="border border-emerald-100 px-1 py-0.5 text-center">
                                {{ stu.division || '-' }}
                            </td>
                            <td class="border border-emerald-100 px-1 py-0.5 text-center">
                                {{ stu.points ?? '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }

    #class-report-document,
    #class-report-document * {
        visibility: visible;
    }

    #class-report-document {
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
