<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    school: {
        type: Object,
        default: () => ({}),
    },
    subjects: {
        type: Array,
        default: () => [],
    },
});

const showMetaModal = ref(false);
const selectedSubject = ref(null);

const metaForm = ref({
    teacher_name: '',
    class_label: '',
    year: new Date().getFullYear().toString(),
    school_name: '',
});

const openMetaModal = (subject) => {
    selectedSubject.value = subject;
    metaForm.value.teacher_name = '';
    metaForm.value.class_label = '';
    metaForm.value.year = new Date().getFullYear().toString();
    metaForm.value.school_name = props.school?.name || '';
    showMetaModal.value = true;
};

const startLessonPlan = () => {
    if (!selectedSubject.value) return;

    router.get(
        route('resources.lesson-plans.preview'),
        {
            subject_id: selectedSubject.value.id,
            teacher_name: metaForm.value.teacher_name,
            class_label: metaForm.value.class_label,
            year: metaForm.value.year,
            school_name: metaForm.value.school_name,
        },
        {
            preserveState: false,
        },
    );
};
</script>

<template>
    <Head title="Lesson Plans" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Lesson Plans
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Choose a subject to start a lesson plan. You will fill basic teacher and class details before entering the full plan.
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-700">
                        Subjects ({{ subjects.length }})
                    </p>
                    <p class="text-[11px] text-gray-500">
                        Tap a subject card to start a new lesson plan.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <button
                        v-for="subject in subjects"
                        :key="subject.id"
                        type="button"
                        class="flex flex-col rounded-lg border border-gray-200 bg-slate-50 p-4 text-left shadow-sm transition hover:border-emerald-400 hover:bg-emerald-50"
                        @click="openMetaModal(subject)"
                    >
                        <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                            {{ subject.subject_code }}
                        </div>
                        <div class="text-sm font-semibold text-gray-800">
                            {{ subject.name || 'Untitled Subject' }}
                        </div>
                        <div class="mt-1 text-[11px] text-gray-500">
                            Start lesson plan
                        </div>
                    </button>
                </div>

                <div v-if="subjects.length === 0" class="mt-6 text-center text-[11px] text-gray-500">
                    No subjects found. Add subjects first in the Subjects section.
                </div>
            </div>

            <!-- Meta details modal -->
            <div
                v-if="showMetaModal"
                class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 sm:px-0"
            >
                <div
                    class="w-full max-w-md rounded-xl bg-white p-5 text-xs text-gray-700 shadow-lg ring-1 ring-gray-200"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">
                                Lesson Plan Details
                            </h3>
                            <p class="mt-0.5 text-[11px] text-gray-500">
                                Fill teacher and class information for this lesson plan.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="text-[11px] text-gray-500 hover:text-gray-700"
                            @click="showMetaModal = false"
                        >
                            Close
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Subject</label>
                            <div class="text-[11px] font-semibold text-gray-800">
                                {{ selectedSubject?.subject_code }} - {{ selectedSubject?.name }}
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Teacher Name</label>
                            <input
                                v-model="metaForm.teacher_name"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="e.g. Mr. John Doe"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Class / Form</label>
                            <input
                                v-model="metaForm.class_label"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="e.g. Form I A, Form III"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-[11px] font-medium">Year</label>
                                <input
                                    v-model="metaForm.year"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-medium">School Name</label>
                                <input
                                    v-model="metaForm.school_name"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-gray-100 px-3 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-gray-200"
                            @click="showMetaModal = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                            @click="startLessonPlan"
                        >
                            Continue to Lesson Plan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
