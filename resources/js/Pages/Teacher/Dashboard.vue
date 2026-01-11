<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
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
                    <div class="p-6">
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                            <Link
                                :href="route('teacher.exams.marks')"
                                class="group flex items-start gap-3 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-800 ring-1 ring-gray-100 transition hover:bg-white"
                            >
                                <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-md bg-emerald-100 text-emerald-700">
                                    <span class="material-icons text-[18px] leading-none">edit_note</span>
                                </span>
                                <span class="flex-1">
                                    <span class="block font-semibold">Enter Marks</span>
                                    <span class="mt-0.5 block text-xs text-gray-500">Only your assigned subjects</span>
                                </span>
                                <span class="mt-1 text-gray-400 group-hover:text-emerald-600">→</span>
                            </Link>

                            <Link
                                :href="route('teacher.students.index')"
                                class="group flex items-start gap-3 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-800 ring-1 ring-gray-100 transition hover:bg-white"
                            >
                                <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-md bg-blue-100 text-blue-700">
                                    <span class="material-icons text-[18px] leading-none">groups</span>
                                </span>
                                <span class="flex-1">
                                    <span class="block font-semibold">My Students</span>
                                    <span class="mt-0.5 block text-xs text-gray-500">Your assigned classes only</span>
                                </span>
                                <span class="mt-1 text-gray-400 group-hover:text-emerald-600">→</span>
                            </Link>

                            <Link
                                :href="route('teacher.timetables.my')"
                                class="group flex items-start gap-3 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-800 ring-1 ring-gray-100 transition hover:bg-white"
                            >
                                <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-md bg-amber-100 text-amber-700">
                                    <span class="material-icons text-[18px] leading-none">calendar_month</span>
                                </span>
                                <span class="flex-1">
                                    <span class="block font-semibold">My Timetable</span>
                                    <span class="mt-0.5 block text-xs text-gray-500">Published timetables only</span>
                                </span>
                                <span class="mt-1 text-gray-400 group-hover:text-emerald-600">→</span>
                            </Link>
                        </div>
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
