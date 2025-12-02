<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    hostelStudents: {
        type: Array,
        default: () => [],
    },
});

const searchStudent = ref('');

const filteredStudents = computed(() => {
    const term = searchStudent.value.trim().toLowerCase();
    if (!term) return props.hostelStudents;
    return props.hostelStudents.filter((s) => {
        const name = String(s.student_name || '').toLowerCase();
        const exam = String(s.exam_number || '').toLowerCase();
        return name.includes(term) || exam.includes(term);
    });
});
</script>

<template>
    <Head title="Hostel Students" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Hostel Students
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Directory of registered students in this school. Use allocations page to assign hostels.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <Link
                        :href="route('hostel-allocations.index')"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                    >
                        Open Hostel Allocations
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-600">
                <div>
                    <span class="font-medium text-gray-700">Total hostel students:</span>
                    <span class="ml-1 font-semibold text-gray-900">{{ hostelStudents.length }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-[11px] font-medium text-gray-600">Search student</label>
                    <input
                        v-model="searchStudent"
                        type="text"
                        class="w-52 rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="Type name or exam number"
                    />
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <table class="min-w-full border-collapse text-left text-[11px]">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">#</th>
                            <th class="px-3 py-2">Student</th>
                            <th class="px-3 py-2">Class</th>
                            <th class="px-3 py-2">Hostel</th>
                            <th class="px-3 py-2">Guardian</th>
                            <th class="px-3 py-2 text-right">Fee</th>
                            <th class="px-3 py-2 text-right">Paid</th>
                            <th class="px-3 py-2 text-right">Balance</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredStudents.length">
                            <td colspan="10" class="px-3 py-6 text-center text-[11px] text-gray-400">
                                No hostel students match your search.
                            </td>
                        </tr>
                        <tr
                            v-for="(student, index) in filteredStudents"
                            :key="student.id || index"
                            class="border-b border-gray-100 text-[11px] text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top">{{ index + 1 }}</td>
                            <td class="px-3 py-2 align-top">
                                <div class="font-medium text-gray-900">{{ student.student_name || '-' }}</div>
                                <div class="text-[10px] text-gray-500">Exam: {{ student.exam_number || '-' }}</div>
                            </td>
                            <td class="px-3 py-2 align-top">{{ student.class_name || '-' }}</td>
                            <td class="px-3 py-2 align-top">
                                <div>{{ student.hostel_name || '-' }}</div>
                                <div v-if="student.room_name" class="text-[10px] text-gray-500">Room: {{ student.room_name }}</div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div>{{ student.guardian_name || '-' }}</div>
                                <div v-if="student.guardian_phone" class="text-[10px] text-gray-500">{{ student.guardian_phone }}</div>
                            </td>
                            <td class="px-3 py-2 align-top text-right">TSh {{ student.amount?.toLocaleString?.() ?? student.amount }}</td>
                            <td class="px-3 py-2 align-top text-right">TSh {{ student.paid?.toLocaleString?.() ?? student.paid }}</td>
                            <td class="px-3 py-2 align-top text-right">TSh {{ student.balance?.toLocaleString?.() ?? student.balance }}</td>
                            <td class="px-3 py-2 align-top">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="{
                                    'bg-emerald-50 text-emerald-700': student.status === 'Paid',
                                    'bg-amber-50 text-amber-700': student.status === 'Partial',
                                    'bg-rose-50 text-rose-700': !student.status || student.status === 'Unpaid',
                                }">
                                    {{ student.status || 'Unpaid' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 align-top text-right">
                                <div class="inline-flex items-center gap-1">
                                    <Link
                                        :href="route('hostel-students.joining', student.id)"
                                        class="inline-flex items-center rounded-md bg-white px-2 py-1 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-50"
                                    >
                                        Joining
                                    </Link>
                                    <Link
                                        :href="route('hostel-students.report', student.id)"
                                        class="inline-flex items-center rounded-md bg-white px-2 py-1 text-[10px] font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                                    >
                                        Report
                                    </Link>
                                    <Link
                                        :href="route('hostel-payments.receipt', student.id)"
                                        class="inline-flex items-center rounded-md bg-white px-2 py-1 text-[10px] font-semibold text-indigo-600 ring-1 ring-indigo-200 hover:bg-indigo-50"
                                    >
                                        Receipt
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
