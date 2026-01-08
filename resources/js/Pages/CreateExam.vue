<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    classes: {
        type: Array,
        default: () => [],
    },
    year: {
        type: [String, Number],
        default: () => new Date().getFullYear(),
    },
    exams: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: '',
    type: '',
    academic_year: String(props.year || ''),
    start_date: '',
    end_date: '',
    term: '',
    notes: '',
    class_ids: [],
});

let editingExamId = null;
const showDeleteModal = ref(false);
const examToDelete = ref(null);

const submit = () => {
    if (editingExamId) {
        form.post(route('exams.update', editingExamId));
    } else {
        form.post(route('exams.store'));
    }
};

const confirmDeleteExam = (exam) => {
    examToDelete.value = exam;
    showDeleteModal.value = true;
};

const performDeleteExam = () => {
    if (!examToDelete.value) return;

    router.delete(route('exams.destroy', examToDelete.value.id), {
        preserveScroll: true,
        onFinish: () => {
            showDeleteModal.value = false;
            examToDelete.value = null;
        },
    });
};

const cancelDeleteExam = () => {
    showDeleteModal.value = false;
    examToDelete.value = null;
};

const startEdit = (exam) => {
    editingExamId = exam.id;
    form.name = exam.name || '';
    form.type = exam.type || '';
    form.academic_year = exam.academic_year || '';
    form.start_date = exam.start_date || '';
    form.end_date = exam.end_date || '';
    form.term = exam.term || '';
    form.notes = exam.notes || '';
    form.class_ids = Array.isArray(exam.class_ids) ? [...exam.class_ids] : [];
};
</script>

<template>
    <Head title="Create Exam" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Create Exam
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Define a new exam and select which classes will sit for it.
                    </p>
                </div>
            </div>
        </template>

        <div class="grid gap-6 lg:grid-cols-3 mb-6">
            <!-- Exam details -->
            <div class="lg:col-span-2 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <form class="space-y-3 text-xs text-gray-700" @submit.prevent="submit">
                    <div>
                        <label class="mb-1 block font-medium">Exam Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="e.g. Midterm Examination 2025"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block font-medium">Exam Type</label>
                            <select
                                v-model="form.type"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option value="">Select type</option>
                                <option value="Midterm">Midterm</option>
                                <option value="Terminal">Terminal</option>
                                <option value="Joint">Joint</option>
                                <option value="Mock">Mock</option>
                                <option value="Trial">Trial</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Term</label>
                            <input
                                v-model="form.term"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="e.g. Term I"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="mb-1 block font-medium">Academic Year</label>
                            <input
                                v-model="form.academic_year"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Start Date</label>
                            <input
                                v-model="form.start_date"
                                type="date"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">End Date</label>
                            <input
                                v-model="form.end_date"
                                type="date"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block font-medium">Notes (optional)</label>
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="Any special instructions or scope for this exam (e.g. Chapters 1-5 only)"
                        ></textarea>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            type="submit"
                            class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                            :disabled="form.processing"
                        >
                            {{ editingExamId ? 'Update Exam' : 'Save Exam' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Class selection sidebar -->
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Classes Sitting For This Exam
                </h3>
                <p class="mb-2 text-[11px] text-gray-500">
                    Select one or more classes that will sit for this exam.
                </p>
                <div class="max-h-64 space-y-1 overflow-y-auto pr-1 text-xs text-gray-700">
                    <label
                        v-for="cls in classes"
                        :key="cls.id"
                        class="flex items-center justify-between rounded px-2 py-1 hover:bg-gray-50"
                    >
                        <span class="font-medium text-gray-800">{{ cls.name }}</span>
                        <input
                            v-model="form.class_ids"
                            :value="cls.id"
                            type="checkbox"
                            class="h-3 w-3 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                        />
                    </label>
                </div>
            </div>
        </div>

        <!-- Existing exams list -->
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                Existing Exams
            </h3>
            <p class="mb-3 text-[11px] text-gray-500">
                These are the exams you have created. You can quickly jump to entering marks or remove an exam that is no longer needed.
            </p>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">Name</th>
                            <th class="px-3 py-2">Type</th>
                            <th class="px-3 py-2">Year / Term</th>
                            <th class="px-3 py-2">Dates</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!exams.length">
                            <td colspan="5" class="px-3 py-4 text-center text-[11px] text-gray-400">
                                No exams have been created yet.
                            </td>
                        </tr>
                        <tr
                            v-for="exam in exams"
                            :key="exam.id"
                            class="border-b border-gray-100 text-[11px] text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">{{ exam.name }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                {{ exam.type || '—' }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-col">
                                    <span>{{ exam.academic_year || '—' }}</span>
                                    <span v-if="exam.term" class="text-[10px] text-gray-500">{{ exam.term }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-col text-[10px] text-gray-600">
                                    <span v-if="exam.start_date || exam.end_date">
                                        {{ exam.start_date || '—' }}
                                        <span v-if="exam.end_date"> → {{ exam.end_date }}</span>
                                    </span>
                                    <span v-else>—</span>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="rounded-md bg-blue-50 px-2 py-1 text-[10px] font-medium text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100"
                                        @click="router.get(route('exams.results', exam.id))"
                                    >
                                        View Results
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-medium text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                                        @click="startEdit(exam)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-red-50 px-2 py-1 text-[10px] font-medium text-red-700 ring-1 ring-red-100 hover:bg-red-100"
                                        @click="confirmDeleteExam(exam)"
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

        <!-- Delete confirmation modal -->
        <div
            v-if="showDeleteModal && examToDelete"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-sm rounded-xl bg-white p-5 shadow-xl">
                <h3 class="mb-2 text-sm font-semibold text-gray-800">
                    Delete Exam
                </h3>
                <p class="mb-3 text-[11px] text-gray-600">
                    Are you sure you want to permanently delete the exam
                    <span class="font-semibold">"{{ examToDelete.name }}"</span>? This action cannot be undone.
                </p>
                <div class="mt-3 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 text-[11px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="cancelDeleteExam"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-red-700"
                        @click="performDeleteExam"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
