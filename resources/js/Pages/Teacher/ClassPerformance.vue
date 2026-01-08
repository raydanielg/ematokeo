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
    performance: {
        type: Array,
        default: () => [],
    },
    topStudents: {
        type: Array,
        default: () => [],
    },
    bottomStudents: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ year: null, exam: null, class: null, stream: null }),
    },
    assignedClasses: {
        type: Array,
        default: () => [],
    },
    streams: {
        type: Array,
        default: () => [],
    },
    preview: {
        type: Object,
        default: () => null,
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
    router.get(route('teacher.results.class'), { year, exam: null }, { preserveState: true, preserveScroll: true, replace: true });
};

const onExamChange = (event) => {
    const exam = event.target.value || null;
    router.get(
        route('teacher.results.class'),
        { year: props.filters.year, exam, class: null, stream: null },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const selectedClassId = computed(() => String(props.filters?.class || ''));
const selectedStreamId = computed(() => String(props.filters?.stream || ''));

const onClassChange = (event) => {
    const cls = event.target.value || null;
    router.get(
        route('teacher.results.class'),
        { year: props.filters.year, exam: props.filters.exam, class: cls, stream: null },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const onStreamChange = (event) => {
    const stream = event.target.value || null;
    router.get(
        route('teacher.results.class'),
        { year: props.filters.year, exam: props.filters.exam, class: props.filters.class, stream },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const openPreviewForClassName = (className) => {
    const match = (props.assignedClasses || []).find((c) => c.name === className);
    if (!match) {
        return;
    }

    router.get(
        route('teacher.results.class'),
        { year: props.filters.year, exam: props.filters.exam, class: match.id, stream: null },
        {
            preserveScroll: true,
            replace: true,
            onSuccess: () => {
                showPreview.value = true;
            },
        },
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

const openStudentModal = async (stu) => {
    if (!stu?.student_id || !props.filters?.exam) {
        return;
    }

    showStudentModal.value = true;
    studentLoading.value = true;
    studentError.value = null;
    studentDetails.value = null;
    showAllStudentSubjects.value = false;

    try {
        const url = route('teacher.results.student-details', { exam: props.filters.exam, student: stu.student_id });
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
    const prevTitle = document.title;
    const clsName = props.preview?.class_name || 'Class';
    document.title = `${clsName} Performance`;
    window.print();
    setTimeout(() => {
        document.title = prevTitle;
    }, 1000);
};
</script>

<template>
    <Head title="Class Performance" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">Class Performance</h2>
                    <p class="mt-1 text-sm text-gray-500">Performance for your assigned classes and streams.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Year</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            @change="onYearChange"
                        >
                            <option value="">All Years</option>
                            <option v-for="year in years" :key="year" :value="year" :selected="filters.year === year">
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

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Class</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            :disabled="!filters.exam"
                            :value="selectedClassId"
                            @change="onClassChange"
                        >
                            <option value="">Select Class</option>
                            <option v-for="cls in assignedClasses" :key="cls.id" :value="cls.id">
                                {{ cls.name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Stream</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            :disabled="!filters.exam || !filters.class"
                            :value="selectedStreamId"
                            @change="onStreamChange"
                        >
                            <option value="">All Streams</option>
                            <option v-for="s in streams" :key="s.id" :value="s.id">
                                {{ s.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 text-xs text-gray-600">
                <p class="mb-1 font-medium">Class Summary</p>
                <p class="text-[11px] text-gray-500">Each row shows a class summary (candidates, average mark, divisions and pass rate).</p>
            </div>

            <div class="mb-3 flex items-center justify-end gap-2">
                <button
                    type="button"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-emerald-300"
                    :disabled="!filters.exam"
                    @click="openPreview"
                >
                    Preview Document
                </button>
            </div>

            <div v-if="!filters.exam" class="py-10 text-center text-xs text-gray-400">
                Please select an exam to view class performance.
            </div>

            <div v-else-if="performance.length === 0" class="py-10 text-center text-xs text-gray-400">
                No class performance data found for this exam yet.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600">
                            <th class="border-b border-gray-200 px-3 py-1.5 text-left">Class</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Candidates</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Avg</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Div I</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Div II</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Div III</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Div IV</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Div 0</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-center">Pass %</th>
                            <th class="border-b border-gray-200 px-3 py-1.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in performance" :key="row.class_level" class="hover:bg-gray-50">
                            <td class="border-b border-gray-100 px-3 py-2 font-medium text-gray-800">{{ row.class_level }}</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.candidates }}</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.average_mark ?? '—' }}</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.divisions?.I ?? 0 }}</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.divisions?.II ?? 0 }}</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.divisions?.III ?? 0 }}</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.divisions?.IV ?? 0 }}</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.divisions?.['0'] ?? 0 }}</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ row.pass_rate ?? 0 }}%</td>
                            <td class="border-b border-gray-100 px-3 py-2 text-right">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2.5 py-1 text-[11px] font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                                    @click="openPreviewForClassName(row.class_level)"
                                >
                                    Preview
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-sm font-semibold text-gray-800">Top Students</p>
                <p class="mt-0.5 text-xs text-gray-500">Only complete entries are ranked.</p>

                <div v-if="topStudents.length === 0" class="py-8 text-center text-xs text-gray-400">No data.</div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600">
                                <th class="border-b border-gray-200 px-3 py-1.5 text-left">Student</th>
                                <th class="border-b border-gray-200 px-3 py-1.5 text-center">Class</th>
                                <th class="border-b border-gray-200 px-3 py-1.5 text-center">Avg</th>
                                <th class="border-b border-gray-200 px-3 py-1.5 text-center">Division</th>
                                <th class="border-b border-gray-200 px-3 py-1.5 text-right">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="s in topStudents" :key="s.student_id" class="hover:bg-gray-50">
                                <td class="border-b border-gray-100 px-3 py-2">
                                    <div class="font-medium text-gray-800">{{ s.full_name }}</div>
                                    <div class="text-[10px] text-gray-500">{{ s.exam_number }}</div>
                                </td>
                                <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ s.class_level }}</td>
                                <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ s.average ?? '—' }}</td>
                                <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ s.division ?? '—' }}</td>
                                <td class="border-b border-gray-100 px-3 py-2 text-right">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-emerald-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                                        @click="openStudentModal(s)"
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-sm font-semibold text-gray-800">Bottom Students</p>
                <p class="mt-0.5 text-xs text-gray-500">Only complete entries are ranked.</p>

                <div v-if="bottomStudents.length === 0" class="py-8 text-center text-xs text-gray-400">No data.</div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600">
                                <th class="border-b border-gray-200 px-3 py-1.5 text-left">Student</th>
                                <th class="border-b border-gray-200 px-3 py-1.5 text-center">Class</th>
                                <th class="border-b border-gray-200 px-3 py-1.5 text-center">Avg</th>
                                <th class="border-b border-gray-200 px-3 py-1.5 text-center">Division</th>
                                <th class="border-b border-gray-200 px-3 py-1.5 text-right">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="s in bottomStudents" :key="s.student_id" class="hover:bg-gray-50">
                                <td class="border-b border-gray-100 px-3 py-2">
                                    <div class="font-medium text-gray-800">{{ s.full_name }}</div>
                                    <div class="text-[10px] text-gray-500">{{ s.exam_number }}</div>
                                </td>
                                <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ s.class_level }}</td>
                                <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ s.average ?? '—' }}</td>
                                <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ s.division ?? '—' }}</td>
                                <td class="border-b border-gray-100 px-3 py-2 text-right">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-emerald-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                                        @click="openStudentModal(s)"
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div
            v-if="showPreview"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/30 p-4 print:static print:bg-transparent"
        >
            <div class="mx-auto w-full max-w-6xl rounded-xl bg-white shadow-lg print:mx-0 print:max-w-none print:rounded-none print:shadow-none">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 print:hidden">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Preview</p>
                        <p class="text-xs text-gray-500">Printable class performance summary</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-[11px] font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                            @click="printPreview"
                        >
                            Print
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-gray-900"
                            @click="closePreview"
                        >
                            Close
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div v-if="!preview" class="py-10 text-center text-xs text-gray-400">No preview available.</div>

                    <div v-else>
                        <div class="mb-4">
                            <p class="text-lg font-semibold text-gray-900">{{ preview.class_name }}</p>
                            <p class="text-xs text-gray-500">
                                Candidates: <span class="font-medium text-gray-700">{{ preview.candidates }}</span>
                                <span class="mx-2 text-gray-300">|</span>
                                Average: <span class="font-medium text-gray-700">{{ preview.average_mark ?? '—' }}</span>
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-5">
                            <div class="rounded-lg bg-gray-50 p-3 text-center">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-gray-500">Div I</p>
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ preview.divisions?.I ?? 0 }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3 text-center">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-gray-500">Div II</p>
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ preview.divisions?.II ?? 0 }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3 text-center">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-gray-500">Div III</p>
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ preview.divisions?.III ?? 0 }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3 text-center">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-gray-500">Div IV</p>
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ preview.divisions?.IV ?? 0 }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3 text-center">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-gray-500">Div 0</p>
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ preview.divisions?.['0'] ?? 0 }}</p>
                            </div>
                        </div>

                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full border-collapse text-[11px]">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-600">
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-left">Student</th>
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-center">Gender</th>
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-center">Total</th>
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-center">Avg</th>
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-center">Division</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(s, idx) in preview.students" :key="idx" class="hover:bg-gray-50">
                                        <td class="border-b border-gray-100 px-3 py-2">
                                            <div class="font-medium text-gray-800">{{ s.full_name }}</div>
                                            <div class="text-[10px] text-gray-500">{{ s.exam_number }}</div>
                                        </td>
                                        <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ s.gender || '—' }}</td>
                                        <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ s.total ?? '—' }}</td>
                                        <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ s.avg ?? '—' }}</td>
                                        <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ s.div ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showStudentModal" class="fixed inset-0 z-50 bg-black/30 p-4">
            <div class="mx-auto w-full max-w-3xl rounded-xl bg-white shadow-lg">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Student Results</p>
                        <p class="text-xs text-gray-500">Exam details for selected student.</p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-gray-900"
                        @click="closeStudentModal"
                    >
                        Close
                    </button>
                </div>

                <div class="p-4">
                    <div v-if="studentLoading" class="py-10 text-center text-xs text-gray-400">Loading...</div>
                    <div v-else-if="studentError" class="rounded-lg bg-red-50 p-3 text-xs text-red-700">{{ studentError }}</div>
                    <div v-else-if="!studentDetails" class="py-10 text-center text-xs text-gray-400">No data.</div>
                    <div v-else>
                        <div class="mb-4 rounded-lg bg-gray-50 p-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">{{ studentDetails.student?.full_name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ studentDetails.student?.exam_number }} • {{ studentDetails.student?.class_level }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Entered</p>
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ enteredSubjectsCount }}/{{ totalSubjectsCount }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-2 grid gap-2 sm:grid-cols-4">
                                <div class="rounded-md bg-white p-2">
                                    <p class="text-[10px] font-medium uppercase tracking-wide text-gray-500">Total</p>
                                    <p class="mt-0.5 text-sm font-semibold text-gray-800">{{ studentDetails.summary?.total ?? '—' }}</p>
                                </div>
                                <div class="rounded-md bg-white p-2">
                                    <p class="text-[10px] font-medium uppercase tracking-wide text-gray-500">Average</p>
                                    <p class="mt-0.5 text-sm font-semibold text-gray-800">{{ studentDetails.summary?.average ?? '—' }}</p>
                                </div>
                                <div class="rounded-md bg-white p-2">
                                    <p class="text-[10px] font-medium uppercase tracking-wide text-gray-500">Points</p>
                                    <p class="mt-0.5 text-sm font-semibold text-gray-800">{{ studentDetails.summary?.points ?? '—' }}</p>
                                </div>
                                <div class="rounded-md bg-white p-2">
                                    <p class="text-[10px] font-medium uppercase tracking-wide text-gray-500">Division</p>
                                    <p class="mt-0.5 text-sm font-semibold text-gray-800">{{ studentDetails.summary?.division ?? '—' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Subjects</p>
                            <button
                                type="button"
                                class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-800"
                                @click="showAllStudentSubjects = !showAllStudentSubjects"
                            >
                                {{ showAllStudentSubjects ? 'Show entered only' : 'Show all' }}
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-[11px]">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-600">
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-left">Subject</th>
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-center">Code</th>
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-center">Marks</th>
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-center">Grade</th>
                                        <th class="border-b border-gray-200 px-3 py-1.5 text-center">Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(sub, idx) in visibleStudentSubjects" :key="idx" class="hover:bg-gray-50">
                                        <td class="border-b border-gray-100 px-3 py-2 font-medium text-gray-800">{{ sub.name }}</td>
                                        <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ sub.code }}</td>
                                        <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ sub.marks ?? '—' }}</td>
                                        <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ sub.grade ?? '—' }}</td>
                                        <td class="border-b border-gray-100 px-3 py-2 text-center text-gray-600">{{ sub.points ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
