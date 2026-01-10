<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    teachers: {
        type: Array,
        default: () => [],
    },
});

const search = ref('');

const filteredTeachers = computed(() => {
    const q = String(search.value || '').toLowerCase().trim();
    if (!q) return props.teachers || [];

    return (props.teachers || []).filter((t) => {
        const teacherName = String(t?.name || '').toLowerCase();
        const email = String(t?.email || '').toLowerCase();
        const phone = String(t?.phone || '').toLowerCase();
        const check = String(t?.check_number || '').toLowerCase();

        const subjectMatch = (t?.subjects || []).some((s) => {
            const code = String(s?.subject_code || '').toLowerCase();
            const name = String(s?.subject_name || '').toLowerCase();
            return code.includes(q) || name.includes(q);
        });

        const classMatch = (t?.subjects || []).some((s) => {
            return (s?.classes || []).some((c) => {
                const label = String(c?.label || '').toLowerCase();
                const stream = String(c?.stream || '').toLowerCase();
                const level = String(c?.level || '').toLowerCase();
                return label.includes(q) || stream.includes(q) || level.includes(q);
            });
        });

        return teacherName.includes(q) || email.includes(q) || phone.includes(q) || check.includes(q) || subjectMatch || classMatch;
    });
});

const subjectLabel = (s) => {
    const code = String(s?.subject_code || '').trim();
    const name = String(s?.subject_name || '').trim();
    if (code && name) return `${code} - ${name}`;
    return code || name || '-';
};

const baseClassIdFor = (c) => {
    const pid = Number(c?.parent_class_id || 0);
    const id = Number(c?.id || 0);
    return pid > 0 ? pid : id;
};

const baseClassLabelFor = (c) => {
    const name = String(c?.name || '').trim();
    if (name) return name;
    const level = String(c?.level || '').trim();
    return level || 'Class';
};

const groupClassesByBase = (classes) => {
    const map = new Map();
    (classes || []).forEach((c) => {
        const baseId = baseClassIdFor(c);
        if (!baseId) return;

        if (!map.has(baseId)) {
            map.set(baseId, {
                base_class_id: baseId,
                base_label: baseClassLabelFor(c),
                streams: new Set(),
            });
        }

        const stream = String(c?.stream || '').trim();
        if (stream) {
            map.get(baseId).streams.add(stream);
        }
    });

    return Array.from(map.values())
        .map((g) => ({
            base_class_id: g.base_class_id,
            base_label: g.base_label,
            streams: Array.from(g.streams.values()).sort((a, b) => a.localeCompare(b)),
        }))
        .sort((a, b) => Number(a.base_class_id) - Number(b.base_class_id));
};

const exportJson = () => {
    const payload = {
        exported_at: new Date().toISOString(),
        total_teachers: (filteredTeachers.value || []).length,
        teachers: filteredTeachers.value || [],
    };

    const json = JSON.stringify(payload, null, 2);
    const blob = new Blob([json], { type: 'application/json;charset=utf-8' });
    const url = URL.createObjectURL(blob);

    const stamp = new Date().toISOString().slice(0, 10);
    const fileName = `teacher-assignments_${stamp}.json`;

    const a = document.createElement('a');
    a.href = url;
    a.download = fileName;
    document.body.appendChild(a);
    a.click();
    a.remove();

    URL.revokeObjectURL(url);
};
</script>

<template>
    <Head title="Teacher Assignments" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Teacher Assignments
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Orodha ya walimu pamoja na masomo wanayofundisha na madarasa/streams walizo-assigniwa.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <div class="text-xs font-semibold text-gray-700">Search:</div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search teacher / subject / class / stream"
                        class="w-72 max-w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-xs shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                    />

                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                        @click="exportJson"
                    >
                        Export JSON
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm font-medium text-gray-700">
                            Teachers ({{ filteredTeachers.length }})
                        </p>
                        <p class="text-[11px] text-gray-500">
                            Tip: unaweza kutafuta kwa jina, subject code, class au stream.
                        </p>
                    </div>

                    <div v-if="filteredTeachers.length === 0" class="py-10 text-center">
                        <div class="text-sm font-semibold text-gray-700">No assignments found</div>
                        <div class="mt-1 text-xs text-gray-500">
                            Hakuna data ya assignments, au search imeficha matokeo.
                        </div>
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="t in filteredTeachers"
                            :key="t.id"
                            class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
                        >
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ t.name }}</div>
                                    <div class="mt-0.5 text-[11px] text-gray-500">
                                        <span v-if="t.check_number">{{ t.check_number }}</span>
                                        <span v-if="t.check_number && (t.email || t.phone)"> · </span>
                                        <span v-if="t.email">{{ t.email }}</span>
                                        <span v-if="t.email && t.phone"> · </span>
                                        <span v-if="t.phone">{{ t.phone }}</span>
                                    </div>
                                </div>

                                <div class="rounded-md bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-100">
                                    {{ (t.subjects || []).length }} subjects
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                <div
                                    v-for="s in (t.subjects || [])"
                                    :key="s.id"
                                    class="rounded-lg border border-gray-100 bg-gray-50 p-3"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span
                                                    class="inline-flex items-center rounded-md bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white"
                                                >
                                                    {{ s.subject_code || 'SUB' }}
                                                </span>
                                                <div class="text-xs font-semibold text-gray-900">
                                                    {{ subjectLabel(s) }}
                                                </div>
                                            </div>
                                            <div class="mt-0.5 text-[11px] text-gray-500">
                                                Assigned classes/streams ({{ (s.classes || []).length }})
                                            </div>
                                        </div>
                                        <div class="text-[10px] font-semibold text-gray-500"></div>
                                    </div>

                                    <div v-if="(s.classes || []).length === 0" class="mt-2 text-[11px] text-gray-500">
                                        No classes assigned.
                                    </div>

                                    <div v-else class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                        <div
                                            v-for="grp in groupClassesByBase(s.classes)"
                                            :key="grp.base_class_id"
                                            class="rounded-lg bg-white p-3 ring-1 ring-gray-200"
                                        >
                                            <div class="text-xs font-semibold text-gray-800">
                                                {{ grp.base_label }}
                                            </div>
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                <span
                                                    v-if="(grp.streams || []).length === 0"
                                                    class="text-[11px] text-gray-500"
                                                >
                                                    No streams
                                                </span>
                                                <span
                                                    v-for="st in (grp.streams || [])"
                                                    :key="st"
                                                    class="inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-semibold text-sky-800 ring-1 ring-sky-100"
                                                >
                                                    {{ st }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
