<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    teachers: {
        type: Array,
        default: () => [],
    },
    subjects: {
        type: Array,
        default: () => [],
    },
});

const rows = reactive(
    props.teachers.map((t) => ({
        id: t.id,
        name: t.name,
        email: t.email,
        username: t.username,
        subject_ids: [...t.subject_ids],
    })),
);

const toggleSubject = (teacherId, subjectId) => {
    const row = rows.find((r) => r.id === teacherId);
    if (!row) return;

    const idx = row.subject_ids.indexOf(subjectId);
    if (idx === -1) {
        row.subject_ids.push(subjectId);
    } else {
        row.subject_ids.splice(idx, 1);
    }
};

const saveAssignments = () => {
    const payload = rows.map((r) => ({
        teacher_id: r.id,
        subject_ids: r.subject_ids,
    }));

    router.post(route('subjects.assign-teachers.save'), { assignments: payload }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Assign Teachers to Subjects" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Assign Teachers to Subjects
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Inline assign one or more subjects to each teacher. Changes are saved for the whole table.
                    </p>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none"
                    @click="saveAssignments"
                >
                    <span class="text-base leading-none">✔</span>
                    <span>Save Assignments</span>
                </button>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-sm font-medium text-gray-700">
                    Teachers ({{ rows.length }})
                </p>
                <p class="text-[11px] text-gray-500">
                    Click on subjects to toggle assignment. A teacher can teach multiple subjects.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">Teacher</th>
                            <th class="px-3 py-2">Username / Email</th>
                            <th class="px-3 py-2">Subjects</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="rows.length === 0">
                            <td colspan="3" class="px-3 py-4 text-center text-xs text-gray-500">
                                No teachers found yet. Set some users with role = "teacher" first.
                            </td>
                        </tr>
                        <tr
                            v-for="row in rows"
                            :key="row.id"
                            class="border-b border-gray-100 text-xs text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top">
                                {{ row.name }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-col">
                                    <span class="text-gray-800">{{ row.username || row.email }}</span>
                                    <span class="text-[10px] text-gray-500">{{ row.email }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-wrap gap-1">
                                    <button
                                        v-for="subject in subjects"
                                        :key="subject.id"
                                        type="button"
                                        class="rounded-full px-2 py-0.5 text-[10px] font-medium ring-1"
                                        :class="
                                            row.subject_ids.includes(subject.id)
                                                ? 'bg-emerald-600 text-white ring-emerald-600'
                                                : 'bg-white text-gray-700 ring-gray-200 hover:bg-gray-50'
                                        "
                                        @click="toggleSubject(row.id, subject.id)"
                                    >
                                        {{ subject.subject_code }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
