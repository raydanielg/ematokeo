<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps({
    teachers: {
        type: Array,
        default: () => [],
    },
    classes: {
        type: Array,
        default: () => [],
    },
    allClasses: {
        type: Array,
        default: () => [],
    },
    subjects: {
        type: Array,
        default: () => [],
    },
    classSubjectMap: {
        type: Object,
        default: () => ({}),
    },
});

const showAddModal = ref(false);
const showImportModal = ref(false);
const showDetailsModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showTimetableModal = ref(false);
const showVipindiModal = ref(false);

const addForm = reactive({
    name: '',
    phone: '',
    class_ids: [],
    subject_ids: [],
});

const editForm = reactive({
    id: null,
    name: '',
    phone: '',
    check_number: '',
    teaching_classes: '',
    class_ids: [],
    subject_ids: [],
});

const selectedClassIdsToAllowedSubjectIds = (classIds) => {
    const ids = (classIds || []).map((v) => Number(v)).filter((v) => Number.isFinite(v));
    if (!ids.length) return null;

    const sets = ids
        .map((cid) => Array.isArray(props.classSubjectMap?.[cid]) ? props.classSubjectMap[cid] : [])
        .map((arr) => new Set(arr.map((v) => Number(v)).filter((v) => Number.isFinite(v))));

    if (!sets.length) return null;

    let intersection = sets[0];
    for (let i = 1; i < sets.length; i++) {
        const next = new Set();
        for (const v of intersection) {
            if (sets[i].has(v)) next.add(v);
        }
        intersection = next;
    }

    return intersection;
};

const filteredSubjectsFor = (classIds) => {
    const allowed = selectedClassIdsToAllowedSubjectIds(classIds);
    if (!allowed) return props.subjects || [];

    return (props.subjects || []).filter((s) => allowed.has(Number(s.id)));
};

const addFilteredSubjects = computed(() => filteredSubjectsFor(addForm.class_ids));
const editFilteredSubjects = computed(() => filteredSubjectsFor(editForm.class_ids));

watch(
    () => addForm.class_ids,
    () => {
        const allowed = selectedClassIdsToAllowedSubjectIds(addForm.class_ids);
        if (!allowed) return;
        addForm.subject_ids = (addForm.subject_ids || []).filter((sid) => allowed.has(Number(sid)));
    },
    { deep: true }
);

watch(
    () => editForm.class_ids,
    () => {
        const allowed = selectedClassIdsToAllowedSubjectIds(editForm.class_ids);
        if (!allowed) return;
        editForm.subject_ids = (editForm.subject_ids || []).filter((sid) => allowed.has(Number(sid)));
    },
    { deep: true }
);

const importForm = reactive({
    class_id: '',
});

const selectedTeacher = ref(null);
const timetablePreviewLoading = ref(false);
const timetablePreview = ref({ teacher: null, limits: [] });
const vipindiLoading = ref(false);
const vipindiSaving = ref(false);
const vipindiRows = ref([]);

const weeklyTotal = computed(() => {
    return (timetablePreview.value?.limits || []).reduce((sum, r) => sum + (Number(r.periods_per_week) || 0), 0);
});

const resetAddForm = () => {
    addForm.name = '';
    addForm.phone = '';
    addForm.class_ids = [];
    addForm.subject_ids = [];
};

const saveTeacher = () => {
    router.post(route('teachers.store'), addForm, {
        preserveScroll: true,
        onSuccess: () => {
            resetAddForm();
            showAddModal.value = false;
        },
    });
};

const importSampleTeachers = () => {
    if (!importForm.class_id) return;

    router.post(route('teachers.import-samples'), importForm, {
        preserveScroll: true,
        onSuccess: () => {
            showImportModal.value = false;
        },
    });
};

const openDetails = (teacher) => {
    selectedTeacher.value = teacher;
    showDetailsModal.value = true;
};

const openEdit = (teacher) => {
    selectedTeacher.value = teacher;
    editForm.id = teacher.id;
    editForm.name = teacher.name;
    editForm.phone = teacher.phone;
    editForm.check_number = teacher.check_number;
    editForm.teaching_classes = teacher.teaching_classes;
    editForm.class_ids = Array.isArray(teacher.assigned_class_ids) ? [...teacher.assigned_class_ids] : [];
    editForm.subject_ids = Array.isArray(teacher.assigned_subject_ids) ? [...teacher.assigned_subject_ids] : [];
    showEditModal.value = true;
};

const updateTeacher = () => {
    if (!editForm.id) return;

    router.put(route('teachers.update', editForm.id), {
        name: editForm.name,
        phone: editForm.phone,
        check_number: editForm.check_number,
        teaching_classes: editForm.teaching_classes,
        class_ids: editForm.class_ids,
        subject_ids: editForm.subject_ids,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
};

const openDelete = (teacher) => {
    selectedTeacher.value = teacher;
    showDeleteModal.value = true;
};

const deleteTeacher = () => {
    if (!selectedTeacher.value) return;

    router.delete(route('teachers.destroy', selectedTeacher.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const openTimetablePreview = async (teacher) => {
    selectedTeacher.value = teacher;
    showTimetableModal.value = true;
    timetablePreviewLoading.value = true;

    try {
        const res = await fetch(route('teachers.weekly-limits', teacher.id), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });
        const json = await res.json();
        timetablePreview.value = {
            teacher: json?.teacher || { id: teacher.id, name: teacher.name },
            limits: Array.isArray(json?.limits) ? json.limits : [],
        };
    } catch {
        timetablePreview.value = { teacher: { id: teacher.id, name: teacher.name }, limits: [] };
    } finally {
        timetablePreviewLoading.value = false;
    }
};

const openVipindiModal = async (teacher) => {
    selectedTeacher.value = teacher;
    showVipindiModal.value = true;
    vipindiLoading.value = true;
    vipindiRows.value = [];

    try {
        const res = await fetch(route('teachers.weekly-limits', teacher.id), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });
        const json = await res.json();
        const limits = Array.isArray(json?.limits) ? json.limits : [];
        vipindiRows.value = limits.map((r) => ({
            school_class_id: Number(r.school_class_id) || '',
            subject_id: Number(r.subject_id) || '',
            periods_per_week: Number(r.periods_per_week) || 0,
        }));
    } catch {
        vipindiRows.value = [];
    } finally {
        vipindiLoading.value = false;
    }
};

const closeVipindiModal = () => {
    if (vipindiSaving.value) return;
    showVipindiModal.value = false;
    vipindiRows.value = [];
};

const addVipindiRow = () => {
    vipindiRows.value = [
        ...vipindiRows.value,
        { school_class_id: '', subject_id: '', periods_per_week: 0 },
    ];
};

const removeVipindiRow = (idx) => {
    vipindiRows.value = vipindiRows.value.filter((_, i) => i !== idx);
};

const saveVipindi = async () => {
    if (!selectedTeacher.value || vipindiSaving.value) return;

    const cleaned = (vipindiRows.value || [])
        .map((r) => ({
            school_class_id: Number(r.school_class_id),
            subject_id: Number(r.subject_id),
            teacher_id: Number(selectedTeacher.value.id),
            periods_per_week: Number(r.periods_per_week),
        }))
        .filter((r) => Number.isFinite(r.school_class_id) && r.school_class_id > 0)
        .filter((r) => Number.isFinite(r.subject_id) && r.subject_id > 0)
        .filter((r) => Number.isFinite(r.periods_per_week) && r.periods_per_week >= 0);

    if (!cleaned.length) return;

    vipindiSaving.value = true;
    try {
        await fetch(route('timetables.weekly-limits.save'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ limits: cleaned }),
        });
        closeVipindiModal();
    } finally {
        vipindiSaving.value = false;
    }
};

const formatClassOption = (cls) => {
    const level = cls?.level ? String(cls.level).trim() : '';
    return level || (cls?.name || '');
};

const formatSubjectOption = (s) => {
    const code = s?.subject_code ? String(s.subject_code).trim() : '';
    const name = s?.name ? String(s.name).trim() : '';
    return code && name ? `${code} - ${name}` : (code || name);
};
</script>

<template>
    <Head title="Teachers" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Teachers
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        List of all registered teachers with their contact details, registration number and assigned classes.
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                        @click="showAddModal = true"
                    >
                        Add Teacher
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-50"
                        @click="showImportModal = true"
                    >
                        Import Sample Teachers
                    </button>
                </div>
            </div>
        </template>

        <!-- Assign vipindi modal -->
        <div
            v-if="showVipindiModal && selectedTeacher"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-3xl rounded-xl bg-white p-5 text-xs text-gray-700 shadow-xl">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Assign vipindi (weekly limits)</h3>
                        <p class="mt-0.5 text-[11px] text-gray-500">
                            {{ selectedTeacher.name }} · Weka vipindi kwa wiki kwa kila darasa na somo.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[11px] font-bold text-gray-600 hover:bg-gray-200"
                        :disabled="vipindiSaving"
                        @click="closeVipindiModal"
                    >
                        ×
                    </button>
                </div>

                <div v-if="vipindiLoading" class="py-8 text-center text-[11px] text-gray-500">
                    Loading...
                </div>

                <div v-else class="space-y-3">
                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full border-collapse text-[11px]">
                            <thead>
                                <tr class="bg-gray-50 text-left">
                                    <th class="border-b border-gray-200 px-3 py-2">Class</th>
                                    <th class="border-b border-gray-200 px-3 py-2">Subject</th>
                                    <th class="border-b border-gray-200 px-3 py-2">Vipindi / Wiki</th>
                                    <th class="border-b border-gray-200 px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!vipindiRows.length">
                                    <td colspan="4" class="px-3 py-6 text-center text-[11px] text-gray-500">
                                        Hakuna row bado. Bonyeza "Add row" kuongeza.
                                    </td>
                                </tr>
                                <tr v-for="(row, idx) in vipindiRows" :key="idx" class="hover:bg-gray-50">
                                    <td class="border-b border-gray-100 px-3 py-2">
                                        <select
                                            v-model="row.school_class_id"
                                            class="w-full rounded-md border border-gray-300 px-2 py-1 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                        >
                                            <option value="">Select class...</option>
                                            <option v-for="c in allClasses" :key="c.id" :value="c.id">
                                                {{ formatClassOption(c) }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="border-b border-gray-100 px-3 py-2">
                                        <select
                                            v-model="row.subject_id"
                                            class="w-full rounded-md border border-gray-300 px-2 py-1 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                        >
                                            <option value="">Select subject...</option>
                                            <option v-for="s in subjects" :key="s.id" :value="s.id">
                                                {{ formatSubjectOption(s) }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="border-b border-gray-100 px-3 py-2">
                                        <input
                                            v-model.number="row.periods_per_week"
                                            type="number"
                                            min="0"
                                            max="50"
                                            class="w-24 rounded-md border border-gray-300 px-2 py-1 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                        />
                                    </td>
                                    <td class="border-b border-gray-100 px-3 py-2 text-right">
                                        <button
                                            type="button"
                                            class="rounded-md bg-red-50 px-2 py-1 text-[11px] font-medium text-red-700 ring-1 ring-red-100 hover:bg-red-100"
                                            :disabled="vipindiSaving"
                                            @click="removeVipindiRow(idx)"
                                        >
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between">
                        <button
                            type="button"
                            class="rounded-md bg-slate-50 px-3 py-1.5 text-[11px] font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100"
                            :disabled="vipindiSaving"
                            @click="addVipindiRow"
                        >
                            Add row
                        </button>
                        <div class="flex justify-end gap-2">
                            <button
                                type="button"
                                class="rounded-md bg-gray-50 px-3 py-1.5 text-[11px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                                :disabled="vipindiSaving"
                                @click="closeVipindiModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-60"
                                :disabled="vipindiSaving || vipindiLoading || !vipindiRows.length"
                                @click="saveVipindi"
                            >
                                {{ vipindiSaving ? 'Saving...' : 'Save vipindi' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-slate-50/70 p-4 ring-1 ring-slate-200">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Teachers</p>
                        <p class="mt-0.5 text-[11px] text-gray-500">Jumla: <span class="font-semibold text-gray-700">{{ teachers.length }}</span></p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Phone</th>
                                    <th class="px-4 py-3">Check No.</th>
                                    <th class="px-4 py-3">Assigned Classes</th>
                                    <th class="px-4 py-3">Assigned Subjects</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="teachers.length === 0">
                                    <td colspan="6" class="px-4 py-10 text-center">
                                        <div class="mx-auto max-w-sm">
                                            <div class="text-sm font-semibold text-gray-800">No teachers yet</div>
                                            <div class="mt-1 text-xs text-gray-500">Bado hujaongeza mwalimu. Bonyeza <span class="font-semibold">Add Teacher</span> kuanza.</div>
                                        </div>
                                    </td>
                                </tr>
                                <tr
                                    v-for="(teacher, idx) in teachers"
                                    :key="teacher.id"
                                    :class="[
                                        'border-b border-gray-100 text-xs text-gray-700',
                                        idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/40',
                                        'hover:bg-emerald-50/40'
                                    ]"
                                >
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-col">
                                    <span class="text-gray-800">{{ teacher.name }}</span>
                                    <span class="text-[10px] text-gray-500">{{ teacher.email }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                {{ teacher.phone || '—' }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                {{ teacher.check_number || '—' }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div v-if="teacher.assigned_classes && teacher.assigned_classes.length" class="flex flex-wrap gap-1">
                                    <span
                                        v-for="c in teacher.assigned_classes"
                                        :key="c"
                                        class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] text-gray-700"
                                    >
                                        {{ c }}
                                    </span>
                                </div>
                                <span v-else class="text-gray-500">—</span>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div v-if="teacher.assigned_subjects && teacher.assigned_subjects.length" class="flex flex-wrap gap-1">
                                    <span
                                        v-for="s in teacher.assigned_subjects"
                                        :key="s"
                                        class="rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] text-emerald-800"
                                    >
                                        {{ s }}
                                    </span>
                                </div>
                                <span v-else class="text-gray-500">—</span>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex justify-end gap-1.5">
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                                        title="Timetable preview"
                                        @click="openTimetablePreview(teacher)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 4h18" />
                                            <path d="M8 2v4" />
                                            <path d="M16 2v4" />
                                            <rect x="3" y="6" width="18" height="16" rx="2" />
                                            <path d="M7 10h4" />
                                            <path d="M7 14h4" />
                                            <path d="M13 10h4" />
                                            <path d="M13 14h4" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-amber-50 text-amber-700 ring-1 ring-amber-100 hover:bg-amber-100"
                                        title="Assign vipindi"
                                        @click="openVipindiModal(teacher)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                                        title="Details"
                                        @click="openDetails(teacher)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M12 16v-4" />
                                            <path d="M12 8h.01" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                                        title="Edit"
                                        @click="openEdit(teacher)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100"
                                        title="Delete"
                                        @click="openDelete(teacher)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18" />
                                            <path d="M8 6V4h8v2" />
                                            <path d="M19 6l-1 14H6L5 6" />
                                            <path d="M10 11v6" />
                                            <path d="M14 11v6" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teacher timetable preview modal -->
        <div
            v-if="showTimetableModal && selectedTeacher"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-3xl rounded-xl bg-white p-5 text-xs text-gray-700 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Teacher timetable preview</h3>
                        <p class="mt-0.5 text-[11px] text-gray-500">
                            {{ timetablePreview.teacher?.name || selectedTeacher.name }} · Total vipindi kwa wiki: <span class="font-semibold">{{ weeklyTotal }}</span>
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[11px] font-bold text-gray-600 hover:bg-gray-200"
                        @click="showTimetableModal = false"
                    >
                        ×
                    </button>
                </div>

                <div v-if="timetablePreviewLoading" class="py-6 text-center text-[11px] text-gray-500">
                    Loading...
                </div>

                <div v-else class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="min-w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="border-b border-gray-200 px-3 py-2">Class</th>
                                <th class="border-b border-gray-200 px-3 py-2">Subject</th>
                                <th class="border-b border-gray-200 px-3 py-2">Vipindi / Wiki</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!timetablePreview.limits.length">
                                <td colspan="3" class="px-3 py-4 text-center text-[11px] text-gray-500">
                                    Hakuna limitations zilizowekwa kwa huyu mwalimu bado.
                                </td>
                            </tr>
                            <tr v-for="row in timetablePreview.limits" :key="row.id" class="hover:bg-gray-50">
                                <td class="border-b border-gray-100 px-3 py-2">{{ row.class_label }}</td>
                                <td class="border-b border-gray-100 px-3 py-2">
                                    <div class="font-medium text-gray-800">{{ row.subject_code }}</div>
                                    <div class="text-[10px] text-gray-500">{{ row.subject_name }}</div>
                                </td>
                                <td class="border-b border-gray-100 px-3 py-2 font-semibold">{{ row.periods_per_week }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        class="rounded-md bg-emerald-50 px-3 py-1.5 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                        @click="showTimetableModal = false"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Teacher details modal -->
        <div
            v-if="showDetailsModal && selectedTeacher"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-5 text-xs text-gray-700 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Teacher details</h3>
                        <p class="mt-0.5 text-[11px] text-gray-500">
                            Full profile preview for this teacher.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[11px] font-bold text-gray-600 hover:bg-gray-200"
                        @click="showDetailsModal = false"
                    >
                        ×
                    </button>
                </div>

                <dl class="space-y-2 text-[11px] text-gray-700">
                    <div class="flex justify-between gap-3">
                        <dt class="font-semibold text-gray-600">Name</dt>
                        <dd class="text-right text-gray-900">{{ selectedTeacher.name }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="font-semibold text-gray-600">Email</dt>
                        <dd class="text-right">{{ selectedTeacher.email }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="font-semibold text-gray-600">Phone</dt>
                        <dd class="text-right">{{ selectedTeacher.phone || '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="font-semibold text-gray-600">Check No.</dt>
                        <dd class="text-right">{{ selectedTeacher.check_number || '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="font-semibold text-gray-600">Classes</dt>
                        <dd class="max-w-[60%] text-right">
                            <span v-if="selectedTeacher.assigned_classes && selectedTeacher.assigned_classes.length">
                                {{ selectedTeacher.assigned_classes.join(', ') }}
                            </span>
                            <span v-else>—</span>
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="font-semibold text-gray-600">Subjects</dt>
                        <dd class="max-w-[60%] text-right">
                            <span v-if="selectedTeacher.assigned_subjects && selectedTeacher.assigned_subjects.length">
                                {{ selectedTeacher.assigned_subjects.join(', ') }}
                            </span>
                            <span v-else>—</span>
                        </dd>
                    </div>
                </dl>

                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        class="rounded-md bg-emerald-50 px-3 py-1.5 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                        @click="showDetailsModal = false"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Teacher modal -->
        <div
            v-if="showEditModal && selectedTeacher"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-lg rounded-xl bg-white p-5 text-xs text-gray-700 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Edit Teacher</h3>
                        <p class="mt-0.5 text-[11px] text-gray-500">
                            Update basic details for this teacher.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[11px] font-bold text-gray-600 hover:bg-gray-200"
                        @click="showEditModal = false"
                    >
                        ×
                    </button>
                </div>

                <form class="space-y-3" @submit.prevent="updateTeacher">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium">Full Name</label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            required
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Phone Number</label>
                            <input
                                v-model="editForm.phone"
                                type="tel"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                required
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Check / Registration No.</label>
                            <input
                                v-model="editForm.check_number"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                required
                            />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium">Classes (one or more)</label>
                        <input
                            v-model="editForm.teaching_classes"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Assigned classes</label>
                            <select
                                v-model="editForm.class_ids"
                                multiple
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                    {{ formatClassOption(cls) }}
                                </option>
                            </select>
                            <p class="mt-1 text-[10px] text-gray-500">
                                Chagua darasa (base class tu, bila streams). Masomo yatakayotangazwa chini yata-apply kwa madarasa yote.
                            </p>
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Assigned subjects</label>
                            <select
                                v-model="editForm.subject_ids"
                                multiple
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option v-for="s in editFilteredSubjects" :key="s.id" :value="s.id">
                                    {{ formatSubjectOption(s) }}
                                </option>
                            </select>
                            <p class="mt-1 text-[10px] text-gray-500">
                                Ukichagua masomo na madarasa mengi, masomo hayo yataunganishwa kwa kila darasa.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-gray-50 px-3 py-1.5 text-[11px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                            @click="showEditModal = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                        >
                            Save changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Teacher confirmation -->
        <div
            v-if="showDeleteModal && selectedTeacher"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-sm rounded-xl bg-white p-5 text-xs text-gray-700 shadow-xl">
                <h3 class="mb-2 text-sm font-semibold text-gray-900">Delete teacher?</h3>
                <p class="mb-3 text-[11px] text-gray-600">
                    This will remove <span class="font-semibold">{{ selectedTeacher.name }}</span> from the list of teachers. You can re-create them later if needed.
                </p>
                <div class="mt-3 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 text-[11px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="showDeleteModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-red-700"
                        @click="deleteTeacher"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Add Teacher modal -->
        <div
            v-if="showAddModal"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-lg rounded-xl bg-white p-5 text-xs text-gray-700 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Add Teacher</h3>
                        <p class="mt-0.5 text-[11px] text-gray-500">
                            Register a new teacher with contact details and the classes they teach.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[11px] font-bold text-gray-600 hover:bg-gray-200"
                        @click="showAddModal = false"
                    >
                        ×
                    </button>
                </div>

                <form class="space-y-3" @submit.prevent="saveTeacher">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium">Full Name</label>
                        <input
                            v-model="addForm.name"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="e.g. John Peter Mwakalinga"
                            required
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium">Phone Number (optional)</label>
                        <input
                            v-model="addForm.phone"
                            type="tel"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="07XXXXXXXX"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Assigned classes</label>
                            <select
                                v-model="addForm.class_ids"
                                multiple
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                    {{ formatClassOption(cls) }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Assigned subjects</label>
                            <select
                                v-model="addForm.subject_ids"
                                multiple
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option v-for="s in addFilteredSubjects" :key="s.id" :value="s.id">
                                    {{ formatSubjectOption(s) }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-gray-50 px-3 py-1.5 text-[11px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                            @click="showAddModal = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                        >
                            Save Teacher
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Import Sample Teachers modal -->
        <div
            v-if="showImportModal"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-5 text-xs text-gray-700 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Import Sample Teachers</h3>
                        <p class="mt-0.5 text-[11px] text-gray-500">
                            Quickly create 38 sample teachers for a selected class and assign them to subjects.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[11px] font-bold text-gray-600 hover:bg-gray-200"
                        @click="showImportModal = false"
                    >
                        ×
                    </button>
                </div>

                <form class="space-y-4" @submit.prevent="importSampleTeachers">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium">Class</label>
                        <select
                            v-model="importForm.class_id"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            required
                        >
                            <option value="" disabled>Select class...</option>
                            <option
                                v-for="cls in classes"
                                :key="cls.id"
                                :value="cls.id"
                            >
                                {{ cls.name }}
                            </option>
                        </select>
                        <p class="mt-1 text-[10px] text-gray-500">
                            We will generate 38 sample teachers linked to this class and rotate them across subjects.
                        </p>
                    </div>

                    <div class="mt-2 flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-gray-50 px-3 py-1.5 text-[11px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                            @click="showImportModal = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                        >
                            Import Sample Teachers
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
