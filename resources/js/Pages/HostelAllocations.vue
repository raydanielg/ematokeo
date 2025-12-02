<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    allocations: {
        type: Array,
        default: () => [],
    },
    hostels: {
        type: Array,
        default: () => [],
    },
    rooms: {
        type: Array,
        default: () => [],
    },
});

const showAddModal = ref(false);
const currentStep = ref(1); // 1: select student, 2: details
const studentSearchTerm = ref('');
const studentResults = ref([]);
const searchingStudents = ref(false);
const selectedStudent = ref(null);

const form = useForm({
    student_id: '',
    hostel_id: '',
    hostel_room_id: '',
    fee_amount: 0,
    guardian_name: '',
    guardian_phone: '',
    guardian_relationship: '',
    notes: '',
});

const filteredRooms = computed(() => {
    if (!form.hostel_id) return [];
    return props.rooms.filter((r) => String(r.hostel_id) === String(form.hostel_id));
});

const openAddModal = () => {
    showAddModal.value = true;
    currentStep.value = 1;
    studentSearchTerm.value = '';
    studentResults.value = [];
    selectedStudent.value = null;
    form.reset();
};

const closeAddModal = () => {
    showAddModal.value = false;
};

const searchStudents = async () => {
    const term = studentSearchTerm.value.trim();
    if (term.length < 2) {
        studentResults.value = [];
        return;
    }
    searchingStudents.value = true;
    try {
        const response = await window.axios.get(route('hostel-students.search'), {
            params: { q: term },
        });
        studentResults.value = response.data || [];
    } catch (e) {
        studentResults.value = [];
    } finally {
        searchingStudents.value = false;
    }
};

const selectStudent = (student) => {
    selectedStudent.value = student;
    form.student_id = student.id;
    studentSearchTerm.value = `${student.full_name} (${student.exam_number || ''})`;
    studentResults.value = [];
};

const submitAllocation = () => {
    form.post(route('hostel-allocations.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Hostel Allocations" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Allocations
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        See which students are allocated to which hostel rooms and beds.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                        @click="openAddModal"
                    >
                        + Add Student in Hostel
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div class="overflow-x-auto rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <table class="min-w-full border-collapse text-left text-[11px]">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">#</th>
                            <th class="px-3 py-2">Student</th>
                            <th class="px-3 py-2">Class</th>
                            <th class="px-3 py-2">Hostel</th>
                            <th class="px-3 py-2">Room</th>
                            <th class="px-3 py-2 text-right">Fee</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!allocations.length">
                            <td colspan="7" class="px-3 py-6 text-center text-[11px] text-gray-400">
                                No hostel allocations yet.
                            </td>
                        </tr>
                        <tr
                            v-for="(item, index) in allocations"
                            :key="item.id || index"
                            class="border-b border-gray-100 text-[11px] text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top">{{ index + 1 }}</td>
                            <td class="px-3 py-2 align-top">{{ item.student_name || '-' }}</td>
                            <td class="px-3 py-2 align-top">{{ item.class_name || '-' }}</td>
                            <td class="px-3 py-2 align-top">{{ item.hostel_name || '-' }}</td>
                            <td class="px-3 py-2 align-top">{{ item.room_name || '-' }}</td>
                            <td class="px-3 py-2 align-top text-right">TSh {{ item.fee_amount?.toLocaleString?.() ?? item.fee_amount }}</td>
                            <td class="px-3 py-2 align-top text-right">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-white px-2 py-1 text-[10px] font-semibold text-indigo-600 ring-1 ring-indigo-200 hover:bg-indigo-50"
                                >
                                    Preview
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add allocation modal -->
        <div
            v-if="showAddModal"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-xl rounded-2xl bg-white p-5 text-xs text-gray-700 shadow-2xl">
                <div class="mb-3 flex items-start justify-between gap-4 border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-emerald-800">
                            Add Student in Hostel
                        </h3>
                        <p class="mt-1 text-[11px] text-gray-500">
                            Step {{ currentStep }} of 2 &mdash;
                            <span v-if="currentStep === 1">Search and select the student from your school.</span>
                            <span v-else>Fill guardian details, choose hostel and set hostel fee.</span>
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[11px] font-bold text-gray-600 hover:bg-gray-200"
                        @click="closeAddModal"
                    >
                        ×
                    </button>
                </div>

                <div class="space-y-3">
                    <!-- STEP 1: student search -->
                    <div v-if="currentStep === 1">
                        <label class="text-[11px] font-medium text-emerald-700">Search student</label>
                        <div class="relative mt-1">
                            <input
                                v-model="studentSearchTerm"
                                @input="searchStudents"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="Type name or exam number"
                            />
                            <div
                                v-if="studentResults.length"
                                class="absolute z-10 mt-1 max-h-40 w-full overflow-auto rounded-md border border-gray-200 bg-white text-[11px] shadow-lg"
                            >
                                <button
                                    v-for="stu in studentResults"
                                    :key="stu.id"
                                    type="button"
                                    class="flex w-full items-start justify-between px-3 py-1.5 text-left hover:bg-emerald-50"
                                    @click="selectStudent(stu)"
                                >
                                    <span>
                                        <span class="font-semibold">{{ stu.full_name }}</span>
                                        <span v-if="stu.exam_number" class="ml-1 text-[10px] text-gray-500">({{ stu.exam_number }})</span>
                                    </span>
                                    <span class="text-[10px] text-gray-500">{{ stu.class_level }} {{ stu.stream }}</span>
                                </button>
                            </div>
                            <p v-if="searchingStudents" class="mt-1 text-[10px] text-gray-400">
                                Searching...
                            </p>
                        </div>
                        <p v-if="selectedStudent" class="mt-2 text-[11px] text-gray-700">
                            <span class="font-semibold">Selected student:</span>
                            <span class="ml-1 font-semibold text-gray-900">{{ selectedStudent.full_name }}</span>
                            <span v-if="selectedStudent.exam_number" class="ml-1 text-[10px] text-gray-500">({{ selectedStudent.exam_number }})</span>
                            <span v-if="selectedStudent.class_level" class="ml-2 text-[10px] text-gray-500">
                                Class: {{ selectedStudent.class_level }} {{ selectedStudent.stream }}
                            </span>
                        </p>
                    </div>

                    <!-- STEP 2: guardian + hostel details -->
                    <div v-if="currentStep === 2" class="space-y-3">
                        <div class="rounded-md bg-emerald-50 px-3 py-2 text-[11px] text-emerald-800">
                            <p class="font-semibold">Student:</p>
                            <p>
                                {{ selectedStudent?.full_name }}
                                <span v-if="selectedStudent?.class_level" class="ml-1 text-[10px] text-emerald-700">
                                    ({{ selectedStudent.class_level }} {{ selectedStudent.stream }})
                                </span>
                            </p>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="text-[11px] font-medium text-gray-600">Guardian name</label>
                                <input
                                    v-model="form.guardian_name"
                                    type="text"
                                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="text-[11px] font-medium text-gray-600">Guardian phone</label>
                                <input
                                    v-model="form.guardian_phone"
                                    type="text"
                                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="e.g. 07xx xxx xxx"
                                />
                            </div>
                            <div>
                                <label class="text-[11px] font-medium text-gray-600">Relationship</label>
                                <input
                                    v-model="form.guardian_relationship"
                                    type="text"
                                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="e.g. Mother, Father, Guardian"
                                />
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="text-[11px] font-medium text-emerald-700">Hostel</label>
                                <select
                                    v-model="form.hostel_id"
                                    class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                >
                                    <option value="" disabled>Select hostel</option>
                                    <option
                                        v-for="hostel in hostels"
                                        :key="hostel.id"
                                        :value="String(hostel.id)"
                                    >
                                        {{ hostel.name }} ({{ hostel.type }})
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[11px] font-medium text-emerald-700">Room (optional)</label>
                                <select
                                    v-model="form.hostel_room_id"
                                    class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                >
                                    <option value="">No specific room</option>
                                    <option
                                        v-for="room in filteredRooms"
                                        :key="room.id"
                                        :value="String(room.id)"
                                    >
                                        {{ room.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="text-[11px] font-medium text-gray-600">Hostel fee (TSh)</label>
                                <input
                                    v-model.number="form.fee_amount"
                                    type="number"
                                    min="0"
                                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="text-[11px] font-medium text-gray-600">Notes (optional)</label>
                                <textarea
                                    v-model="form.notes"
                                    rows="2"
                                    class="mt-1 w-full resize-none rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="Extra info e.g. special needs, payment plan, etc."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2 text-[11px]">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="closeAddModal"
                    >
                        Cancel
                    </button>
                    <button
                        v-if="currentStep === 1"
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="!selectedStudent || form.processing"
                        @click="currentStep = 2"
                    >
                        Next
                    </button>
                    <button
                        v-else
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="
                            form.processing ||
                            !form.student_id ||
                            !form.hostel_id ||
                            !form.guardian_name ||
                            !form.guardian_phone ||
                            !form.fee_amount
                        "
                        @click="submitAllocation"
                    >
                        Save allocation
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
