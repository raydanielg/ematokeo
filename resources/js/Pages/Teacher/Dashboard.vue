<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    studentsCount: {
        type: Number,
        default: 0,
    },
    pendingMarksCount: {
        type: Number,
        default: 0,
    },
    timetablesCount: {
        type: Number,
        default: 0,
    },
    announcementsCount: {
        type: Number,
        default: 0,
    },
});

const page = usePage();
const user = computed(() => page?.props?.auth?.user || null);
</script>

<template>
    <Head title="Teacher Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                    Teacher Dashboard
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    👋 Welcome, {{ user?.name || 'Teacher' }}
                </p>
            </div>
        </template>

        <div class="space-y-8">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">My Students</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ studentsCount }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Pending Marks</p>
                            <p class="mt-2 text-3xl font-bold text-blue-600">{{ pendingMarksCount }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Timetables</p>
                            <p class="mt-2 text-3xl font-bold text-amber-600">{{ timetablesCount }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100">
                            <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h8m-8 5h12" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Announcements</p>
                            <p class="mt-2 text-3xl font-bold text-purple-600">{{ announcementsCount }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8a9 9 0 110-18 9 9 0 010 18z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100 lg:col-span-1">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                        <p class="mt-1 text-xs text-gray-500">Common teacher tasks</p>
                    </div>
                    <div class="p-6 space-y-3">
                        <a
                            href="/exams/marks"
                            class="block rounded-md bg-gray-50 px-4 py-3 text-sm font-medium text-gray-800 hover:bg-white ring-1 ring-gray-100"
                        >
                            Enter Marks
                        </a>
                        <a
                            href="/students"
                            class="block rounded-md bg-gray-50 px-4 py-3 text-sm font-medium text-gray-800 hover:bg-white ring-1 ring-gray-100"
                        >
                            View Students
                        </a>
                        <a
                            href="/timetables"
                            class="block rounded-md bg-gray-50 px-4 py-3 text-sm font-medium text-gray-800 hover:bg-white ring-1 ring-gray-100"
                        >
                            View Timetables
                        </a>
                    </div>
                </div>

                <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100 lg:col-span-2">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">Teacher Notes</h3>
                        <p class="mt-1 text-xs text-gray-500">Updates and guidance</p>
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-gray-600">
                            This dashboard matches the admin UI style, but shows teacher tools only.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
