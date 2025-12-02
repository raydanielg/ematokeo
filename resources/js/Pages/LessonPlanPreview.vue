<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    school: {
        type: Object,
        default: () => ({}),
    },
    subject: {
        type: Object,
        default: () => null,
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
});

const printPlan = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Lesson Plan - ${subject?.subject_code || ''}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Lesson Plan Preview
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Printable lesson plan layout based on the details you provided.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                        @click="printPlan"
                    >
                        Print
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div
                id="lesson-plan-preview"
                class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 text-[11px] text-gray-900"
            >
                <!-- Header -->
                <div class="mb-4 text-center">
                    <div class="text-[12px] font-semibold uppercase tracking-wide">
                        {{ meta.school_name || school?.name || 'School Name' }}
                    </div>
                    <div class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-emerald-800">
                        LESSON PLAN
                    </div>
                </div>

                <!-- Meta details -->
                <div class="mb-4 grid grid-cols-2 gap-3 text-[11px]">
                    <div>
                        <div><span class="font-semibold">Teacher:</span> {{ meta.teacher_name || '__________' }}</div>
                        <div><span class="font-semibold">Subject:</span> {{ subject?.subject_code }} - {{ subject?.name }}</div>
                        <div><span class="font-semibold">Class/Form:</span> {{ meta.class_label || '__________' }}</div>
                    </div>
                    <div>
                        <div><span class="font-semibold">Year:</span> {{ meta.year || '__________' }}</div>
                        <div><span class="font-semibold">Date:</span> ____________________</div>
                        <div><span class="font-semibold">Period:</span> ____________________</div>
                    </div>
                </div>

                <!-- Plan sections -->
                <div class="space-y-3 text-[11px]">
                    <div>
                        <div class="font-semibold">Topic / Sub-topic:</div>
                        <div class="min-h-[40px] rounded border border-gray-300 px-2 py-1"></div>
                    </div>
                    <div>
                        <div class="font-semibold">General Objectives:</div>
                        <div class="min-h-[40px] rounded border border-gray-300 px-2 py-1"></div>
                    </div>
                    <div>
                        <div class="font-semibold">Specific Objectives:</div>
                        <div class="min-h-[60px] rounded border border-gray-300 px-2 py-1"></div>
                    </div>
                    <div>
                        <div class="font-semibold">Teaching / Learning Materials:</div>
                        <div class="min-h-[40px] rounded border border-gray-300 px-2 py-1"></div>
                    </div>
                    <div>
                        <div class="font-semibold">Teaching / Learning Activities (Introduction, Presentation, Practice, Conclusion):</div>
                        <div class="min-h-[100px] rounded border border-gray-300 px-2 py-1"></div>
                    </div>
                    <div>
                        <div class="font-semibold">Assessment / Evaluation:</div>
                        <div class="min-h-[60px] rounded border border-gray-300 px-2 py-1"></div>
                    </div>
                    <div>
                        <div class="font-semibold">Remarks:</div>
                        <div class="min-h-[40px] rounded border border-gray-300 px-2 py-1"></div>
                    </div>
                </div>

                <!-- Signatures -->
                <div class="mt-6 grid grid-cols-2 gap-6 text-[11px]">
                    <div>
                        Teacher's Name & Signature: __________________________
                    </div>
                    <div>
                        Head of Department / Academic: ______________________
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }

    #lesson-plan-preview,
    #lesson-plan-preview * {
        visibility: visible;
    }

    #lesson-plan-preview {
        position: absolute;
        inset: 0;
        width: 100%;
        margin: 0;
        padding: 0.5cm;
        box-shadow: none;
        border: none;
    }
}
</style>
