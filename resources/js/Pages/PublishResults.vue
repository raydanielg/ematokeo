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
        // array of { id, name, academic_year, type, levels, is_published }
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ year: null }),
    },
});

const showPreview = ref(false);

const onYearChange = (event) => {
    const year = event.target.value || null;
    router.get(
        route('results.publish'),
        { year },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const togglePublish = (examId, publish) => {
    router.post(
        route('results.publish.toggle', examId),
        { publish },
        { preserveScroll: true },
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
    <Head title="Publish Results" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Publish Results
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Control which exams are published and ready to be shared with students, parents and the public.
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
                </div>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 flex items-center justify-between text-xs text-gray-600">
                <div>
                    <p class="mb-1 font-medium">Exams Publish Control</p>
                    <p class="text-[11px] text-gray-500">
                        Use this page to mark exam results as published. Once published, results and related reports can be shared publicly.
                    </p>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                    @click="openPreview"
                >
                    Preview Publish Notice
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600">
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Exam</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Year / Type</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Class Levels</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Status</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="exams.length === 0">
                            <td colspan="5" class="px-3 py-4 text-center text-[11px] text-gray-400">
                                No exams found for the selected filters.
                            </td>
                        </tr>
                        <tr
                            v-for="exam in exams"
                            :key="exam.id"
                            class="border-b border-gray-100 text-gray-700 odd:bg-white even:bg-gray-50"
                        >
                            <td class="px-3 py-1.5 align-top text-sm font-medium text-gray-900">
                                {{ exam.name }}
                            </td>
                            <td class="px-3 py-1.5 align-top">
                                <div class="flex flex-col">
                                    <span>{{ exam.academic_year || '—' }}</span>
                                    <span v-if="exam.type" class="text-[10px] text-gray-500">{{ exam.type }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-1.5 align-top">
                                <span v-if="exam.levels && exam.levels.length" class="inline-flex flex-wrap gap-1">
                                    <span
                                        v-for="level in exam.levels"
                                        :key="level"
                                        class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-emerald-100"
                                    >
                                        {{ level }}
                                    </span>
                                </span>
                                <span v-else class="text-gray-400">—</span>
                            </td>
                            <td class="px-3 py-1.5 align-top text-center">
                                <span
                                    v-if="exam.is_published"
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-emerald-100"
                                >
                                    Published
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-0.5 text-[10px] font-medium text-yellow-700 ring-1 ring-yellow-100"
                                >
                                    Draft
                                </span>
                            </td>
                            <td class="px-3 py-1.5 align-top text-right">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="rounded-md border px-2 py-0.5 text-[10px] font-medium shadow-sm"
                                        :class="exam.is_published ? 'border-gray-300 text-gray-600 hover:bg-gray-100' : 'border-emerald-600 text-emerald-700 hover:bg-emerald-50'"
                                        @click="togglePublish(exam.id, !exam.is_published)"
                                    >
                                        {{ exam.is_published ? 'Unpublish' : 'Publish Results' }}
                                    </button>
                                    <a
                                        :href="route('exams.results', exam.id)"
                                        class="rounded-md border border-gray-200 px-2 py-0.5 text-[10px] font-medium text-gray-700 hover:bg-gray-100"
                                    >
                                        View Results
                                    </a>
                                </div>
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
                                Taarifa Ya Kuchapishwa Kwa Matokeo
                            </h3>
                            <p class="mt-0.5 text-[11px] text-gray-500">
                                Waraka huu unaonyesha mitihani iliyochapishwa rasmi na ipo tayari kutangazwa kwa wanafunzi na wazazi.
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

                    <div class="border border-gray-200 bg-white p-4" id="publish-results-document">
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
                                Taarifa Ya Kuchapishwa Kwa Matokeo Ya Mitihani
                            </div>
                            <div class="text-[10px] text-gray-500">
                                Mwaka: <span class="font-medium">{{ filters.year || 'Mwaka Wote' }}</span>
                            </div>
                        </div>

                        <table class="w-full border-collapse text-[11px]">
                            <thead>
                                <tr class="bg-emerald-50 text-emerald-800">
                                    <th class="border border-emerald-100 px-2 py-1 text-left">Exam</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-left">Year / Type</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-left">Class Levels</th>
                                    <th class="border border-emerald-100 px-2 py-1 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!exams.length">
                                    <td colspan="4" class="border border-gray-200 px-2 py-3 text-center text-[11px] text-gray-400">
                                        Hakuna mitihani iliyopatikana kwa mwaka huu.
                                    </td>
                                </tr>
                                <tr
                                    v-for="exam in exams"
                                    :key="exam.id"
                                    class="text-gray-700"
                                >
                                    <td class="border border-emerald-100 px-2 py-1 bg-white">
                                        {{ exam.name }}
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 bg-white">
                                        {{ exam.academic_year || '—' }}<span v-if="exam.type"> • {{ exam.type }}</span>
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 bg-white">
                                        <span v-if="exam.levels && exam.levels.length">
                                            {{ exam.levels.join(', ') }}
                                        </span>
                                        <span v-else>—</span>
                                    </td>
                                    <td class="border border-emerald-100 px-2 py-1 text-center bg-white">
                                        <span v-if="exam.is_published">Published</span>
                                        <span v-else>Draft</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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

    #publish-results-document,
    #publish-results-document * {
        visibility: visible;
    }

    #publish-results-document {
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
