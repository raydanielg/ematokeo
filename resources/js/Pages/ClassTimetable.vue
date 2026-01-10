<script setup>
import { computed, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    school: {
        type: Object,
        default: null,
    },
    classes: {
        type: Array,
        default: () => [],
    },
    timetables: {
        type: Array,
        default: () => [],
    },
    timetable: {
        type: Object,
        default: null,
    },
});

const schoolLogoUrl = computed(() => {
    const raw = String(props.school?.logo_path || '').trim();
    if (!raw) return '';
    if (raw.startsWith('http://') || raw.startsWith('https://')) return raw;
    if (raw.startsWith('/')) return raw;
    return `/storage/${raw}`;
});

const days = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY'];

const normalizeSchedule = (rawSchedule) => {
    try {
        let data = rawSchedule;
        if (!data) return {};

        if (typeof data === 'string') {
            const s = String(data).trim();
            if (!s) return {};
            data = JSON.parse(s);
        }

        if (!data || typeof data !== 'object') return {};

        const out = {};
        Object.keys(data).forEach((day) => {
            const rows = Array.isArray(data[day]) ? data[day] : [];
            out[day] = rows.map((row) => {
                const slots = Array.isArray(row?.slots) ? row.slots : [];
                const normalizedSlots = Array.from({ length: 9 }).map((_, i) => {
                    const slot = slots[i];
                    if (slot === null || slot === undefined) return null;

                    if (typeof slot === 'string') {
                        const val = String(slot || '').trim();
                        if (!val) return null;
                        return { subject: val, teacher: '', teacher_initials: '', teacher_name: '' };
                    }

                    if (typeof slot === 'object') {
                        const subject = String(slot?.subject ?? '').trim();
                        const teacher = String(slot?.teacher ?? '').trim();
                        const teacherInitials = String(slot?.teacher_initials ?? teacher).trim();
                        const teacherName = String(slot?.teacher_name ?? '').trim();
                        if (!subject && !teacher && !teacherInitials && !teacherName) return null;
                        return { subject, teacher, teacher_initials: teacherInitials, teacher_name: teacherName };
                    }

                    return null;
                });

                return {
                    ...(row || {}),
                    slots: normalizedSlots,
                };
            });
        });

        return out;
    } catch {
        return {};
    }
};

const selectedTimetableId = ref(props.timetable?.id || null);

watch(() => props.timetable?.id, (id) => {
    if (!selectedTimetableId.value && id) {
        selectedTimetableId.value = id;
    }
});

watch(selectedTimetableId, (id) => {
    const tid = Number(id || 0) || null;
    if (!tid) return;
    router.get(route('timetables.class', { timetable_id: tid }), {}, { preserveScroll: true });
});

const activeTimetable = computed(() => {
    const id = Number(selectedTimetableId.value || 0) || null;
    if (!id) return props.timetable;
    return (props.timetables || []).find((t) => Number(t?.id || 0) === id) || props.timetable;
});

const schedule = computed(() => normalizeSchedule(activeTimetable.value?.schedule_json));

const normalizeStr = (v) => String(v || '').trim().toUpperCase();

const classById = computed(() => {
    const map = new Map();
    (props.classes || []).forEach((c) => {
        const id = Number(c?.id || 0);
        if (!id) return;
        map.set(id, c);
    });
    return map;
});

const baseIdForClass = (c) => {
    const id = Number(c?.id || 0);
    const base = Number(c?.parent_class_id || 0);
    return base > 0 ? base : id;
};

const classLabelFor = (c) => {
    const name = String(c?.name || '').trim();
    if (name) return name;
    const level = String(c?.level || '').trim();
    return level;
};

const baseClassOptions = computed(() => {
    const map = new Map();
    (props.classes || []).forEach((c) => {
        const baseId = baseIdForClass(c);
        if (!baseId) return;
        if (!map.has(baseId)) {
            const baseClass = classById.value.get(baseId) || null;
            map.set(baseId, {
                id: baseId,
                label: classLabelFor(baseClass || c) || `Class ${baseId}`,
            });
        }
    });

    return Array.from(map.values()).sort((a, b) => Number(a.id) - Number(b.id));
});

const selectedBaseClassLabel = computed(() => {
    const id = Number(selectedBaseClassId.value || 0) || null;
    if (!id) return '';
    return baseClassOptions.value.find((c) => Number(c.id) === id)?.label || '';
});

const previewScopeTitle = computed(() => {
    const cls = String(selectedBaseClassLabel.value || '').trim();
    const stream = String(selectedStream.value || '').trim();
    if (!cls) return 'CLASS TIME TABLE';
    if (!stream || stream === 'ALL') return `${cls} TIME TABLE`;
    return `${cls} - ${stream} TIME TABLE`;
});

const streamsForBase = computed(() => {
    const baseId = Number(selectedBaseClassId.value || 0) || null;
    if (!baseId) return [];
    const set = new Set();
    (props.classes || []).forEach((c) => {
        if (baseIdForClass(c) !== baseId) return;
        const s = normalizeStr(c?.stream);
        if (s) set.add(s);
    });
    return Array.from(set.values()).sort((a, b) => a.localeCompare(b));
});

const selectedBaseClassId = ref(null);
const selectedStream = ref('ALL');

watch(baseClassOptions, (opts) => {
    if (selectedBaseClassId.value) return;
    if (opts && opts.length) {
        selectedBaseClassId.value = opts[0].id;
    }
}, { immediate: true });

watch(selectedBaseClassId, () => {
    selectedStream.value = 'ALL';
});

const selectedClassIds = computed(() => {
    const baseId = Number(selectedBaseClassId.value || 0) || null;
    if (!baseId) return [];
    const stream = normalizeStr(selectedStream.value);

    const out = [];
    (props.classes || []).forEach((c) => {
        if (baseIdForClass(c) !== baseId) return;
        const cid = Number(c?.id || 0);
        if (!cid) return;

        const s = normalizeStr(c?.stream);
        if (stream !== 'ALL') {
            if (s !== stream) return;
        }
        out.push(cid);
    });

    // If no streams exist and stream=ALL, include base class id itself.
    if (!out.length && stream === 'ALL') {
        out.push(baseId);
    }
    return out;
});

const filteredRowsByDay = computed(() => {
    const ids = new Set((selectedClassIds.value || []).map((v) => Number(v)));
    const out = {};
    days.forEach((d) => {
        const rows = schedule.value?.[d] || [];
        out[d] = rows.filter((r) => ids.has(Number(r?.school_class_id || 0)));
    });
    return out;
});

const dayRowCount = (day) => {
    const d = String(day || '');
    const rows = filteredRowsByDay.value?.[d] || [];
    return Array.isArray(rows) && rows.length ? rows.length : 1;
};

const primaryRowByDay = computed(() => {
    const ids = (selectedClassIds.value || []).map((v) => Number(v));
    const firstId = ids.length ? ids[0] : null;
    const out = {};
    days.forEach((d) => {
        const rows = schedule.value?.[d] || [];
        out[d] = rows.find((r) => Number(r?.school_class_id || 0) === Number(firstId || 0)) || null;
    });
    return out;
});

const hasAnySchedule = computed(() => {
    const s = schedule.value || {};
    return Object.keys(s).length > 0;
});

const printTimetable = () => {
    window.print();
};
</script>

<template>
    <Head title="Class Timetable" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3 no-print">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Class Timetable
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Choose a class and stream to preview its timetable.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="text-xs font-semibold text-gray-700">Saved Timetable:</div>
                    <select
                        v-model="selectedTimetableId"
                        class="rounded-md border border-gray-300 bg-white px-2 py-1 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                    >
                        <option v-if="!timetables || timetables.length === 0" :value="null">No Published timetables</option>
                        <option v-for="t in timetables" :key="t.id" :value="t.id">
                            {{ t.title }}
                        </option>
                    </select>

                    <div class="text-xs font-semibold text-gray-700">Class:</div>
                    <select
                        v-model="selectedBaseClassId"
                        class="rounded-md border border-gray-300 bg-white px-2 py-1 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                    >
                        <option v-for="c in baseClassOptions" :key="c.id" :value="c.id">{{ c.label }}</option>
                    </select>

                    <div class="text-xs font-semibold text-gray-700">Stream:</div>
                    <select
                        v-model="selectedStream"
                        class="rounded-md border border-gray-300 bg-white px-2 py-1 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                    >
                        <option value="ALL">All</option>
                        <option v-for="s in streamsForBase" :key="s" :value="s">{{ s }}</option>
                    </select>

                    <button
                        type="button"
                        class="ml-2 rounded-md bg-white px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                        @click="printTimetable"
                    >
                        Print
                    </button>

                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                        :disabled="!activeTimetable?.id"
                        @click="activeTimetable?.id && router.get(route('timetables.show', activeTimetable.id))"
                    >
                        Edit
                    </button>

                    <button
                        type="button"
                        class="rounded-md bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100"
                        :disabled="!activeTimetable?.file_path"
                        @click="activeTimetable?.file_path && window.open(route('timetables.download', activeTimetable.id), '_blank')"
                    >
                        PDF
                    </button>
                </div>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100 print-area">
                    <div class="mb-4 border-b border-gray-200 pb-3 text-center">
                        <div class="mb-1 text-[11px] font-semibold text-gray-700">Emblem</div>

                        <div v-if="schoolLogoUrl" class="mx-auto mb-1 h-14 w-14 overflow-hidden rounded-full border border-gray-200 bg-white">
                            <img
                                :src="schoolLogoUrl"
                                alt="School logo"
                                class="h-full w-full object-contain"
                            />
                        </div>

                        <div class="text-[12px] font-extrabold uppercase tracking-wide text-gray-900">
                            {{ props.school?.name || 'ANGELINA MABULA SECONDARY SCHOOL' }}
                        </div>
                        <div class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-700">
                            {{ props.school?.address || 'P . O . BOX 523 · MWANZA' }}
                        </div>

                        <div class="mt-2 text-[11px] font-extrabold uppercase tracking-wide text-emerald-800">
                            {{ previewScopeTitle }}
                        </div>
                        <div class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-800">
                            MWAKA: {{ activeTimetable?.academic_year || new Date().getFullYear() }}
                        </div>
                    </div>

                    <div v-if="!hasAnySchedule" class="mb-3 rounded-md bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-900 ring-1 ring-amber-200">
                        No timetable schedule found yet.
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full border-collapse text-[10px] leading-tight">
                            <thead>
                                <tr>
                                    <th class="w-20 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        DAY
                                    </th>
                                    <th class="w-20 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        CLASS
                                    </th>
                                    <th class="w-16 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        STREAM
                                    </th>
                                    <th class="w-24 border border-slate-300 bg-sky-50 px-1 py-1 text-center align-middle">
                                        07:00 - 07:30 AM
                                    </th>
                                    <th class="w-16 border border-slate-300 bg-fuchsia-100 px-1 py-1 text-center align-middle">
                                        07:30 - 07:50 AM
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        08:00 - 08:40
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        08:40 - 09:20
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        09:20 - 10:00
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        10:00 - 10:40
                                    </th>
                                    <th class="w-16 border border-slate-300 bg-sky-200 px-1 py-1 text-center align-middle">
                                        10:40 - 11:05
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        11:05 - 11:45
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        11:45 - 12:25
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        12:25 - 01:05
                                    </th>
                                    <th class="w-16 border border-slate-300 bg-sky-200 px-1 py-1 text-center align-middle">
                                        01:05 - 01:20
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        01:20 - 02:00
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        02:00 - 02:40
                                    </th>
                                    <th class="w-20 border border-slate-300 bg-emerald-100 px-1 py-1 text-center align-middle">
                                        03:30 - 05:30 PM
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="day in days" :key="day">
                                    <template v-if="(filteredRowsByDay[day] || []).length">
                                        <tr v-for="(row, idx) in filteredRowsByDay[day]" :key="`${day}-${row?.school_class_id || idx}`">
                                            <td
                                                v-if="idx === 0"
                                                class="border border-slate-300 bg-sky-100 px-1 py-3 text-center text-[10px] font-semibold uppercase text-sky-900"
                                                :rowspan="dayRowCount(day)"
                                            >
                                                {{ day }}
                                            </td>

                                            <td class="border border-slate-300 bg-slate-50 px-1 py-1 text-center font-semibold">
                                                {{ row?.class_label || row?.form || row?.class_name || '-' }}
                                            </td>
                                            <td class="border border-slate-300 bg-slate-50 px-1 py-1 text-center">
                                                {{ row?.stream || '-' }}
                                            </td>
                                            <td class="border border-slate-300 bg-sky-50 px-1 py-1 text-center">
                                                <div class="font-semibold">ARRIVAL &amp; CLEANING</div>
                                            </td>
                                            <td class="border border-slate-300 bg-fuchsia-100 px-1 py-1 text-center font-semibold text-fuchsia-900">
                                                PREP
                                            </td>

                                            <td v-for="i in 4" :key="`am-${day}-${idx}-${i}`" class="border border-slate-300 bg-emerald-50 px-1 py-1 text-center">
                                                <template v-if="row?.slots?.[i - 1]?.subject">
                                                    <div class="font-semibold">{{ row.slots[i - 1].subject }}</div>
                                                    <div class="break-words text-[9px] font-semibold leading-tight text-gray-700">
                                                        {{ row.slots[i - 1].teacher_initials || row.slots[i - 1].teacher || '' }}
                                                    </div>
                                                </template>
                                                <template v-else>
                                                    <div>&nbsp;</div>
                                                </template>
                                            </td>

                                            <td class="border border-slate-300 bg-sky-200 px-1 py-1 text-center font-semibold text-sky-900">
                                                BREAK
                                            </td>

                                            <td v-for="j in 3" :key="`mid-${day}-${idx}-${j}`" class="border border-slate-300 bg-indigo-50 px-1 py-1 text-center">
                                                <template v-if="row?.slots?.[3 + j]?.subject">
                                                    <div class="font-semibold">{{ row.slots[3 + j].subject }}</div>
                                                    <div class="break-words text-[9px] font-semibold leading-tight text-gray-700">
                                                        {{ row.slots[3 + j].teacher_initials || row.slots[3 + j].teacher || '' }}
                                                    </div>
                                                </template>
                                                <template v-else>
                                                    <div>&nbsp;</div>
                                                </template>
                                            </td>

                                            <td class="border border-slate-300 bg-sky-200 px-1 py-1 text-center font-semibold text-sky-900">
                                                BREAK
                                            </td>

                                            <td v-for="k in 2" :key="`pm-${day}-${idx}-${k}`" class="border border-slate-300 bg-indigo-50 px-1 py-1 text-center">
                                                <template v-if="row?.slots?.[6 + k]?.subject">
                                                    <div class="font-semibold">{{ row.slots[6 + k].subject }}</div>
                                                    <div class="break-words text-[9px] font-semibold leading-tight text-gray-700">
                                                        {{ row.slots[6 + k].teacher_initials || row.slots[6 + k].teacher || '' }}
                                                    </div>
                                                </template>
                                                <template v-else>
                                                    <div class="font-semibold text-gray-400">PS</div>
                                                </template>
                                            </td>

                                            <td class="border border-slate-300 bg-emerald-200 px-1 py-1 text-center font-semibold text-emerald-900">
                                                REMEDIAL
                                            </td>
                                        </tr>
                                    </template>

                                    <template v-else>
                                        <tr>
                                            <td class="border border-slate-300 bg-sky-100 px-1 py-3 text-center text-[10px] font-semibold uppercase text-sky-900">
                                                {{ day }}
                                            </td>
                                            <td class="border border-slate-300 bg-white px-2 py-2" colspan="15">
                                                <span class="text-xs text-gray-500">No row found for selected class/stream on this day.</span>
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 text-xs text-gray-500">
                        Coming soon: editing tools and full multi-stream view.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    .no-print {
        display: none !important;
    }

    /* Print only the preview card */
    .print-area {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
    }

    body {
        background: white !important;
    }
}
</style>
