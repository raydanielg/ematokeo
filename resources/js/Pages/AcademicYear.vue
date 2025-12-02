<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    years: {
        type: Array,
        default: () => [],
    },
});

const showAddModal = ref(false);
const newYear = ref('');
const newNotes = ref('');

const currentYear = computed(() => props.years.find((y) => y.is_current));

const openAddModal = () => {
    newYear.value = '';
    newNotes.value = '';
    showAddModal.value = true;
};

const createYear = () => {
    if (!newYear.value) return;

    router.post(
        route('settings.academic-year.store'),
        {
            year: newYear.value,
            notes: newNotes.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showAddModal.value = false;
            },
        },
    );
};

const setCurrent = (id) => {
    router.put(
        route('settings.academic-year.set-current', id),
        {},
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <Head title="Academic Year" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Academic Year
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage your school academic years. The current year controls where new student and exam data is attached.
                    </p>
                    <div v-if="currentYear" class="mt-2 inline-flex items-center gap-2">
                        <span class="text-[11px] uppercase tracking-wide text-gray-500">
                            Active year:
                        </span>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
                            {{ currentYear.year }}
                        </span>
                    </div>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none"
                    @click="openAddModal"
                >
                    <span class="text-base leading-none">+</span>
                    <span>Add Academic Year</span>
                </button>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 flex items-center justify-between">
                <p class="text-sm font-medium text-gray-700">
                    Academic years ({{ years.length }})
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">Year</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Students</th>
                            <th class="px-3 py-2">Results</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="years.length === 0">
                            <td colspan="5" class="px-3 py-4 text-center text-xs text-gray-500">
                                No academic years defined yet.
                            </td>
                        </tr>
                        <tr
                            v-for="year in years"
                            :key="year.id"
                            class="border-b border-gray-100 text-xs text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top font-mono text-xs text-gray-900">
                                {{ year.year }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                <span
                                    v-if="year.is_current"
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-200"
                                >
                                    Current
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500 ring-1 ring-gray-200"
                                >
                                    Past
                                </span>
                            </td>
                            <td class="px-3 py-2 align-top text-[11px] text-gray-600">
                                {{ year.students_count }} students
                            </td>
                            <td class="px-3 py-2 align-top text-[11px] text-gray-600">
                                {{ year.results_count }} records
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex justify-end gap-2">
                                    <button
                                        v-if="!year.is_current"
                                        type="button"
                                        class="rounded-md bg-emerald-50 px-2 py-1 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                                        @click="setCurrent(year.id)"
                                    >
                                        Set as current
                                    </button>
                                    <span
                                        v-else
                                        class="text-[11px] text-gray-400"
                                    >
                                        Active year
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="mt-4 text-[11px] text-gray-500">
                Changing the current academic year will affect where new students, classes and exam data are recorded. Past years act as history for reporting.
            </p>
        </div>

        <!-- Add Academic Year modal -->
        <div
            v-if="showAddModal"
            class="fixed inset-0 z-30 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">
                        Add Academic Year
                    </h3>
                    <button
                        type="button"
                        class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                        @click="showAddModal = false"
                    >
                        ✕
                    </button>
                </div>
                <div class="space-y-3 text-xs text-gray-700">
                    <div>
                        <label class="mb-1 block font-medium">Year</label>
                        <input
                            v-model="newYear"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="e.g. 2025"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block font-medium">Notes (optional)</label>
                        <textarea
                            v-model="newNotes"
                            rows="3"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="Any extra information about this academic year"
                        ></textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="showAddModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                        @click="createYear"
                    >
                        Save
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
