<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page?.props?.auth?.user || null);
</script>

<template>
    <Head title="Teacher Panel" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                    Teacher Panel
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Karibu, {{ user?.name || 'Teacher' }}
                </p>
            </div>
        </template>

        <div class="space-y-4">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="text-sm font-semibold text-gray-800">Quick Links</div>
                <div class="mt-3 grid gap-3 md:grid-cols-3">
                    <a
                        href="/teachers"
                        class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 hover:bg-white"
                    >
                        Teachers
                    </a>
                    <a
                        href="/students"
                        class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 hover:bg-white"
                    >
                        Students
                    </a>
                    <a
                        href="/timetables"
                        class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 hover:bg-white"
                    >
                        Timetables
                    </a>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="text-sm font-semibold text-gray-800">Info</div>
                <div class="mt-2 text-sm text-gray-600">
                    This panel is for teachers. More teacher tools can be added here.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
