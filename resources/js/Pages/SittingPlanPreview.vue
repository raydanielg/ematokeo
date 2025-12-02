<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    plan: {
        type: Object,
        required: true,
    },
    students: {
        type: Array,
        default: () => [],
    },
    school: {
        type: Object,
        default: () => ({}),
    },
});

// Single-room layout like NECTA template
const DESKS_PER_ROW = 5;

const room = computed(() => {
    return {
        name: 'ROOM NO: 1',
        students: props.students,
    };
});

const roomRows = computed(() => {
    const rows = [];
    const list = room.value.students || [];
    for (let i = 0; i < list.length; i += DESKS_PER_ROW) {
        rows.push(list.slice(i, i + DESKS_PER_ROW));
    }
    return rows;
});

const printPlan = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Sitting Plan - ${plan.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Sitting Plan Preview
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Exam sitting arrangement for the selected exam and class. Print and pin on classroom doors.
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
                id="sitting-plan-preview"
                class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 text-[11px] text-gray-900"
            >
                <!-- Official-style header -->
                <div class="mb-6 text-center leading-snug">
                    <div class="text-[11px] font-semibold uppercase tracking-wide">
                        Ministry of Education and Vocational Training
                    </div>
                    <div class="text-[11px] font-semibold uppercase tracking-wide">
                        {{ plan.exam?.name || 'NATIONAL EXAMINATIONS' }}
                    </div>
                    <div class="mt-2 text-[11px]">
                        CENTRE NAME:
                        <span class="inline-block min-w-[160px] border-b border-gray-700 align-middle text-left">
                            &nbsp;{{ school?.name || '' }}
                        </span>
                    </div>
                    <div class="mt-1 text-[11px]">
                        CENTRE NO:
                        <span class="inline-block min-w-[120px] border-b border-gray-700 align-middle text-left">
                            &nbsp;
                        </span>
                    </div>
                    <div class="mt-1 text-[11px]">
                        SUBJECT:
                        <span class="inline-block min-w-[180px] border-b border-gray-700 align-middle text-left">
                            &nbsp;
                        </span>
                        <span class="ms-4">ROOM NO: 1</span>
                    </div>
                </div>

                <!-- Room rectangle -->
                <div class="mx-auto w-full max-w-3xl border border-gray-900 p-4">
                    <!-- Invigilator table -->
                    <div class="mb-4 flex justify-center">
                        <div class="inline-block border border-gray-900 px-4 py-1 text-[10px] font-semibold uppercase">
                            INVIGILATOR'S TABLE
                        </div>
                    </div>

                    <!-- Door on the right -->
                    <div class="relative">
                        <div class="absolute right-0 top-1/2 -translate-y-1/2 border border-gray-900 px-2 py-1 text-[10px] font-semibold uppercase">
                            DOOR
                        </div>

                        <!-- Desks grid -->
                        <div class="space-y-3 pr-10">
                            <div
                                v-for="(row, rIndex) in roomRows"
                                :key="`row-${rIndex}`"
                                class="flex justify-center gap-4"
                            >
                                <div
                                    v-for="student in row"
                                    :key="student.id"
                                    class="flex h-10 w-14 items-center justify-center border border-gray-900 text-[10px] font-mono"
                                >
                                    {{ student.exam_number }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Signatures -->
                <div class="mx-auto mt-6 w-full max-w-3xl text-[11px]">
                    <div class="mb-2">
                        1. INVIGILATOR'S NAME
                        <span class="inline-block min-w-[220px] border-b border-gray-700 align-middle" />
                        Signature
                        <span class="inline-block min-w-[160px] border-b border-gray-700 align-middle" />
                    </div>
                    <div>
                        2. SUPERVISOR'S NAME
                        <span class="inline-block min-w-[220px] border-b border-gray-700 align-middle" />
                        Signature
                        <span class="inline-block min-w-[160px] border-b border-gray-700 align-middle" />
                    </div>
                </div>

                <div v-if="students.length === 0" class="mt-6 text-center text-[11px] text-gray-500">
                    No students found for this class. Ensure students are registered with matching class level and stream.
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

    #sitting-plan-preview,
    #sitting-plan-preview * {
        visibility: visible;
    }

    #sitting-plan-preview {
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
