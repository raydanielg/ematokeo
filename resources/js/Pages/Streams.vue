<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

const props = defineProps({
    classes: {
        type: Array,
        default: () => [],
    },
    subjects: {
        type: Array,
        default: () => [],
    },
});

const groupedByLevel = computed(() => {
    const groups = {};
    for (const c of props.classes) {
        const key = c.level || 'Other';
        if (!groups[key]) groups[key] = [];
        groups[key].push(c);
    }
    return groups;
});

const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const form = reactive({
    level: '',
    stream: '',
    description: '',
    subject_ids: [],
});

const openForm = () => {
    showForm.value = true;
    isEditing.value = false;
    editingId.value = null;
    form.level = '';
    form.stream = '';
    form.description = '';

    // Default: select first 7 subjects if available
    const ids = (props.subjects || []).slice(0, 7).map((s) => s.id);
    form.subject_ids = ids;
};

const closeForm = () => {
    showForm.value = false;
};

const toggleSubject = (id) => {
    const idx = form.subject_ids.indexOf(id);
    if (idx === -1) {
        form.subject_ids.push(id);
    } else {
        form.subject_ids.splice(idx, 1);
    }
};

const editStream = (stream) => {
    showForm.value = true;
    isEditing.value = true;
    editingId.value = stream.id;
    form.level = stream.level ?? '';
    form.stream = stream.stream ?? '';
    form.description = stream.description ?? '';
    form.subject_ids = Array.isArray(stream.subject_ids) ? [...stream.subject_ids] : [];
};

const deleteStream = (stream) => {
    if (!confirm('Delete this stream? This cannot be undone.')) {
        return;
    }

    router.delete(route('streams.destroy', stream.id));
};

const saveStream = () => {
    if (!form.level || !form.stream || form.subject_ids.length < 7) {
        alert('Please select level, stream and at least 7 subjects.');
        return;
    }

    const payload = {
        level: Number(form.level),
        stream: form.stream,
        description: form.description,
        subject_ids: form.subject_ids,
    };

    if (isEditing.value && editingId.value) {
        router.post(route('streams.update', editingId.value), payload);
    } else {
        router.post(route('streams.store'), payload);
    }
};
</script>

<template>
    <Head title="Streams" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Streams
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Overview of streams inside each class level (e.g. Form I A, Form I B) with their subjects.
                    </p>
                </div>
                <div>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                        @click="openForm"
                    >
                        Add Stream
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Add / Edit Stream form panel -->
            <div v-if="showForm" class="rounded-xl bg-white p-4 text-xs text-gray-800 shadow-sm ring-1 ring-emerald-200">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">
                            {{ isEditing ? 'Edit Stream' : 'Add New Stream' }}
                        </h3>
                        <p class="mt-0.5 text-[11px] text-gray-500">
                            Define level, stream name and assign subjects. At least 7 subjects are required.
                        </p>
                    </div>
                    <button type="button" class="text-[11px] text-gray-500 hover:text-gray-700" @click="closeForm">Close</button>
                </div>

                <div class="grid gap-3 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium">Level (Form)</label>
                        <select
                            v-model="form.level"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="">Select level</option>
                            <option value="1">Form I</option>
                            <option value="2">Form II</option>
                            <option value="3">Form III</option>
                            <option value="4">Form IV</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium">Stream (e.g. A, B)</label>
                        <input
                            v-model="form.stream"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] uppercase focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="A"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium">Description (optional)</label>
                        <input
                            v-model="form.description"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="Short note about this stream"
                        />
                    </div>
                </div>

                <div class="mt-3 border-t border-gray-100 pt-3">
                    <div class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-gray-500">
                        Subjects (minimum 7)
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <label
                            v-for="s in subjects"
                            :key="s.id"
                            class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-[10px] text-gray-700 hover:bg-gray-100"
                        >
                            <input
                                type="checkbox"
                                class="h-3 w-3 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                :value="s.id"
                                :checked="form.subject_ids.includes(s.id)"
                                @change="toggleSubject(s.id)"
                            />
                            <span>{{ s.subject_code }}</span>
                        </label>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">
                        At least 7 subjects are required for a stream. The first 7 are pre-selected when creating a new one.
                    </p>
                </div>

                <div class="mt-3 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-gray-100 px-3 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-gray-200"
                        @click="closeForm"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-emerald-700"
                        @click="saveStream"
                    >
                        {{ isEditing ? 'Update Stream' : 'Save Stream' }}
                    </button>
                </div>
            </div>

            <div
                v-for="(items, level) in groupedByLevel"
                :key="level"
                class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100"
            >
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">
                            {{ level === 'Other' ? 'Other Streams' : level }}
                        </h3>
                        <p class="text-[11px] text-gray-500">
                            {{ items.length }} stream(s) in this level.
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="c in items"
                        :key="c.id"
                        class="flex flex-col justify-between rounded-lg border border-gray-200 bg-white p-3 text-xs text-gray-800 shadow-sm"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700">
                                        {{ c.name }}
                                    </div>
                                    <div class="text-[10px] text-gray-500">
                                        Stream: {{ c.stream || '—' }}
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <button
                                        type="button"
                                        class="rounded border border-emerald-100 bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 hover:bg-emerald-100"
                                        @click="editStream(c)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded border border-red-100 bg-red-50 px-2 py-0.5 text-[10px] font-medium text-red-700 hover:bg-red-100"
                                        @click="deleteStream(c)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                            <p v-if="c.description" class="mt-1 text-[11px] text-gray-600">
                                {{ c.description }}
                            </p>
                        </div>

                        <div class="mt-2 border-t border-gray-100 pt-2">
                            <div class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-gray-500">
                                Subjects
                            </div>
                            <div v-if="c.subject_codes.length" class="flex flex-wrap gap-1">
                                <span
                                    v-for="code in c.subject_codes"
                                    :key="code"
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-emerald-100"
                                >
                                    {{ code }}
                                </span>
                            </div>
                            <div v-else class="text-[11px] text-gray-400">
                                No subjects assigned yet.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="Object.keys(groupedByLevel).length === 0" class="rounded-xl bg-white p-6 text-center text-xs text-gray-500 shadow-sm ring-1 ring-gray-100">
                No streams/classes defined yet. Use the Add Stream button above to create one.
            </div>
        </div>
    </AuthenticatedLayout>
</template>
