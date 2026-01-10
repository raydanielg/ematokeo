<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    timetables: {
        type: Array,
        default: () => [],
    },
    assignedClasses: {
        type: Array,
        default: () => [],
    },
    teacherInitials: {
        type: String,
        default: '',
    },
});

const count = computed(() => (Array.isArray(props.timetables) ? props.timetables.length : 0));

const normalizeStr = (v) => String(v || '').trim().toUpperCase();

const selectedClass = ref('ALL');
const selectedTimetableId = ref(null);

const classOptions = computed(() => {
    const list = Array.isArray(props.assignedClasses) ? props.assignedClasses : [];
    const cleaned = list
        .map((v) => String(v || '').trim())
        .filter(Boolean);
    return cleaned;
});

const filteredTimetables = computed(() => {
    const list = Array.isArray(props.timetables) ? props.timetables : [];
    const cls = normalizeStr(selectedClass.value);
    if (!cls || cls === 'ALL') return list;
    return list.filter((t) => normalizeStr(t?.class_name).includes(cls));
});

watch([selectedClass, filteredTimetables], () => {
    const list = filteredTimetables.value || [];
    const currentId = Number(selectedTimetableId.value || 0) || null;
    if (!list.length) {
        selectedTimetableId.value = null;
        return;
    }
    if (!currentId || !list.some((t) => Number(t?.id || 0) === currentId)) {
        selectedTimetableId.value = list[0].id;
    }
}, { immediate: true });

const resetFilters = () => {
    selectedClass.value = 'ALL';
    selectedTimetableId.value = null;
};

const selectedTimetable = computed(() => {
    const id = Number(selectedTimetableId.value || 0) || null;
    const list = filteredTimetables.value;
    if (!id) return list?.[0] || null;
    return list.find((t) => Number(t?.id || 0) === id) || list?.[0] || null;
});

const days = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY'];

const slotTimeLabel = (idx) => {
    const map = {
        0: '08:00 - 08:40',
        1: '08:40 - 09:20',
        2: '09:20 - 10:00',
        3: '10:00 - 10:40',
        4: '11:05 - 11:45',
        5: '11:45 - 12:25',
        6: '12:25 - 01:05',
        7: '01:20 - 02:00',
        8: '02:00 - 02:40',
    };
    return map[idx] || `Slot ${Number(idx) + 1}`;
};

const teacherPeriodsPreview = computed(() => {
    const t = selectedTimetable.value;
    const initials = normalizeStr(props.teacherInitials);
    if (!t || !t.schedule_json || !initials) return [];

    let schedule = t.schedule_json;
    if (typeof schedule === 'string') {
        try {
            schedule = JSON.parse(schedule);
        } catch {
            schedule = null;
        }
    }
    if (!schedule || typeof schedule !== 'object') return [];

    const out = [];
    Object.keys(schedule).forEach((day) => {
        const rows = Array.isArray(schedule[day]) ? schedule[day] : [];
        rows.forEach((row) => {
            const classLabel = String(row?.class_label || row?.form || '').trim();
            const stream = String(row?.stream || '').trim();
            const slots = Array.isArray(row?.slots) ? row.slots : [];
            slots.forEach((slot, idx) => {
                if (!slot || typeof slot !== 'object') return;
                const teacher = String(slot?.teacher_initials || slot?.teacher || '').trim();
                if (!teacher) return;
                const parts = teacher
                    .split('/')
                    .map((p) => normalizeStr(p))
                    .filter(Boolean);
                if (!parts.includes(initials)) return;
                out.push({
                    day: String(day || '').toUpperCase(),
                    class_label: classLabel,
                    stream,
                    subject: String(slot?.subject || '').trim(),
                    slot_index: Number(idx) || 0,
                });
            });
        });
    });

    const dayOrder = { MONDAY: 1, TUESDAY: 2, WEDNESDAY: 3, THURSDAY: 4, FRIDAY: 5 };
    return out.sort((a, b) => {
        const da = dayOrder[String(a.day || '').toUpperCase()] || 99;
        const db = dayOrder[String(b.day || '').toUpperCase()] || 99;
        if (da !== db) return da - db;
        const ca = String(a.class_label || '').localeCompare(String(b.class_label || ''));
        if (ca !== 0) return ca;
        const sa = String(a.stream || '').localeCompare(String(b.stream || ''));
        if (sa !== 0) return sa;
        return Number(a.slot_index || 0) - Number(b.slot_index || 0);
    });
});

const teacherPreviewForClass = computed(() => {
    const cls = normalizeStr(selectedClass.value);
    if (!cls || cls === 'ALL') return teacherPeriodsPreview.value;
    return teacherPeriodsPreview.value.filter((p) => normalizeStr(p?.class_label).includes(cls));
});

const previewCellItems = (day, slotIndex) => {
    const d = String(day || '').toUpperCase();
    const idx = Number(slotIndex);
    return teacherPreviewForClass.value.filter((p) => String(p.day || '').toUpperCase() === d && Number(p.slot_index) === idx);
};

const preview = (id) => {
    router.get(route('timetables.show', id));
};

const myPeriods = (id) => {
    router.get(route('timetables.periods', id));
};
</script>

<template>
    <Head title="My Timetable" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">My Timetable</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Open a saved timetable and view only your own periods automatically.
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="text-sm font-semibold text-gray-800">Assigned classes</div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span
                                v-for="c in assignedClasses"
                                :key="c"
                                class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100"
                            >
                                {{ c }}
                            </span>
                            <span v-if="!assignedClasses || assignedClasses.length === 0" class="text-sm text-gray-500">—</span>
                        </div>
                    </div>

                    <div class="w-full sm:w-auto sm:min-w-[560px]">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Filters</div>
                                <div class="text-[11px] text-gray-500">Chuja ratiba kwa darasa lako na ratiba iliyochapishwa.</div>
                            </div>
                            <button
                                type="button"
                                class="rounded-md bg-white px-3 py-1.5 text-[11px] font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                                @click="resetFilters"
                            >
                                Clear
                            </button>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3 sm:items-end">
                        <div class="sm:col-span-1">
                            <div class="mb-1 text-[11px] font-semibold text-gray-700">Class</div>
                            <select
                                v-model="selectedClass"
                                class="w-full rounded-md border border-gray-300 bg-white px-2 py-2 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option value="ALL">All my classes</option>
                                <option v-for="c in classOptions" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <div class="mb-1 text-[11px] font-semibold text-gray-700">Timetable</div>
                            <select
                                v-model="selectedTimetableId"
                                class="w-full rounded-md border border-gray-300 bg-white px-2 py-2 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option v-if="!filteredTimetables.length" :value="null">No timetables</option>
                                <option v-for="t in filteredTimetables" :key="t.id" :value="t.id">{{ t.title }}</option>
                            </select>
                        </div>

                        <div class="text-xs text-gray-500 sm:col-span-3 sm:text-right">
                            Showing <span class="font-semibold text-gray-800">{{ filteredTimetables.length }}</span> timetable(s)
                        </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-gray-800">Quick preview (my periods)</div>
                        <div class="mt-1 text-xs text-gray-500">
                            Initials: <span class="font-semibold text-gray-800">{{ teacherInitials || '—' }}</span>
                            <span v-if="selectedTimetable?.title"> · Timetable: <span class="font-semibold text-gray-800">{{ selectedTimetable.title }}</span></span>
                        </div>
                    </div>

                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                        <button
                            type="button"
                            class="rounded-md bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                            :disabled="!selectedTimetable?.id"
                            @click="selectedTimetable?.id && myPeriods(selectedTimetable.id)"
                        >
                            Open My Periods
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100"
                            :disabled="!selectedTimetable?.id"
                            @click="selectedTimetable?.id && preview(selectedTimetable.id)"
                        >
                            Open Timetable
                        </button>
                    </div>
                </div>

                <div class="mt-4 -mx-5 overflow-x-auto px-5">
                    <table class="min-w-[980px] border-collapse text-[10px] sm:min-w-full sm:text-[11px] text-gray-800">
                        <thead>
                            <tr>
                                <th class="w-20 border border-slate-200 bg-slate-100 px-2 py-2 text-left">Day</th>
                                <th class="border border-slate-200 bg-slate-100 px-2 py-2 text-center">08:00</th>
                                <th class="border border-slate-200 bg-slate-100 px-2 py-2 text-center">08:40</th>
                                <th class="border border-slate-200 bg-slate-100 px-2 py-2 text-center">09:20</th>
                                <th class="border border-slate-200 bg-slate-100 px-2 py-2 text-center">10:00</th>
                                <th class="border border-slate-200 bg-sky-100 px-2 py-2 text-center">BREAK</th>
                                <th class="border border-slate-200 bg-slate-100 px-2 py-2 text-center">11:05</th>
                                <th class="border border-slate-200 bg-slate-100 px-2 py-2 text-center">11:45</th>
                                <th class="border border-slate-200 bg-slate-100 px-2 py-2 text-center">12:25</th>
                                <th class="border border-slate-200 bg-sky-100 px-2 py-2 text-center">BREAK</th>
                                <th class="border border-slate-200 bg-slate-100 px-2 py-2 text-center">01:20</th>
                                <th class="border border-slate-200 bg-slate-100 px-2 py-2 text-center">02:00</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!teacherPreviewForClass.length">
                                <td colspan="12" class="border border-slate-200 px-4 py-6 text-center text-sm text-gray-500">
                                    No periods found for your initials in the selected timetable.
                                </td>
                            </tr>

                            <tr v-for="day in days" :key="day" class="hover:bg-gray-50">
                                <td class="border border-slate-200 bg-sky-50 px-2 py-2 text-left font-semibold">{{ day }}</td>

                                <td v-for="i in 4" :key="`am-${day}-${i}`" class="border border-slate-200 px-2 py-2 align-top">
                                    <div v-for="(p, idx) in previewCellItems(day, i - 1)" :key="`p-${day}-${i}-${idx}`" class="mb-1 rounded bg-emerald-50 px-2 py-1">
                                        <div class="font-semibold text-emerald-900">{{ p.subject || '—' }}</div>
                                        <div class="text-[10px] text-gray-700">{{ p.class_label }} <span v-if="p.stream">· {{ p.stream }}</span></div>
                                    </div>
                                </td>

                                <td class="border border-slate-200 bg-sky-100 px-2 py-2 text-center font-semibold text-sky-900">BREAK</td>

                                <td v-for="j in 3" :key="`mid-${day}-${j}`" class="border border-slate-200 px-2 py-2 align-top">
                                    <div v-for="(p, idx) in previewCellItems(day, 3 + j)" :key="`p-${day}-m-${j}-${idx}`" class="mb-1 rounded bg-indigo-50 px-2 py-1">
                                        <div class="font-semibold text-indigo-900">{{ p.subject || '—' }}</div>
                                        <div class="text-[10px] text-gray-700">{{ p.class_label }} <span v-if="p.stream">· {{ p.stream }}</span></div>
                                    </div>
                                </td>

                                <td class="border border-slate-200 bg-sky-100 px-2 py-2 text-center font-semibold text-sky-900">BREAK</td>

                                <td v-for="k in 2" :key="`pm-${day}-${k}`" class="border border-slate-200 px-2 py-2 align-top">
                                    <div v-for="(p, idx) in previewCellItems(day, 6 + k)" :key="`p-${day}-p-${k}-${idx}`" class="mb-1 rounded bg-indigo-50 px-2 py-1">
                                        <div class="font-semibold text-indigo-900">{{ p.subject || '—' }}</div>
                                        <div class="text-[10px] text-gray-700">{{ p.class_label }} <span v-if="p.stream">· {{ p.stream }}</span></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Title</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Class</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Year / Term</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!filteredTimetables || filteredTimetables.length === 0">
                                <td colspan="4" class="px-5 py-6 text-center text-sm text-gray-500">
                                    No saved timetables found yet. Ask the admin to generate and save a timetable.
                                </td>
                            </tr>

                            <tr v-for="t in filteredTimetables" :key="t.id" class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ t.title }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ t.class_name || '—' }}</td>
                                <td class="px-5 py-3 text-gray-700">
                                    <span v-if="t.academic_year">{{ t.academic_year }}</span>
                                    <span v-if="t.term"> • {{ t.term }}</span>
                                    <span v-if="!t.academic_year && !t.term">—</span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="rounded-md bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100"
                                            @click="preview(t.id)"
                                        >
                                            Preview
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-md bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                                            @click="myPeriods(t.id)"
                                        >
                                            My Periods
                                        </button>
                                        <button
                                            type="button"
                                            :disabled="!t.file_path"
                                            :class="[
                                                'rounded-md px-3 py-1.5 text-xs font-semibold ring-1',
                                                t.file_path
                                                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-100 hover:bg-emerald-100'
                                                    : 'bg-gray-50 text-gray-400 ring-gray-100 cursor-not-allowed',
                                            ]"
                                            @click="t.file_path && window.open(route('timetables.download', t.id), '_blank')"
                                        >
                                            Download
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
