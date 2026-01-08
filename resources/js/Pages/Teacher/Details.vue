<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    teacher: {
        type: Object,
        default: null,
    },
    assignments: {
        type: Array,
        default: () => [],
    },
});

const teacherName = computed(() => props.teacher?.name || 'Teacher');
const assignmentCount = computed(() => (Array.isArray(props.assignments) ? props.assignments.length : 0));
</script>

<template>
    <Head title="My Details" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">My Details</h2>
                <p class="mt-1 text-sm text-gray-500">👋 {{ teacherName }}</p>
            </div>
        </template>

        <div class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100 lg:col-span-1">
                    <h3 class="text-sm font-semibold text-gray-800">Profile</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Full Name</dt>
                            <dd class="font-semibold text-gray-900">{{ teacher?.name || '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Username</dt>
                            <dd class="font-semibold text-gray-900">{{ teacher?.username || '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Email</dt>
                            <dd class="font-semibold text-gray-900">{{ teacher?.email || '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Phone</dt>
                            <dd class="font-semibold text-gray-900">{{ teacher?.phone || '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Check #</dt>
                            <dd class="font-semibold text-gray-900">{{ teacher?.check_number || '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100 lg:col-span-2">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Teaching Assignments</h3>
                            <p class="mt-1 text-xs text-gray-500">
                                Showing <span class="font-semibold text-gray-800">{{ assignmentCount }}</span> subject(s) assigned to you.
                            </p>
                        </div>
                    </div>

                    <div v-if="!assignments || assignments.length === 0" class="mt-4 rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-600">
                        No subjects/classes have been assigned to you yet.
                    </div>

                    <div v-else class="mt-4 space-y-3">
                        <div
                            v-for="a in assignments"
                            :key="a.subject_id"
                            class="rounded-lg border border-gray-100 bg-white p-4"
                        >
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ a.subject_code }}
                                        <span class="font-normal text-gray-500">{{ a.subject_name ? `- ${a.subject_name}` : '' }}</span>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">Assigned classes/streams</div>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    v-for="c in a.classes"
                                    :key="c.name"
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100"
                                >
                                    {{ c.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
