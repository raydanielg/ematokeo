<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },
    latestExam: {
        type: Object,
        default: null,
    },
    latestResults: {
        type: Array,
        default: () => [],
    },
    latestSummary: {
        type: Object,
        default: () => ({ total: null, average: null }),
    },
});
</script>

<template>
    <Head :title="`Student: ${student.full_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">Student Details</h2>
                <p class="mt-1 text-sm text-gray-500">👨‍🎓 {{ student.full_name }} ({{ student.exam_number }})</p>
            </div>
        </template>

        <div class="space-y-6">
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <Link
                    :href="route('teacher.students.index')"
                    class="inline-flex items-center rounded-md bg-white px-3 py-1.5 font-semibold text-gray-700 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50"
                >
                    ← Back to students
                </Link>

                <a
                    v-if="latestExam"
                    :href="route('students.marks.show', student.id)"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-emerald-700"
                >
                    View Full Results
                </a>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100 lg:col-span-1">
                    <h3 class="text-sm font-semibold text-gray-800">Profile</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Class</dt>
                            <dd class="font-semibold text-gray-900">{{ student.class_level }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Stream</dt>
                            <dd class="font-semibold text-gray-900">{{ student.stream || '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Gender</dt>
                            <dd class="font-semibold text-gray-900">{{ student.gender || '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">DOB</dt>
                            <dd class="font-semibold text-gray-900">{{ student.date_of_birth || '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100 lg:col-span-2">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Latest Results</h3>
                            <p class="mt-1 text-xs text-gray-500">
                                <span v-if="latestExam">{{ latestExam.name }} ({{ latestExam.academic_year }})</span>
                                <span v-else>No exam results found yet.</span>
                            </p>
                        </div>
                        <div v-if="latestExam" class="text-right">
                            <div class="text-xs text-gray-500">Average</div>
                            <div class="text-lg font-bold text-emerald-700">
                                {{ latestSummary.average ?? '—' }}
                            </div>
                        </div>
                    </div>

                    <div v-if="latestExam" class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-600">Subject</th>
                                    <th class="px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-600">Marks</th>
                                    <th class="px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-600">Grade</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="r in latestResults" :key="r.subject_code" class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-900">
                                        {{ r.subject_code }}
                                        <span class="text-xs font-normal text-gray-500">{{ r.subject_name ? `- ${r.subject_name}` : '' }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-gray-700">{{ r.marks ?? '—' }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                            {{ r.grade || '—' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="text-sm font-semibold text-gray-800">Guardian</h3>
                <div class="mt-4 grid gap-4 sm:grid-cols-3 text-sm">
                    <div>
                        <div class="text-xs text-gray-500">Name</div>
                        <div class="font-semibold text-gray-900">{{ student.guardian_name || '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Phone</div>
                        <div class="font-semibold text-gray-900">{{ student.guardian_phone || '—' }}</div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="text-xs text-gray-500">Address</div>
                        <div class="font-semibold text-gray-900">{{ student.guardian_address || '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
