<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';

const props = defineProps({
    school: {
        type: Object,
        default: () => ({}),
    },
    timetable: {
        type: Object,
        default: () => null,
    },
    subjects: {
        type: Array,
        default: () => [],
    },
    classes: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    type: 'class',
    title: props.timetable?.title || '',
    class_name: props.timetable?.class_name || '',
    academic_year: props.timetable?.academic_year || '',
    term: props.timetable?.term || '',
    start_time: '07:30',
    end_time: '16:00',
});

const schoolName = computed(() => props.school.name || 'Your School Name');
const schoolAddress = computed(() => props.school.address || 'P.O. Box 123, City');
const schoolRegion = computed(() => props.school.region || 'Region');

const typeLabel = computed(() => {
    if (form.type === 'invigilation') return 'Invigilation Timetable';
    if (form.type === 'exams') return 'Exams Timetable';

    // Default label for this page is the school-wide general timetable
    return 'School General Timetable';
});

const subjectPool = computed(() => (props.subjects || []).map((s) => s.subject_code));

const teacherBySubject = computed(() => {
    const map = {};

    (props.subjects || []).forEach((s) => {
        const firstTeacher = (s.teachers || [])[0];

        if (firstTeacher) {
            map[s.subject_code] = firstTeacher.initials || firstTeacher.name;
        }
    });

    return map;
});

const days = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY'];

const levelToForm = (level) => {
    const map = {
        1: 'I',
        2: 'II',
        3: 'III',
        4: 'IV',
    };

    if (!level) return '';

    return map[level] || String(level);
};

const forms = computed(() => {
    const unique = new Set();

    (props.classes || []).forEach((c) => {
        if (c.level) {
            unique.add(levelToForm(c.level));
        }
    });

    if (unique.size === 0) {
        return ['I', 'II', 'III', 'IV'];
    }

    return Array.from(unique.values());
});

const streams = computed(() => {
    const unique = new Set();

    (props.classes || []).forEach((c) => {
        if (c.stream) {
            unique.add(String(c.stream).toUpperCase());
        }
    });

    if (unique.size === 0) {
        return ['A'];
    }

    return Array.from(unique.values());
});

const selectedForm = ref('ALL');
const selectedStream = ref('ALL');

const headerClassName = computed(() => {
    // When a specific form and stream are selected, show that combination
    if (selectedForm.value !== 'ALL' && selectedStream.value !== 'ALL') {
        return `Form ${selectedForm.value} ${selectedStream.value}`;
    }

    // When only a specific form is selected, treat it as a class-level view
    if (selectedForm.value !== 'ALL') {
        return `Form ${selectedForm.value}`;
    }

    // Otherwise fall back to the manually entered class name or blanks
    return form.class_name || '____';
});

// schedule[day][formIndex][slotIndex] = { subject, teacher }
const schedule = ref({});

const generateSampleTimetable = () => {
    const next = {};

    const codes = subjectPool.value;
    const teachers = teacherBySubject.value;

    if (!codes.length) {
        schedule.value = {};
        return;
    }

    days.forEach((day) => {
        next[day] = [];

        // Build one row per actual class from the database so that
        // only existing streams/levels are shown in the timetable.
        (props.classes || []).forEach((c) => {
            const formLabel = levelToForm(c.level);
            const streamLabel = c.stream ? String(c.stream).toUpperCase() : '';

            const row = {
                form: formLabel,
                stream: streamLabel,
                class_name: c.name || `${formLabel} ${streamLabel}`.trim(),
                slots: [],
            };

            // 8 academic slots per row (3 before break, 5 after), index 0-7
            // Choose subjects randomly from the pool so that each regenerate
            // gives a slightly different distribution per class and per day.
            for (let i = 0; i < 8; i += 1) {
                const subject = codes[Math.floor(Math.random() * codes.length)];
                row.slots.push({
                    subject,
                    teacher: teachers[subject] || '',
                });
            }

            next[day].push(row);
        });
    });

    schedule.value = next;
};

onMounted(() => {
    generateSampleTimetable();
});

const showDetailsModal = ref(false);

const saveTimetable = () => {
    form.post(route('timetables.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showDetailsModal.value = false;
        },
    });
};

const printTimetable = () => {
    window.print();
};

const draggedCell = ref(null);

const isDraggableSlot = (day, slotIndex) => {
    // Morning lessons (08:00-10:00) always draggable
    if (slotIndex >= 0 && slotIndex <= 2) {
        return true;
    }

    // Mid-day lessons between breaks (11:05-13:05)
    // Keep Friday MEWAKA slot (index 5) fixed
    if (slotIndex >= 3 && slotIndex <= 5) {
        if (day === 'FRIDAY' && slotIndex === 5) {
            return false;
        }

        return true;
    }

    // Afternoon lessons after second break (13:20-14:40)
    // Keep special activities (RELIGION, CLUBS, SPORTS) fixed on Wed/Thu/Fri
    if (slotIndex >= 6 && slotIndex <= 7) {
        return !['WEDNESDAY', 'THURSDAY', 'FRIDAY'].includes(day);
    }

    return false;
};

const onDragStart = (day, rowIndex, slotIndex) => {
    draggedCell.value = { day, rowIndex, slotIndex };
};

const onDrop = (day, rowIndex, slotIndex) => {
    if (!draggedCell.value) return;

    const from = draggedCell.value;

    if (from.day === day && from.rowIndex === rowIndex && from.slotIndex === slotIndex) {
        draggedCell.value = null;
        return;
    }

    const fromRow = schedule.value[from.day]?.[from.rowIndex];
    const toRow = schedule.value[day]?.[rowIndex];

    if (!fromRow || !toRow) {
        draggedCell.value = null;
        return;
    }

    const tmp = fromRow.slots[from.slotIndex];
    fromRow.slots[from.slotIndex] = toRow.slots[slotIndex];
    toRow.slots[slotIndex] = tmp;

    draggedCell.value = null;
};
</script>

<template>
    <Head title="Create School Timetable" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Create School Timetable
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Preview below shows the school general timetable. Use the popup to enter timetable details when you are ready to save.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                        @click="showDetailsModal = true"
                    >
                        Create / Edit Timetable Details
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Preview: full-width card -->
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Timetable Preview
                </h3>

                <!-- Toolbar: filters + actions -->
                <div class="mt-2 mb-3 flex flex-wrap items-center justify-between gap-3 text-[9px] text-gray-600">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-semibold">View class:</span>
                        <select
                            v-model="selectedForm"
                            class="rounded border border-gray-300 bg-white px-1 py-0.5 text-[9px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="ALL">All Forms</option>
                            <option v-for="f in forms" :key="f" :value="f">Form {{ f }}</option>
                        </select>
                        <select
                            v-model="selectedStream"
                            class="rounded border border-gray-300 bg-white px-1 py-0.5 text-[9px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="ALL">All Streams</option>
                            <option v-for="s in streams" :key="s" :value="s">Stream {{ s }}</option>
                        </select>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-medium text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                            @click="generateSampleTimetable"
                        >
                            Regenerate Sample Timetable
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-white px-2 py-1 text-[10px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                            @click="printTimetable"
                        >
                            Print
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-white px-2 py-1 text-[10px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                            @click="showDetailsModal = true"
                        >
                            Open Timetable Details
                        </button>
                    </div>
                </div>

                <div
                    id="class-timetable-preview"
                    class="overflow-hidden rounded-lg border border-gray-300 bg-white text-[10px] text-gray-800"
                >
                    <!-- Top header styled like reports (emblem + titles) -->
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
                            {{ schoolName }}
                        </div>
                        <div class="text-[9px] text-gray-600">
                            {{ schoolAddress }} · {{ schoolRegion }}
                        </div>
                        <div class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-emerald-800">
                            SCHOOL GENERAL TIME TABLE
                        </div>
                        <div class="text-[9px] text-gray-700">
                            Mwaka: <span class="font-semibold">{{ form.academic_year || '____' }}</span>
                            <span v-if="selectedForm !== 'ALL'">
                                · Darasa: <span class="font-semibold">{{ headerClassName }}</span>
                            </span>
                            · Kipindi: <span class="font-semibold">{{ form.term || '____' }}</span>
                        </div>
                    </div>

                    <!-- Meta row -->
                    <div class="grid grid-cols-3 gap-2 border-b border-gray-200 bg-emerald-50/60 px-4 py-1.5 text-[9px] text-gray-700">
                        <div>
                            <span class="font-semibold">Kichwa:</span>
                            <span class="ml-1">{{ form.title || 'Ratiba ya Darasa' }}</span>
                        </div>
                        <div>
                            <span class="font-semibold">Muda wa vipindi:</span>
                            <span class="ml-1">{{ form.start_time }} - {{ form.end_time }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold">Aina ya ratiba:</span>
                            <span class="ml-1">{{ typeLabel }}</span>
                        </div>
                    </div>

                    <!-- Timetable grid (soft colours) -->
                    <div class="overflow-x-auto bg-slate-50 px-2 pb-3 pt-2">
                        <table class="min-w-full border-collapse text-[9px] leading-tight">
                            <thead>
                                <tr>
                                    <th class="w-12 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        DAY
                                    </th>
                                    <th class="w-10 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        FORM
                                    </th>
                                    <th class="w-12 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        CLASS
                                    </th>
                                    <th class="w-20 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        07:00 - 07:30 AM
                                    </th>
                                    <th class="w-14 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        07:30 - 07:50 AM
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        08:00 - 08:40 AM
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        08:40 - 09:20 AM
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        09:20 - 10:00 AM
                                    </th>
                                    <th class="w-18 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        10:00 - 10:40 AM
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        11:05 - 11:45 AM
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        11:45 - 12:25 PM
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        12:25 - 01:05 PM
                                    </th>
                                    <th class="w-18 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        01:05 - 01:20 PM
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        01:20 - 02:00 PM
                                    </th>
                                    <th class="border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        02:00 - 02:40 PM
                                    </th>
                                    <th class="w-20 border border-slate-300 bg-emerald-100 px-1 py-1 text-center align-middle">
                                        03:30 - 05:30 PM
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- One block per day (Monday-Friday), each with 4 rows (Form I-IV) -->
                                <template v-for="day in days" :key="day">
                                    <tr
                                        v-for="(row, rowIndex) in (schedule[day] || []).filter((r) =>
                                            (selectedForm === 'ALL' || r.form === selectedForm) &&
                                            (selectedStream === 'ALL' || r.stream === selectedStream)
                                        )"
                                        :key="`${day}-${row.form}-${row.stream}-${rowIndex}`"
                                    >
                                        <td
                                            v-if="rowIndex === 0"
                                            class="border border-slate-300 bg-sky-100 px-1 py-4 text-center text-[10px] font-semibold uppercase text-sky-900"
                                            :rowspan="(schedule[day] || []).filter((r) =>
                                                (selectedForm === 'ALL' || r.form === selectedForm) &&
                                                (selectedStream === 'ALL' || r.stream === selectedStream)
                                            ).length || 1"
                                        >
                                            {{ day }}
                                        </td>
                                        <td class="border border-slate-300 bg-slate-50 px-1 py-1 text-center font-semibold">
                                            {{ row.form }}
                                        </td>
                                        <td class="border border-slate-300 bg-slate-50 px-1 py-1 text-center">
                                            {{ row.stream }}
                                        </td>
                                        <td class="border border-slate-300 bg-sky-50 px-1 py-1 text-center">
                                            <div class="font-semibold">ARRIVAL &amp; CLEANING</div>
                                        </td>
                                        <td class="border border-slate-300 bg-fuchsia-100 px-1 py-1 text-center font-semibold text-fuchsia-900">
                                            PREP
                                        </td>

                                        <!-- 3 slots before first break (08:00-08:40, 08:40-09:20, 09:20-10:00) - normal lessons for all days -->
                                        <td
                                            v-for="(slot, index) in row.slots.slice(0, 3)"
                                            :key="`am-${index}`"
                                            class="border border-slate-300 bg-emerald-50 px-1 py-1 text-center"
                                            :draggable="isDraggableSlot(day, index)"
                                            @dragstart="isDraggableSlot(day, index) && onDragStart(day, rowIndex, index)"
                                            @dragover.prevent
                                            @drop="isDraggableSlot(day, index) && onDrop(day, rowIndex, index)"
                                        >
                                            <div>{{ slot.subject }}</div>
                                            <div class="text-[8px] text-gray-600">{{ slot.teacher }}</div>
                                        </td>

                                        <!-- First BREAK column (10:00-10:40) -->
                                        <td class="border border-slate-300 bg-sky-200 px-1 py-1 text-center font-semibold text-sky-900">
                                            BREAK
                                        </td>

                                        <!-- 3 slots between breaks (11:05-11:45, 11:45-12:25, 12:25-01:05)
                                             - normal lessons, except Friday last slot is MEWAKA -->
                                        <td
                                            v-for="(slot, index) in row.slots.slice(3, 6)"
                                            :key="`mid-${index}`"
                                            class="border border-slate-300 bg-indigo-50 px-1 py-1 text-center"
                                            :draggable="isDraggableSlot(day, 3 + index)"
                                            @dragstart="isDraggableSlot(day, 3 + index) && onDragStart(day, rowIndex, 3 + index)"
                                            @dragover.prevent
                                            @drop="isDraggableSlot(day, 3 + index) && onDrop(day, rowIndex, 3 + index)"
                                        >
                                            <template v-if="day === 'FRIDAY' && index === 2">
                                                <div class="font-semibold text-orange-700">MEWAKA</div>
                                            </template>
                                            <template v-else>
                                                <div>{{ slot.subject }}</div>
                                                <div class="text-[8px] text-gray-600">{{ slot.teacher }}</div>
                                            </template>
                                        </td>

                                        <!-- Second BREAK column (01:05-01:20) -->
                                        <td class="border border-slate-300 bg-sky-200 px-1 py-1 text-center font-semibold text-sky-900">
                                            BREAK
                                        </td>

                                        <!-- 2 slots after second break (01:20-02:00, 02:00-02:40) -->
                                        <td
                                            v-for="(slot, index) in row.slots.slice(6, 8)"
                                            :key="`pm-${index}`"
                                            :class="[
                                                'border border-slate-300 px-1 py-1 text-center',
                                                day === 'WEDNESDAY'
                                                    ? 'bg-amber-100 text-[9px] font-semibold text-amber-900'
                                                    : day === 'THURSDAY'
                                                        ? 'bg-amber-200 text-[9px] font-semibold text-amber-900'
                                                        : day === 'FRIDAY'
                                                            ? 'bg-lime-300 text-[9px] font-semibold text-lime-900'
                                                            : 'bg-indigo-50',
                                            ]"
                                            :draggable="isDraggableSlot(day, 6 + index)"
                                            @dragstart="isDraggableSlot(day, 6 + index) && onDragStart(day, rowIndex, 6 + index)"
                                            @dragover.prevent
                                            @drop="isDraggableSlot(day, 6 + index) && onDrop(day, rowIndex, 6 + index)"
                                        >
                                            <template v-if="day === 'WEDNESDAY'">
                                                <div>RELIGION</div>
                                            </template>
                                            <template v-else-if="day === 'THURSDAY'">
                                                <div>DEBATE OR CLUBS</div>
                                            </template>
                                            <template v-else-if="day === 'FRIDAY'">
                                                <div>SPORTS AND GAMES</div>
                                            </template>
                                            <template v-else>
                                                <div>{{ slot.subject }}</div>
                                                <div class="text-[8px] text-gray-600">{{ slot.teacher }}</div>
                                            </template>
                                        </td>

                                        <!-- REMEDIAL column -->
                                        <td class="border border-slate-300 bg-emerald-200 px-1 py-1 text-center font-semibold text-emerald-900">
                                            REMEDIAL
                                        </td>
                                    </tr>
                                    <!-- Separator between days -->
                                    <tr>
                                        <td :colspan="5 + 8 + 2 + 1" class="h-1 bg-yellow-300"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Details modal -->
            <div
                v-if="showDetailsModal"
                class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 sm:px-0"
            >
                <div
                    class="max-h-full w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-5 text-xs text-gray-700 shadow-lg ring-1 ring-gray-200"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">
                                School Timetable Details
                            </h3>
                            <p class="mt-0.5 text-[11px] text-gray-500">
                                Set timetable type, class, year and time range. These details will appear in the preview header when you save.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="text-[11px] text-gray-500 hover:text-gray-700"
                            @click="showDetailsModal = false"
                        >
                            Close
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Timetable Type</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button
                                    type="button"
                                    class="rounded-md px-2 py-1.5 text-[11px] font-semibold ring-1"
                                    :class="
                                        form.type === 'class'
                                            ? 'bg-emerald-600 text-white ring-emerald-600'
                                            : 'bg-white text-gray-700 ring-gray-200 hover:bg-gray-50'
                                    "
                                    @click="form.type = 'class'"
                                >
                                    Class Timetable
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md px-2 py-1.5 text-[11px] font-semibold ring-1"
                                    :class="
                                        form.type === 'invigilation'
                                            ? 'bg-emerald-600 text-white ring-emerald-600'
                                            : 'bg-white text-gray-700 ring-gray-200 hover:bg-gray-50'
                                    "
                                    @click="form.type = 'invigilation'"
                                >
                                    Invigilation
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md px-2 py-1.5 text-[11px] font-semibold ring-1"
                                    :class="
                                        form.type === 'exams'
                                            ? 'bg-emerald-600 text-white ring-emerald-600'
                                            : 'bg-white text-gray-700 ring-gray-200 hover:bg-gray-50'
                                    "
                                    @click="form.type = 'exams'"
                                >
                                    Exams Timetable
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Title</label>
                            <input
                                v-model="form.title"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="e.g. Form I A - Term I Timetable"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-[11px] font-medium">Class / Group</label>
                                <input
                                    v-model="form.class_name"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="e.g. Form I A"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-medium">Term</label>
                                <input
                                    v-model="form.term"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="e.g. Term I"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-[11px] font-medium">Academic Year</label>
                                <input
                                    v-model="form.academic_year"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="e.g. 2025"
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="mb-1 block text-[11px] font-medium">Start Time</label>
                                    <input
                                        v-model="form.start_time"
                                        type="time"
                                        class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-medium">End Time</label>
                                    <input
                                        v-model="form.end_time"
                                        type="time"
                                        class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <p class="mt-2 text-[11px] text-gray-500">
                            Hifadhi ratiba hii kwa matumizi ya baadaye kama timetable ya darasa au shule nzima.
                        </p>

                        <div class="mt-3 flex items-center justify-end gap-2">
                            <button
                                type="button"
                                class="rounded-md bg-gray-100 px-3 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-gray-200"
                                @click="showDetailsModal = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                                @click="saveTimetable"
                            >
                                Generate &amp; Save Timetable
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }

    #class-timetable-preview,
    #class-timetable-preview * {
        visibility: visible;
    }

    #class-timetable-preview {
        position: absolute;
        inset: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        box-shadow: none;
        border: none;
    }
}
</style>
