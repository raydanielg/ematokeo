<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';

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
    academic_year: props.timetable?.academic_year || String(new Date().getFullYear()),
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

const levelToForm = (value) => {
    const map = {
        1: 'I',
        2: 'II',
        3: 'III',
        4: 'IV',
        5: 'V',
        6: 'VI',
        7: 'VII',
    };

    if (value === null || value === undefined) return '';

    const s = String(value).trim();
    if (s === '') return '';

    const n = Number(s);
    if (!Number.isNaN(n) && map[n]) {
        return map[n];
    }

    const upper = s.toUpperCase();

    if (/^[IVXLCDM]+$/.test(upper)) {
        return upper;
    }

    const match = upper.match(/\b(?:FORM|CLASS|STD|STANDARD)\s*([IVXLCDM]+|\d+)\b/);
    if (match) {
        const token = match[1];
        const tokenNum = Number(token);
        if (!Number.isNaN(tokenNum) && map[tokenNum]) {
            return map[tokenNum];
        }

        if (/^[IVXLCDM]+$/.test(token)) {
            return token;
        }
    }

    return '';
};

const romanToNumber = (roman) => {
    if (!roman) return null;

    const s = String(roman).toUpperCase().trim();
    if (!s) return null;

    const map = { I: 1, V: 5, X: 10, L: 50, C: 100, D: 500, M: 1000 };
    let total = 0;
    let prev = 0;

    for (let i = s.length - 1; i >= 0; i -= 1) {
        const val = map[s[i]];
        if (!val) return null;

        if (val < prev) {
            total -= val;
        } else {
            total += val;
            prev = val;
        }
    }

    return total;
};

const getFormOrder = (c) => {
    const roman = getClassForm(c);
    if (!roman) return Number.POSITIVE_INFINITY;

    const num = romanToNumber(roman);
    return num === null ? Number.POSITIVE_INFINITY : num;
};

const getClassForm = (c) => {
    return levelToForm(c?.parent_name) || levelToForm(c?.level) || levelToForm(c?.name);
};

const getClassLabel = (c) => {
    const parentName = (c?.parent_name || '').trim();
    if (parentName) return parentName;

    const name = (c?.name || '').trim();
    if (name && /\b(?:FORM|CLASS|STD|STANDARD)\b/i.test(name)) {
        return name;
    }

    const level = (c?.level || '').trim();
    if (level && /\b(?:FORM|CLASS|STD|STANDARD)\b/i.test(level)) {
        return level;
    }

    const form = getClassForm(c);
    if (form) return `Form ${form}`;

    return level || name;
};

const timetableClasses = computed(() => {
    const all = Array.isArray(props.classes) ? props.classes : [];

    const streamsByParent = new Map();
    all.forEach((c) => {
        if (!c || !c.parent_class_id) return;
        const stream = c.stream ? String(c.stream).trim() : '';
        if (!stream) return;

        if (!streamsByParent.has(c.parent_class_id)) {
            streamsByParent.set(c.parent_class_id, []);
        }

        streamsByParent.get(c.parent_class_id).push(c);
    });

    const result = [];

    // Prefer stream rows if a base class has streams; otherwise include the base class itself
    all.filter((c) => !c?.parent_class_id).forEach((base) => {
        const streams = streamsByParent.get(base.id) || [];
        if (streams.length) {
            streams
                .slice()
                .sort((a, b) => String(a.stream || '').localeCompare(String(b.stream || '')))
                .forEach((s) => result.push(s));
            return;
        }

        result.push(base);
    });

    // Add orphan streams (in case parent not present in payload)
    const baseIds = new Set(all.filter((c) => !c?.parent_class_id).map((c) => c.id));
    all.filter((c) => c?.parent_class_id && !baseIds.has(c.parent_class_id)).forEach((c) => result.push(c));

    const seen = new Set();
    const unique = result.filter((c) => {
        const id = c?.id;
        if (id === null || id === undefined) return false;
        if (seen.has(id)) return false;
        seen.add(id);
        return true;
    });

    return unique.sort((a, b) => {
        const orderA = getFormOrder(a);
        const orderB = getFormOrder(b);
        if (orderA !== orderB) return orderA - orderB;

        const streamA = (a?.stream ? String(a.stream) : '').toUpperCase();
        const streamB = (b?.stream ? String(b.stream) : '').toUpperCase();
        const streamCmp = streamA.localeCompare(streamB);
        if (streamCmp !== 0) return streamCmp;

        return (a?.id || 0) - (b?.id || 0);
    });
});

const classLabels = computed(() => {
    const unique = new Set();

    timetableClasses.value.forEach((c) => {
        const label = getClassLabel(c);
        if (label) {
            unique.add(label);
        }
    });

    if (unique.size === 0) {
        return ['Form I', 'Form II', 'Form III', 'Form IV'];
    }

    const list = Array.from(unique.values());

    return list.sort((a, b) => {
        const fa = levelToForm(a);
        const fb = levelToForm(b);
        const na = romanToNumber(fa) ?? Number.POSITIVE_INFINITY;
        const nb = romanToNumber(fb) ?? Number.POSITIVE_INFINITY;
        if (na !== nb) return na - nb;
        return String(a).localeCompare(String(b));
    });
});

const streams = computed(() => {
    const unique = new Set();

    timetableClasses.value.forEach((c) => {
        const stream = c.stream ? String(c.stream).toUpperCase().trim() : '';
        if (!stream) return;

        if (selectedClass.value !== 'ALL') {
            const label = getClassLabel(c);
            if (label && label !== selectedClass.value) {
                return;
            }
        }

        unique.add(stream);
    });

    if (unique.size === 0) {
        return [];
    }

    return Array.from(unique.values());
});

const selectedClass = ref('ALL');
const selectedStream = ref('ALL');

watch(selectedClass, () => {
    if (selectedStream.value === 'ALL') {
        return;
    }

    if (!streams.value.includes(selectedStream.value)) {
        selectedStream.value = 'ALL';
    }
});

const headerClassName = computed(() => {
    // When a specific form and stream are selected, show that combination
    if (selectedClass.value !== 'ALL' && selectedStream.value !== 'ALL') {
        return `${selectedClass.value} ${selectedStream.value}`;
    }

    // When only a specific form is selected, treat it as a class-level view
    if (selectedClass.value !== 'ALL') {
        return `${selectedClass.value}`;
    }

    // Otherwise fall back to the manually entered class name or blanks
    return form.class_name || '____';
});

// schedule[day][formIndex][slotIndex] = { subject, teacher }
const schedule = ref({});

const scheduleStorageKey = computed(() => {
    const schoolId = props.school?.id ?? 'school';
    return `timetable_schedule_v2_${schoolId}`;
});

const loadScheduleFromStorage = () => {
    try {
        const raw = window.localStorage.getItem(scheduleStorageKey.value);
        if (!raw) return null;
        const parsed = JSON.parse(raw);
        if (!parsed || typeof parsed !== 'object') return null;
        return parsed;
    } catch {
        return null;
    }
};

const saveScheduleToStorage = () => {
    try {
        window.localStorage.setItem(scheduleStorageKey.value, JSON.stringify(schedule.value || {}));
    } catch {
        // ignore storage failures
    }
};

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
        timetableClasses.value.forEach((c) => {
            const formLabel = getClassForm(c);
            const classLabel = getClassLabel(c);
            const streamLabel = c.stream ? String(c.stream).toUpperCase().trim() : '';

            const classSubjectCodes = Array.isArray(c.subject_codes) && c.subject_codes.length
                ? c.subject_codes
                : codes;

            const row = {
                form: formLabel,
                class_label: classLabel,
                stream: streamLabel,
                class_name: c.name || `${classLabel} ${streamLabel}`.trim(),
                slots: [],
            };

            // 9 academic slots per row (4 before break, 5 after), index 0-8
            // Choose subjects randomly from the pool so that each regenerate
            // gives a slightly different distribution per class and per day.
            for (let i = 0; i < 9; i += 1) {
                const subject = classSubjectCodes[Math.floor(Math.random() * classSubjectCodes.length)];
                row.slots.push({
                    subject,
                    teacher: teachers[subject] || '',
                });
            }

            next[day].push(row);
        });
    });

    schedule.value = next;
    saveScheduleToStorage();
};

const getFilteredRows = (day) => {
    const rows = schedule.value[day] || [];

    return rows
        .map((row, originalIndex) => ({ row, originalIndex }))
        .filter(({ row }) =>
            (selectedClass.value === 'ALL' || row.class_label === selectedClass.value) &&
            (selectedStream.value === 'ALL' || row.stream === selectedStream.value)
        );
};

const getGroupedRows = (day) => {
    const filtered = getFilteredRows(day);
    const groups = [];
    const indexByKey = new Map();

    filtered.forEach((item) => {
        const label = item.row.class_label || item.row.form || '';
        const key = label.trim() || 'UNKNOWN';

        if (!indexByKey.has(key)) {
            indexByKey.set(key, groups.length);
            groups.push({ key, label, rows: [] });
        }

        groups[indexByKey.get(key)].rows.push(item);
    });

    return groups.sort((a, b) => {
        const fa = levelToForm(a.label);
        const fb = levelToForm(b.label);

        const oa = romanToNumber(fa) ?? Number.POSITIVE_INFINITY;
        const ob = romanToNumber(fb) ?? Number.POSITIVE_INFINITY;
        if (oa !== ob) return oa - ob;

        return String(a.label || '').localeCompare(String(b.label || ''));
    });
};

const getGroupedRowCount = (day) => {
    return getGroupedRows(day).reduce((sum, g) => sum + g.rows.length, 0);
};

onMounted(() => {
    const stored = loadScheduleFromStorage();
    if (stored) {
        schedule.value = stored;
        return;
    }

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

const showLimitationsModal = ref(false);
const selectedLimitClassId = ref('');
const limitsLoading = ref(false);
const limitsSaving = ref(false);
const limitsByKey = ref({});

const limitationsMode = ref('subject_only');
const subjectLimitsById = ref({});

const selectedLimitClass = computed(() => {
    const selectedId = selectedLimitClassId.value ? Number(selectedLimitClassId.value) : null;
    if (!Number.isFinite(selectedId)) return null;
    return timetableClasses.value.find((c) => Number(c?.id) === selectedId) || null;
});

const selectedLimitClassHasSubjects = computed(() => {
    const codes = Array.isArray(selectedLimitClass.value?.subject_codes) ? selectedLimitClass.value.subject_codes : [];
    return codes.length > 0;
});

const csrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
};

const teacherSubjectRows = computed(() => {
    const rows = [];

    (props.subjects || []).forEach((s) => {
        const teachers = Array.isArray(s.teachers) ? s.teachers : [];
        teachers.forEach((t) => {
            if (!t?.id) return;
            rows.push({
                teacher_id: t.id,
                teacher_name: t.name,
                teacher_initials: t.initials,
                subject_id: s.id,
                subject_code: s.subject_code,
                subject_name: s.name,
            });
        });
    });

    const selectedId = selectedLimitClassId.value ? Number(selectedLimitClassId.value) : null;
    const selectedClass = Number.isFinite(selectedId)
        ? timetableClasses.value.find((c) => Number(c?.id) === selectedId)
        : null;
    const classSubjectCodes = Array.isArray(selectedClass?.subject_codes) ? selectedClass.subject_codes : [];

    if (!selectedClass) {
        return [];
    }

    const filtered = selectedClass
        ? rows.filter((r) => classSubjectCodes.includes(r.subject_code))
        : rows;

    return filtered.sort((a, b) => {
        const tc = String(a.teacher_name || '').localeCompare(String(b.teacher_name || ''));
        if (tc !== 0) return tc;
        return String(a.subject_code || '').localeCompare(String(b.subject_code || ''));
    });
});

const classSubjectRows = computed(() => {
    const selectedId = selectedLimitClassId.value ? Number(selectedLimitClassId.value) : null;
    const selectedClass = Number.isFinite(selectedId)
        ? timetableClasses.value.find((c) => Number(c?.id) === selectedId)
        : null;

    if (!selectedClass) {
        return [];
    }

    const codes = Array.isArray(selectedClass?.subject_codes) ? selectedClass.subject_codes : [];
    const subjects = (props.subjects || [])
        .filter((s) => codes.includes(s.subject_code))
        .map((s) => ({
            subject_id: s.id,
            subject_code: s.subject_code,
            subject_name: s.name,
        }));

    return subjects.sort((a, b) => String(a.subject_code || '').localeCompare(String(b.subject_code || '')));
});

const limitationClassOptions = computed(() => {
    return timetableClasses.value.map((c) => {
        const label = getClassLabel(c) || c.name || '';
        const stream = c.stream ? String(c.stream).toUpperCase().trim() : '';
        const display = stream ? `${label} ${stream}` : label;

        return {
            id: String(c.id),
            display,
        };
    });
});

const limitKey = (teacherId, subjectId) => `${teacherId}:${subjectId}`;

const subjectLimitKey = (subjectId) => `${subjectId}`;

const loadSubjectLimits = async () => {
    if (!selectedLimitClassId.value) {
        subjectLimitsById.value = {};
        return;
    }

    limitsLoading.value = true;
    try {
        const res = await fetch(
            `${route('timetables.class-subject-limits.index')}?school_class_id=${encodeURIComponent(selectedLimitClassId.value)}`,
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            }
        );

        const json = await res.json();
        const map = {};
        (json?.limits || []).forEach((row) => {
            map[subjectLimitKey(row.subject_id)] = row.periods_per_week;
        });
        subjectLimitsById.value = map;
    } catch {
        subjectLimitsById.value = {};
    } finally {
        limitsLoading.value = false;
    }
};

const loadLimits = async () => {
    if (!selectedLimitClassId.value) {
        limitsByKey.value = {};
        return;
    }

    limitsLoading.value = true;
    try {
        const res = await fetch(
            `${route('timetables.weekly-limits.index')}?school_class_id=${encodeURIComponent(selectedLimitClassId.value)}`,
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            }
        );

        const json = await res.json();
        const map = {};
        (json?.limits || []).forEach((row) => {
            map[limitKey(row.teacher_id, row.subject_id)] = row.periods_per_week;
        });
        limitsByKey.value = map;
    } catch {
        limitsByKey.value = {};
    } finally {
        limitsLoading.value = false;
    }
};

const saveLimits = async () => {
    if (!selectedLimitClassId.value) return;

    limitsSaving.value = true;
    try {
        const limits = teacherSubjectRows.value
            .map((row) => {
                const key = limitKey(row.teacher_id, row.subject_id);
                const val = limitsByKey.value[key];
                return {
                    school_class_id: Number(selectedLimitClassId.value),
                    subject_id: row.subject_id,
                    teacher_id: row.teacher_id,
                    periods_per_week: val === '' || val === null || val === undefined ? 0 : Number(val),
                };
            })
            .filter((r) => Number.isFinite(r.periods_per_week) && r.periods_per_week >= 0);

        await fetch(route('timetables.weekly-limits.save'), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ limits }),
        });

        showLimitationsModal.value = false;
    } finally {
        limitsSaving.value = false;
    }
};

const saveSubjectLimits = async () => {
    if (!selectedLimitClassId.value) return;

    limitsSaving.value = true;
    try {
        const limits = classSubjectRows.value
            .map((row) => {
                const key = subjectLimitKey(row.subject_id);
                const val = subjectLimitsById.value[key];
                return {
                    school_class_id: Number(selectedLimitClassId.value),
                    subject_id: row.subject_id,
                    periods_per_week: val === '' || val === null || val === undefined ? 0 : Number(val),
                };
            })
            .filter((r) => Number.isFinite(r.periods_per_week) && r.periods_per_week >= 0);

        await fetch(route('timetables.class-subject-limits.save'), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ limits }),
        });

        showLimitationsModal.value = false;
    } finally {
        limitsSaving.value = false;
    }
};

watch(showLimitationsModal, (open) => {
    if (!open) return;
    if (!selectedLimitClassId.value && limitationClassOptions.value.length) {
        selectedLimitClassId.value = limitationClassOptions.value[0].id;
    }
    if (limitationsMode.value === 'subject_only') {
        loadSubjectLimits();
    } else {
        loadLimits();
    }
});

watch(selectedLimitClassId, () => {
    if (!showLimitationsModal.value) return;
    if (limitationsMode.value === 'subject_only') {
        loadSubjectLimits();
    } else {
        loadLimits();
    }
});

watch(limitationsMode, () => {
    if (!showLimitationsModal.value) return;
    if (limitationsMode.value === 'subject_only') {
        loadSubjectLimits();
    } else {
        loadLimits();
    }
});

const draggedCell = ref(null);

const isDraggableSlot = (day, slotIndex) => {
    // Morning lessons (08:00-10:00) always draggable
    if (slotIndex >= 0 && slotIndex <= 3) {
        return true;
    }

    // Mid-day lessons between breaks (11:05-13:05)
    // Keep Friday MEWAKA slot (index 6) fixed
    if (slotIndex >= 4 && slotIndex <= 6) {
        if (day === 'FRIDAY' && slotIndex === 6) {
            return false;
        }

        return true;
    }

    // Afternoon lessons after second break (13:20-14:40)
    // Keep special activities (RELIGION, CLUBS, SPORTS) fixed on Wed/Thu/Fri
    if (slotIndex >= 7 && slotIndex <= 8) {
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

    saveScheduleToStorage();

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
                            v-model="selectedClass"
                            class="rounded border border-gray-300 bg-white px-1 py-0.5 text-[9px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="ALL">All Classes</option>
                            <option v-for="c in classLabels" :key="c" :value="c">{{ c }}</option>
                        </select>
                        <select
                            v-model="selectedStream"
                            class="rounded border border-gray-300 bg-white px-1 py-0.5 text-[9px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="ALL">All Streams</option>
                            <option v-if="!streams.length" value="" disabled>No streams</option>
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
                            @click="showLimitationsModal = true"
                        >
                            Limitations &amp; More
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
                            <span v-if="selectedClass !== 'ALL'">
                                · Darasa: <span class="font-semibold">{{ headerClassName }}</span>
                            </span>
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
                                        CLASS
                                    </th>
                                    <th class="w-12 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        STREAM
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
                                    <th class="w-18 border border-slate-300 bg-slate-100 px-1 py-1 text-center align-middle">
                                        10:40 - 11:05 AM
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
                                    <template v-for="(group, groupIndex) in getGroupedRows(day)" :key="`${day}-${group.key}`">
                                        <tr
                                            v-for="({ row, originalIndex }, rowIndex) in group.rows"
                                            :key="`${day}-${group.key}-${row.stream}-${originalIndex}`"
                                        >
                                            <td
                                                v-if="groupIndex === 0 && rowIndex === 0"
                                                class="border border-slate-300 bg-sky-100 px-1 py-4 text-center text-[10px] font-semibold uppercase text-sky-900"
                                                :rowspan="getGroupedRowCount(day) || 1"
                                            >
                                                {{ day }}
                                            </td>
                                            <td
                                                v-if="rowIndex === 0"
                                                class="border border-slate-300 bg-slate-50 px-1 py-1 text-center font-semibold"
                                                :rowspan="group.rows.length"
                                            >
                                                {{ group.label || row.class_label || row.form }}
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

                                        <!-- 4 slots before first break (08:00-08:40, 08:40-09:20, 09:20-10:00, 10:00-10:40) - normal lessons for all days -->
                                        <td
                                            v-for="(slot, index) in row.slots.slice(0, 4)"
                                            :key="`am-${index}`"
                                            class="border border-slate-300 bg-emerald-50 px-1 py-1 text-center"
                                            :draggable="isDraggableSlot(day, index)"
                                            @dragstart="isDraggableSlot(day, index) && onDragStart(day, originalIndex, index)"
                                            @dragover.prevent
                                            @drop="isDraggableSlot(day, index) && onDrop(day, originalIndex, index)"
                                        >
                                            <div>{{ slot.subject }}</div>
                                            <div class="text-[8px] text-gray-600">{{ slot.teacher }}</div>
                                        </td>

                                        <!-- First BREAK column (10:40-11:05) -->
                                        <td class="border border-slate-300 bg-sky-200 px-1 py-1 text-center font-semibold text-sky-900">
                                            BREAK
                                        </td>

                                        <!-- 3 slots between breaks (11:05-11:45, 11:45-12:25, 12:25-01:05)
                                             - normal lessons, except Friday last slot is MEWAKA -->
                                        <td
                                            v-for="(slot, index) in row.slots.slice(4, 7)"
                                            :key="`mid-${index}`"
                                            class="border border-slate-300 bg-indigo-50 px-1 py-1 text-center"
                                            :draggable="isDraggableSlot(day, 4 + index)"
                                            @dragstart="isDraggableSlot(day, 4 + index) && onDragStart(day, originalIndex, 4 + index)"
                                            @dragover.prevent
                                            @drop="isDraggableSlot(day, 4 + index) && onDrop(day, originalIndex, 4 + index)"
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
                                            v-for="(slot, index) in row.slots.slice(7, 9)"
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
                                            :draggable="isDraggableSlot(day, 7 + index)"
                                            @dragstart="isDraggableSlot(day, 7 + index) && onDragStart(day, originalIndex, 7 + index)"
                                            @dragover.prevent
                                            @drop="isDraggableSlot(day, 7 + index) && onDrop(day, originalIndex, 7 + index)"
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
                                    </template>
                                    <!-- Separator between days -->
                                    <tr>
                                        <td :colspan="5 + 9 + 2 + 1" class="h-1 bg-yellow-300"></td>
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

            <!-- Limitations modal -->
            <div
                v-if="showLimitationsModal"
                class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 sm:px-0"
            >
                <div
                    class="max-h-full w-full max-w-4xl overflow-y-auto rounded-xl bg-white p-5 text-xs text-gray-700 shadow-lg ring-1 ring-gray-200"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">
                                Limitations &amp; More
                            </h3>
                            <p class="mt-0.5 text-[11px] text-gray-500">
                                Weka vipindi kwa wiki kwa kila mwalimu na somo kwa darasa/stream.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="text-[11px] text-gray-500 hover:text-gray-700"
                            @click="showLimitationsModal = false"
                        >
                            Close
                        </button>
                    </div>

                    <div class="mb-3 flex flex-wrap items-end gap-3">
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Class / Stream</label>
                            <select
                                v-model="selectedLimitClassId"
                                class="w-64 rounded-md border border-gray-300 bg-white px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option value="" disabled>Select class</option>
                                <option v-for="c in limitationClassOptions" :key="c.id" :value="c.id">
                                    {{ c.display }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Mode</label>
                            <select
                                v-model="limitationsMode"
                                class="w-56 rounded-md border border-gray-300 bg-white px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option value="subject_only">Subject limitation</option>
                                <option value="teacher_subject">Teacher + Subject limitation</option>
                            </select>
                        </div>

                        <div v-if="limitsLoading" class="text-[11px] text-gray-500">Loading...</div>
                    </div>

                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table v-if="limitationsMode === 'subject_only'" class="min-w-full border-collapse text-[11px]">
                            <thead>
                                <tr class="bg-gray-50 text-left">
                                    <th class="border-b border-gray-200 px-3 py-2">Subject</th>
                                    <th class="border-b border-gray-200 px-3 py-2">Periods / Week</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!classSubjectRows.length">
                                    <td colspan="2" class="px-3 py-3 text-center text-[11px] text-gray-500">
                                        <span v-if="!selectedLimitClassId">Select a class to view subject limitations.</span>
                                        <span v-else-if="selectedLimitClassId && !selectedLimitClassHasSubjects">No subjects assigned to this class.</span>
                                        <span v-else>No subjects found.</span>
                                    </td>
                                </tr>

                                <tr v-for="row in classSubjectRows" :key="row.subject_id" class="hover:bg-gray-50">
                                    <td class="border-b border-gray-100 px-3 py-2">
                                        <div class="font-medium text-gray-800">{{ row.subject_code }}</div>
                                        <div class="text-[10px] text-gray-500">{{ row.subject_name }}</div>
                                    </td>
                                    <td class="border-b border-gray-100 px-3 py-2">
                                        <input
                                            type="number"
                                            min="0"
                                            max="50"
                                            class="w-24 rounded-md border border-gray-300 px-2 py-1 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                            :disabled="!selectedLimitClassId"
                                            :value="subjectLimitsById[subjectLimitKey(row.subject_id)] ?? 0"
                                            @input="(e) => { subjectLimitsById[subjectLimitKey(row.subject_id)] = e.target.value; }"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table v-else class="min-w-full border-collapse text-[11px]">
                            <thead>
                                <tr class="bg-gray-50 text-left">
                                    <th class="border-b border-gray-200 px-3 py-2">Teacher</th>
                                    <th class="border-b border-gray-200 px-3 py-2">Subject</th>
                                    <th class="border-b border-gray-200 px-3 py-2">Periods / Week</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!teacherSubjectRows.length">
                                    <td colspan="3" class="px-3 py-3 text-center text-[11px] text-gray-500">
                                        <span v-if="!selectedLimitClassId">Select a class to view teacher limitations.</span>
                                        <span v-else-if="selectedLimitClassId && !selectedLimitClassHasSubjects">No subjects assigned to this class.</span>
                                        <span v-else>No teacher-subject mappings found.</span>
                                    </td>
                                </tr>

                                <tr v-for="row in teacherSubjectRows" :key="`${row.teacher_id}-${row.subject_id}`" class="hover:bg-gray-50">
                                    <td class="border-b border-gray-100 px-3 py-2">
                                        <div class="font-medium text-gray-800">{{ row.teacher_name }}</div>
                                        <div class="text-[10px] text-gray-500">{{ row.teacher_initials }}</div>
                                    </td>
                                    <td class="border-b border-gray-100 px-3 py-2">
                                        <div class="font-medium text-gray-800">{{ row.subject_code }}</div>
                                        <div class="text-[10px] text-gray-500">{{ row.subject_name }}</div>
                                    </td>
                                    <td class="border-b border-gray-100 px-3 py-2">
                                        <input
                                            type="number"
                                            min="0"
                                            max="50"
                                            class="w-24 rounded-md border border-gray-300 px-2 py-1 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                            :disabled="!selectedLimitClassId"
                                            :value="limitsByKey[limitKey(row.teacher_id, row.subject_id)] ?? 0"
                                            @input="(e) => { limitsByKey[limitKey(row.teacher_id, row.subject_id)] = e.target.value; }"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-5 rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <pre class="whitespace-pre-wrap text-[11px] leading-relaxed text-gray-700">Jedwali la Muhtasari wa Mzigo wa Mwalimu (Kwa Zoezi):
No.	Subject	Form I &amp; II	Form III &amp; IV	Jumla kwa Wiki
1.	Hisabati	5	5	10
2.	Physics	3	4	7
3.	Computer Science	4	4	8
Jumla				25
Vipengele vya Zoezi Unaweza Kufanya:
Chagua mchanganyiko tofauti wa masomo.

Badilisha ngazi za masomo (mfano: Fomu I &amp; II tu).

Ongeza idadi ya walimu na uhesabu usambazaji wa mzigo kwa shule nzima.

Tengeneza chati ya pie au bar kuonyesha usambazaji wa vipindi.

Kama unataka, naweza kukusaidia kuandaa hii kwenye Excel na kukupa faili iliyo na:

Jedwali linaloweza kurekebishwa

Fomu za kuhesabu automatikali

Chati za kuona

Tuambie unapendelea kuendelea vipi!

andika yote kama iluvo kwnye podf usibadilsihe andika kila kitu kama kilivyo
Hapa ni nakala kamili ya maudhui ya faili yako kama ilivyo, bila mabadiliko yoyote:

===== Page 1 =====

Table 4: Compulsory Core Subjects and the Number of Periods per Week
No.	Subject	Number of periods per week
Form I &amp; II	Form III &amp; IV
1.	Mathematics	5	5
2.	Kiswahili	4	4
3.	English	5	5
4.	Business studies	3	3
5.	Geography	2	3
6.	Historia ya Tanzania na Maadili	3	3
Table 5: Electric Subjects and Number of Periods per Week
No.	Subject	Number of periods per week
Form I &amp; II	Form III &amp; IV
1.	Physics	3	4
2.	Chemistry	3	4
3.	Biology	3	4
4.	Agriculture	5	5
5.	Computer science	4	4
6.	Food and Human Nutrition	5	5
7.	Sports	4	4
8.	Textile and Gurment Construction	5	5
9.	Fine Art	4	4
10.	Theatre Arts	4	4
11.	Additional Mathematics	–	3
12.	Literature in English	–	2
13.	Fasihi ya Kiswahili	–	2
14.	French	3	3
15.	Arabic	3	3
16.	Chinese	3	3
17.	Music	4	4
18.	History	3	4
19.	Book-kooping	3	4
20.	Bible Knowledge	3	3
21.	Dani ya Kislamu	3	3
 kwa kila osmo ziangtia usischangenye osmo lolote</pre>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-gray-100 px-3 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-gray-200"
                            :disabled="limitsSaving"
                            @click="showLimitationsModal = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                            :disabled="limitsSaving || !selectedLimitClassId"
                            @click="limitationsMode === 'subject_only' ? saveSubjectLimits() : saveLimits()"
                        >
                            {{ limitsSaving ? 'Saving...' : 'Save Limitations' }}
                        </button>
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
