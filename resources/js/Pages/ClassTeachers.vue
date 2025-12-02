<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    classes: {
        type: Array,
        default: () => [],
    },
    teachers: {
        type: Array,
        default: () => [],
    },
});

const rows = reactive(
    props.classes.map((c) => ({
        id: c.id,
        name: c.name,
        level: c.level,
        stream: c.stream,
        teacher_id: c.teacher_id,
        teacher_name: c.teacher_name,
    })),
);

const saveAssignments = () => {
    const payload = rows.map((r) => ({
        class_id: r.id,
        teacher_id: r.teacher_id,
    }));

    router.post(route('classes.assign-teachers.save'), { assignments: payload }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Class Teachers" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Class Teachers
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Assign one teacher to be responsible for each class stream.
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                    @click="saveAssignments"
                >
                    Save Class Teacher Assignments
                </button>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">Class</th>
                            <th class="px-3 py-2">Level / Stream</th>
                            <th class="px-3 py-2">Class Teacher</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="rows.length === 0">
                            <td colspan="3" class="px-3 py-4 text-center text-xs text-gray-500">
                                No classes defined yet.
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
                                <div class="flex flex-col text-[11px] text-gray-700">
                                    <span>{{ row.level || '—' }}</span>
                                    <span v-if="row.stream" class="text-[10px] text-gray-500">Stream: {{ row.stream }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <select
                                    v-model="row.teacher_id"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                >
                                    <option :value="null">-- Select teacher --</option>
                                    <option
                                        v-for="t in teachers"
                                        :key="t.id"
                                        :value="t.id"
                                    >
                                        {{ t.name }}
                                    </option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
