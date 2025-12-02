<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
    exams: {
        type: Array,
        default: () => [],
    },
    classes: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    exam_id: '',
    class_id: '',
    title: '',
    rooms_count: 1,
});

const createPlan = () => {
    form.post(route('sitting-plans.store'), {
        preserveScroll: true,
    });
};

const deletePlan = (id) => {
    if (!confirm('Delete this sitting plan?')) return;
    router.delete(route('sitting-plans.destroy', id));
};
</script>

<template>
    <Head title="Sitting Plans" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Sitting Plans
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Create and manage exam sitting plans by exam and class.
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Create form -->
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">
                    Create Sitting Plan
                </h3>
                <div class="grid gap-3 md:grid-cols-4 text-xs">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium">Exam</label>
                        <select
                            v-model="form.exam_id"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="">
                                -- Select exam --
                            </option>
                            <option
                                v-for="exam in exams"
                                :key="exam.id"
                                :value="exam.id"
                            >
                                {{ exam.name }} ({{ exam.academic_year || '-' }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium">Class</label>
                        <select
                            v-model="form.class_id"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="">
                                -- Select class --
                            </option>
                            <option
                                v-for="cls in classes"
                                :key="cls.id"
                                :value="cls.id"
                            >
                                {{ cls.level || cls.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium">Title</label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="e.g. Midterm Exam Form II A Sitting Plan"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium">Number of Rooms</label>
                        <input
                            v-model.number="form.rooms_count"
                            type="number"
                            min="1"
                            max="50"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        />
                    </div>
                </div>
                <div class="mt-3 flex justify-end">
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                        @click="createPlan"
                    >
                        Create Sitting Plan
                    </button>
                </div>
            </div>

            <!-- Plans list -->
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-700">
                        Sitting Plans ({{ plans.length }})
                    </p>
                    <p class="text-[11px] text-gray-500">
                        View, preview or delete existing sitting plans.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <th class="px-3 py-2">Title</th>
                                <th class="px-3 py-2">Exam</th>
                                <th class="px-3 py-2">Class</th>
                                <th class="px-3 py-2">Rooms</th>
                                <th class="px-3 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="plans.length === 0">
                                <td colspan="5" class="px-3 py-4 text-center text-xs text-gray-500">
                                    No sitting plans created yet.
                                </td>
                            </tr>
                            <tr
                                v-for="plan in plans"
                                :key="plan.id"
                                class="border-b border-gray-100 text-xs text-gray-700 hover:bg-gray-50"
                            >
                                <td class="px-3 py-2 align-top">
                                    {{ plan.title }}
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <div>
                                        {{ plan.exam?.name || '-' }}
                                    </div>
                                    <div class="text-[10px] text-gray-500">
                                        {{ plan.exam?.academic_year || '' }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-top">
                                    {{ plan.class?.name || '-' }}
                                </td>
                                <td class="px-3 py-2 align-top">
                                    {{ plan.rooms_count || 1 }}
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="rounded-md bg-blue-50 px-2 py-1 text-[11px] font-medium text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100"
                                            @click="router.get(route('sitting-plans.show', plan.id))"
                                        >
                                            Preview
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-md bg-red-50 px-2 py-1 text-[11px] font-medium text-red-700 ring-1 ring-red-100 hover:bg-red-100"
                                            @click="deletePlan(plan.id)"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
