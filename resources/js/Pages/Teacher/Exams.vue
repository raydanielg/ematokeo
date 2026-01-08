<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    exams: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ q: '' }),
    },
});

const qModel = ref(props.filters?.q || '');

const apply = () => {
    router.get(route('teacher.exams.index'), { q: qModel.value || null }, { preserveState: true, preserveScroll: true, replace: true });
};

const filteredCount = computed(() => (Array.isArray(props.exams) ? props.exams.length : 0));
</script>

<template>
    <Head title="My Exams" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">My Exams</h2>
                <p class="mt-1 text-sm text-gray-500">Select an exam to enter marks for your assigned subjects.</p>
            </div>
        </template>

        <div class="space-y-6">
            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="w-full max-w-lg">
                        <label class="block text-xs font-semibold text-gray-700">Search</label>
                        <input
                            v-model="qModel"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Search exam name / type / year"
                            @keyup.enter="apply"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="inline-flex h-10 items-center justify-center rounded-md bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700"
                            @click="apply"
                        >
                            Search
                        </button>
                    </div>
                    <div class="text-xs text-gray-500">Showing <span class="font-semibold text-gray-800">{{ filteredCount }}</span> exams</div>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Name</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Type</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Year</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!exams || exams.length === 0">
                                <td colspan="4" class="px-5 py-6 text-center text-sm text-gray-500">
                                    No exams found.
                                </td>
                            </tr>
                            <tr v-for="e in exams" :key="e.id" class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ e.name }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ e.type || '—' }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ e.academic_year || '—' }}</td>
                                <td class="px-5 py-3">
                                    <Link
                                        :href="route('teacher.exams.marks', { exam: e.id })"
                                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700"
                                    >
                                        Enter Marks
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
