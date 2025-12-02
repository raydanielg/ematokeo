<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    years: Array,
    exams: Array,
    students: Array,
    classes: Array,
    filters: Object,
});

const onFilterChange = (key, value) => {
    const params = {
        ...props.filters,
        [key]: value || null,
    };

    router.get(route('reports.students.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openReport = (studentId) => {
    const params = new URLSearchParams();
    if (props.filters.exam) {
        params.set('exam', props.filters.exam);
    }
    if (props.filters.test_exam) {
        params.set('test_exam', props.filters.test_exam);
    }

    const query = params.toString();
    const url = route('reports.students.show', studentId) + (query ? `?${query}` : '');
    router.visit(url);
};
</script>

<template>
    <Head title="Student Report Card" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Student Report Cards
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Choose exam and class, then open individual student report cards for printing.
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
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Test Exam (Jaribio)</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            :value="filters.test_exam || ''"
                            @change="(e) => onFilterChange('test_exam', e.target.value)"
                        >
                            <option value="">None</option>
                            <option
                                v-for="exam in exams"
                                :key="exam.id"
                                :value="exam.id"
                            >
                                {{ exam.name }} ({{ exam.academic_year }})
                            </option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Class</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            :value="filters.class || ''"
                            @change="(e) => onFilterChange('class', e.target.value)"
                        >
                            <option value="">All Classes</option>
                            <option
                                v-for="cls in classes"
                                :key="cls"
                                :value="cls"
                            >
                                {{ cls }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 text-xs text-gray-600">
                <p class="font-medium">Students</p>
                <p class="text-[11px] text-gray-500">
                    Select a student and open their report card. Make sure you have selected the correct exam above.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600">
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Exam No.</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Student Name</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Class</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Gender</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="students.length === 0">
                            <td colspan="5" class="px-3 py-4 text-center text-[11px] text-gray-400">
                                No students found for the selected filters.
                            </td>
                        </tr>
                        <tr
                            v-for="student in students"
                            :key="student.id"
                            class="border-b border-gray-100 text-gray-700 odd:bg-white even:bg-gray-50"
                        >
                            <td class="px-3 py-1.5 align-top text-[11px] font-medium text-gray-900">
                                {{ student.exam_number || '-' }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-[11px]">
                                {{ student.full_name }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-[11px]">
                                {{ student.class_level }}<span v-if="student.stream"> - {{ student.stream }}</span>
                            </td>
                            <td class="px-3 py-1.5 align-top text-[11px] uppercase">
                                {{ student.gender || '-' }}
                            </td>
                            <td class="px-3 py-1.5 align-top text-right">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1 text-[10px] font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-gray-300"
                                    :disabled="!filters.exam"
                                    @click="openReport(student.id)"
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
