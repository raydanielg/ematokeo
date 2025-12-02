<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Alert from '@/Components/Alert.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps({
    classes: {
        type: Array,
        default: () => [],
    },
    subjects: {
        type: Array,
        default: () => [],
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

// New state for single-class subject assignment panel
const selectedClassId = ref(null);
const selectedClassSubjectIds = ref([]);
const isSavingSingleAssignment = ref(false);

const showForm = ref(false);
const editingId = ref(null);
const form = reactive({
    name: '',
    description: '',
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
                                <div class="text-[11px] text-gray-700">
                                    <span class="font-medium">{{
                                        subjects
                                            .filter((s) =>
                                                row.subject_ids
                                                    .map((id) => String(id))
                                                    .includes(String(s.id)),
                                            )
                                            .map((s) => s.subject_code)
                                            .join(', ') || 'None'
                                    }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="rounded-md bg-blue-50 px-2 py-1 text-[11px] font-medium text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100"
                                        @click="openEdit(row)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-red-50 px-2 py-1 text-[11px] font-medium text-red-700 ring-1 ring-red-100 hover:bg-red-100"
                                        @click="deleteClass(row.id)"
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

                    <div class="grid grid-cols-2 gap-3">
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
                        <div>
                            <label class="mb-1 block font-medium">Level (optional)</label>
                            <select
                                v-model="form.level"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option value="">Select level...</option>
                                <option
                                    v-for="level in levelOptions"
                                    :key="level"
                                    :value="level"
                                >
                                    {{ level }}
                                </option>
                            </select>
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
