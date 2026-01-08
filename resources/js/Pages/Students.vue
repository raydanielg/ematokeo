<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Eye, Pencil, Trash2, ClipboardList } from 'lucide-vue-next';

const props = defineProps({
    students: {
        type: Array,
        default: () => [],
    },
    classes: {
        type: Array,
        default: () => [],
    },
    classSubjects: {
        type: Object,
        default: () => ({}),
    },
    streamSubjects: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({ class: null }),
    },
    exams: {
        type: Array,
        default: () => [],
    },
    streamsByClass: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash && page.props.flash.success ? page.props.flash.success : '');

const showAddModal = ref(false);
const showBulkModal = ref(false);
const showImportSamplesModal = ref(false);
const showDetailsModal = ref(false);
const showEditModal = ref(false);
const showBehaviourModal = ref(false);
const showBulkDeleteConfirm = ref(false);
const showSingleDeleteConfirm = ref(false);
const selectedStudent = ref(null);
const isSavingStudent = ref(false);
const isUpdatingStudent = ref(false);
const newStudentErrors = ref({});
const editStudentErrors = ref({});
const studentToDelete = ref(null);
const isDeletingSingle = ref(false);
const selectedStudentIds = ref([]);
const isBulkDeleting = ref(false);

const activeDetailsTab = ref('personal');
const isBehaviourEditing = ref(false);

const newStudent = ref({
    full_name: '',
    class_level: '',
    stream: '',
    gender: '',
    date_of_birth: '',
    guardian_name: '',
    guardian_phone: '',
    guardian_address: '',
});

const editStudent = ref({
    full_name: '',
    class_level: '',
    stream: '',
    gender: '',
    date_of_birth: '',
    guardian_name: '',
    guardian_phone: '',
    guardian_address: '',
});

const editAvailableStreams = computed(() => {
    const key = String(editStudent.value.class_level || '').trim();
    const arr = props.streamsByClass && Object.prototype.hasOwnProperty.call(props.streamsByClass, key)
        ? props.streamsByClass[key]
        : [];
    return Array.isArray(arr) ? arr : [];
});

const addAvailableStreams = computed(() => {
    const key = String(newStudent.value.class_level || '').trim();
    const arr = props.streamsByClass && Object.prototype.hasOwnProperty.call(props.streamsByClass, key)
        ? props.streamsByClass[key]
        : [];
    return Array.isArray(arr) ? arr : [];
});

const showAddStreamHelper = computed(() => {
    const hasClass = !!String(newStudent.value.class_level || '').trim();
    return hasClass && (!addAvailableStreams.value || addAvailableStreams.value.length === 0);
});

const showEditStreamHelper = computed(() => {
    const hasClass = !!String(editStudent.value.class_level || '').trim();
    return hasClass && (!editAvailableStreams.value || editAvailableStreams.value.length === 0);
});

const computeAssignedSubjects = (classLevel, stream) => {
    const clsKey = String(classLevel || '').trim();
    const streamKey = String(stream || '').trim();

    if (clsKey && streamKey && props.streamSubjects && props.streamSubjects[clsKey] && props.streamSubjects[clsKey][streamKey]) {
        const s = props.streamSubjects[clsKey][streamKey];
        if (Array.isArray(s) && s.length) return s;
    }

    if (clsKey && props.classSubjects && props.classSubjects[clsKey]) {
        const base = props.classSubjects[clsKey];
        if (Array.isArray(base)) return base;
    }

    return [];
};

const editAssignedSubjects = computed(() => {
    return computeAssignedSubjects(editStudent.value.class_level, editStudent.value.stream);
});

const addAssignedSubjects = computed(() => {
    return computeAssignedSubjects(newStudent.value.class_level, newStudent.value.stream);
});

watch(
    () => editStudent.value.class_level,
    () => {
        if (!editAvailableStreams.value.length) {
            editStudent.value.stream = '';
        } else if (editStudent.value.stream && !editAvailableStreams.value.includes(editStudent.value.stream)) {
            editStudent.value.stream = '';
        }
    },
);

watch(
    () => newStudent.value.class_level,
    () => {
        if (!addAvailableStreams.value.length) {
            newStudent.value.stream = '';
        } else if (newStudent.value.stream && !addAvailableStreams.value.includes(newStudent.value.stream)) {
            newStudent.value.stream = '';
        }
    },
);

const groupedStudents = computed(() => {
    const groups = new Map();
    (props.students || []).forEach((s) => {
        const key = String(s.class_level || 'Unknown');
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(s);
    });

    return Array.from(groups.entries()).map(([class_level, students]) => ({
        class_level,
        students,
    }));
});

const importClassLevel = ref('');
const isImportingSamples = ref(false);

const classFilterModel = ref(String(props.filters?.class || ''));

watch(
    () => props.filters?.class,
    (next) => {
        classFilterModel.value = String(next || '');
    },
);

const onClassChange = (event) => {
    const value = event?.target?.value ?? classFilterModel.value;
    classFilterModel.value = String(value || '');
    router.get(
        route('students.index'),
        { class: value || null, sort: props.filters?.sort || null, dir: props.filters?.dir || null },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const sortKey = computed(() => String(props.filters?.sort || '').trim());
const sortDir = computed(() => String(props.filters?.dir || 'asc').toLowerCase() === 'desc' ? 'desc' : 'asc');

const arrowFor = (key) => {
    if (sortKey.value !== key) return '';
    return sortDir.value === 'asc' ? '▲' : '▼';
};

const sortBy = (key) => {
    const currentKey = sortKey.value;
    const currentDir = sortDir.value;

    const nextDir = currentKey === key ? (currentDir === 'asc' ? 'desc' : 'asc') : 'asc';

    router.get(
        route('students.index'),
        {
            class: classFilterModel.value ? classFilterModel.value : null,
            sort: key,
            dir: nextDir,
        },
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
    showBehaviourModal.value = false;
    activeDetailsTab.value = 'personal';
    isBehaviourEditing.value = false;

    selectedExamId.value = '';
    loadResultsPreview();
    loadBehaviourRatings('');
};

const openBehaviour = (student) => {
    selectedStudent.value = student;
    showBehaviourModal.value = true;
    showDetailsModal.value = false;
    isBehaviourEditing.value = true;

    behaviourDate.value = '';
    loadBehaviourRatings(behaviourDate.value);
};

const openEdit = (student) => {
    selectedStudent.value = student;
    editStudentErrors.value = {};
    editStudent.value = {
        full_name: student?.full_name || '',
        class_level: student?.class_level || '',
        stream: student?.stream || '',
        gender: student?.gender || '',
        date_of_birth: student?.date_of_birth || '',
        guardian_name: student?.guardian_name || '',
        guardian_phone: student?.guardian_phone || '',
        guardian_address: student?.guardian_address || '',
    };
    showEditModal.value = true;
};

const updateStudent = () => {
    if (!selectedStudent.value || isUpdatingStudent.value) return;

    isUpdatingStudent.value = true;
    editStudentErrors.value = {};

    router.patch(
        route('students.update', selectedStudent.value.id),
        {
            full_name: editStudent.value.full_name,
            class_level: editStudent.value.class_level,
            stream: editStudent.value.stream || null,
            gender: editStudent.value.gender || null,
            date_of_birth: editStudent.value.date_of_birth || null,
            guardian_name: editStudent.value.guardian_name || null,
            guardian_phone: editStudent.value.guardian_phone || null,
            guardian_address: editStudent.value.guardian_address || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedStudent.value) {
                    selectedStudent.value.full_name = editStudent.value.full_name;
                    selectedStudent.value.class_level = editStudent.value.class_level;
                    selectedStudent.value.stream = editStudent.value.stream || null;
                    selectedStudent.value.gender = editStudent.value.gender || null;
                    selectedStudent.value.date_of_birth = editStudent.value.date_of_birth || null;
                    selectedStudent.value.guardian_name = editStudent.value.guardian_name || null;
                    selectedStudent.value.guardian_phone = editStudent.value.guardian_phone || null;
                    selectedStudent.value.guardian_address = editStudent.value.guardian_address || null;
                    selectedStudent.value.subjects = editAssignedSubjects.value;
                }

                showEditModal.value = false;
            },
            onError: (errors) => {
                editStudentErrors.value = errors || {};
            },
            onFinish: () => {
                isUpdatingStudent.value = false;
            },
        },
    );
};

const deleteStudent = (student) => {
    if (!student) return;
    studentToDelete.value = student;
    showSingleDeleteConfirm.value = true;
};

const confirmDeleteStudent = () => {
    if (!studentToDelete.value || isDeletingSingle.value) return;

    isDeletingSingle.value = true;

    router.post(
        route('students.bulk-delete'),
        { ids: [studentToDelete.value.id] },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedStudentIds.value = selectedStudentIds.value.filter((id) => id !== studentToDelete.value?.id);

                if (selectedStudent.value && selectedStudent.value.id === studentToDelete.value.id) {
                    showDetailsModal.value = false;
                    showEditModal.value = false;
                    showBehaviourModal.value = false;
                    selectedStudent.value = null;
                }

                showSingleDeleteConfirm.value = false;
                studentToDelete.value = null;
            },
            onFinish: () => {
                isDeletingSingle.value = false;
            },
        },
    );
};

const closeSingleDeleteConfirm = () => {
    showSingleDeleteConfirm.value = false;
    studentToDelete.value = null;
};

const resetNewStudent = () => {
    newStudent.value = {
        full_name: '',
        class_level: '',
        stream: '',
        gender: '',
        date_of_birth: '',
        guardian_name: '',
        guardian_phone: '',
        guardian_address: '',
    };
};

const openAddModal = () => {
    resetNewStudent();
    newStudentErrors.value = {};
    showAddModal.value = true;
};

const saveStudent = () => {
    if (isSavingStudent.value) return;

    isSavingStudent.value = true;
    newStudentErrors.value = {};

    router.post(
        route('students.store'),
        {
            full_name: newStudent.value.full_name,
            class_level: newStudent.value.class_level,
            stream: newStudent.value.stream || null,
            gender: newStudent.value.gender || null,
            date_of_birth: newStudent.value.date_of_birth || null,
            guardian_name: newStudent.value.guardian_name || null,
            guardian_phone: newStudent.value.guardian_phone || null,
            guardian_address: newStudent.value.guardian_address || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showAddModal.value = false;
                router.reload({ only: ['students'] });
            },
            onError: (errors) => {
                newStudentErrors.value = errors || {};
            },
            onFinish: () => {
                isSavingStudent.value = false;
            },
        },
    );
};

const closeDetails = () => {
    showDetailsModal.value = false;
    selectedExamId.value = '';
    resultsPreviewExam.value = null;
    resultsPreviewSummary.value = null;
    resultsPreview.value = [];
    resultsPreviewError.value = '';

    behaviourDate.value = '';
    behaviourRatings.value = [];
    behaviourError.value = '';
};

const closeBehaviour = () => {
    showBehaviourModal.value = false;
    isBehaviourEditing.value = false;
    behaviourDate.value = '';
    behaviourRatings.value = [];
    behaviourError.value = '';
};

const closeEdit = () => {
    showEditModal.value = false;
    editStudentErrors.value = {};
};

const selectedExamId = ref('');

const resultsPreview = ref([]);
const resultsPreviewExam = ref(null);
const resultsPreviewSummary = ref(null);
const isLoadingResultsPreview = ref(false);
const resultsPreviewError = ref('');

const loadResultsPreview = async () => {
    if (!selectedStudent.value) return;

    resultsPreviewError.value = '';
    isLoadingResultsPreview.value = true;

    try {
        const params = new URLSearchParams();
        if (selectedExamId.value) params.set('exam', selectedExamId.value);

        const url = route('students.results-preview', selectedStudent.value.id) + (params.toString() ? `?${params.toString()}` : '');
        const res = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!res.ok) {
            throw new Error(`Request failed (${res.status})`);
        }

        const data = await res.json();
        resultsPreviewExam.value = data.exam;
        resultsPreviewSummary.value = data.summary;
        resultsPreview.value = Array.isArray(data.results) ? data.results : [];

        if (!selectedExamId.value && data.exam && data.exam.id) {
            selectedExamId.value = String(data.exam.id);
        }
    } catch (e) {
        resultsPreviewExam.value = null;
        resultsPreviewSummary.value = null;
        resultsPreview.value = [];
        resultsPreviewError.value = 'Could not load results preview.';
    } finally {
        isLoadingResultsPreview.value = false;
    }
};

const onExamChange = () => {
    loadResultsPreview();
};

const onBehaviourDateChange = () => {
    loadBehaviourRatings(behaviourDate.value);
};

const behaviourDate = ref('');
const behaviourRatings = ref([]);
const isLoadingBehaviour = ref(false);
const isSavingBehaviour = ref(false);
const behaviourError = ref('');

const behaviourTemplates = [
    { code: '901', label: 'Bidii ya Kazi' },
    { code: '902', label: 'Mahudhurio Darasani' },
    { code: '903', label: 'Kutunza mali za shule' },
    { code: '904', label: 'Utii kazini/darasani' },
    { code: '905', label: 'Uelewano na ushirikiano' },
    { code: '906', label: 'Heshima kwa wote' },
    { code: '907', label: 'Kumudu uongozi' },
    { code: '908', label: 'Adabu' },
    { code: '909', label: 'Kujua usafi binafsi' },
    { code: '910', label: 'Kukubali ushauri' },
    { code: '911', label: 'Kuthamini kazi' },
    { code: '912', label: 'Kujituma' },
    { code: '913', label: 'Kushiriki michezo' },
    { code: '914', label: 'Sifa nzuri ya Ushawishi' },
];

const ensureBehaviourTemplate = (savedRows) => {
    const map = new Map();
    (savedRows || []).forEach((r) => {
        map.set(String(r.code), {
            code: String(r.code),
            label: r.label,
            grade: r.grade,
            comment: r.comment || '',
        });
    });

    return behaviourTemplates.map((tpl) => {
        const found = map.get(String(tpl.code));
        return {
            code: tpl.code,
            label: tpl.label,
            grade: found?.grade || '',
            comment: found?.comment || '',
        };
    });
};

const canSaveBehaviour = computed(() => {
    if (!behaviourDate.value) return false;
    return behaviourRatings.value.every((r) => ['A', 'B', 'C', 'D'].includes(String(r.grade || '').toUpperCase()));
});

const gradeDefaultComments = {
    A: 'Excellent',
    B: 'Good',
    C: 'Average',
    D: 'Poor',
};

const setBehaviourGrade = (row, grade) => {
    const next = String(grade || '').toUpperCase();
    const prevAuto = row?._autoComment || '';
    const currentComment = String(row?.comment || '');

    row.grade = next;

    const nextAuto = gradeDefaultComments[next] || '';
    row._autoComment = nextAuto;

    if (!currentComment || currentComment === prevAuto) {
        row.comment = nextAuto;
    }
};

const loadBehaviourRatings = async (ratedDate) => {
    if (!selectedStudent.value) return;

    behaviourError.value = '';
    isLoadingBehaviour.value = true;

    try {
        const params = new URLSearchParams();
        if (ratedDate) params.set('rated_date', ratedDate);

        const url = route('students.behaviour-ratings.index', selectedStudent.value.id) + (params.toString() ? `?${params.toString()}` : '');
        const res = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!res.ok) {
            throw new Error(`Request failed (${res.status})`);
        }

        const data = await res.json();
        const rows = Array.isArray(data.ratings) ? data.ratings : [];
        behaviourRatings.value = ensureBehaviourTemplate(rows);

        if (data && data.rated_date) {
            behaviourDate.value = data.rated_date;
        } else if (rows.length && rows[0].rated_date) {
            behaviourDate.value = rows[0].rated_date;
        } else {
            const today = new Date();
            behaviourDate.value = today.toISOString().slice(0, 10);
        }
    } catch (e) {
        behaviourRatings.value = ensureBehaviourTemplate([]);
        behaviourError.value = 'Could not load behaviour ratings.';
    } finally {
        isLoadingBehaviour.value = false;
    }
};

const saveBehaviourRatings = async () => {
    if (!selectedStudent.value || isSavingBehaviour.value) return;
    if (!canSaveBehaviour.value) {
        behaviourError.value = 'Please select grades (A/B/C/D) for all behaviours before saving.';
        return;
    }

    isSavingBehaviour.value = true;
    behaviourError.value = '';

    try {
        const payload = {
            rated_date: behaviourDate.value || null,
            ratings: behaviourRatings.value.map((r) => ({
                code: r.code,
                label: r.label,
                grade: r.grade,
                comment: r.comment || null,
            })),
        };

        const res = await fetch(route('students.behaviour-ratings.store', selectedStudent.value.id), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(payload),
        });

        if (!res.ok) {
            let message = '';
            try {
                const err = await res.json();
                if (err?.message) message = err.message;
                if (!message && err?.errors) {
                    const firstKey = Object.keys(err.errors)[0];
                    message = firstKey ? String(err.errors[firstKey]?.[0] || '') : '';
                }
            } catch (_) {
                try {
                    message = await res.text();
                } catch (_) {
                    message = '';
                }
            }

            behaviourError.value = `Save failed (${res.status}). ${message || 'Please try again.'}`;
            return;
        }

        await loadBehaviourRatings(behaviourDate.value);
    } catch (e) {
        behaviourError.value = 'Could not save behaviour ratings.';
    } finally {
        isSavingBehaviour.value = false;
    }
};

const printResultsPreview = () => {
    const el = document.getElementById('student-results-preview');
    if (!el) return;

    const w = window.open('', '_blank', 'noopener,noreferrer,width=900,height=650');
    if (!w) return;

    w.document.open();
    w.document.write(`
      <html>
        <head>
          <title>Student Results Preview</title>
          <style>
            body{font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; padding:16px;}
            table{width:100%; border-collapse:collapse; font-size:12px;}
            th,td{border:1px solid #e5e7eb; padding:6px;}
            th{background:#f9fafb; text-align:left;}
            .muted{color:#6b7280; font-size:12px;}
            .row{display:flex; justify-content:space-between; gap:12px; margin-top:10px; font-size:12px;}
            @media print{body{padding:0;}}
          </style>
        </head>
        <body>
          ${el.innerHTML}
        </body>
      </html>
    `);
    w.document.close();

    w.focus();
    w.print();
    w.close();
};
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
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Class</label>
                    <select
                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        v-model="classFilterModel"
                        @change="onClassChange"
                    >
                        <option value="">All</option>
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
        </template>

        <!-- Single delete confirmation modal -->
        <div
            v-if="showSingleDeleteConfirm && studentToDelete"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
                <div class="border-b border-gray-100 bg-gradient-to-r from-red-50 to-white px-5 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">Delete student</h3>
                    <p class="mt-1 text-[11px] text-gray-600">
                        You are about to delete
                        <span class="font-semibold text-gray-800">{{ studentToDelete.full_name }}</span>.
                        This action cannot be undone.
                    </p>
                </div>
                <div class="px-5 py-4">
                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-[11px] text-gray-700 ring-1 ring-gray-100">
                        <span class="font-medium">Exam No:</span>
                        <span class="font-mono">{{ studentToDelete.exam_number }}</span>
                    </div>
                    <div class="mt-4 flex justify-end gap-2 text-xs">
                        <button
                            type="button"
                            class="rounded-md bg-gray-50 px-3 py-1.5 font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                            :disabled="isDeletingSingle"
                            @click="closeSingleDeleteConfirm"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-red-600 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="isDeletingSingle"
                            @click="confirmDeleteStudent"
                        >
                            <span v-if="!isDeletingSingle">Delete</span>
                            <span v-else>Deleting...</span>
                        </button>
                    </div>
                </div>
            </div>
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
                            <th class="px-3 py-2">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="sortBy('exam_number')">
                                    <span>Exam Number</span>
                                    <span class="text-[10px] text-gray-400">{{ arrowFor('exam_number') }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-2">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="sortBy('full_name')">
                                    <span>Full Name</span>
                                    <span class="text-[10px] text-gray-400">{{ arrowFor('full_name') }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-2">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="sortBy('class_level')">
                                    <span>Class</span>
                                    <span class="text-[10px] text-gray-400">{{ arrowFor('class_level') }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-2">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="sortBy('stream')">
                                    <span>Stream</span>
                                    <span class="text-[10px] text-gray-400">{{ arrowFor('stream') }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-2">Subjects</th>
                            <th class="px-3 py-2">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="sortBy('gender')">
                                    <span>Gender</span>
                                    <span class="text-[10px] text-gray-400">{{ arrowFor('gender') }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="group in groupedStudents" :key="`group-` + group.class_level">
                            <tr class="bg-gray-50 text-xs font-semibold text-gray-700">
                                <td class="px-3 py-2" colspan="9">
                                    <div class="flex items-center justify-between">
                                        <span>{{ group.class_level }}</span>
                                        <span class="text-[11px] font-medium text-gray-500">
                                            {{ group.students.length }} student(s)
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <tr
                                v-for="(student, index) in group.students"
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
                                <td class="px-3 py-2 align-top text-xs text-gray-500">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-3 py-2 align-top font-mono text-xs text-gray-900">
                                    {{ student.exam_number }}
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <div class="font-medium text-gray-900">{{ student.full_name }}</div>
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <div class="font-medium text-gray-800">
                                        {{ student.class_level }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <div class="font-medium text-gray-800">
                                        {{ student.stream || '-' }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-if="student.subjects && student.subjects.length"
                                            v-for="sub in student.subjects"
                                            :key="sub.code"
                                            class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-emerald-100"
                                        >
                                            {{ sub.code }}
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
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-blue-50 text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100"
                                            title="View details"
                                            aria-label="View details"
                                            @click="openDetails(student)"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-violet-50 text-violet-700 ring-1 ring-violet-100 hover:bg-violet-100"
                                            title="Behaviour"
                                            aria-label="Behaviour"
                                            @click="openBehaviour(student)"
                                        >
                                            <ClipboardList class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                                            title="Edit"
                                            aria-label="Edit"
                                            @click="openEdit(student)"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-700 ring-1 ring-red-100 hover:bg-red-100"
                                            title="Delete"
                                            aria-label="Delete"
                                            @click="deleteStudent(student)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
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
                        <p v-if="newStudentErrors?.full_name" class="mt-1 text-[11px] font-medium text-red-600">
                            {{ newStudentErrors.full_name }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
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
                            <p v-if="newStudentErrors?.class_level" class="mt-1 text-[11px] font-medium text-red-600">
                                {{ newStudentErrors.class_level }}
                            </p>
                        </div>
                        <div v-if="addAvailableStreams.length">
                            <label class="mb-1 block font-medium">Stream</label>
                            <select
                                v-model="newStudent.stream"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option value="">Select stream</option>
                                <option v-for="s in addAvailableStreams" :key="`add-stream-` + s" :value="s">{{ s }}</option>
                            </select>
                            <p v-if="newStudentErrors?.stream" class="mt-1 text-[11px] text-red-600">
                                {{ newStudentErrors.stream }}
                            </p>
                        </div>
                        <div v-else-if="showAddStreamHelper" class="text-[11px] text-gray-500">
                            <label class="mb-1 block font-medium">Stream</label>
                            <div class="rounded-md border border-dashed border-gray-200 bg-gray-50 px-2 py-2">
                                No streams configured for this class.
                            </div>
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
                            <p v-if="newStudentErrors?.gender" class="mt-1 text-[11px] font-medium text-red-600">
                                {{ newStudentErrors.gender }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block font-medium">Parent Phone</label>
                        <input
                            type="tel"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="e.g. 07XXXXXXXX"
                            v-model="newStudent.guardian_phone"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block font-medium">Parent/Guardian Name</label>
                        <input
                            v-model="newStudent.guardian_name"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="e.g. Mary Doe"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block font-medium">Address (optional)</label>
                        <input
                            v-model="newStudent.guardian_address"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="e.g. Mtaa wa ..., Mkoa ..."
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
            <div class="w-full max-w-6xl rounded-xl bg-white p-5 shadow-xl">
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

                <div class="mb-4 flex flex-wrap gap-2 border-b border-gray-100 pb-3 text-[11px]">
                    <button
                        type="button"
                        class="rounded-md px-3 py-1.5 font-semibold"
                        :class="activeDetailsTab === 'personal' ? 'bg-emerald-600 text-white' : 'bg-gray-50 text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100'"
                        @click="activeDetailsTab = 'personal'"
                    >
                        Personal Details
                    </button>
                    <button
                        type="button"
                        class="rounded-md px-3 py-1.5 font-semibold"
                        :class="activeDetailsTab === 'results' ? 'bg-emerald-600 text-white' : 'bg-gray-50 text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100'"
                        @click="activeDetailsTab = 'results'"
                    >
                        Results
                    </button>
                    <button
                        type="button"
                        class="rounded-md px-3 py-1.5 font-semibold"
                        :class="activeDetailsTab === 'behaviours' ? 'bg-emerald-600 text-white' : 'bg-gray-50 text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100'"
                        @click="activeDetailsTab = 'behaviours'"
                    >
                        Behaviours
                    </button>
                </div>

                <div v-if="activeDetailsTab === 'personal'" class="grid gap-4 md:grid-cols-2 text-xs text-gray-700">
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
                            Guardian & Contacts
                        </h4>
                        <div class="space-y-1">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-600">Guardian Name:</span>
                                <span class="text-gray-800">{{ selectedStudent.guardian_name || '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-600">Guardian Phone:</span>
                                <span class="text-gray-800">{{ selectedStudent.guardian_phone || '—' }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="font-medium text-gray-600">Address:</span>
                                <span class="text-right text-gray-800">{{ selectedStudent.guardian_address || '—' }}</span>
                            </div>
                        </div>
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
                                {{ sub.code }}
                            </span>
                            <span
                                v-if="!selectedStudent.subjects || !selectedStudent.subjects.length"
                                class="text-[11px] text-gray-400"
                            >
                                Subjects not configured yet.
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="activeDetailsTab === 'results'" class="space-y-3 text-xs text-gray-700">
                    <div class="flex items-center justify-end">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-2 py-1 font-semibold text-white shadow-sm hover:bg-emerald-700"
                            @click="printResultsPreview"
                        >
                            Print Preview
                        </button>
                    </div>

                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                        <div id="student-results-preview">
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
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-700">Exam:</span>
                                        <select
                                            v-model="selectedExamId"
                                            @change="onExamChange"
                                            class="rounded-md border border-gray-300 px-2 py-0.5 text-[10px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                        >
                                            <option value="">Latest exam</option>
                                            <option
                                                v-for="exam in exams"
                                                :key="exam.id"
                                                :value="String(exam.id)"
                                            >
                                                {{ exam.name }} ({{ exam.academic_year }})
                                            </option>
                                        </select>
                                    </div>
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
                                </div>
                            </div>

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
                                        <tr v-if="isLoadingResultsPreview" class="text-gray-500">
                                            <td colspan="3" class="border-t border-gray-100 px-2 py-2 text-center">Loading results...</td>
                                        </tr>
                                        <tr v-else-if="resultsPreviewError" class="text-red-600">
                                            <td colspan="3" class="border-t border-gray-100 px-2 py-2 text-center">{{ resultsPreviewError }}</td>
                                        </tr>
                                        <tr v-else-if="!resultsPreview.length" class="text-gray-500">
                                            <td colspan="3" class="border-t border-gray-100 px-2 py-2 text-center">No results found for this exam.</td>
                                        </tr>
                                        <tr v-else v-for="row in resultsPreview" :key="row.subject_code" class="text-gray-700">
                                            <td class="border-t border-gray-100 px-2 py-1">{{ row.subject_code || row.subject_name || '—' }}</td>
                                            <td class="border-t border-gray-100 px-2 py-1 text-center">{{ row.marks ?? '—' }}</td>
                                            <td class="border-t border-gray-100 px-2 py-1 text-center">{{ row.grade ?? '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-if="resultsPreviewSummary && (resultsPreviewSummary.total !== null || resultsPreviewSummary.average !== null)" class="mt-2 flex justify-between text-[10px] text-gray-600">
                                    <div>
                                        <span class="font-semibold text-gray-700">Total:</span>
                                        <span class="ml-1">{{ resultsPreviewSummary.total ?? '—' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-gray-700">Average:</span>
                                        <span class="ml-1">{{ resultsPreviewSummary.average ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="activeDetailsTab === 'behaviours'" class="space-y-3 text-xs text-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="text-[11px] text-gray-500">
                            View only. To edit, use the Behaviour button in Actions.
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-semibold text-gray-700">Date:</span>
                            <input
                                v-model="behaviourDate"
                                type="date"
                                class="rounded-md border border-gray-300 px-2 py-0.5 text-[10px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                @change="onBehaviourDateChange"
                            />
                        </div>
                    </div>

                    <div v-if="behaviourError" class="text-[11px] font-medium text-red-600">
                        {{ behaviourError }}
                    </div>

                    <div class="overflow-hidden rounded-md border border-gray-200">
                        <table class="w-full border-collapse text-[11px]">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600">
                                    <th class="px-2 py-1 text-left">Code</th>
                                    <th class="px-2 py-1 text-left">Behaviour</th>
                                    <th class="px-2 py-1 text-center">Grade</th>
                                    <th class="px-2 py-1 text-left">Comment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="isLoadingBehaviour" class="text-gray-500">
                                    <td colspan="4" class="border-t border-gray-100 px-2 py-2 text-center">Loading behaviour ratings...</td>
                                </tr>
                                <tr
                                    v-else
                                    v-for="row in behaviourRatings"
                                    :key="row.code"
                                    class="text-gray-700"
                                >
                                    <td class="border-t border-gray-100 px-2 py-1 font-mono">{{ row.code }}</td>
                                    <td class="border-t border-gray-100 px-2 py-1">{{ row.label }}</td>
                                    <td class="border-t border-gray-100 px-2 py-1 text-center font-semibold">{{ row.grade }}</td>
                                    <td class="border-t border-gray-100 px-2 py-1">{{ row.comment || '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 flex justify-between gap-2">
                    <div></div>
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

        <!-- Behaviour modal (edit) -->
        <div
            v-if="showBehaviourModal && selectedStudent"
            class="fixed inset-0 z-30 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-6xl rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">
                            Behaviour Ratings
                        </h3>
                        <p class="mt-0.5 text-[11px] text-gray-500">
                            Set behaviour grades and optional comments for {{ selectedStudent.full_name }}.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                        @click="closeBehaviour"
                    >
                        ✕
                    </button>
                </div>

                <div class="mb-3 flex flex-wrap items-center justify-between gap-2 text-[11px]">
                    <div class="text-gray-600">
                        <span class="font-semibold text-gray-700">Exam No:</span>
                        <span class="ml-1 font-mono">{{ selectedStudent.exam_number }}</span>
                        <span class="mx-2 text-gray-300">|</span>
                        <span class="font-semibold text-gray-700">Class:</span>
                        <span class="ml-1">{{ selectedStudent.class_level }}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-semibold text-gray-700">Date:</span>
                            <input
                                v-model="behaviourDate"
                                type="date"
                                class="rounded-md border border-gray-300 px-2 py-0.5 text-[10px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                @change="onBehaviourDateChange"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="behaviourError" class="mb-2 text-[11px] font-medium text-red-600">
                    {{ behaviourError }}
                </div>

                <div class="overflow-hidden rounded-md border border-gray-200">
                    <table class="w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600">
                                <th class="px-2 py-1 text-left">Code</th>
                                <th class="px-2 py-1 text-left">Behaviour</th>
                                <th class="px-2 py-1 text-center">Grade</th>
                                <th class="px-2 py-1 text-left">Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="isLoadingBehaviour" class="text-gray-500">
                                <td colspan="4" class="border-t border-gray-100 px-2 py-2 text-center">Loading behaviour ratings...</td>
                            </tr>
                            <tr
                                v-else
                                v-for="row in behaviourRatings"
                                :key="row.code"
                                class="text-gray-700"
                            >
                                <td class="border-t border-gray-100 px-2 py-1 font-mono">{{ row.code }}</td>
                                <td class="border-t border-gray-100 px-2 py-1">{{ row.label }}</td>
                                <td class="border-t border-gray-100 px-2 py-1 text-center">
                                    <div class="inline-flex overflow-hidden rounded-md ring-1 ring-gray-200">
                                        <button
                                            type="button"
                                            class="px-2 py-0.5 text-[10px] font-semibold"
                                            :class="row.grade === 'A' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                            @click="setBehaviourGrade(row, 'A')"
                                        >
                                            A
                                        </button>
                                        <button
                                            type="button"
                                            class="px-2 py-0.5 text-[10px] font-semibold"
                                            :class="row.grade === 'B' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                            @click="setBehaviourGrade(row, 'B')"
                                        >
                                            B
                                        </button>
                                        <button
                                            type="button"
                                            class="px-2 py-0.5 text-[10px] font-semibold"
                                            :class="row.grade === 'C' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                            @click="setBehaviourGrade(row, 'C')"
                                        >
                                            C
                                        </button>
                                        <button
                                            type="button"
                                            class="px-2 py-0.5 text-[10px] font-semibold"
                                            :class="row.grade === 'D' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                            @click="setBehaviourGrade(row, 'D')"
                                        >
                                            D
                                        </button>
                                    </div>
                                </td>
                                <td class="border-t border-gray-100 px-2 py-1">
                                    <input
                                        v-model="row.comment"
                                        type="text"
                                        class="w-full rounded-md border border-gray-200 px-2 py-1 text-[10px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                        placeholder="Optional comment"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="closeBehaviour"
                    >
                        Close
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="isSavingBehaviour || isLoadingBehaviour || !canSaveBehaviour"
                        @click="saveBehaviourRatings"
                    >
                        <span v-if="!isSavingBehaviour">Save Behaviour</span>
                        <span v-else>Saving...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Student modal (UI only) -->
        <div
            v-if="showEditModal && selectedStudent"
            class="fixed inset-0 z-30 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
                <div class="flex items-start justify-between border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-white px-5 py-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Edit Student</h3>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-gray-600">
                            <span class="rounded-full bg-white px-2 py-0.5 font-mono text-gray-700 ring-1 ring-gray-200">
                                {{ selectedStudent.exam_number }}
                            </span>
                            <span class="text-gray-300">|</span>
                            <span class="font-medium text-gray-700">{{ selectedStudent.full_name }}</span>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="rounded-full p-1 text-gray-400 hover:bg-white hover:text-gray-600"
                        @click="closeEdit"
                    >
                        ✕
                    </button>
                </div>

                <form class="space-y-4 px-5 py-4 text-xs text-gray-700" @submit.prevent="updateStudent">
                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block font-medium">Full Name</label>
                            <input
                                v-model="editStudent.full_name"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="e.g. John Doe"
                            />
                            <p v-if="editStudentErrors.full_name" class="mt-1 text-[11px] font-medium text-red-600">
                                {{ editStudentErrors.full_name }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block font-medium">Class Level</label>
                            <select
                                v-model="editStudent.class_level"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option value="">Select class</option>
                                <option v-for="cls in classes" :key="`edit-` + cls" :value="cls">{{ cls }}</option>
                            </select>
                            <p v-if="editStudentErrors.class_level" class="mt-1 text-[11px] font-medium text-red-600">
                                {{ editStudentErrors.class_level }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block font-medium">Stream</label>
                            <div v-if="editAvailableStreams.length">
                                <select
                                    v-model="editStudent.stream"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                >
                                    <option value="">Select stream</option>
                                    <option v-for="s in editAvailableStreams" :key="`stream-` + s" :value="s">{{ s }}</option>
                                </select>
                                <p v-if="editStudentErrors.stream" class="mt-1 text-[11px] font-medium text-red-600">
                                    {{ editStudentErrors.stream }}
                                </p>
                            </div>
                            <div v-else-if="showEditStreamHelper" class="rounded-md border border-dashed border-gray-200 bg-gray-50 px-3 py-2 text-[11px] text-gray-500">
                                No streams configured for this class.
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block font-medium">Assigned Subjects</label>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-if="editAssignedSubjects && editAssignedSubjects.length"
                                    v-for="sub in editAssignedSubjects"
                                    :key="sub.code"
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-100"
                                >
                                    {{ sub.code }}
                                </span>
                                <span
                                    v-if="!editAssignedSubjects || !editAssignedSubjects.length"
                                    class="text-[11px] text-gray-400"
                                >
                                    —
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block font-medium">Gender</label>
                            <select
                                v-model="editStudent.gender"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option value="">Not set</option>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select>
                            <p v-if="editStudentErrors.gender" class="mt-1 text-[11px] font-medium text-red-600">
                                {{ editStudentErrors.gender }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block font-medium">Date of Birth</label>
                            <input
                                v-model="editStudent.date_of_birth"
                                type="date"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            />
                            <p v-if="editStudentErrors.date_of_birth" class="mt-1 text-[11px] font-medium text-red-600">
                                {{ editStudentErrors.date_of_birth }}
                            </p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Guardian Details (optional)</p>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block font-medium text-gray-700">Guardian Name</label>
                                <input
                                    v-model="editStudent.guardian_name"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="e.g. Mary Doe"
                                />
                                <p v-if="editStudentErrors.guardian_name" class="mt-1 text-[11px] font-medium text-red-600">
                                    {{ editStudentErrors.guardian_name }}
                                </p>
                            </div>
                            <div>
                                <label class="mb-1 block font-medium text-gray-700">Guardian Phone</label>
                                <input
                                    v-model="editStudent.guardian_phone"
                                    type="tel"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="e.g. 07XXXXXXXX"
                                />
                                <p v-if="editStudentErrors.guardian_phone" class="mt-1 text-[11px] font-medium text-red-600">
                                    {{ editStudentErrors.guardian_phone }}
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block font-medium text-gray-700">Address</label>
                                <input
                                    v-model="editStudent.guardian_address"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="e.g. Mtaa wa ..., Mkoa ..."
                                />
                                <p v-if="editStudentErrors.guardian_address" class="mt-1 text-[11px] font-medium text-red-600">
                                    {{ editStudentErrors.guardian_address }}
                                </p>
                            </div>
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
                                {{ sub.code }}
                            </span>
                            <span
                                v-if="!selectedStudent.subjects || !selectedStudent.subjects.length"
                                class="text-[11px] text-gray-400"
                            >
                                —
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4">
                        <button
                            type="button"
                            class="rounded-md bg-gray-50 px-3 py-2 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                            @click="closeEdit"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="isUpdatingStudent"
                        >
                            <span v-if="!isUpdatingStudent">Save Changes</span>
                            <span v-else>Saving...</span>
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
