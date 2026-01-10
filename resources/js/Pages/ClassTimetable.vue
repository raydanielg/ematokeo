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
    timetable: {
        type: Object,
        default: null,
    },
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

const schedule = computed(() => normalizeSchedule(props.timetable?.schedule_json));

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

const schoolName = computed(() => String(props.school?.name || '').trim());
const schoolAddress = computed(() => String(props.school?.address || '').trim());
const schoolRegion = computed(() => String(props.school?.region || '').trim());

const selectedClassObj = computed(() => {
    const id = Number((selectedClassIds.value || [])[0] || 0);
    if (!id) return null;
    return classById.value.get(id) || null;
});

const headerClassName = computed(() => {
    const c = selectedClassObj.value;
    if (!c) return '';
    const baseId = baseIdForClass(c);
    const base = classById.value.get(Number(baseId)) || c;
    const baseLabel = classLabelFor(base);
    const stream = normalizeStr(selectedStream.value);
    if (stream && stream !== 'ALL') return `${baseLabel} ${stream}`;
    const cStream = String(c?.stream || '').trim();
    return cStream ? `${baseLabel} ${cStream}` : baseLabel;
});

const generateForSelection = () => {
    router.get(route('timetables.create'), {
        school_class_id: Number(selectedBaseClassId.value || 0) || undefined,
        stream: normalizeStr(selectedStream.value) !== 'ALL' ? normalizeStr(selectedStream.value) : undefined,
    });
};
</script>

<template>
    <Head title="Class Timetable" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Class Timetable
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Choose a class and stream to preview its timetable.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
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
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-gray-800">Preview</div>
                            <div class="mt-0.5 text-xs text-gray-500">
                                {{ timetable?.title || 'Latest timetable' }}
                                <span v-if="timetable?.academic_year"> · {{ timetable.academic_year }}</span>
                                <span v-if="timetable?.term"> · {{ timetable.term }}</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                class="rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                                @click="generateForSelection"
                            >
                                Generate
                            </button>
                            <div v-if="!hasAnySchedule" class="rounded-md bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-900 ring-1 ring-amber-200">
                                No timetable schedule found yet.
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-lg border border-gray-300 bg-white text-[11px] text-gray-800">
                        <div class="border-b border-gray-300 bg-white px-4 py-3 text-center">
                            <div class="mb-1 flex justify-center">
                                <img
                                    src="/images/emblem.png"
                                    alt="Emblem"
                                    class="h-8 w-8 object-contain"
                                    onerror="this.style.display='none'"
                                />
                            </div>
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-800">
                                {{ schoolName || 'SCHOOL' }}
                            </div>
                            <div class="text-[9px] text-gray-600" v-if="schoolAddress || schoolRegion">
                                {{ schoolAddress }}<span v-if="schoolAddress && schoolRegion"> · </span>{{ schoolRegion }}
                            </div>
                            <div class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-emerald-800">
                                CLASS TIME TABLE
                            </div>
                            <div class="text-[9px] text-gray-700" v-if="headerClassName">
                                Darasa: <span class="font-semibold">{{ headerClassName }}</span>
                            </div>
                        </div>

                        <div class="overflow-x-auto bg-slate-50 px-2 pb-3 pt-2">
                            <table class="min-w-full border-collapse text-[10px] leading-tight">
                                <thead>
                                    <tr>
                                        <th class="w-20 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">DAY</th>
                                        <th class="w-20 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">CLASS</th>
                                        <th class="w-16 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">STREAM</th>
                                        <th class="w-20 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">P1</th>
                                        <th class="w-20 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">P2</th>
                                        <th class="w-20 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">P3</th>
                                        <th class="w-20 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">P4</th>
                                        <th class="w-14 border border-slate-300 bg-sky-100 px-1 py-1 text-center align-middle">BREAK</th>
                                        <th class="w-20 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">P5</th>
                                        <th class="w-20 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">P6</th>
                                        <th class="w-20 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">P7</th>
                                        <th class="w-14 border border-slate-300 bg-sky-100 px-1 py-1 text-center align-middle">BREAK</th>
                                        <th class="w-20 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">P8</th>
                                        <th class="w-20 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">P9</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="day in days" :key="day" class="hover:bg-white/40">
                                        <td class="border border-slate-300 bg-white px-1 py-1 text-center font-semibold">
                                            {{ day }}
                                        </td>
                                        <td class="border border-slate-300 bg-white px-1 py-1 text-center">
                                            {{ headerClassName || '-' }}
                                        </td>
                                        <td class="border border-slate-300 bg-white px-1 py-1 text-center">
                                            {{ normalizeStr(selectedStream) === 'ALL' ? (selectedClassObj?.stream || '-') : selectedStream }}
                                        </td>

                                        <template v-if="primaryRowByDay[day]">
                                            <td v-for="i in 4" :key="`p${i}-${day}`" class="border border-slate-300 bg-white px-1 py-1 text-center">
                                                <div class="font-semibold">{{ primaryRowByDay[day].slots[i - 1]?.subject || '' }}</div>
                                                <div class="text-[9px] text-gray-600">{{ primaryRowByDay[day].slots[i - 1]?.teacher_initials || primaryRowByDay[day].slots[i - 1]?.teacher || '' }}</div>
                                            </td>
                                            <td class="border border-slate-300 bg-sky-100 px-1 py-1 text-center text-[9px] font-semibold text-sky-900">
                                                BREAK
                                            </td>
                                            <td v-for="j in 3" :key="`p${4 + j}-${day}`" class="border border-slate-300 bg-white px-1 py-1 text-center">
                                                <div class="font-semibold">{{ primaryRowByDay[day].slots[3 + j]?.subject || '' }}</div>
                                                <div class="text-[9px] text-gray-600">{{ primaryRowByDay[day].slots[3 + j]?.teacher_initials || primaryRowByDay[day].slots[3 + j]?.teacher || '' }}</div>
                                            </td>
                                            <td class="border border-slate-300 bg-sky-100 px-1 py-1 text-center text-[9px] font-semibold text-sky-900">
                                                BREAK
                                            </td>
                                            <td v-for="k in 2" :key="`p${7 + k}-${day}`" class="border border-slate-300 bg-white px-1 py-1 text-center">
                                                <div class="font-semibold">{{ primaryRowByDay[day].slots[6 + k]?.subject || '' }}</div>
                                                <div class="text-[9px] text-gray-600">{{ primaryRowByDay[day].slots[6 + k]?.teacher_initials || primaryRowByDay[day].slots[6 + k]?.teacher || '' }}</div>
                                            </td>
                                        </template>

                                        <template v-else>
                                            <td class="border border-slate-300 bg-white px-1 py-1" colspan="11">
                                                <span class="text-[10px] text-gray-500">No row found for selected class/stream.</span>
                                            </td>
                                        </template>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3 text-xs text-gray-500">
                        Coming soon: editing tools and full multi-stream view.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
