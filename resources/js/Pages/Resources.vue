<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const resourceTypes = [
    {
        key: 'books',
        title: 'Books & References',
        description: 'Digital copies of textbooks, reference books and revision materials for different classes and subjects.',
        badge: 'Library',
    },
    {
        key: 'lesson-plans',
        title: 'Lesson Plans',
        description: 'Standard lesson plan templates and examples aligned to the syllabus for daily teaching.',
        badge: 'Teaching',
    },
    {
        key: 'schemes',
        title: 'Schemes of Work',
        description: 'Annual and termly schemes of work organised by subject and class level.',
        badge: 'Planning',
    },
    {
        key: 'assessments',
        title: 'Assessment Items',
        description: 'Question banks, past papers, and continuous assessment tools for teachers.',
        badge: 'Assessment',
    },
    {
        key: 'activities',
        title: 'Classroom Activities',
        description: 'Group work ideas, projects, and co-curricular activity guides.',
        badge: 'Activities',
    },
    {
        key: 'policies',
        title: 'Policies & Guidelines',
        description: 'Important circulars, policies and official teaching guidelines from authorities.',
        badge: 'Admin',
    },
];
</script>

<template>
    <Head title="Resources" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Teaching Resources
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        A central place for books, lesson plans, schemes of work and other materials to support teachers.
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-700">
                        Resource Categories
                    </p>
                    <p class="text-[11px] text-gray-500">
                        Choose a category to manage or upload resources (some sections are coming soon).
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="type in resourceTypes"
                        :key="type.key"
                        class="flex flex-col rounded-lg border border-gray-200 bg-slate-50 p-4 shadow-sm"
                    >
                        <div class="mb-2 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800">
                                {{ type.title }}
                            </h3>
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-emerald-100">
                                {{ type.badge }}
                            </span>
                        </div>
                        <p class="mb-3 text-[11px] leading-snug text-gray-600">
                            {{ type.description }}
                        </p>
                        <div class="mt-auto flex justify-end">
                            <Link
                                v-if="type.key === 'books'"
                                :href="route('resources.books.index')"
                                class="rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-medium text-white shadow-sm hover:bg-emerald-700"
                            >
                                Open
                            </Link>
                            <Link
                                v-else-if="type.key === 'lesson-plans'"
                                :href="route('resources.lesson-plans.index')"
                                class="rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-medium text-white shadow-sm hover:bg-emerald-700"
                            >
                                Open
                            </Link>
                            <button
                                v-else
                                type="button"
                                class="rounded-md bg-white px-3 py-1.5 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-50"
                                disabled
                            >
                                Open (coming soon)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
