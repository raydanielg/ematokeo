<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    students: {
        type: Array,
        default: () => [],
    },
    classes: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ class: null }),
    },
    exams: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash && page.props.flash.success ? page.props.flash.success : '');

const showAddModal = ref(false);
const showBulkModal = ref(false);
const showImportSamplesModal = ref(false);
const showDetailsModal = ref(false);
const showEditModal = ref(false);
const showBulkDeleteConfirm = ref(false);
const selectedStudent = ref(null);
const isSavingStudent = ref(false);
const selectedStudentIds = ref([]);
const isBulkDeleting = ref(false);

const newStudent = ref({
    full_name: '',
    class_level: '',
    gender: '',
    division: '',
    date_of_birth: '',
});

const importClassLevel = ref('');
const isImportingSamples = ref(false);

const onClassChange = (event) => {
    const value = event.target.value || null;
    router.get(
        route('students.index'),
        { class: value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const toggleStudentSelection = (id) => {
    const idx = selectedStudentIds.value.indexOf(id);
    if (idx === -1) {
        selectedStudentIds.value.push(id);
    } else {
        selectedStudentIds.value.splice(idx, 1);
    }
};

const allStudentsSelected = () => {
    return props.students.length > 0 && selectedStudentIds.value.length === props.students.length;
};

const toggleSelectAllStudents = () => {
    if (allStudentsSelected()) {
        selectedStudentIds.value = [];
    } else {
        selectedStudentIds.value = props.students.map((s) => s.id);
    }
};

const bulkDeleteStudents = () => {
    if (!selectedStudentIds.value.length || isBulkDeleting.value) return;

    isBulkDeleting.value = true;

    router.post(
        route('students.bulk-delete'),
        { ids: selectedStudentIds.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedStudentIds.value = [];
                showBulkDeleteConfirm.value = false;
            },
            onFinish: () => {
                isBulkDeleting.value = false;
            },
        },
    );
};

const openBulkDeleteConfirm = () => {
    if (!selectedStudentIds.value.length) return;
    showBulkDeleteConfirm.value = true;
};

const openImportSamplesModal = () => {
    importClassLevel.value = '';
    showImportSamplesModal.value = true;
};

const importSampleStudents = () => {
    if (!importClassLevel.value || isImportingSamples.value) return;

    isImportingSamples.value = true;

    router.post(
        route('students.import-samples'),
        {
            class_level: importClassLevel.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showImportSamplesModal.value = false;
            },
            onFinish: () => {
                isImportingSamples.value = false;
            },
        },
    );
};

const openDetails = (student) => {
    selectedStudent.value = student;
    showDetailsModal.value = true;
};

const openEdit = (student) => {
    selectedStudent.value = student;
    showEditModal.value = true;
};

const resetNewStudent = () => {
    newStudent.value = {
        full_name: '',
        class_level: '',
        gender: '',
        division: '',
        date_of_birth: '',
    };
};

const openAddModal = () => {
    resetNewStudent();
    showAddModal.value = true;
};

const saveStudent = () => {
    if (isSavingStudent.value) return;

    isSavingStudent.value = true;

    router.post(
        route('students.store'),
        {
            full_name: newStudent.value.full_name,
            class_level: newStudent.value.class_level,
            gender: newStudent.value.gender || null,
            division: newStudent.value.division || null,
            date_of_birth: newStudent.value.date_of_birth || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showAddModal.value = false;
            },
            onFinish: () => {
                isSavingStudent.value = false;
            },
        },
    );
};

const closeDetails = () => {
    showDetailsModal.value = false;
};

const closeEdit = () => {
    showEditModal.value = false;
};

const openStudentReport = () => {
    if (!selectedStudent.value) return;
    const id = selectedStudent.value.id;
    // Go to student report preview page for this student; user can pick exam there
    router.get(route('reports.students.show', id));
};

const selectedExamId = ref('');
</script>

<template>
    <Head title="Students" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Students
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Overview of all registered students. Use the class filter to narrow down the list.
                    </p>
                </div>

        <!-- Bulk delete confirmation modal -->
        <div
            v-if="showBulkDeleteConfirm"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-sm rounded-xl bg-white p-5 shadow-xl">
                <h3 class="mb-2 text-sm font-semibold text-gray-800">
                    Confirm bulk delete
                </h3>
                <p class="mb-4 text-xs text-gray-600">
                    You are about to delete {{ selectedStudentIds.length }} student(s). This action cannot be undone.
                </p>
                <div class="flex justify-end gap-2 text-xs">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="showBulkDeleteConfirm = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="isBulkDeleting"
                        @click="bulkDeleteStudents"
                    >
                        <span v-if="!isBulkDeleting">Delete</span>
                        <span v-else>Deleting...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Centered success flash message -->
        <div
            v-if="flashSuccess"
            class="pointer-events-none fixed inset-0 z-50 flex items-start justify-center pt-16"
        >
            <div class="pointer-events-auto rounded-md bg-emerald-600 px-4 py-2 text-xs font-medium text-white shadow-lg">
                {{ flashSuccess }}
            </div>
        </div>

        <!-- Import Sample Students modal -->
        <div
            v-if="showImportSamplesModal"
            class="fixed inset-0 z-30 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-sm rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">
                        Import 100 Sample Students
                    </h3>
                    <button
                        type="button"
                        class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                        @click="showImportSamplesModal = false"
                    >
                        ✕
                    </button>
                </div>
                <div class="space-y-3 text-xs text-gray-700">
                    <p class="text-[11px] text-gray-600">
                        Choose the class level where you want to quickly add 100 sample students. They will
                        automatically inherit all subjects already assigned to that class level on the Classes page.
                        Useful for testing reports and features without entering data manually.
                    </p>
                    <div>
                        <label class="mb-1 block font-medium">Class Level</label>
                        <select
                            v-model="importClassLevel"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="">Select class level</option>
                            <option
                                v-for="cls in classes"
                                :key="`import-` + cls"
                                :value="cls"
                            >
                                {{ cls }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="showImportSamplesModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="!importClassLevel || isImportingSamples"
                        @click="importSampleStudents"
                    >
                        <span v-if="!isImportingSamples">Import 100 Students</span>
                        <span v-else>Importing...</span>
                    </button>
                </div>
            </div>
        </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Class</label>
                    <select
                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        @change="onClassChange"
                    >
                        <option value="">All</option>
                        <option
                            v-for="cls in classes"
                            :key="cls"
                            :value="cls"
                            :selected="filters.class === cls"
                        >
                            {{ cls }}
                        </option>
                    </select>
                </div>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-sm font-medium text-gray-700">
                    Students list ({{ students.length }})
                </p>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none"
                        @click="openAddModal"
                    >
                        <span class="text-base leading-none">+</span>
                        <span>Add Student</span>
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 transition hover:bg-emerald-100 focus:outline-none"
                        @click="showBulkModal = true"
                    >
                        <span class="text-sm leading-none">⇪</span>
                        <span>Bulk Upload</span>
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 ring-1 ring-blue-200 transition hover:bg-blue-100 focus:outline-none"
                        @click="openImportSamplesModal"
                    >
                        <span class="text-sm leading-none">★</span>
                        <span>Import 100 Samples</span>
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-1 rounded-md bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 ring-1 ring-red-200 transition hover:bg-red-100 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="!selectedStudentIds.length || isBulkDeleting"
                        @click="openBulkDeleteConfirm"
                    >
                        <span v-if="!isBulkDeleting">Bulk Delete</span>
                        <span v-else>Deleting...</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">
                                <input
                                    type="checkbox"
                                    class="h-3 w-3 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                    :checked="allStudentsSelected()"
                                    @change="toggleSelectAllStudents"
                                />
                            </th>
                            <th class="px-3 py-2">#</th>
                            <th class="px-3 py-2">Exam Number</th>
                            <th class="px-3 py-2">Full Name</th>
                            <th class="px-3 py-2">Class</th>
                            <th class="px-3 py-2">Subjects</th>
                            <th class="px-3 py-2">Gender</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="students.length === 0">
                            <td colspan="7" class="px-3 py-4 text-center text-xs text-gray-500">
                                No students found yet.
                            </td>
                        </tr>
                        <tr
                            v-for="(student, index) in students"
                            :key="student.id"
                            class="border-b border-gray-100 text-xs text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top">
                                <input
                                    type="checkbox"
                                    class="h-3 w-3 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                    :checked="selectedStudentIds.includes(student.id)"
                                    @change="toggleStudentSelection(student.id)"
                                />
                            </td>
                            <td class="px-3 py-2 align-top">
                                {{ index + 1 }}
                            </td>
                            <td class="px-3 py-2 align-top font-mono text-xs text-gray-900">
                                {{ student.exam_number }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                {{ student.full_name }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                <span>{{ student.class_level }}</span>
                                <span v-if="student.stream"> - {{ student.stream }}</span>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-if="student.subjects && student.subjects.length"
                                        v-for="sub in student.subjects"
                                        :key="sub.code"
                                        class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-emerald-100"
                                    >
                                        {{ sub.code }} - {{ sub.name }}
                                    </span>
                                    <span
                                        v-if="!student.subjects || !student.subjects.length"
                                        class="text-[11px] text-gray-400"
                                    >
                                        —
                                    </span>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                {{ student.gender || '—' }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="rounded-md bg-blue-50 px-2 py-1 text-[11px] font-medium text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100"
                                        @click="openDetails(student)"
                                    >
                                        Details
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-emerald-50 px-2 py-1 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                                        @click="openEdit(student)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-red-50 px-2 py-1 text-[11px] font-medium text-red-700 ring-1 ring-red-100 hover:bg-red-100"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Student modal -->
        <div
            v-if="showAddModal"
            class="fixed inset-0 z-30 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">
                        Add Student
                    </h3>
                    <button
                        type="button"
                        class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                        @click="showAddModal = false"
                    >
                        ✕
                    </button>
                </div>
                <div class="space-y-3 text-xs text-gray-700">
                    <div>
                        <label class="mb-1 block font-medium">Full Name</label>
                        <input
                            v-model="newStudent.full_name"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="e.g. John Doe"
                        />
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="mb-1 block font-medium">Class</label>
                            <select
                                v-model="newStudent.class_level"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option value="">Select class</option>
                                <option
                                    v-for="cls in classes"
                                    :key="`add-` + cls"
                                    :value="cls"
                                >
                                    {{ cls }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Division</label>
                            <select
                                v-model="newStudent.division"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option value="">Select division</option>
                                <option value="I">I</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Gender</label>
                            <select
                                v-model="newStudent.gender"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option value="">Select gender</option>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block font-medium">Parent Phone</label>
                        <input
                            type="tel"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="e.g. 07XXXXXXXX"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block font-medium">Date of Birth</label>
                        <input
                            v-model="newStudent.date_of_birth"
                            type="date"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block font-medium">Exam Number</label>
                        <input
                            type="text"
                            class="w-full cursor-not-allowed rounded-md border border-dashed border-gray-300 bg-gray-50 px-2 py-1.5 text-xs text-gray-500"
                            value="Will be generated automatically from school code"
                            readonly
                        />
                        <p class="mt-1 text-[10px] text-gray-500">
                            After you save, the system will assign an exam number using your school code and class.
                        </p>
                    </div>
                    <div>
                        <label class="mb-1 block font-medium">Notes (optional)</label>
                        <textarea
                            rows="2"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="Any extra information about this student"
                        ></textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="showAddModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                        :disabled="isSavingStudent"
                        @click="saveStudent"
                    >
                        <span v-if="!isSavingStudent">Save</span>
                        <span v-else>Saving...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Student Details modal -->
        <div
            v-if="showDetailsModal && selectedStudent"
            class="fixed inset-0 z-30 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-3xl rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">
                            Student Details
                        </h3>
                        <p class="mt-0.5 text-[11px] text-gray-500">
                            Full profile and a sample academic summary for this student.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                        @click="closeDetails"
                    >
                        ✕
                    </button>
                </div>

                <div class="mb-3 flex items-center justify-between text-[11px] text-gray-600">
                    <div></div>
                    <a
                        v-if="selectedStudent"
                        :href="route('students.marks.show', selectedStudent.id)"
                        target="_blank"
                        class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 font-medium text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                    >
                        <span>Open Marks JSON</span>
                    </a>
                </div>

                <div class="grid gap-4 md:grid-cols-2 text-xs text-gray-700">
                    <div class="space-y-2">
                        <h4 class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Basic Information
                        </h4>
                        <div class="space-y-1">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-600">Full Name:</span>
                                <span class="text-gray-800">{{ selectedStudent.full_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-600">Exam No:</span>
                                <span class="font-mono text-gray-800">{{ selectedStudent.exam_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-600">Class / Stream:</span>
                                <span class="text-gray-800">
                                    {{ selectedStudent.class_level }}
                                    <span v-if="selectedStudent.stream">- {{ selectedStudent.stream }}</span>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-600">Gender:</span>
                                <span class="text-gray-800">{{ selectedStudent.gender || '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-600">Date of Birth:</span>
                                <span class="text-gray-800">{{ selectedStudent.date_of_birth || '—' }}</span>
                            </div>
                        </div>

                        <h4 class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Guardian & Contacts (placeholder)
                        </h4>
                        <p class="text-[11px] text-gray-500">
                            In the full system this section will show parent/guardian name, phone and address linked to this student.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <h4 class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Subjects
                        </h4>
                        <div class="mb-2 flex flex-wrap gap-1">
                            <span
                                v-if="selectedStudent.subjects && selectedStudent.subjects.length"
                                v-for="sub in selectedStudent.subjects"
                                :key="sub.code"
                                class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-100"
                            >
                                {{ sub.code }} - {{ sub.name }}
                            </span>
                            <span
                                v-if="!selectedStudent.subjects || !selectedStudent.subjects.length"
                                class="text-[11px] text-gray-400"
                            >
                                Subjects not configured yet.
                            </span>
                        </div>

                        <div class="mt-2 overflow-hidden rounded-lg border border-gray-200 bg-white">
                            <!-- Mini report header -->
                            <div class="border-b border-gray-200 bg-gray-50 px-3 py-2">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <img
                                            src="/images/emblem.png"
                                            alt="Emblem"
                                            class="h-7 w-7 flex-shrink-0"
                                        />
                                        <div class="leading-tight">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-700">
                                                United Republic of Tanzania
                                            </p>
                                            <p class="text-[10px] font-semibold text-gray-800">
                                                Student Report Card (Preview)
                                            </p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        class="hidden rounded-md bg-emerald-600 px-2 py-1 text-[10px] font-semibold text-white shadow-sm hover:bg-emerald-700 md:inline-flex"
                                        @click="openStudentReport"
                                    >
                                        Open & Print
                                    </button>
                                </div>
                                <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-[10px] text-gray-600">
                                    <div>
                                        <span class="font-semibold text-gray-700">Student:</span>
                                        <span class="ml-1 text-gray-800">{{ selectedStudent.full_name }}</span>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-gray-700">Exam No:</span>
                                        <span class="ml-1 font-mono text-gray-800">{{ selectedStudent.exam_number }}</span>
                                    </div>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center justify-between gap-2 text-[10px] text-gray-600">
                                    <div>
                                        <span class="font-semibold text-gray-700">Class:</span>
                                        <span class="ml-1 text-gray-800">
                                            {{ selectedStudent.class_level }}
                                            <span v-if="selectedStudent.stream">- {{ selectedStudent.stream }}</span>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-700">Exam:</span>
                                        <select
                                            v-model="selectedExamId"
                                            class="rounded-md border border-gray-300 px-2 py-0.5 text-[10px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                        >
                                            <option value="">
                                                Select exam
                                            </option>
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

                            <!-- Mini results table -->
                            <div class="px-3 py-2">
                                <table class="w-full border-collapse text-[11px]">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-600">
                                            <th class="border-b border-gray-200 px-2 py-1 text-left">Subject</th>
                                            <th class="border-b border-gray-200 px-2 py-1 text-center">Marks</th>
                                            <th class="border-b border-gray-200 px-2 py-1 text-center">Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="text-gray-700">
                                            <td class="border-t border-gray-100 px-2 py-1">Mathematics</td>
                                            <td class="border-t border-gray-100 px-2 py-1 text-center">78</td>
                                            <td class="border-t border-gray-100 px-2 py-1 text-center">B</td>
                                        </tr>
                                        <tr class="text-gray-700">
                                            <td class="border-t border-gray-100 px-2 py-1">English</td>
                                            <td class="border-t border-gray-100 px-2 py-1 text-center">65</td>
                                            <td class="border-t border-gray-100 px-2 py-1 text-center">C</td>
                                        </tr>
                                        <tr class="text-gray-700">
                                            <td class="border-t border-gray-100 px-2 py-1">Kiswahili</td>
                                            <td class="border-t border-gray-100 px-2 py-1 text-center">82</td>
                                            <td class="border-t border-gray-100 px-2 py-1 text-center">A</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p class="mt-1 text-[10px] text-gray-400">
                                    This is a quick in-page preview. Use the "Print Report Card" button below to open the full printable report card with all details.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-between gap-2">
                    <button
                        type="button"
                        class="text-[11px] text-emerald-700 hover:underline"
                        @click="openStudentReport"
                    >
                        Print Report Card
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="closeDetails"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Student modal (UI only) -->
        <div
            v-if="showEditModal && selectedStudent"
            class="fixed inset-0 z-30 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">
                        Edit Student
                    </h3>
                    <button
                        type="button"
                        class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                        @click="closeEdit"
                    >
                        ✕
                    </button>
                </div>

                <form class="space-y-3 text-xs text-gray-700">
                    <div>
                        <label class="mb-1 block font-medium">Full Name</label>
                        <input
                            :value="selectedStudent.full_name"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block font-medium">Class Level</label>
                            <input
                                :value="selectedStudent.class_level"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Stream</label>
                            <input
                                :value="selectedStudent.stream"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block font-medium">Gender</label>
                            <input
                                :value="selectedStudent.gender || ''"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Date of Birth</label>
                            <input
                                :value="selectedStudent.date_of_birth || ''"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block font-medium">Assigned Subjects</label>
                        <div class="flex flex-wrap gap-1">
                            <span
                                v-if="selectedStudent.subjects && selectedStudent.subjects.length"
                                v-for="sub in selectedStudent.subjects"
                                :key="sub.code"
                                class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-100"
                            >
                                {{ sub.code }} - {{ sub.name }}
                            </span>
                            <span
                                v-if="!selectedStudent.subjects || !selectedStudent.subjects.length"
                                class="text-[11px] text-gray-400"
                            >
                                No subjects assigned yet. In future you will be able to pick per-student subjects here.
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                            @click="closeEdit"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                        >
                            Save Changes (UI only)
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bulk upload modal -->
        <div
            v-if="showBulkModal"
            class="fixed inset-0 z-30 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-xl rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">
                        Bulk Upload Students
                    </h3>
                    <button
                        type="button"
                        class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                        @click="showBulkModal = false"
                    >
                        ✕
                    </button>
                </div>
                <div class="space-y-3 text-xs text-gray-700">
                    <p class="text-[11px] text-gray-600">
                        Paste or type one student per line in the format:
                        <span class="font-mono">Full Name, Class, Gender</span>.
                        Exam numbers will be generated automatically based on your school code and class.
                    </p>
                    <textarea
                        rows="8"
                        class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs font-mono focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g.
John Doe, Form I, M
Jane Doe, Form I, F"
                    ></textarea>
                </div>
                <div class="mt-4 flex justify-between gap-2">
                    <button
                        type="button"
                        class="text-[11px] text-emerald-700 hover:underline"
                    >
                        Download sample CSV (coming soon)
                    </button>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                            @click="showBulkModal = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                        >
                            Process Upload
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
