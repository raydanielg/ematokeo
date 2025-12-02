<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    school: {
        type: Object,
        default: () => ({}),
    },
    students: {
        type: Array,
        default: () => [],
    },
    year: {
        type: [String, Number],
        default: () => new Date().getFullYear(),
    },
    classes: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ class: null }),
    },
});

const printDocument = () => {
    window.print();
};

const onClassChange = (event) => {
    const value = event.target.value || null;
    router.get(
        route('students.promote'),
        { class: value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};
</script>

<template>
    <Head title="Promote Students" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Promote Students Preview
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Preview of the top 10 students to be promoted for the selected academic year. You can print this as a document.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-gray-500">Class</label>
                        <select
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            @change="onClassChange"
                        >
                            <option value="">Whole School</option>
                            <option
                                v-for="cls in classes"
                                :key="cls"
                                :value="cls"
                                :selected="filters.class === cls"
                            >
                                {{ cls }}
                            </option>
                        </select>
                    </div>
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                        @click="printDocument"
                    >
                        Print / Export
                    </button>
                </div>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mx-auto max-w-4xl border border-gray-200 bg-white p-6 text-xs text-gray-800" id="promote-document">
                <!-- Document header -->
                <div class="mb-4 border-b border-gray-200 pb-3 text-center">
                    <!-- National emblem and government text -->
                    <div class="mb-2 flex justify-center">
                        <img
                            src="/images/emblem.png"
                            alt="National Emblem"
                            class="h-12 w-12 object-contain"
                        />
                    </div>
                    <div class="leading-tight">
                        <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-700">
                            Jamhuri Ya Muungano Wa Tanzania
                        </div>
                        <div class="text-[10px] text-gray-700">
                            Wizara Ya Elimu, Sayansi Na Teknolojia
                        </div>
                        <div class="mt-0.5 text-[10px] text-gray-500">
                            Mfumo Wa Taarifa Za Mitihani Na Matokeo Ya Shule (E-Matokeo)
                        </div>
                    </div>

                    <!-- School details and report title -->
                    <div class="mt-3 leading-tight">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-800">
                            {{ school?.name }}
                        </div>
                        <div class="text-[10px] text-gray-600">
                            <span v-if="school?.registration_number">Reg. No: {{ school.registration_number }}</span>
                            <span v-if="school?.registration_number && school?.school_code"> | </span>
                            <span v-if="school?.school_code">School Code: {{ school.school_code }}</span>
                        </div>
                        <div class="text-[10px] text-gray-500">
                            Mwaka Wa Masomo: {{ year }}
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-800">
                            Orodha Ya Wanafunzi 10 Bora Wanaopendekezwa Kupandishwa Darasa
                        </div>
                        <div class="text-[10px] text-gray-500">
                            Taarifa ya awali iliyoandaliwa kupitia mfumo wa E-Matokeo
                        </div>
                    </div>
                </div>

                <!-- Intro text -->
                <p class="mb-3 text-[11px] text-gray-700">
                    Hii ni taarifa fupi ya wanafunzi wanaopendekezwa kupandishwa darasa kwa kuzingatia mwenendo wa jumla wa masomo na nidhamu.
                    Katika toleo lijalo la mfumo, taarifa hizi zitahesabiwa moja kwa moja kutokana na matokeo halisi ya mitihani ya muhula na mwaka.
                </p>

                <!-- Students table -->
                <div class="overflow-hidden rounded-md border border-gray-200">
                    <table class="w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600">
                                <th class="border-b border-gray-200 px-2 py-1 text-left">#</th>
                                <th class="border-b border-gray-200 px-2 py-1 text-left">Student Name</th>
                                <th class="border-b border-gray-200 px-2 py-1 text-left">Exam No</th>
                                <th class="border-b border-gray-200 px-2 py-1 text-left">Class</th>
                                <th class="border-b border-gray-200 px-2 py-1 text-center">Gender</th>
                                <th class="border-b border-gray-200 px-2 py-1 text-center">Summary</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="students.length === 0">
                                <td colspan="6" class="px-2 py-3 text-center text-[11px] text-gray-400">
                                    No students found yet. Once you start recording results, this list will show your top 10.
                                </td>
                            </tr>
                            <tr
                                v-for="(student, index) in students"
                                :key="student.exam_number"
                                class="text-gray-700 odd:bg-white even:bg-gray-50"
                            >
                                <td class="border-t border-gray-100 px-2 py-1 align-top">
                                    {{ index + 1 }}
                                </td>
                                <td class="border-t border-gray-100 px-2 py-1 align-top">
                                    {{ student.full_name }}
                                </td>
                                <td class="border-t border-gray-100 px-2 py-1 align-top font-mono">
                                    {{ student.exam_number }}
                                </td>
                                <td class="border-t border-gray-100 px-2 py-1 align-top">
                                    {{ student.class_level }}<span v-if="student.stream">-{{ student.stream }}</span>
                                </td>
                                <td class="border-t border-gray-100 px-2 py-1 text-center align-top">
                                    {{ student.gender || '—' }}
                                </td>
                                <td class="border-t border-gray-100 px-2 py-1 align-top">
                                    Consistent performance and good behaviour. Recommended for promotion.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer note -->
                <div class="mt-4 flex items-center justify-between text-[10px] text-gray-500">
                    <p>
                        This is a preview layout. When results are integrated, you will see average marks, grades and divisions here.
                    </p>
                    <p>Date: {{ new Date().toLocaleDateString() }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    /* Hide everything by default */
    body * {
        visibility: hidden;
    }

    /* Only show the promotion document preview */
    #promote-document,
    #promote-document * {
        visibility: visible;
    }

    /* Position the document at the top for clean printing */
    #promote-document {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        border: none;
        box-shadow: none;
    }
}
</style>
