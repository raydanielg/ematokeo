<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    years: Array,
    exams: Array,
    classes: Array,
    filters: Object,
});

const onFilterChange = (key, value) => {
    const params = {
        ...props.filters,
        [key]: value || null,
    };

    router.get(route('reports.classes.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openClassReport = (classId) => {
    const params = new URLSearchParams();
    if (props.filters.exam) {
        params.set('exam', props.filters.exam);
    }

    const query = params.toString();
    const url = route('reports.classes.show', classId) + (query ? `?${query}` : '');
    router.visit(url);
};
</script>

<template>
    <Head title="Class Reports" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Class Reports
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        View performance reports per class for a selected exam.
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
                            <option value="">All Years</option>
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
                            <option value="">Select Exam</option>
                            <option
                                v-for="exam in exams"
                                :key="exam.id"
                                :value="exam.id"
                            >
                                {{ exam.name }} ({{ exam.academic_year }})
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 text-xs text-gray-600">
                <p class="font-medium">Classes</p>
                <p class="text-[11px] text-gray-500">
                    Select a class and open its performance report. Make sure you have selected the correct exam above.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600">
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Class</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Streams</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="classes.length === 0">
                            <td colspan="3" class="px-3 py-4 text-center text-[11px] text-gray-400">
                                No classes found.
                            </td>
                        </tr>
                        <tr
                            v-for="cls in classes"
                            :key="cls.level"
                            class="border-b border-gray-100 text-gray-700 odd:bg-white even:bg-gray-50"
                        >
                            <td class="px-3 py-1.5 align-top text-[11px] font-medium text-gray-900">
                                {{ cls.level }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-[11px]">
                                <span class="font-medium">{{ (cls.streams || []).length }}</span>
                                <span class="text-gray-500">stream(s)</span>
                            </td>
                            <td class="px-3 py-1.5 align-top text-right">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1 text-[10px] font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-gray-300"
                                    :disabled="!filters.exam || !(cls.streams && cls.streams.length)"
                                    @click="openClassReport(cls.streams[0].id)"
                                >
                                    Open Report
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
