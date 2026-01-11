<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps({
    exams: {
        type: Array,
        default: () => [],
    },
    classes: {
        type: Array,
        default: () => [],
    },
    subjects: {
        type: Array,
        default: () => [],
    },
    students: {
        type: Array,
        default: () => [],
    },
    existingMarks: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ exam: null, class: null, subject: null }),
    },
});

const marksGrid = reactive({});
props.existingMarks.forEach((row) => {
    const key = String(row.student_id);
    if (row.marks !== null && row.marks !== undefined) {
        marksGrid[key] = String(row.marks);
    }
});

const selectedExam = computed(() => props.filters?.exam ? String(props.filters.exam) : '');
const selectedClass = computed(() => props.filters?.class ? String(props.filters.class) : '');
const selectedSubject = computed(() => props.filters?.subject ? String(props.filters.subject) : '');

const examQuery = ref('');
const classQuery = ref('');
const subjectQuery = ref('');

const examLabelById = computed(() => {
    const map = {};
    (props.exams || []).forEach((e) => {
        map[String(e.id)] = `${e.name}${e.academic_year ? ` (${e.academic_year})` : ''}`;
    });
    return map;
});

const classLabelById = computed(() => {
    const map = {};
    (props.classes || []).forEach((c) => {
        map[String(c.id)] = String(c.name || '');
    });
    return map;
});

const subjectLabelById = computed(() => {
    const map = {};
    (props.subjects || []).forEach((s) => {
        map[String(s.id)] = String(s.code || '');
    });
    return map;
});

watch(
    () => props.filters,
    () => {
        examQuery.value = selectedExam.value ? (examLabelById.value[selectedExam.value] || '') : '';
        classQuery.value = selectedClass.value ? (classLabelById.value[selectedClass.value] || '') : '';
        subjectQuery.value = selectedSubject.value ? (subjectLabelById.value[selectedSubject.value] || '') : '';
    },
    { immediate: true, deep: true },
);

const canEnter = computed(() => !!selectedExam.value && !!selectedClass.value && !!selectedSubject.value);

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const normalize = (v) => String(v || '').trim().toLowerCase();

const resolveExamId = () => {
    const q = normalize(examQuery.value);
    if (!q) return null;
    const found = (props.exams || []).find((e) => normalize(`${e.name}${e.academic_year ? ` (${e.academic_year})` : ''}`) === q);
    return found ? found.id : null;
};

const resolveClassId = () => {
    const q = normalize(classQuery.value);
    if (!q) return null;
    const found = (props.classes || []).find((c) => normalize(c.name) === q);
    return found ? found.id : null;
};

const resolveSubjectId = () => {
    const q = normalize(subjectQuery.value);
    if (!q) return null;
    const found = (props.subjects || []).find((s) => normalize(s.code) === q);
    return found ? found.id : null;
};

const onExamPick = () => {
    const examId = resolveExamId();
    router.get(route('teacher.exams.marks'), { exam: examId }, { preserveState: true, preserveScroll: true, replace: true });
};

const onClassPick = () => {
    const classId = resolveClassId();
    router.get(route('teacher.exams.marks'), { exam: props.filters.exam, class: classId }, { preserveState: true, preserveScroll: true, replace: true });
};

const onSubjectPick = () => {
    const subjectId = resolveSubjectId();
    router.get(route('teacher.exams.marks'), { exam: props.filters.exam, class: props.filters.class, subject: subjectId }, { preserveState: true, preserveScroll: true, replace: true });
};

const savingKey = ref(null);
const saveError = ref('');

const saveCell = async (studentId) => {
    if (!canEnter.value) return;

    saveError.value = '';
    const raw = marksGrid[String(studentId)] ?? '';
    const value = raw === '' ? null : Number(raw);

    if (value !== null && (!Number.isInteger(value) || value < 0 || value > 100)) {
        saveError.value = 'Marks must be a whole number between 0 and 100.';
        return;
    }

    savingKey.value = String(studentId);

    const payload = {
        exam_id: Number(props.filters.exam),
        class_id: Number(props.filters.class),
        subject_id: Number(props.filters.subject),
        student_id: Number(studentId),
        marks: value,
    };

    try {
        const res = await fetch(route('teacher.exams.marks.save-cell'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        if (!res.ok) {
            const txt = await res.text();
            throw new Error(txt || 'Failed to save');
        }

        await res.json();
    } catch (e) {
        saveError.value = 'Failed to save marks. Please try again.';
    } finally {
        savingKey.value = null;
    }
};

const markInputClass = (studentId) => {
    const base = 'h-9 w-24 rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500';
    if (savingKey.value === String(studentId)) return base + ' opacity-60';
    return base;
};
</script>

<template>
    <Head title="Enter Marks" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">Enter Marks</h2>
                <p class="mt-1 text-sm text-gray-500">You can enter marks for your subject only.</p>
            </div>
        </template>

        <div class="space-y-6">
            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="text-xs text-gray-500">
                        <span class="font-semibold text-gray-800">Selected:</span>
                        <span class="ml-1">
                            {{ selectedExam ? (examLabelById[selectedExam] || '—') : '—' }}
                        </span>
                        <span class="mx-1">/</span>
                        <span>
                            {{ selectedClass ? (classLabelById[selectedClass] || '—') : '—' }}
                        </span>
                        <span class="mx-1">/</span>
                        <span>
                            {{ selectedSubject ? (subjectLabelById[selectedSubject] || '—') : '—' }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Exam</label>
                        <input
                            v-model="examQuery"
                            list="teacher-exams"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Type exam name..."
                            @change="onExamPick"
                        />
                        <datalist id="teacher-exams">
                            <option v-for="e in exams" :key="e.id" :value="`${e.name}${e.academic_year ? ` (${e.academic_year})` : ''}`" />
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Class</label>
                        <input
                            v-model="classQuery"
                            list="teacher-classes"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-gray-50"
                            placeholder="Type class name..."
                            :disabled="!selectedExam"
                            @change="onClassPick"
                        />
                        <datalist id="teacher-classes">
                            <option v-for="c in classes" :key="c.id" :value="c.name" />
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Subject</label>
                        <input
                            v-model="subjectQuery"
                            list="teacher-subjects"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-gray-50"
                            placeholder="Type subject code..."
                            :disabled="!selectedExam || !selectedClass"
                            @change="onSubjectPick"
                        />
                        <datalist id="teacher-subjects">
                            <option v-for="s in subjects" :key="s.id" :value="s.code" />
                        </datalist>
                    </div>
                </div>

                <div v-if="saveError" class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ saveError }}
                </div>
            </div>

            <div v-if="!canEnter" class="rounded-lg bg-white p-6 text-sm text-gray-600 shadow-sm ring-1 ring-gray-100">
                Select Exam, Class and Subject to start entering marks.
            </div>

            <div v-else class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Exam #</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Student</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Stream</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Marks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!students || students.length === 0">
                                <td colspan="4" class="px-5 py-6 text-center text-sm text-gray-500">
                                    No students found in this class.
                                </td>
                            </tr>
                            <tr v-for="st in students" :key="st.id" class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ st.exam_number }}</td>
                                <td class="px-5 py-3 text-gray-800">{{ st.full_name }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ st.stream || '—' }}</td>
                                <td class="px-5 py-3">
                                    <input
                                        :class="markInputClass(st.id)"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        v-model="marksGrid[String(st.id)]"
                                        :disabled="savingKey === String(st.id)"
                                        @blur="saveCell(st.id)"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
