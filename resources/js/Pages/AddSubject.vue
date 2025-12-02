<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const props = defineProps({
    subjects: {
        type: Array,
        default: () => [],
    },
    classes: {
        type: Array,
        default: () => [],
    },
});

const form = reactive({
    class_id: '',
    subject_ids: [],
    assign_to_current_user: false,
});

const isSaving = ref(false);

const toggleSubject = (id) => {
    const idx = form.subject_ids.indexOf(id);
    if (idx === -1) {
        form.subject_ids.push(id);
    } else {
        form.subject_ids.splice(idx, 1);
    }
};

const importSubjects = () => {
    if (isSaving.value) return;
    isSaving.value = true;

    router.post(
        route('subjects.import'),
        {
            class_id: form.class_id,
            subject_ids: form.subject_ids,
            assign_to_current_user: form.assign_to_current_user,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isSaving.value = false;
            },
            onError: () => {
                isSaving.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="Import Subjects" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Import Subjects
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Select a class and import subjects from the pre-defined list for this school and user.
                    </p>
                </div>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="space-y-4 text-xs text-gray-700">
                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block font-medium">Select Class</label>
                        <select
                            v-model="form.class_id"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="" disabled>Select class...</option>
                            <option
                                v-for="c in classes"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 pt-5 md:pt-0">
                        <input
                            id="assign_to_me_import"
                            v-model="form.assign_to_current_user"
                            type="checkbox"
                            class="h-3 w-3 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                        />
                        <label for="assign_to_me_import" class="text-[11px] text-gray-600">
                            Also assign selected subjects to me as a teacher.
                        </label>
                    </div>
                </div>

                <div>
                    <p class="mb-1 text-[11px] font-medium text-gray-700">Add Subject(s) from the list below</p>
                    <p class="mb-2 text-[11px] text-gray-500">
                        Chagua masomo unayotaka kuyaleta kwa hili darasa kisha bonyeza <span class="font-semibold">Import
                        Selected</span> chini.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <label
                            v-for="subject in subjects"
                            :key="subject.id"
                            class="inline-flex items-center gap-1 rounded-full border px-2 py-1 text-[10px] font-medium"
                            :class="
                                form.subject_ids.includes(subject.id)
                                    ? 'border-emerald-500 bg-emerald-50 text-emerald-800'
                                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
                            "
                        >
                            <input
                                type="checkbox"
                                class="h-3 w-3 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                :value="subject.id"
                                :checked="form.subject_ids.includes(subject.id)"
                                @change="toggleSubject(subject.id)"
                            />
                            <span>{{ subject.subject_code }} - {{ subject.name }}</span>
                        </label>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="$inertia.visit(route('subjects.index'))"
                        :disabled="isSaving"
                    >
                        Back to Subjects
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="!form.class_id || !form.subject_ids.length || isSaving"
                        @click="importSubjects"
                    >
                        <span v-if="!isSaving">Import Selected</span>
                        <span v-else>Importing...</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
