<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref, computed } from 'vue';

const props = defineProps({
    exams: {
        type: Array,
        default: () => [],
    },
    classes: {
        type: Array,
        default: () => [],
    },
    students: {
        type: Array,
        default: () => [],
    },
    subjects: {
        type: Array,
        default: () => [],
    },
    existingMarks: {
        type: Array,
        default: () => [],
    },
    gradingSchemes: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ exam: null, class: null }),
    },
});

const marksGrid = reactive({});
const dirtyCells = reactive({}); // track edited cells: key => true
const invalidCells = reactive({}); // track invalid entries: key => true

const isSaving = ref(false);
const showSaved = ref(false);
const savedRowIds = ref([]); // track rows that have been saved at least once

const showStandardizeModal = ref(false);
const standardizeValue = ref('');
const isStandardizing = ref(false);

props.existingMarks.forEach((row) => {
    const key = `${row.student_id}-${row.subject_id}`;
    if (row.marks !== null && row.marks !== undefined) {
        marksGrid[key] = String(row.marks);
    }
});

const onExamChange = (event) => {
    const examId = event.target.value || null;
    router.get(
        route('exams.marks'),
        { exam: examId },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const getSchemeForMark = (mark) => {
    if (mark === null || mark === undefined || mark === '') return null;

    const intMark = Number(mark);
    if (Number.isNaN(intMark)) return null;

    // Hardcoded NECTA-style grading
    if (intMark >= 75 && intMark <= 100) {
        return { grade: 'A', points: 1 };
    }
    if (intMark >= 65 && intMark <= 74) {
        return { grade: 'B', points: 2 };
    }
    if (intMark >= 45 && intMark <= 64) {
        return { grade: 'C', points: 3 };
    }
    if (intMark >= 30 && intMark <= 44) {
        return { grade: 'D', points: 4 };
    }

    if (intMark >= 0 && intMark <= 29) {
        return { grade: 'F', points: 5 };
    }

    return null;
};

const computeRowDivisionAndPoints = (studentId) => {
    const marksForStudent = [];

    props.subjects.forEach((subject) => {
        const key = `${studentId}-${subject.id}`;
        const raw = marksGrid[key];
        const num = raw === '' || raw === undefined ? null : Number(raw);
        if (num !== null && !Number.isNaN(num)) {
            const scheme = getSchemeForMark(num);
            if (scheme && scheme.points !== null && scheme.points !== undefined) {
                marksForStudent.push(Number(scheme.points));
            }
        }
    });

    if (!marksForStudent.length) {
        return { division: '—', points: '—' };
    }

    // Sum of best seven (lowest) points
    const sorted = [...marksForStudent].sort((a, b) => a - b);
    const bestSeven = sorted.slice(0, 7);
    const totalPoints = bestSeven.reduce((acc, v) => acc + v, 0);

    let division;
    if (totalPoints >= 34) {
        division = '0';
    } else if (totalPoints >= 26) {
        division = 'IV';
    } else if (totalPoints >= 22) {
        division = 'III';
    } else if (totalPoints >= 18) {
        division = 'II';
    } else {
        division = 'I';
    }

    return { division, points: totalPoints }; 
};

const hasAnyMarksForStudent = (studentId) => {
    return props.subjects.some((subject) => {
        const key = `${studentId}-${subject.id}`;
        const raw = marksGrid[key];
        const num = raw === '' || raw === undefined ? null : Number(raw);
        return num !== null && !Number.isNaN(num);
    });
};

const goToExam = (examId) => {
    if (!examId) return;
    router.get(
        route('exams.marks'),
        { exam: examId },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const selectClass = (classId) => {
    const clsId = classId || null;
    router.get(
        route('exams.marks'),
        { exam: props.filters.exam, class: clsId },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const getMark = (studentId, subjectId) => {
    const key = `${studentId}-${subjectId}`;
    return marksGrid[key] ?? '';
};

const setMark = (studentId, subjectId, value) => {
    const key = `${studentId}-${subjectId}`;
    const numeric = value === '' ? '' : Number(value);

    // Reset invalid flag by default
    invalidCells[key] = false;

    if (numeric === '' || (Number.isInteger(numeric) && numeric >= 0 && numeric <= 100)) {
        marksGrid[key] = value;
        dirtyCells[key] = true;
    } else {
        // Mark this cell as invalid client-side (will not be persisted)
        invalidCells[key] = true;
    }
};

const focusNextCell = (studentId, subjectId) => {
    if (!props.students.length || !props.subjects.length) return;

    const rowIndex = props.students.findIndex((s) => s.id === studentId);
    const colIndex = props.subjects.findIndex((sub) => sub.id === subjectId);

    if (rowIndex === -1 || colIndex === -1) return;

    const nextRow = props.students[rowIndex + 1];
    if (!nextRow) return;

    const selector = `input[data-student-id="${nextRow.id}"][data-subject-id="${subjectId}"]`;
    const el = document.querySelector(selector);
    if (el) {
        el.focus();
        el.select?.();
    }
};

const computeRowTotals = (studentId) => {
    let total = 0;
    let count = 0;
    props.subjects.forEach((subject) => {
        const key = `${studentId}-${subject.id}`;
        const raw = marksGrid[key];
        const num = raw === '' || raw === undefined ? null : Number(raw);
        if (num !== null && !Number.isNaN(num)) {
            total += num;
            count += 1;
        }
    });
    const average = count > 0 ? (total / count).toFixed(1) : '';
    return { total: count > 0 ? total : '', average };
};

const saveMarks = () => {
    if (!props.filters.exam || !props.filters.class) return;

    const rows = props.students.map((student) => {
        const marks = [];
        props.subjects.forEach((subject) => {
            const key = `${student.id}-${subject.id}`;
            const raw = marksGrid[key];
            const value = raw === '' || raw === undefined ? null : Number(raw);
            marks.push({ subject_id: subject.id, marks: value });
        });
        return {
            student_id: student.id,
            marks,
        };
    });

    isSaving.value = true;
    router.post(
        route('exams.marks.save'),
        {
            exam_id: props.filters.exam,
            class_id: props.filters.class,
            rows,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                isSaving.value = false;
                // Mark rows that currently have any marks as saved
                savedRowIds.value = props.students
                    .filter((s) => hasAnyMarksForStudent(s.id))
                    .map((s) => s.id);

                showSaved.value = true;
                setTimeout(() => {
                    showSaved.value = false;
                }, 1800);
            },
        },
    );
};
</script>

<template>
    <Head title="Enter Marks" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Enter Marks
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Choose an exam and class, then enter subject marks for each student in an Excel-style grid.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <label class="font-medium text-gray-600">Exam</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            @change="onExamChange"
                        >
                            <option value="">Select exam</option>
                            <option
                                v-for="exam in exams"
                                :key="exam.id"
                                :value="exam.id"
                                :selected="String(filters.exam) === String(exam.id)"
                            >
                                {{ exam.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <!-- Quick list of exams with forward arrow to enter marks -->
        <div class="mb-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                Exams
            </h3>
            <p class="mb-3 text-[11px] text-gray-500">
                Pick an exam from the list or use the filter above. Click the arrow button to move forward to entering marks.
            </p>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[11px]">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-1.5">Name</th>
                            <th class="px-3 py-1.5">Year / Type</th>
                            <th class="px-3 py-1.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!exams.length">
                            <td colspan="3" class="px-3 py-3 text-center text-[11px] text-gray-400">
                                No exams available yet.
                            </td>
                        </tr>
                        <tr
                            v-for="exam in exams"
                            :key="exam.id"
                            class="border-b border-gray-100 text-[11px] text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-1.5 align-top">
                                <span class="font-medium text-gray-900">{{ exam.name }}</span>
                            </td>
                            <td class="px-3 py-1.5 align-top">
                                <div class="flex flex-col">
                                    <span>{{ exam.academic_year || '—' }}</span>
                                    <span v-if="exam.type" class="text-[10px] text-gray-500">{{ exam.type }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-1.5 align-top">
                                <div class="flex justify-end">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-medium text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                                        @click="goToExam(exam.id)"
                                    >
                                        <span class="mr-1">Enter marks</span>
                                        <span aria-hidden="true">→</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div v-if="!filters.exam" class="py-10 text-center text-xs text-gray-500">
                Please select an exam first.
            </div>
            <div
                v-else-if="filters.exam && classes.length === 0"
                class="py-10 text-center text-xs text-gray-500"
            >
                No classes are assigned to this exam yet.
            </div>
            <div
                v-else-if="filters.exam && classes.length > 0 && !filters.class"
                class="space-y-4"
            >
                <div class="text-xs text-gray-600">
                    <p class="mb-2 font-medium">Classes assigned to this exam</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="cls in classes"
                            :key="cls.id"
                            type="button"
                            class="rounded-full border px-3 py-1 text-[11px] font-medium"
                            :class="
                                String(filters.class) === String(cls.id)
                                    ? 'border-emerald-600 bg-emerald-50 text-emerald-700'
                                    : 'border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100'
                            "
                            @click="selectClass(cls.id)"
                        >
                            {{ cls.level ? (cls.stream ? `${cls.level} - ${cls.stream}` : cls.level) : cls.name }}
                        </button>
                    </div>
                    <p class="mt-3 text-[11px] text-gray-500">
                        Choose a class above to load its students for this exam and start entering marks.
                    </p>
                </div>
            </div>
            <div
                v-else-if="filters.exam && filters.class && students.length === 0"
                class="space-y-4"
            >
                <div class="text-xs text-gray-600">
                    <p class="mb-2 font-medium">Classes assigned to this exam</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="cls in classes"
                            :key="cls.id"
                            type="button"
                            class="rounded-full border px-3 py-1 text-[11px] font-medium"
                            :class="
                                String(filters.class) === String(cls.id)
                                    ? 'border-emerald-600 bg-emerald-50 text-emerald-700'
                                    : 'border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100'
                            "
                            @click="selectClass(cls.id)"
                        >
                            {{ cls.name }}
                        </button>
                    </div>
                </div>
                <div class="py-6 text-center text-xs text-gray-500">
                    No students found for this class.
                </div>
            </div>
            <div v-else class="space-y-4">
                <!-- Summary bar -->
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-md bg-gray-50 px-3 py-2 text-[11px] text-gray-700">
                    <div class="flex flex-wrap items-center gap-4">
                        <div>
                            <span class="font-semibold text-gray-800">Exam:</span>
                            <span class="ml-1">
                                {{
                                    exams.find((e) => String(e.id) === String(filters.exam))
                                        ? exams.find((e) => String(e.id) === String(filters.exam)).name
                                        : '—'
                                }}
                                <span v-if="exams.find((e) => String(e.id) === String(filters.exam))?.academic_year" class="ml-1 text-gray-500">
                                    ({{ exams.find((e) => String(e.id) === String(filters.exam)).academic_year }})
                                </span>
                            </span>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-800">Class:</span>
                            <span class="ml-1">{{ filters.class }}</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span><span class="font-semibold">Students:</span> {{ students.length }}</span>
                        <span><span class="font-semibold">Subjects:</span> {{ subjects.length }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-600">
                    <div class="flex flex-wrap gap-2">
                        <span class="font-medium">Classes:</span>
                        <button
                            v-for="cls in classes"
                            :key="cls.id"
                            type="button"
                            class="rounded-full border px-3 py-1 text-[11px] font-medium"
                            :class="
                                String(filters.class) === String(cls.id)
                                    ? 'border-emerald-600 bg-emerald-50 text-emerald-700'
                                    : 'border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100'
                            "
                            @click="selectClass(cls.id)"
                        >
                            {{ cls.level ? (cls.stream ? `${cls.level} - ${cls.stream}` : cls.level) : cls.name }}
                        </button>
                    </div>
                    <button
                        type="button"
                        class="rounded-md bg-emerald-50 px-3 py-1 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="!students.length || !subjects.length"
                        @click="openStandardization"
                    >
                        Standardization
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600">
                                <th class="border-b border-gray-200 px-2 py-1 text-left">#</th>
                                <th class="border-b border-gray-200 px-2 py-1 text-left">Exam No</th>
                                <th class="border-b border-gray-200 px-2 py-1 text-left">Student Name</th>
                                <th
                                    v-for="subject in subjects"
                                    :key="subject.id"
                                    class="border-b border-gray-200 px-2 py-1 text-center"
                                >
                                    {{ subject.code }}
                                </th>
                                <th class="border-b border-gray-200 px-2 py-1 text-center">Total</th>
                                <th class="border-b border-gray-200 px-2 py-1 text-center">Average</th>
                                <th class="border-b border-gray-200 px-2 py-1 text-center">Div</th>
                                <th class="border-b border-gray-200 px-2 py-1 text-center">Pts</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(student, rowIndex) in students"
                                :key="student.id"
                                class="odd:bg-white even:bg-gray-50"
                            >
                                <td class="border-t border-gray-100 px-2 py-1 align-top">
                                    <div class="flex items-center gap-1">
                                        <span>{{ rowIndex + 1 }}</span>
                                        <span
                                            v-if="savedRowIds.includes(student.id)"
                                            class="inline-flex items-center justify-center rounded-full bg-emerald-100 px-1 text-[9px] font-semibold text-emerald-700"
                                        >
                                            ✓
                                        </span>
                                    </div>
                                </td>
                                <td class="border-t border-gray-100 px-2 py-1 align-top font-mono">
                                    {{ student.exam_number }}
                                </td>
                                <td class="border-t border-gray-100 px-2 py-1 align-top">
                                    {{ student.full_name }}
                                </td>
                                <td
                                    v-for="subject in subjects"
                                    :key="subject.id"
                                    class="border-t border-gray-100 px-1 py-0.5 text-center align-top"
                                >
                                    <input
                                        :value="getMark(student.id, subject.id)"
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="w-14 rounded px-1 py-0.5 text-[11px] text-center focus:outline-none focus:ring-emerald-500"
                                        :class="[
                                            existingMarks.some(
                                                (m) =>
                                                    m.student_id === student.id &&
                                                    m.subject_id === subject.id &&
                                                    m.marks !== null &&
                                                    m.marks !== undefined,
                                            )
                                                ? 'bg-emerald-50 border border-emerald-100'
                                                : 'border border-gray-300',
                                            dirtyCells[`${student.id}-${subject.id}`]
                                                ? 'border-blue-400'
                                                : '',
                                            invalidCells[`${student.id}-${subject.id}`]
                                                ? 'border-red-500 bg-red-50'
                                                : '',
                                        ]"
                                        :data-student-id="student.id"
                                        :data-subject-id="subject.id"
                                        @input="(e) => setMark(student.id, subject.id, e.target.value)"
                                        @keydown.enter.prevent="focusNextCell(student.id, subject.id)"
                                    />
                                </td>
                                <td class="border-t border-gray-100 px-2 py-1 text-center align-top">
                                    {{ computeRowTotals(student.id).total }}
                                </td>
                                <td class="border-t border-gray-100 px-2 py-1 text-center align-top">
                                    {{ computeRowTotals(student.id).average }}
                                </td>
                                <td class="border-t border-gray-100 px-2 py-1 text-center align-top">
                                    {{ computeRowDivisionAndPoints(student.id).division }}
                                </td>
                                <td class="border-t border-gray-100 px-2 py-1 text-center align-top">
                                    {{ computeRowDivisionAndPoints(student.id).points }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 flex items-center justify-between gap-3">
                    <p class="text-[10px] text-gray-400">
                        This is an Excel-style entry grid for marks. Division and points are calculated automatically from the grading scheme using the best seven subjects.
                    </p>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="filters.exam"
                            type="button"
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                            @click="router.get(route('exams.results', filters.exam))"
                        >
                            View Results
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70"
                            :disabled="isSaving"
                            @click="saveMarks"
                        >
                            <span v-if="isSaving">Saving...</span>
                            <span v-else>Save Marks</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Standardization modal -->
        <div
            v-if="showStandardizeModal"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-xs rounded-xl bg-white p-5 text-xs text-gray-700 shadow-xl">
                <h3 class="mb-2 text-sm font-semibold text-gray-800">
                    Standardization
                </h3>
                <p class="mb-3 text-[11px] text-gray-500">
                    Enter a single mark (0 7f 7f 7f 7f 7f 7f 7f 7f100) to apply to all students and all subjects in this class for the selected exam.
                </p>
                <div class="mb-4">
                    <label class="mb-1 block font-medium">Standard mark</label>
                    <input
                        v-model="standardizeValue"
                        type="number"
                        min="0"
                        max="100"
                        class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                    />
                </div>
                <div class="flex justify-end gap-2 text-[11px]">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="showStandardizeModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="isStandardizing"
                        @click="applyStandardization"
                    >
                        <span v-if="!isStandardizing">Apply</span>
                        <span v-else>Applying...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Floating save status toast -->
        <transition name="fade">
            <div
                v-if="isSaving || showSaved"
                class="fixed bottom-4 right-4 z-40 flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-[11px] shadow-lg ring-1 ring-gray-200"
            >
                <div v-if="isSaving" class="flex items-center gap-2 text-gray-600">
                    <span class="inline-flex h-3 w-3 animate-spin rounded-full border border-emerald-500 border-t-transparent"></span>
                    <span>Saving marks...</span>
                </div>
                <div v-else class="flex items-center gap-2 text-emerald-700">
                    <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-emerald-100 text-[9px] font-bold">✓</span>
                    <span>Marks saved successfully</span>
                </div>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>
