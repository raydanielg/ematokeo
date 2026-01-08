<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({
    subjects: {
        type: Array,
        default: () => [],
    },
    classes: {
        type: Array,
        default: () => [],
    },
});

const isAddModalOpen = ref(false);
const isSavingSubject = ref(false);

const form = reactive({
    class_ids: [],
    subject_code: '',
    name: '',
    assign_to_current_user: false,
});

// Hardcoded suggested subjects (templates) matching SubjectSeeder
const subjectTemplates = [
    { code: 'KIS', name: 'Kiswahili' },
    { code: 'ENG', name: 'English Language' },
    { code: 'B/MAT', name: 'Basic Mathematics' },
    { code: 'CIV', name: 'Civics' },
    { code: 'HIS', name: 'History' },
    { code: 'GEO', name: 'Geography' },
    { code: 'BIO', name: 'Biology' },
    { code: 'CHE', name: 'Chemistry' },
    { code: 'PHY', name: 'Physics' },
    { code: 'GK', name: 'General Knowledge' },
    { code: 'AGRI', name: 'Agriculture' },
    { code: 'COMM', name: 'Commerce' },
    { code: 'BOOK', name: 'Book Keeping' },
    { code: 'ECON', name: 'Economics' },
    { code: 'ACC', name: 'Accountancy' },
    { code: 'ENT', name: 'Entrepreneurship' },
    { code: 'COMP', name: 'Computer Studies' },
    { code: 'ICT', name: 'Information and Computer Studies' },
    { code: 'LIT', name: 'Literature in English' },
    { code: 'K/F', name: 'Kiswahili for Foreigners' },
    { code: 'ARAB', name: 'Arabic' },
    { code: 'FREN', name: 'French' },
    { code: 'GER', name: 'German' },
    { code: 'CHI', name: 'Chinese' },
    { code: 'ISL', name: 'Islamic Knowledge' },
    { code: 'BIB', name: 'Bible Knowledge' },
    { code: 'ECO', name: 'Ecology' },
    { code: 'HSC', name: 'Home Science' },
    { code: 'F/N', name: 'Food and Nutrition' },
    { code: 'TD', name: 'Technical Drawing' },
    { code: 'WD', name: 'Woodwork' },
    { code: 'MW', name: 'Metalwork' },
    { code: 'BC', name: 'Building Construction' },
    { code: 'ELC', name: 'Electrical Installation' },
    { code: 'AUT', name: 'Automotive Mechanics' },
    { code: 'PLU', name: 'Plumbing' },
    { code: 'TAIL', name: 'Tailoring' },
    { code: 'ART', name: 'Fine Art' },
    { code: 'MUS', name: 'Music' },
    { code: 'PE', name: 'Physical Education' },
    { code: 'SPO', name: 'Sports' },
    { code: 'THE', name: 'Theatre Arts' },
    { code: 'BUS', name: 'Business Studies' },
];

const selectedTemplateCode = ref('');

const openAddModal = () => {
    form.class_ids = [];
    form.subject_code = '';
    form.name = '';
    form.assign_to_current_user = false;
    selectedTemplateCode.value = '';
    isAddModalOpen.value = true;
};

const closeAddModal = () => {
    if (isSavingSubject.value) return;
    isAddModalOpen.value = false;
};

const saveSubject = () => {
    if (isSavingSubject.value) return;

    isSavingSubject.value = true;

    router.post(route('subjects.store'), form, {
        preserveScroll: true,
        onSuccess: () => {
            isSavingSubject.value = false;
            isAddModalOpen.value = false;
            router.reload({ only: ['subjects'] });
        },
        onError: () => {
            isSavingSubject.value = false;
        },
    });
};

const toggleSelectedClass = (classId) => {
    const idx = form.class_ids.indexOf(classId);
    if (idx === -1) {
        form.class_ids.push(classId);
    } else {
        form.class_ids.splice(idx, 1);
    }
};

watch(
    () => selectedTemplateCode.value,
    (newCode) => {
        if (!newCode) return;
        const template = subjectTemplates.find((t) => t.code === newCode);
        if (!template) return;

        form.subject_code = template.code;
        form.name = template.name;
    },
);
</script>

<template>
    <Head title="Subjects" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Subjects
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        List of all subjects with codes and the classes they are assigned to.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                        @click="openAddModal"
                    >
                        Add Subject
                    </button>
                </div>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-sm font-medium text-gray-700">
                    Subjects ({{ subjects.length }})
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">Code</th>
                            <th class="px-3 py-2">Subject Name</th>
                            <th class="px-3 py-2">Classes Assigned</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="subjects.length === 0">
                            <td colspan="4" class="px-3 py-4 text-center text-xs text-gray-500">
                                No subjects found yet.
                            </td>
                        </tr>
                        <tr
                            v-for="subject in subjects"
                            :key="subject.id"
                            class="border-b border-gray-100 text-xs text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top font-mono text-xs text-gray-900">
                                {{ subject.subject_code }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                {{ subject.name }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                {{ subject.class_levels || '—' }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="rounded-md bg-blue-50 px-2 py-1 text-[11px] font-medium text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100"
                                    >
                                        View
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-emerald-50 px-2 py-1 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
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

        <!-- Add Subject Modal -->
        <div
            v-if="isAddModalOpen"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4"
        >
            <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl ring-1 ring-gray-200">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Add Subject</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Select a class for this school, then add the subject you want to import for that class
                            and for this user.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="text-xs text-gray-400 hover:text-gray-600"
                        @click="closeAddModal"
                    >
                        ✕
                    </button>
                </div>
                <form class="space-y-3 text-xs text-gray-700" @submit.prevent="saveSubject">
                    <div>
                        <label class="mb-1 block font-medium">Suggested subjects</label>
                        <select
                            v-model="selectedTemplateCode"
                            class="w-full rounded-md border border-gray-300 bg-white px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="">-- Select from suggested list (or type manually below) --</option>
                            <option
                                v-for="tpl in subjectTemplates"
                                :key="tpl.code"
                                :value="tpl.code"
                            >
                                {{ tpl.code }} - {{ tpl.name }}
                            </option>
                        </select>
                        <p class="mt-2 text-[11px] text-gray-500">
                            Ukichagua kwenye list, itajaza Subject Name na Code automatically. Ukikosa somo, andika manually.
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block font-medium">Select class(es) for this Subject</label>
                        <div class="rounded-md border border-gray-200 bg-white p-2">
                            <div class="grid grid-cols-2 gap-2">
                                <label
                                    v-for="c in classes"
                                    :key="c.id"
                                    class="flex cursor-pointer items-center gap-2 rounded-md border px-2 py-2 text-[11px]"
                                    :class="form.class_ids.includes(c.id) ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200 bg-white hover:bg-gray-50'"
                                >
                                    <input
                                        type="checkbox"
                                        class="h-3 w-3 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                        :checked="form.class_ids.includes(c.id)"
                                        @change="toggleSelectedClass(c.id)"
                                    />
                                    <span class="font-medium text-gray-800">{{ c.name }}</span>
                                </label>
                            </div>
                            <p class="mt-2 text-[11px] text-gray-500">
                                You can select multiple classes.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block font-medium">Subject Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="e.g. CIVICS"
                                required
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Subject Code</label>
                            <input
                                v-model="form.subject_code"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="e.g. CIV"
                                required
                            />
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="assign_to_me"
                            v-model="form.assign_to_current_user"
                            type="checkbox"
                            class="h-3 w-3 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                        />
                        <label for="assign_to_me" class="text-[11px] text-gray-600">
                            Also assign this subject to me as a teacher.
                        </label>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                            @click="closeAddModal"
                            :disabled="isSavingSubject"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70"
                            :disabled="isSavingSubject"
                        >
                            <span v-if="!isSavingSubject">Save Subject</span>
                            <span v-else>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
