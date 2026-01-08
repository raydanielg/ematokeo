<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

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

const canEnter = computed(() => !!selectedExam.value && !!selectedClass.value && !!selectedSubject.value);

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const onExamChange = (e) => {
    const examId = e.target.value || null;
    router.get(route('teacher.exams.marks'), { exam: examId }, { preserveState: true, preserveScroll: true, replace: true });
};

const onClassChange = (e) => {
    const classId = e.target.value || null;
    router.get(route('teacher.exams.marks'), { exam: props.filters.exam, class: classId }, { preserveState: true, preserveScroll: true, replace: true });
};

const onSubjectChange = (e) => {
    const subjectId = e.target.value || null;
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
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Exam</label>
                        <select
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            :value="selectedExam"
                            @change="onExamChange"
                        >
                            <option value="">Select exam</option>
                            <option v-for="e in exams" :key="e.id" :value="e.id">{{ e.name }} ({{ e.academic_year || '—' }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Class</label>
                        <select
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            :disabled="!selectedExam"
                            :value="selectedClass"
                            @change="onClassChange"
                        >
                            <option value="">Select class</option>
                            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Subject</label>
                        <select
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            :disabled="!selectedExam || !selectedClass"
                            :value="selectedSubject"
                            @change="onSubjectChange"
                        >
                            <option value="">Select subject</option>
                            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.code }}</option>
                        </select>
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
