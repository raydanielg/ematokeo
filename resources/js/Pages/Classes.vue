<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Alert from '@/Components/Alert.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import { BookOpen, Layers, Pencil, Trash2, X } from 'lucide-vue-next';

const props = defineProps({
    classes: {
        type: Array,
        default: () => [],
    },
    streams: {
        type: Array,
        default: () => [],
    },
    subjects: {
        type: Array,
        default: () => [],
    },
    schoolLevel: {
        type: String,
        default: '',
    },
});

const rows = reactive(
    props.classes.map((c) => ({
        id: c.id,
        name: c.name,
        level: c.level,
        description: c.description,
        subject_ids: [...c.subject_ids],
    })),
);

watch(
    () => props.classes,
    (newClasses) => {
        // Replace rows content with latest classes from backend
        rows.splice(
            0,
            rows.length,
            ...newClasses.map((c) => ({
                id: c.id,
                name: c.name,
                level: c.level,
                description: c.description,
                subject_ids: [...c.subject_ids],
            })),
        );
    },
);

const selectedIds = ref([]);
const isBulkDeleting = ref(false);
const isImportingClasses = ref(false);

const showSubjectsModal = ref(false);
const subjectsModalClass = ref(null);

const showStreamsModal = ref(false);
const streamsModalBaseClass = ref(null);
const activeStreamTab = ref('');

const streamForm = reactive({
    stream: '',
    subject_ids: [],
});
const isSavingStream = ref(false);

const toast = reactive({
    show: false,
    variant: 'success',
    title: '',
    message: '',
});

let toastTimer = null;

const showToast = (variant, title, message) => {
    toast.variant = variant;
    toast.title = title;
    toast.message = message;
    toast.show = true;

    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.show = false;
    }, 3500);
};

const isLevelLockedToSchool = computed(() => !!props.schoolLevel);

const subjectsById = computed(() => {
    const map = new Map();
    (props.subjects || []).forEach((s) => map.set(String(s.id), s));
    return map;
});

const streamsPerClass = computed(() => {
    const map = {};
    (props.streams || []).forEach((s) => {
        const key = String(s.parent_class_id || '');
        if (!key) return;
        if (!map[key]) map[key] = [];
        map[key].push(s);
    });
    return map;
});

const openSubjectsModal = (row) => {
    subjectsModalClass.value = row;
    showSubjectsModal.value = true;
};

const closeSubjectsModal = () => {
    showSubjectsModal.value = false;
    subjectsModalClass.value = null;
};

const openStreamsModal = (row) => {
    streamsModalBaseClass.value = row;
    showStreamsModal.value = true;

    const items = streamsPerClass.value[String(row.id)] || [];
    activeStreamTab.value = items.length ? String(items[0].id) : 'new';
    setActiveStreamTab(activeStreamTab.value);
};

const closeStreamsModal = () => {
    showStreamsModal.value = false;
    streamsModalBaseClass.value = null;
    activeStreamTab.value = '';
    streamForm.stream = '';
    streamForm.subject_ids = [];
    isSavingStream.value = false;
};

const setActiveStreamTab = (id) => {
    activeStreamTab.value = id;
    if (id === 'new') {
        streamForm.stream = '';
        streamForm.subject_ids = [];
        return;
    }

    const items = streamsPerClass.value[String(streamsModalBaseClass.value?.id || '')] || [];
    const stream = items.find((s) => String(s.id) === String(id));
    if (!stream) return;
    streamForm.stream = stream.stream || '';
    streamForm.subject_ids = Array.isArray(stream.subject_ids) ? [...stream.subject_ids] : [];
};

const toggleStreamSubject = (subjectId) => {
    const idx = streamForm.subject_ids.indexOf(subjectId);
    if (idx === -1) {
        streamForm.subject_ids.push(subjectId);
    } else {
        streamForm.subject_ids.splice(idx, 1);
    }
};

const saveStream = () => {
    if (!streamsModalBaseClass.value || isSavingStream.value) return;
    if (!streamForm.stream || !streamForm.subject_ids.length) return;

    isSavingStream.value = true;

    const payload = {
        class_id: Number(streamsModalBaseClass.value.id),
        stream: streamForm.stream,
        subject_ids: streamForm.subject_ids,
    };

    if (activeStreamTab.value && activeStreamTab.value !== 'new') {
        router.post(route('streams.update', activeStreamTab.value), payload, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['streams'] });
            },
            onFinish: () => {
                isSavingStream.value = false;
            },
        });
    } else {
        router.post(route('streams.store'), payload, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['streams'] });
            },
            onFinish: () => {
                isSavingStream.value = false;
            },
        });
    }
};

const deleteStream = (streamId) => {
    if (!streamId) return;
    openConfirm('delete-stream', String(streamId), 'Are you sure you want to delete this stream? This action cannot be undone.');
};

// New state for single-class subject assignment panel
const selectedClassId = ref(null);
const selectedClassSubjectIds = ref([]);
const isSavingSingleAssignment = ref(false);

const showForm = ref(false);
const editingId = ref(null);
const form = reactive({
    name: '',
    description: '',
    level: '', // Will be set to schoolLevel automatically
});
const formSubjectIds = ref([]);
const classSaveError = ref(null);
const isSavingClass = ref(false);

const showConfirmModal = ref(false);
const confirmMessage = ref('');
const confirmActionType = ref(null); // 'delete-single' | 'delete-bulk'
const confirmPayload = ref(null);

// Core subjects (same list as SchoolClassSeeder::$coreSubjectCodes)
const coreSubjectCodes = [
    'CIV',
    'HIS',
    'GEO',
    'KIS',
    'ENG',
    'PHY',
    'CHE',
    'BIO',
    'B/MAT',
];

const coreSubjectIds = computed(() =>
    props.subjects
        .filter((s) => coreSubjectCodes.includes(s.subject_code))
        .map((s) => s.id),
);

const openCreate = () => {
    editingId.value = null;
    form.name = '';
    form.description = '';
    form.level = props.schoolLevel; // Set to school level automatically
    formSubjectIds.value = [];
    classSaveError.value = null;
    showForm.value = true;
};

const initializeSelectedClass = (classId) => {
    const row = rows.find((r) => r.id === classId);
    if (!row) {
        selectedClassSubjectIds.value = [];
        return;
    }

    if (row.subject_ids && row.subject_ids.length > 0) {
        selectedClassSubjectIds.value = [...row.subject_ids];
    } else {
        // If class has no subjects yet, pre-select core subjects by default
        selectedClassSubjectIds.value = [...coreSubjectIds.value];
    }
};

watch(
    () => selectedClassId.value,
    (newId) => {
        if (!newId) {
            selectedClassSubjectIds.value = [];
            return;
        }
        initializeSelectedClass(newId);
    },
);

const openEdit = (row) => {
    editingId.value = row.id;
    form.name = row.name;
    form.description = row.description || '';
    formSubjectIds.value = [...row.subject_ids];
    classSaveError.value = null;
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
};

const openConfirm = (type, payload, message) => {
    confirmActionType.value = type;
    confirmPayload.value = payload;
    confirmMessage.value = message;
    showConfirmModal.value = true;
};

const closeConfirm = () => {
    showConfirmModal.value = false;
    confirmActionType.value = null;
    confirmPayload.value = null;
    confirmMessage.value = '';
};

const toggleRowSelection = (id) => {
    const idx = selectedIds.value.indexOf(id);
    if (idx === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(idx, 1);
    }
};

const allSelected = () => {
    return rows.length > 0 && selectedIds.value.length === rows.length;
};

const toggleSelectAll = () => {
    if (allSelected()) {
        selectedIds.value = [];
    } else {
        selectedIds.value = rows.map((r) => r.id);
    }
};

const saveClass = () => {
    isSavingClass.value = true;
    classSaveError.value = null;

    if (editingId.value) {
        router.post(
            route('classes.update', editingId.value),
            {
                name: form.name,
                // level is now derived from the school's level on the backend
                description: form.description,
                subject_ids: formSubjectIds.value,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeForm();
                    router.reload({ only: ['classes'] });
                },
                onError: (errors) => {
                    classSaveError.value = Object.values(errors || {}).join(' ');
                },
                onFinish: () => {
                    isSavingClass.value = false;
                },
            },
        );
    } else {
        router.post(
            route('classes.store'),
            {
                name: form.name,
                // level is now derived from the school's level on the backend
                description: form.description,
                subject_ids: formSubjectIds.value,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeForm();
                    router.reload({ only: ['classes'] });
                },
                onError: (errors) => {
                    classSaveError.value = Object.values(errors || {}).join(' ');
                },
                onFinish: () => {
                    isSavingClass.value = false;
                },
            },
        );
    }
};

const deleteClass = (id) => {
    openConfirm('delete-single', id, 'Are you sure you want to delete this class? This action cannot be undone.');
};

const bulkDelete = () => {
    if (!selectedIds.value.length) return;
    openConfirm('delete-bulk', [...selectedIds.value], 'Are you sure you want to delete all selected classes? This action cannot be undone.');
};

const confirmImport = () => {
    const levelToImport = props.schoolLevel || '';
    if (!levelToImport) {
        showToast('danger', 'Missing school level', 'Please set your school level in School Information first.');
        return;
    }
    if (isImportingClasses.value) return;

    isImportingClasses.value = true;

    router.post(
        route('classes.bulk-create-standard'),
        { level: levelToImport },
        {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['classes'] });
                showToast('success', 'Import complete', 'Classes have been imported and updated in the table.');
            },
            onError: () => {
                showToast('danger', 'Import failed', 'Something went wrong while importing classes. Please try again.');
            },
            onFinish: () => {
                isImportingClasses.value = false;
            },
        },
    );
};

const confirmProceed = () => {
    if (confirmActionType.value === 'delete-single' && confirmPayload.value) {
        router.delete(route('classes.destroy', confirmPayload.value), {
            preserveScroll: true,
        });
    } else if (confirmActionType.value === 'delete-bulk' && Array.isArray(confirmPayload.value)) {
        isBulkDeleting.value = true;

        router.post(
            route('classes.bulk-delete'),
            { ids: confirmPayload.value },
            {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['classes'] });
                },
                onFinish: () => {
                    isBulkDeleting.value = false;
                    selectedIds.value = [];
                },
            },
        );
    } else if (confirmActionType.value === 'delete-stream' && confirmPayload.value) {
        router.delete(route('streams.destroy', confirmPayload.value), {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['streams'] });
            },
        });
    }

    closeConfirm();
};

const toggleSelectedClassSubject = (subjectId) => {
    const idx = selectedClassSubjectIds.value.indexOf(subjectId);
    if (idx === -1) {
        selectedClassSubjectIds.value.push(subjectId);
    } else {
        selectedClassSubjectIds.value.splice(idx, 1);
    }
};

const saveSelectedClassAssignments = () => {
    if (!selectedClassId.value || isSavingSingleAssignment.value) return;

    isSavingSingleAssignment.value = true;

    const payload = [
        {
            class_id: selectedClassId.value,
            subject_ids: selectedClassSubjectIds.value,
        },
    ];

    router.post(
        '/classes/assign-subjects',
        { assignments: payload },
        {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['classes'] });
            },
            onFinish: () => {
                isSavingSingleAssignment.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="Classes" />

    <AuthenticatedLayout>
        <div v-if="toast.show" class="pointer-events-none fixed right-4 top-4 z-50 w-full max-w-sm">
            <div class="pointer-events-auto">
                <Alert :variant="toast.variant" :title="toast.title" :message="toast.message" />
            </div>
        </div>

        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Classes
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage classes and inline assign subjects to each class.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                        @click="openCreate"
                    >
                        + Add Class
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 ring-1 ring-red-100 hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="!selectedIds.length || isBulkDeleting"
                        @click="bulkDelete"
                    >
                        <span v-if="isBulkDeleting" class="inline-flex items-center gap-1">
                            <span
                                class="inline-flex h-3 w-3 animate-spin rounded-full border border-red-500 border-t-transparent"
                            ></span>
                            <span>Deleting...</span>
                        </span>
                        <span v-else>Bulk Delete</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <!-- Single-class subject assignment panel -->
            <div class="mb-5 flex flex-col gap-3 border-b border-gray-100 pb-4 text-xs text-gray-700">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex flex-col gap-1">
                        <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Assign Subjects to a Class
                        </span>
                        <span class="text-[11px] text-gray-500">
                            Chagua darasa kisha weka alama kwenye masomo unayotaka lifundishwe; masomo ya msingi
                            yatajiwekwa alama kiotomatiki kama darasa halina masomo bado.
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div class="w-full max-w-xs">
                        <label class="mb-1 block text-[11px] font-medium text-gray-700">Select Class</label>
                        <select
                            v-model="selectedClassId"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="" disabled>Select class...</option>
                            <option
                                v-for="c in rows"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }} (ID #{{ c.id }})
                            </option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="!selectedClassId || isSavingSingleAssignment"
                            @click="saveSelectedClassAssignments"
                        >
                            <span v-if="!isSavingSingleAssignment">Save Assignments</span>
                            <span v-else>Saving...</span>
                        </button>
                    </div>
                </div>

                <div v-if="selectedClassId" class="mt-2 rounded-md bg-gray-50 p-3">
                    <p class="mb-2 text-[11px] font-medium text-gray-700">Subjects</p>
                    <div class="flex flex-wrap gap-2">
                        <label
                            v-for="subject in subjects"
                            :key="subject.id"
                            class="inline-flex items-center gap-1 rounded-full border px-2 py-1 text-[10px] font-medium"
                            :class="
                                selectedClassSubjectIds.includes(subject.id)
                                    ? 'border-emerald-500 bg-emerald-50 text-emerald-800'
                                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
                            "
                        >
                            <input
                                type="checkbox"
                                class="h-3 w-3 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                :value="subject.id"
                                :checked="selectedClassSubjectIds.includes(subject.id)"
                                @change="toggleSelectedClassSubject(subject.id)"
                            />
                            <span>{{ subject.subject_code }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="w-10 px-3 py-2">
                                <input
                                    type="checkbox"
                                    class="h-3 w-3 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                    :checked="allSelected()"
                                    @change="toggleSelectAll"
                                />
                            </th>
                            <th class="w-16 px-3 py-2">ID No</th>
                            <th class="px-3 py-2">Class</th>
                            <th class="px-3 py-2">Subjects Assigned</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="rows.length === 0">
                            <td colspan="4" class="px-3 py-4 text-center text-xs text-gray-500">
                                No classes defined yet.
                            </td>
                        </tr>
                        <tr
                            v-for="row in rows"
                            :key="row.id"
                            class="border-b border-gray-100 text-xs text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top">
                                <input
                                    type="checkbox"
                                    class="h-3 w-3 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                    :checked="selectedIds.includes(row.id)"
                                    @change="toggleRowSelection(row.id)"
                                />
                            </td>
                            <td class="px-3 py-2 align-top">
                                <span class="text-xs text-gray-500">#{{ row.id }}</span>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-col">
                                    <span class="text-gray-800">{{ row.name }}</span>
                                    <span v-if="row.description" class="text-[10px] text-gray-500">
                                        {{ row.description }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <button
                                    type="button"
                                    class="text-left text-[11px] font-medium text-emerald-700 hover:text-emerald-800"
                                    @click="openSubjectsModal(row)"
                                >
                                    {{
                                        subjects
                                            .filter((s) =>
                                                row.subject_ids
                                                    .map((id) => String(id))
                                                    .includes(String(s.id)),
                                            )
                                            .map((s) => s.subject_code)
                                            .join(', ') || 'None'
                                    }}
                                </button>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-violet-50 text-violet-700 ring-1 ring-violet-100 hover:bg-violet-100"
                                        title="Streams"
                                        aria-label="Streams"
                                        @click="openStreamsModal(row)"
                                    >
                                        <Layers class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                                        title="Edit"
                                        aria-label="Edit"
                                        @click="openEdit(row)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-700 ring-1 ring-red-100 hover:bg-red-100"
                                        title="Delete"
                                        aria-label="Delete"
                                        @click="deleteClass(row.id)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Subjects modal (view) -->
        <div
            v-if="showSubjectsModal && subjectsModalClass"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
                <div class="flex items-start justify-between border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-white px-5 py-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Subjects</h3>
                        <p class="mt-1 text-[11px] text-gray-600">
                            {{ subjectsModalClass.name }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-full p-1 text-gray-400 hover:bg-white hover:text-gray-600"
                        @click="closeSubjectsModal"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="px-5 py-4">
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="id in (subjectsModalClass.subject_ids || [])"
                            :key="`sub-` + id"
                            class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-medium text-emerald-700 ring-1 ring-emerald-100"
                        >
                            {{ subjectsById.get(String(id))?.subject_code || id }}
                        </span>
                        <span
                            v-if="!(subjectsModalClass.subject_ids && subjectsModalClass.subject_ids.length)"
                            class="text-xs text-gray-500"
                        >
                            No subjects assigned.
                        </span>
                    </div>
                </div>

                <div class="flex justify-end border-t border-gray-100 px-5 py-3">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="closeSubjectsModal"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Streams modal (tabbed) -->
        <div
            v-if="showStreamsModal && streamsModalBaseClass"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
                <div class="flex items-start justify-between border-b border-gray-100 bg-gradient-to-r from-violet-50 to-white px-5 py-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Streams</h3>
                        <p class="mt-1 text-[11px] text-gray-600">
                            Manage streams for {{ streamsModalBaseClass.name }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-full p-1 text-gray-400 hover:bg-white hover:text-gray-600"
                        @click="closeStreamsModal"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="border-b border-gray-100 px-5 py-3">
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="s in (streamsPerClass[String(streamsModalBaseClass.id)] || [])"
                            :key="`tab-` + s.id"
                            type="button"
                            class="rounded-md px-3 py-1.5 text-[11px] font-semibold"
                            :class="activeStreamTab === String(s.id) ? 'bg-violet-600 text-white' : 'bg-gray-50 text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100'"
                            @click="setActiveStreamTab(String(s.id))"
                        >
                            {{ s.stream || 'Stream' }}
                        </button>
                        <button
                            type="button"
                            class="rounded-md px-3 py-1.5 text-[11px] font-semibold"
                            :class="activeStreamTab === 'new' ? 'bg-violet-600 text-white' : 'bg-gray-50 text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100'"
                            @click="setActiveStreamTab('new')"
                        >
                            + New
                        </button>
                    </div>
                </div>

                <div class="grid gap-4 px-5 py-4 md:grid-cols-2">
                    <div class="space-y-3 text-xs text-gray-700">
                        <div>
                            <label class="mb-1 block font-medium">Stream name</label>
                            <input
                                v-model="streamForm.stream"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-xs focus:border-violet-500 focus:outline-none focus:ring-violet-500"
                                placeholder="e.g. A, B, Science"
                            />
                        </div>

                        <div>
                            <div class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                                Subjects (minimum 1)
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <label
                                    v-for="subject in subjects"
                                    :key="`stream-sub-` + subject.id"
                                    class="inline-flex items-center gap-1 rounded-full border px-2 py-1 text-[10px] font-medium"
                                    :class="
                                        streamForm.subject_ids.includes(subject.id)
                                            ? 'border-violet-500 bg-violet-50 text-violet-800'
                                            : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
                                    "
                                >
                                    <input
                                        type="checkbox"
                                        class="h-3 w-3 rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                                        :value="subject.id"
                                        :checked="streamForm.subject_ids.includes(subject.id)"
                                        @change="toggleStreamSubject(subject.id)"
                                    />
                                    <span>{{ subject.subject_code }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700">
                            <div class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                                Current stream subjects
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="id in streamForm.subject_ids"
                                    :key="`pill-` + id"
                                    class="inline-flex items-center rounded-full bg-white px-2 py-0.5 text-[10px] font-medium text-gray-700 ring-1 ring-gray-200"
                                >
                                    {{ subjectsById.get(String(id))?.subject_code || id }}
                                </span>
                                <span v-if="!streamForm.subject_ids.length" class="text-[11px] text-gray-500">
                                    Select subjects on the left.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3">
                    <button
                        v-if="activeStreamTab && activeStreamTab !== 'new'"
                        type="button"
                        class="rounded-md bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 ring-1 ring-red-200 hover:bg-red-100"
                        @click="deleteStream(activeStreamTab)"
                    >
                        Delete stream
                    </button>
                    <div v-else></div>

                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                            @click="closeStreamsModal"
                        >
                            Close
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="isSavingStream || !streamForm.stream || !streamForm.subject_ids.length"
                            @click="saveStream"
                        >
                            <span v-if="!isSavingStream">Save</span>
                            <span v-else>Saving...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pop-up modal for create/edit class -->
        <div
            v-if="showForm"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4"
        >
            <div class="w-full max-w-lg rounded-lg bg-white p-5 shadow-lg">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800">
                        {{ editingId ? 'Edit Class' : 'Add Class' }}
                    </h3>
                    <button
                        type="button"
                        class="text-sm text-gray-400 hover:text-gray-600"
                        @click="closeForm"
                    >
                        ✕
                    </button>
                </div>

                <form class="space-y-3 text-xs text-gray-700" @submit.prevent="saveClass">
                    <Alert
                        v-if="classSaveError"
                        variant="danger"
                        title="Could not save class"
                        :message="classSaveError"
                    />

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="mb-1 block font-medium">Class Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="e.g. Form I A"
                                required
                            />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block font-medium">Assign Subjects</label>
                        <div class="flex flex-wrap gap-1">
                            <button
                                v-for="subject in subjects"
                                :key="subject.id"
                                type="button"
                                class="rounded-full px-2 py-0.5 text-[10px] font-medium ring-1"
                                :class="
                                    formSubjectIds.includes(subject.id)
                                        ? 'bg-emerald-600 text-white ring-emerald-600'
                                        : 'bg-white text-gray-700 ring-gray-200 hover:bg-gray-50'
                                "
                                @click="
                                    formSubjectIds = formSubjectIds.includes(subject.id)
                                        ? formSubjectIds.filter((id) => id !== subject.id)
                                        : [...formSubjectIds, subject.id]
                                "
                            >
                                {{ subject.subject_code }}
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                            @click="closeForm"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="isSavingClass"
                        >
                            <span v-if="isSavingClass">Saving...</span>
                            <span v-else>Save</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Generic confirmation modal -->
        <div
            v-if="showConfirmModal"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4"
        >
            <div class="w-full max-w-sm rounded-lg bg-white p-5 shadow-lg">
                <h3 class="mb-2 text-sm font-semibold text-gray-800">
                    Please confirm
                </h3>
                <p class="mb-4 text-xs text-gray-600">
                    {{ confirmMessage }}
                </p>
                <div class="mt-2 flex justify-end gap-2 text-xs">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="closeConfirm"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-red-700"
                        @click="confirmProceed"
                    >
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
