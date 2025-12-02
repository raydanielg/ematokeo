<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    subjects: {
        type: Array,
        default: () => [],
    },
    classLevels: {
        type: Array,
        default: () => [],
    },
    topics: {
        type: Array,
        default: () => [],
    },
    bulkPreview: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    subject_id: '',
    class_level: '',
    title: '',
    description: '',
});

const bulkForm = useForm({
    file: null,
});

const submit = () => {
    form.post(route('topics.store'));
};

const handleBulkFileChange = (event) => {
    const [file] = event.target.files;
    bulkForm.file = file || null;
};

const submitBulkPreview = () => {
    if (!bulkForm.file) return;
    bulkForm.post(route('topics.bulk-preview'), {
        forceFormData: true,
        preserveScroll: true,
    });
};

const confirmBulkSave = () => {
    if (!props.bulkPreview || props.bulkPreview.length === 0) return;

    const rows = props.bulkPreview.filter((row) => !row.has_error);
    if (rows.length === 0) return;

    router.post(route('topics.bulk-store'), { rows }, { preserveScroll: true });
};

const subjectMap = computed(() => {
    const map = {};
    (props.subjects || []).forEach((s) => {
        map[s.id] = s;
    });
    return map;
});
</script>

<template>
    <Head title="Topics" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Topics
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Add topics based on subject and class/form level.
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Add topic form -->
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <h3 class="mb-3 text-sm font-semibold text-gray-800">
                    Add Topic
                </h3>
                <form class="grid gap-3 text-xs md:grid-cols-2" @submit.prevent="submit">
                    <div class="md:col-span-1">
                        <label class="mb-1 block text-[11px] font-medium">Subject</label>
                        <select
                            v-model="form.subject_id"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="" disabled>Select subject</option>
                            <option
                                v-for="s in subjects"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.subject_code }} - {{ s.name }}
                            </option>
                        </select>
                    </div>
                    <div class="md:col-span-1">
                        <label class="mb-1 block text-[11px] font-medium">Class / Form</label>
                        <select
                            v-model="form.class_level"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="">Select class/form (optional)</option>
                            <option
                                v-for="lvl in classLevels"
                                :key="lvl"
                                :value="lvl"
                            >
                                {{ lvl }}
                            </option>
                        </select>
                    </div>
                    <div class="md:col-span-1">
                        <label class="mb-1 block text-[11px] font-medium">Topic Title</label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="e.g. Introduction to Algebra"
                        />
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-[11px] font-medium">Short Description (optional)</label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        />
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                            :disabled="form.processing"
                        >
                            Save Topic
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bulk upload -->
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">
                            Bulk Upload Topics
                        </h3>
                        <p class="mt-0.5 text-[11px] text-gray-500">
                            Download the template, fill topics in Excel/CSV, then upload to preview before saving.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a
                            :href="route('topics.template')"
                            class="rounded-md bg-white px-3 py-1.5 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-50"
                        >
                            Download Template
                        </a>
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:gap-3">
                        <input
                            type="file"
                            accept=".csv,.txt,.xlsx,.xls"
                            class="w-full max-w-xs rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            @change="handleBulkFileChange"
                        />
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                            :disabled="bulkForm.processing || !bulkForm.file"
                            @click="submitBulkPreview"
                        >
                            Upload & Preview
                        </button>
                    </div>

                    <div v-if="bulkPreview && bulkPreview.length" class="mt-3 rounded-md border border-emerald-100 bg-emerald-50 p-3">
                        <div class="mb-2 flex items-center justify-between text-[11px]">
                            <span class="font-medium text-emerald-900">
                                Preview ({{ bulkPreview.length }} rows)
                            </span>
                            <button
                                type="button"
                                class="rounded-md bg-emerald-600 px-3 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                                @click="confirmBulkSave"
                            >
                                Confirm & Save Valid Rows
                            </button>
                        </div>
                        <div class="max-h-64 overflow-auto text-[11px]">
                            <table class="min-w-full border-collapse text-left">
                                <thead class="bg-emerald-100">
                                    <tr>
                                        <th class="border-b border-emerald-200 px-2 py-1">Subject Code</th>
                                        <th class="border-b border-emerald-200 px-2 py-1">Class / Form</th>
                                        <th class="border-b border-emerald-200 px-2 py-1">Title</th>
                                        <th class="border-b border-emerald-200 px-2 py-1">Description</th>
                                        <th class="border-b border-emerald-200 px-2 py-1 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(row, index) in bulkPreview"
                                        :key="index"
                                        :class="row.has_error ? 'bg-red-50' : 'bg-white'"
                                    >
                                        <td class="border-b border-gray-200 px-2 py-1">
                                            {{ row.subject_code || '-' }}
                                        </td>
                                        <td class="border-b border-gray-200 px-2 py-1">
                                            {{ row.class_level || '-' }}
                                        </td>
                                        <td class="border-b border-gray-200 px-2 py-1">
                                            {{ row.title || '-' }}
                                        </td>
                                        <td class="border-b border-gray-200 px-2 py-1">
                                            {{ row.description || '-' }}
                                        </td>
                                        <td class="border-b border-gray-200 px-2 py-1 text-center">
                                            <span
                                                v-if="row.has_error"
                                                class="rounded bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700"
                                            >
                                                Fix subject/title
                                            </span>
                                            <span
                                                v-else
                                                class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800"
                                            >
                                                Ready
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">
                            Rows marked in red have missing or unknown subject code or title and will be skipped when saving.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Topics list -->
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-700">
                        Existing Topics ({{ topics.length }})
                    </p>
                </div>
                <div v-if="topics.length === 0" class="text-[11px] text-gray-500">
                    No topics added yet.
                </div>
                <div v-else class="space-y-2 text-xs">
                    <div
                        v-for="topic in topics"
                        :key="topic.id"
                        class="rounded border border-gray-200 bg-slate-50 px-3 py-2"
                    >
                        <div class="flex items-center justify-between">
                            <div class="font-semibold text-gray-800">
                                {{ topic.title }}
                            </div>
                            <div class="text-[10px] text-gray-500">
                                {{ topic.class_level || 'No class level' }}
                            </div>
                        </div>
                        <div class="mt-0.5 text-[11px] text-gray-600">
                            <span class="font-medium">
                                {{ subjectMap[topic.subject_id]?.subject_code }} -
                                {{ subjectMap[topic.subject_id]?.name }}
                            </span>
                        </div>
                        <div v-if="topic.description" class="mt-0.5 text-[11px] text-gray-600">
                            {{ topic.description }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
