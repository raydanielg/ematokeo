<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    students: {
        type: Array,
        default: () => [],
    },
    classes: {
        type: Array,
        default: () => [],
    },
    subjects: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ class: null, subject: null, q: '' }),
    },
});

const classFilterModel = ref(props.filters?.class || '');
const subjectFilterModel = ref(props.filters?.subject || '');
const qModel = ref(props.filters?.q || '');

const filteredCount = computed(() => (Array.isArray(props.students) ? props.students.length : 0));

const applyFilters = () => {
    router.get(
        route('teacher.students.index'),
        {
            class: classFilterModel.value || null,
            subject: subjectFilterModel.value || null,
            q: qModel.value || null,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const clearFilters = () => {
    classFilterModel.value = '';
    subjectFilterModel.value = '';
    qModel.value = '';
    applyFilters();
};
</script>

<template>
    <Head title="My Students" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">My Students</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Students from your assigned classes only.
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="grid w-full gap-3 sm:grid-cols-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Search</label>
                            <input
                                v-model="qModel"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                placeholder="Search by name or exam number"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Class</label>
                            <select
                                v-model="classFilterModel"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                @change="applyFilters"
                            >
                                <option value="">All assigned classes</option>
                                <option
                                    v-for="c in classes"
                                    :key="c.key"
                                    :value="c.key"
                                >
                                    {{ c.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Subject</label>
                            <select
                                v-model="subjectFilterModel"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                @change="applyFilters"
                            >
                                <option value="">All my subjects</option>
                                <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.label }}</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button
                                type="button"
                                class="inline-flex h-10 items-center justify-center rounded-md bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700"
                                @click="applyFilters"
                            >
                                Apply
                            </button>
                            <button
                                type="button"
                                class="inline-flex h-10 items-center justify-center rounded-md bg-white px-4 text-sm font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                                @click="clearFilters"
                            >
                                Clear
                            </button>
                        </div>
                    </div>

                    <div class="text-xs text-gray-500">
                        Showing <span class="font-semibold text-gray-800">{{ filteredCount }}</span> students
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Exam #</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Full Name</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Class</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Stream</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Gender</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!students || students.length === 0">
                                <td colspan="6" class="px-5 py-6 text-center text-sm text-gray-500">
                                    No students found for your assigned classes.
                                </td>
                            </tr>

                            <tr v-for="s in students" :key="s.id" class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ s.exam_number }}</td>
                                <td class="px-5 py-3 text-gray-800">{{ s.full_name }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ s.class_level }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ s.stream || '—' }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ s.gender || '—' }}</td>
                                <td class="px-5 py-3">
                                    <Link
                                        :href="route('teacher.students.show', s.id)"
                                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700"
                                    >
                                        View
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
