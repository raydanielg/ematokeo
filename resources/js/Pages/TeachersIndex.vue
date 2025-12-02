<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const props = defineProps({
    teachers: {
        type: Array,
        default: () => [],
    },
    classes: {
        type: Array,
        default: () => [],
    },
});

const showAddModal = ref(false);
const showImportModal = ref(false);
const showDetailsModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);

const addForm = reactive({
    name: '',
    email: '',
    phone: '',
    check_number: '',
    teaching_classes: '',
});

const importForm = reactive({
    class_id: '',
});

const selectedTeacher = ref(null);

const editForm = reactive({
    id: null,
    name: '',
    phone: '',
    check_number: '',
    teaching_classes: '',
});

const resetAddForm = () => {
    addForm.name = '';
    addForm.email = '';
    addForm.phone = '';
    addForm.check_number = '';
    addForm.teaching_classes = '';
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
    showEditModal.value = true;
};

const updateTeacher = () => {
    if (!editForm.id) return;

    router.put(route('teachers.update', editForm.id), {
        name: editForm.name,
        phone: editForm.phone,
        check_number: editForm.check_number,
        teaching_classes: editForm.teaching_classes,
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

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-sm font-medium text-gray-700">
                    Teachers ({{ teachers.length }})
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">Name</th>
                            <th class="px-3 py-2">Phone</th>
                            <th class="px-3 py-2">Check No.</th>
                            <th class="px-3 py-2">Classes</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="teachers.length === 0">
                            <td colspan="5" class="px-3 py-4 text-center text-xs text-gray-500">
                                No teachers registered yet.
                            </td>
                        </tr>
                        <tr
                            v-for="teacher in teachers"
                            :key="teacher.id"
                            class="border-b border-gray-100 text-xs text-gray-700 hover:bg-gray-50"
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
                                {{ teacher.teaching_classes || '—' }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="rounded-md bg-white px-2 py-1 text-[11px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                                        @click="openDetails(teacher)"
                                    >
                                        Details
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-emerald-50 px-2 py-1 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                                        @click="openEdit(teacher)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-red-50 px-2 py-1 text-[11px] font-medium text-red-700 ring-1 ring-red-200 hover:bg-red-100"
                                        @click="openDelete(teacher)"
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
                        <dd class="max-w-[60%] text-right">{{ selectedTeacher.teaching_classes || '—' }}</dd>
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
                        <label class="mb-1 block text-[11px] font-medium">Email (for login)</label>
                        <input
                            v-model="addForm.email"
                            type="email"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="teacher@example.com"
                            required
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Phone Number</label>
                            <input
                                v-model="addForm.phone"
                                type="tel"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="07XXXXXXXX"
                                required
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Check / Registration No.</label>
                            <input
                                v-model="addForm.check_number"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="Registration number"
                                required
                            />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium">Classes (one or more)</label>
                        <input
                            v-model="addForm.teaching_classes"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="e.g. Form I A, Form II B"
                            required
                        />
                        <p class="mt-1 text-[10px] text-gray-500">
                            Enter a comma-separated list of classes this teacher is responsible for.
                        </p>
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
