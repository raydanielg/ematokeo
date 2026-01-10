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
    teacherAssignments: {
        type: Object,
        default: () => ({}),
    },
});

const selectedLimitClassId = ref('');

const sessionRulesByClassId = ref({});

const showTeacherInitialsModal = ref(false);
const showSubjectPalette = ref(false);
const showAssignSubjectsModal = ref(false);
const dropFeedback = ref({ type: '', message: '' });

const subjectPaletteSearch = ref('');
const assignSubjectsSearch = ref('');

const selectedLimitClassSessionRules = computed(() => {
    const classId = selectedLimitClassId.value ? Number(selectedLimitClassId.value) : null;
    if (!Number.isFinite(classId) || !classId) return {};
    return sessionRulesByClassId.value?.[String(classId)] || {};
});

const allSubjectCodes = computed(() => {
    const unique = new Set();
    (props.subjects || []).forEach((s) => {
        const code = String(s?.subject_code || '').trim();
        if (!code) return;
        unique.add(code);
    });
    return Array.from(unique.values()).sort((a, b) => String(a).localeCompare(String(b)));
});

const assignedSubjectCodes = ref([]);

watch(
    allSubjectCodes,
    (codes) => {
        if (!Array.isArray(assignedSubjectCodes.value) || assignedSubjectCodes.value.length === 0) {
            assignedSubjectCodes.value = Array.isArray(codes) ? codes.slice() : [];
        }
    },
    { immediate: true }
);

const filteredAssignSubjectCodes = computed(() => {
    const q = String(assignSubjectsSearch.value || '').trim().toUpperCase();
    if (!q) return allSubjectCodes.value;
    return (allSubjectCodes.value || []).filter((c) => String(c).toUpperCase().includes(q));
});

const openSubjectsPopup = () => {
    showSubjectPalette.value = true;
};

const closeSubjectsPopup = () => {
    showSubjectPalette.value = false;
    subjectPaletteSearch.value = '';
};

const filteredSubjectPaletteCodes = computed(() => {
    const allowed = new Set((assignedSubjectCodes.value || []).map((c) => String(c).trim()));
    const q = String(subjectPaletteSearch.value || '').trim().toUpperCase();
    return (allSubjectCodes.value || [])
        .filter((code) => allowed.has(String(code).trim()))
        .filter((code) => (!q ? true : String(code).toUpperCase().includes(q)));
});

const setSelectedLimitSubjectSessionRule = (subjectId, rule) => {
    const classId = selectedLimitClassId.value ? Number(selectedLimitClassId.value) : null;
    if (!Number.isFinite(classId) || !classId) return;
    if (!sessionRulesByClassId.value[String(classId)]) {
        sessionRulesByClassId.value[String(classId)] = {};
    }
    if (rule === 'morning' || rule === 'afternoon') {
        sessionRulesByClassId.value[String(classId)][String(subjectId)] = rule;
    } else {
        delete sessionRulesByClassId.value[String(classId)][String(subjectId)];
    }
    saveSessionRulesToStorage();
};

const form = useForm({
    type: 'class',
    title: props.timetable?.title || '',
    class_name: props.timetable?.class_name || '',
    academic_year: props.timetable?.academic_year || String(new Date().getFullYear()),
    term: props.timetable?.term || '',
    start_time: '07:30',
    end_time: '16:00',
    schedule_json: props.timetable?.schedule_json || null,
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

const classById = computed(() => {
    const map = {};
    (props.classes || []).forEach((c) => {
        if (!c?.id) return;
        map[String(c.id)] = c;
    });
    return map;
});

const subjectIdByCode = computed(() => {
    const map = {};
    (props.subjects || []).forEach((s) => {
        if (!s?.subject_code || !s?.id) return;
        map[String(s.subject_code)] = Number(s.id);
    });
    return map;
});

const teacherIdByInitials = computed(() => {
    const map = {};
    (props.subjects || []).forEach((s) => {
        const teachers = Array.isArray(s?.teachers) ? s.teachers : [];
        teachers.forEach((t) => {
            if (!t?.id) return;
            const initials = String(t?.initials || '').trim();
            if (!initials) return;
            if (!map[initials]) {
                map[initials] = Number(t.id);
            }
        });
    });
    return map;
});

const teacherInitialsForClassSubject = (schoolClassId, subjectCode) => {
    const classId = schoolClassId ? String(schoolClassId) : '';
    const code = String(subjectCode || '').trim();

    const cls = classById.value?.[classId];
    const parentId = cls?.parent_class_id ? String(cls.parent_class_id) : '';

    // If stream-specific assignments exist, use them ONLY (do not mix with parent Form assignments).
    const streamMap = props.teacherAssignments?.[classId];
    const classMap = streamMap !== undefined ? streamMap : (parentId ? props.teacherAssignments?.[parentId] : null);
    if (classMap && typeof classMap === 'object') {
        // Strict mode: if assignments exist for this class, do not fall back to global teachers.
        const fromAssignments = classMap?.[code];
        return fromAssignments ? String(fromAssignments) : '';
    }

    // Fallback only when the school has not configured per-class assignments.
    const fromAssignments = props.teacherAssignments?.[classId]?.[code];
    if (fromAssignments) return String(fromAssignments);
    return teacherBySubject.value?.[code] || '';
};

const classHasTeacherAssignments = (schoolClassId) => {
    const classId = schoolClassId ? String(schoolClassId) : '';
    const cls = classById.value?.[classId];
    const parentId = cls?.parent_class_id ? String(cls.parent_class_id) : '';

    const streamMap = props.teacherAssignments?.[classId];
    const classMap = streamMap !== undefined ? streamMap : (parentId ? props.teacherAssignments?.[parentId] : null);
    return !!(classMap && typeof classMap === 'object' && Object.keys(classMap).length);
};

const pickTeacherInitialForSlot = (schoolClassId, subjectCode, day, slotIndex) => {
    const raw = teacherInitialsForClassSubject(schoolClassId, subjectCode);
    const subjectKey = String(subjectCode || '').trim().toUpperCase();
    const parts = String(raw || '')
        .split('/')
        .map((s) => s.trim())
        .filter(Boolean);

    // If the "teacher" value is accidentally the same as the subject code (e.g. CIV under CIV), hide it.
    const cleanParts = parts.filter((p) => String(p).trim().toUpperCase() !== subjectKey);

    if (!cleanParts.length) return '';
    if (cleanParts.length === 1) return cleanParts[0];

    // Co-teaching: show both teachers when exactly two are assigned
    if (cleanParts.length === 2) return `${cleanParts[0]}/${cleanParts[1]}`;

    const d = String(day || '').toUpperCase();
    const dayIdx = days.indexOf(d);
    const seed = (Math.max(0, dayIdx) * 31) + Number(slotIndex || 0);
    return cleanParts[seed % cleanParts.length];
};

const teacherBySubject = computed(() => {
    const map = {};

    (props.subjects || []).forEach((s) => {
        const teachers = Array.isArray(s.teachers) ? s.teachers : [];
        if (!s?.subject_code || !teachers.length) return;

        const parts = teachers
            .map((t) => String(t?.initials || '').trim() || simpleTeacherName(t?.name) || String(t?.name || '').trim())
            .filter(Boolean);

        if (!parts.length) return;

        const unique = Array.from(new Set(parts));
        map[s.subject_code] = unique.join('/');
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

    const wordToNum = {
        ONE: 1,
        TWO: 2,
        THREE: 3,
        FOUR: 4,
        FIVE: 5,
        SIX: 6,
        SEVEN: 7,
    };

    if (wordToNum[upper] && map[wordToNum[upper]]) {
        return map[wordToNum[upper]];
    }

    const wordMatch = upper.match(/\b(ONE|TWO|THREE|FOUR|FIVE|SIX|SEVEN)\b/);
    if (wordMatch) {
        const tokenNum = wordToNum[wordMatch[1]];
        if (tokenNum && map[tokenNum]) {
            return map[tokenNum];
        }
    }

    if (/^[IVXLCDM]+$/.test(upper)) {
        return upper;
    }

    const match = upper.match(/\b(?:FORM|CLASS|STD|STANDARD)\s*(ONE|TWO|THREE|FOUR|FIVE|SIX|SEVEN|[IVXLCDM]+|\d+)\b/);
    if (match) {
        const token = match[1];
        const wordNum = wordToNum[token];
        if (wordNum && map[wordNum]) {
            return map[wordNum];
        }

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
        const baseClassA = a?.parent_class_id
            ? (all.find((c) => Number(c?.id) === Number(a.parent_class_id)) || a)
            : a;
        const baseClassB = b?.parent_class_id
            ? (all.find((c) => Number(c?.id) === Number(b.parent_class_id)) || b)
            : b;
        const formA = getFormOrder(baseClassA);
        const formB = getFormOrder(baseClassB);
        if (formA !== formB) return Number(formA || 0) - Number(formB || 0);

        const baseA = a?.parent_class_id ? Number(a.parent_class_id) : Number(a?.id || 0);
        const baseB = b?.parent_class_id ? Number(b.parent_class_id) : Number(b?.id || 0);
        if (baseA !== baseB) return baseA - baseB;

        const streamA = (a?.stream ? String(a.stream) : '').toUpperCase();
        const streamB = (b?.stream ? String(b.stream) : '').toUpperCase();
        const streamCmp = streamA.localeCompare(streamB);
        if (streamCmp !== 0) return streamCmp;

        return (Number(a?.id || 0) - Number(b?.id || 0));
    });
});

const classLabels = computed(() => {
    // Preserve the same ordering used by `timetableClasses` (base class id asc, then stream).
    // Do not alphabetically re-sort, because the user expects DB order (e.g. ids 1..4).
    const seen = new Set();
    const out = [];

    (timetableClasses.value || []).forEach((c) => {
        const label = getClassLabel(c);
        if (!label) return;
        const key = String(label);
        if (seen.has(key)) return;
        seen.add(key);
        out.push(label);
    });

    return out;
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

const getGroupedRows = (day) => {
    const d = String(day || '').toUpperCase();
    const allRows = Array.isArray(schedule.value?.[d]) ? schedule.value[d] : [];

    const filtered = allRows
        .map((row, originalIndex) => ({ row, originalIndex }))
        .filter(({ row }) => {
            if (!row) return false;

            if (selectedClass.value !== 'ALL') {
                const label = String(row.class_label || row.form || '').trim();
                if (label !== selectedClass.value) return false;
            }

            if (selectedStream.value !== 'ALL') {
                const s = String(row.stream || '').toUpperCase().trim();
                if (s !== String(selectedStream.value || '').toUpperCase().trim()) return false;
            }

            return true;
        });

    const groups = new Map();
    filtered.forEach(({ row, originalIndex }) => {
        const label = String(row.class_label || row.form || '').trim() || 'CLASS';
        const key = label;
        if (!groups.has(key)) {
            groups.set(key, { key, label, rows: [] });
        }
        groups.get(key).rows.push({ row, originalIndex });
    });

    const out = Array.from(groups.values());
    out.forEach((g) => {
        g.rows.sort((a, b) => String(a.row?.stream || '').localeCompare(String(b.row?.stream || '')));
    });
    return out;
};

const getGroupedRowCount = (day) => {
    return getGroupedRows(day).reduce((sum, g) => sum + (g?.rows?.length || 0), 0);
};

// schedule[day][formIndex][slotIndex] = { subject, teacher }
const schedule = ref({});

const initEmptySchedule = () => {
    const next = {};
    days.forEach((day) => {
        next[day] = [];
        (timetableClasses.value || []).forEach((c) => {
            next[day].push({
                form: getClassForm(c),
                class_label: getClassLabel(c),
                stream: c?.stream ? String(c.stream).toUpperCase().trim() : '',
                school_class_id: c?.id ? Number(c.id) : null,
                class_name: c?.name || `${getClassLabel(c)} ${c?.stream ? String(c.stream).toUpperCase().trim() : ''}`.trim(),
                slots: Array.from({ length: 9 }).map(() => null),
            });
        });
    });
    schedule.value = next;
};

watch(
    () => timetableClasses.value,
    (val) => {
        if (!val || !val.length) return;
        const current = schedule.value;
        if (current && typeof current === 'object' && Object.keys(current).length) return;
        initEmptySchedule();
    },
    { immediate: true }
);

const generationWarnings = ref([]);
const generationNonce = ref(0);

const showResolveModal = ref(false);
const resolveRunning = ref(false);
const resolveStatusByWarning = ref({});
const resolveMaxAttempts = 12;
const resolveAllStatus = ref({ status: 'idle', attempts: 0, message: '' });

const sessionRulesStorageKey = computed(() => {
    const schoolId = props.school?.id ?? 'school';
    return `timetable_subject_session_rules_v1_${schoolId}`;
});

const loadSessionRulesFromStorage = () => {
    try {
        const raw = window.localStorage.getItem(sessionRulesStorageKey.value);
        if (!raw) return {};
        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch {
        return {};
    }
};

const saveSessionRulesToStorage = () => {
    try {
        window.localStorage.setItem(sessionRulesStorageKey.value, JSON.stringify(sessionRulesByClassId.value || {}));
    } catch {
        // ignore storage failures
    }
};

const getSlotSession = (slotIndex) => {
    if (slotIndex >= 0 && slotIndex <= 3) return 'morning';
    if (slotIndex >= 7 && slotIndex <= 8) return 'afternoon';
    return 'other';
};

const getSubjectSessionRule = (schoolClassId, subjectId) => {
    if (!schoolClassId || !subjectId) return 'any';
    const classMap = sessionRulesByClassId.value?.[String(schoolClassId)] || {};
    const val = classMap?.[String(subjectId)];
    return val === 'morning' || val === 'afternoon' ? val : 'any';
};

const isSubjectAllowedInSlot = (schoolClassId, subjectCode, slotIndex) => {
    const forcedMorning = new Set(['ENG', 'B/MAT']);
    const code = String(subjectCode || '').trim().toUpperCase();
    const slotSession = getSlotSession(slotIndex);
    if (forcedMorning.has(code)) {
        // Prefer morning for core subjects, but allow the first period after break (slot 4)
        // to support the pattern shown in the school's timetable.
        if (slotIndex === 4) return true;
        return slotSession === 'morning';
    }

    const subjectId = subjectIdByCode.value?.[String(subjectCode)] || null;
    const rule = getSubjectSessionRule(schoolClassId, subjectId);
    if (rule === 'any') return true;
    return slotSession === rule;
};

const isTeachableLessonSlot = (day, slotIndex) => {
    // Afternoon slots are fixed activities on Wed/Thu/Fri
    if (slotIndex >= 7 && slotIndex <= 8) {
        return !['WEDNESDAY', 'THURSDAY', 'FRIDAY'].includes(day);
    }

    // Friday last mid slot is MEWAKA (fixed)
    if (day === 'FRIDAY' && slotIndex === 6) {
        return false;
    }

    return true;
};

const isDraggableSlot = (day, slotIndex) => {
    const d = String(day || '').toUpperCase();
    const s = Number(slotIndex);
    if (!Number.isFinite(s)) return false;
    if (s < 0 || s > 8) return false;
    return isTeachableLessonSlot(d, s);
};

const sessionCapacityForClass = () => {
    let morningSlots = 0;
    let afternoonSlots = 0;
    let morningDoubleStarts = 0;
    let afternoonDoubleStarts = 0;

    days.forEach((day) => {
        for (let i = 0; i < 9; i += 1) {
            if (!isTeachableLessonSlot(day, i)) continue;
            const session = getSlotSession(i);
            if (session === 'morning') morningSlots += 1;
            if (session === 'afternoon') afternoonSlots += 1;
        }

        for (let s = 0; s <= 2; s += 1) {
            if (isTeachableLessonSlot(day, s) && isTeachableLessonSlot(day, s + 1)) {
                morningDoubleStarts += 1;
            }
        }

        if (isTeachableLessonSlot(day, 7) && isTeachableLessonSlot(day, 8)) {
            afternoonDoubleStarts += 1;
        }
    });

    return {
        morningSlots,
        afternoonSlots,
        morningDoubleStarts,
        afternoonDoubleStarts,
    };
};

const scheduleStorageKey = computed(() => {
    const schoolId = props.school?.id ?? 'school';
    return `timetable_schedule_v3_${schoolId}`;
});

const normalizeSchedule = (rawSchedule) => {
    if (!rawSchedule || typeof rawSchedule !== 'object') return {};
    const out = {};
    Object.keys(rawSchedule).forEach((day) => {
        const rows = Array.isArray(rawSchedule[day]) ? rawSchedule[day] : [];
        out[day] = rows.map((row) => {
            const slots = Array.isArray(row?.slots) ? row.slots : [];
            const normalizedSlots = Array.from({ length: 9 }).map((_, i) => {
                const slot = slots[i];
                if (slot === null || slot === undefined) return null;

                if (typeof slot === 'string') {
                    const s = String(slot || '').trim();
                    if (!s) return { subject: '', teacher: '', teacher_initials: '', teacher_name: '' };
                    return { subject: s, teacher: '', teacher_initials: '', teacher_name: '' };
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
};

const loadScheduleFromStorage = () => {
    try {
        const raw = window.localStorage.getItem(scheduleStorageKey.value);
        if (!raw) return null;
        const parsed = JSON.parse(raw);
        if (!parsed || typeof parsed !== 'object') return null;
        return normalizeSchedule(parsed);
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

const clearStoredSchedule = () => {
    try {
        window.localStorage.removeItem(scheduleStorageKey.value);
    } catch {
        // ignore storage failures
    }
};

const generateSampleTimetable = () => {
    generationWarnings.value = [];
    const next = {};
    days.forEach((day) => {
        next[day] = [];
    });

    const fallbackSubjects = subjectPool.value || [];

    const teacherBusyByDaySlot = {};
    days.forEach((day) => {
        teacherBusyByDaySlot[day] = Array.from({ length: 9 }).map(() => new Set());
    });

    const addBusyTeacher = (day, slotIndex, teacherVal) => {
        const d = String(day || '').toUpperCase();
        const s = Number(slotIndex);
        if (!teacherBusyByDaySlot[d] || !teacherBusyByDaySlot[d][s]) return;
        const parts = String(teacherVal || '')
            .split('/')
            .map((x) => x.trim())
            .filter(Boolean);
        parts.forEach((p) => teacherBusyByDaySlot[d][s].add(p));
    };

    const isTeacherFree = (day, slotIndex, teacherVal) => {
        const d = String(day || '').toUpperCase();
        const s = Number(slotIndex);
        if (!teacherBusyByDaySlot[d] || !teacherBusyByDaySlot[d][s]) return true;
        const parts = String(teacherVal || '')
            .split('/')
            .map((x) => x.trim())
            .filter(Boolean);
        if (!parts.length) return true;
        return parts.every((p) => !teacherBusyByDaySlot[d][s].has(p));
    };

    const normalizeCode = (code) => String(code || '').trim().toUpperCase();
    const canStartDouble = (day, i) => {
        if (i >= 0 && i <= 2) {
            return isTeachableLessonSlot(day, i) && isTeachableLessonSlot(day, i + 1);
        }
        if (i === 7) {
            return isTeachableLessonSlot(day, 7) && isTeachableLessonSlot(day, 8);
        }
        return false;
    };

    const allDoubleStarts = (day) => [0, 1, 2, 7].filter((i) => canStartDouble(day, i));

    // Sync doubles across streams of the same base class (parent_class_id). Keyed by base class id.
    // Structure: syncDoubleRequests[baseId][day][startIndex] = subjectCode
    const syncDoubleRequests = {};

    (timetableClasses.value || []).forEach((c) => {
        const classId = c?.id ? Number(c.id) : null;
        const formLabel = getClassForm(c);
        const classLabel = getClassLabel(c);
        const streamLabel = c?.stream ? String(c.stream).toUpperCase().trim() : '';

        const baseClassId = c?.parent_class_id ? Number(c.parent_class_id) : (classId || null);
        const syncKey = baseClassId ? String(baseClassId) : '';

        const baseClass = c?.parent_class_id
            ? ((Array.isArray(props.classes) ? props.classes : []).find((x) => Number(x?.id) === Number(c.parent_class_id)) || null)
            : null;

        const subjectCodesRaw = Array.isArray(c?.subject_codes) && c.subject_codes.length
            ? c.subject_codes
            : (Array.isArray(baseClass?.subject_codes) && baseClass.subject_codes.length ? baseClass.subject_codes : []);

        // IMPORTANT: do not fall back to all school subjects; only use assigned subject codes for that class.
        const classSubjectCodes = subjectCodesRaw;

        const hasTeacherForSubject = (code) => {
            const k = normalizeCode(code);
            if (!k) return false;
            if (k === 'PS') return true;
            return !!String(teacherInitialsForClassSubject(classId, k) || '').trim();
        };

        const pickFreeTeacherInitial = (day, slotIndex, subjectCode) => {
            const subjectKey = normalizeCode(subjectCode);
            if (!subjectKey) return '';
            if (subjectKey === 'PS') return '';

            const raw = teacherInitialsForClassSubject(classId, subjectKey);
            const parts = String(raw || '')
                .split('/')
                .map((s) => s.trim())
                .filter(Boolean)
                .filter((p) => String(p).trim().toUpperCase() !== subjectKey);

            if (!parts.length) return '';

            const d = String(day || '').toUpperCase();
            const dayIdx = days.indexOf(d);
            const seed = (Math.max(0, dayIdx) * 31) + Number(slotIndex || 0);
            const ordered = parts.slice(seed % parts.length).concat(parts.slice(0, seed % parts.length));

            for (let i = 0; i < ordered.length; i += 1) {
                const candidate = ordered[i];
                if (isTeacherFree(day, slotIndex, candidate)) return candidate;
            }
            return '';
        };

        const codes = (classSubjectCodes || [])
            .map(normalizeCode)
            .filter(Boolean)
            .filter((code) => hasTeacherForSubject(code));
        const uniqueCodes = Array.from(new Set(codes));
        const hasCode = (code) => uniqueCodes.includes(normalizeCode(code));
        const psCode = 'PS';

        // Build empty rows (one per day) first
        const rowByDay = {};
        days.forEach((day) => {
            const row = {
                form: formLabel,
                class_label: classLabel,
                stream: streamLabel,
                school_class_id: classId,
                class_name: c?.name || `${classLabel} ${streamLabel}`.trim(),
                slots: Array.from({ length: 9 }).map(() => null),
            };

            for (let i = 0; i < 9; i += 1) {
                if (!isTeachableLessonSlot(day, i)) {
                    row.slots[i] = { subject: '', teacher: '', teacher_initials: '', teacher_name: '' };
                }
            }

            rowByDay[day] = row;
            next[day].push(row);
        });

        // Quotas (Rule #1)
        const remainingSingles = {};
        const remainingDoubles = {};

        // ENG & B/MAT: 2 doubles + 3 singles (5 periods/week) so they can appear daily
        ['ENG', 'B/MAT'].forEach((core) => {
            if (!hasCode(core)) return;
            remainingSingles[core] = (remainingSingles[core] || 0) + 3;
            remainingDoubles[core] = (remainingDoubles[core] || 0) + 2;
        });

        // Other subjects: 1 double + 1 single
        uniqueCodes.forEach((code) => {
            if (code === 'ENG' || code === 'B/MAT') return;
            remainingSingles[code] = (remainingSingles[code] || 0) + 1;
            remainingDoubles[code] = (remainingDoubles[code] || 0) + 1;
        });

        const weeklyCount = {};
        const incWeekly = (code, delta) => {
            const k = normalizeCode(code);
            weeklyCount[k] = (weeklyCount[k] || 0) + delta;
        };

        const slotSubject = (day, i) => {
            const s = rowByDay?.[day]?.slots?.[i];
            return s?.subject ? normalizeCode(s.subject) : '';
        };

        const subjectIndicesForDay = (day, code) => {
            const key = normalizeCode(code);
            const row = rowByDay?.[day];
            const slots = Array.isArray(row?.slots) ? row.slots : [];
            const out = [];
            for (let ii = 0; ii < slots.length; ii += 1) {
                if (slotSubject(day, ii) === key) out.push(ii);
            }
            return out;
        };

        const canPlaceDoubleHere = (day, i, code) => {
            if (!canStartDouble(day, i)) return false;
            const row = rowByDay[day];
            if (!row) return false;
            if (row.slots[i] !== null) return false;
            if (row.slots[i + 1] !== null) return false;

            // Daily max: a subject can appear at most twice per day (as a double)
            if (normalizeCode(code) !== 'PS' && subjectIndicesForDay(day, code).length > 0) return false;

            if (!isSubjectAllowedInSlot(classId, code, i)) return false;
            if (!isSubjectAllowedInSlot(classId, code, i + 1)) return false;

            // Teacher conflict: ensure a teacher is available for both slots (except PS)
            const subjectKey = normalizeCode(code);
            if (subjectKey !== 'PS') {
                const t1 = pickFreeTeacherInitial(day, i, subjectKey);
                if (!t1) return false;
                // Reserve t1 virtually for the second slot too if needed
                const t2 = pickFreeTeacherInitial(day, i + 1, subjectKey);
                if (!t2) return false;
            }

            return true;
        };

        const canPlaceSingleHere = (day, i, code) => {
            const row = rowByDay[day];
            if (!row) return false;
            if (!isTeachableLessonSlot(day, i)) return false;
            if (row.slots[i] !== null) return false;

            // Daily max: allow a subject twice only when the 2nd one is adjacent (double period)
            const indices = subjectIndicesForDay(day, code);
            const subjectKey = normalizeCode(code);
            const existing = indices.length === 1 ? indices[0] : null;
            const isMakingDouble = existing !== null && Math.abs(existing - i) === 1;
            if (subjectKey !== 'PS') {
                if (indices.length >= 2) return false;
                if (indices.length === 1 && !isMakingDouble) return false;
            }

            if (!isSubjectAllowedInSlot(classId, code, i)) return false;

            // Teacher conflict: must have a free teacher (except PS)
            if (subjectKey !== 'PS') {
                const t = pickFreeTeacherInitial(day, i, subjectKey);
                if (!t) return false;
            }

            const prevCode = i > 0 && ![4, 7].includes(i) ? slotSubject(day, i - 1) : '';
            if (prevCode && prevCode === normalizeCode(code) && !isMakingDouble) return false;
            const nextCode = (i + 1) < 9 && ![3, 6].includes(i) ? slotSubject(day, i + 1) : '';
            if (nextCode && nextCode === normalizeCode(code) && !isMakingDouble) return false;
            return true;
        };

        const placeDouble = (day, i, code) => {
            const row = rowByDay[day];
            const t1 = classId ? pickFreeTeacherInitial(day, i, code) : '';
            const t2 = classId ? pickFreeTeacherInitial(day, i + 1, code) : '';
            row.slots[i] = { subject: code, teacher: t1, teacher_initials: t1, teacher_name: '' };
            row.slots[i + 1] = { subject: code, teacher: t2, teacher_initials: t2, teacher_name: '' };
            addBusyTeacher(day, i, t1);
            addBusyTeacher(day, i + 1, t2);
            incWeekly(code, 2);

            // Record this double so sibling streams can try to follow the same timing.
            if (syncKey) {
                if (!syncDoubleRequests[syncKey]) syncDoubleRequests[syncKey] = {};
                const d = String(day || '').toUpperCase();
                if (!syncDoubleRequests[syncKey][d]) syncDoubleRequests[syncKey][d] = {};
                syncDoubleRequests[syncKey][d][String(i)] = normalizeCode(code);
            }
        };

        const placeSingle = (day, i, code) => {
            const row = rowByDay[day];
            const teacher = classId ? pickFreeTeacherInitial(day, i, code) : '';
            row.slots[i] = code ? { subject: code, teacher, teacher_initials: teacher, teacher_name: '' } : null;
            addBusyTeacher(day, i, teacher);
            incWeekly(code, 1);
        };

        // 1) Place required doubles first
        const pickRandom = (arr) => arr[Math.floor(Math.random() * arr.length)];
        const allPlacements = [];
        days.forEach((day) => {
            allDoubleStarts(day).forEach((i) => allPlacements.push({ day, i }));
        });

        // 1a) Apply already chosen synced doubles (from earlier streams of the same base class)
        if (syncKey && syncDoubleRequests[syncKey]) {
            days.forEach((day) => {
                const d = String(day || '').toUpperCase();
                const starts = syncDoubleRequests[syncKey]?.[d] || {};
                Object.keys(starts).forEach((startIdx) => {
                    const i = Number(startIdx);
                    const code = starts[startIdx];
                    if (!code) return;
                    if ((remainingDoubles[code] || 0) <= 0) return;
                    if (!canPlaceDoubleHere(day, i, code)) return;
                    placeDouble(day, i, code);
                    remainingDoubles[code] -= 1;
                });
            });
        }

        Object.keys(remainingDoubles).forEach((code) => {
            const need = remainingDoubles[code] || 0;
            for (let k = 0; k < need; k += 1) {
                const feasible = allPlacements.filter(({ day, i }) => canPlaceDoubleHere(day, i, code));
                if (!feasible.length) break;
                const { day, i } = pickRandom(feasible);
                placeDouble(day, i, code);
                remainingDoubles[code] -= 1;
            }
        });

        // Daily shaping + core enforcement
        const countDoublesForDay = (day) => {
            let count = 0;
            const starts = allDoubleStarts(day);
            starts.forEach((i) => {
                const a = rowByDay?.[day]?.slots?.[i];
                const b = rowByDay?.[day]?.slots?.[i + 1];
                if (a?.subject && b?.subject && String(a.subject) === String(b.subject)) count += 1;
            });
            return count;
        };

        const dayHasSubject = (day, code) => {
            const k = normalizeCode(code);
            const row = rowByDay?.[day];
            if (!row) return false;
            return (row.slots || []).some((s) => s?.subject && normalizeCode(s.subject) === k);
        };

        const coreCodes = ['ENG', 'B/MAT'].filter((core) => hasCode(core));

        const tryPlaceCoreSingle = (day, core) => {
            // Prefer first period after break (slot 4), then other allowed slots.
            const preferred = [4, 0, 1, 2, 3, 5, 6, 7, 8];
            for (let p = 0; p < preferred.length; p += 1) {
                const i = preferred[p];
                if (!canPlaceSingleHere(day, i, core)) continue;
                placeSingle(day, i, core);
                remainingSingles[core] -= 1;
                return true;
            }
            return false;
        };

        const tryPlaceCoreDouble = (day, core) => {
            const starts = allDoubleStarts(day);
            for (let s = 0; s < starts.length; s += 1) {
                const i = starts[s];
                if (!canPlaceDoubleHere(day, i, core)) continue;
                placeDouble(day, i, core);
                remainingDoubles[core] -= 1;
                return true;
            }
            return false;
        };

        const tryPlaceBalancedDouble = (day) => {
            const placements = allDoubleStarts(day);
            const codesByNeed = Object.keys(remainingDoubles)
                .filter((code) => (remainingDoubles[code] || 0) > 0)
                .sort((a, b) => (weeklyCount[a] || 0) - (weeklyCount[b] || 0));

            for (let p = 0; p < placements.length; p += 1) {
                const i = placements[p];
                for (let cidx = 0; cidx < codesByNeed.length; cidx += 1) {
                    const code = codesByNeed[cidx];
                    if (!canPlaceDoubleHere(day, i, code)) continue;
                    placeDouble(day, i, code);
                    remainingDoubles[code] -= 1;
                    return true;
                }
            }
            return false;
        };

        const totalRemainingDoubles = () => Object.values(remainingDoubles).reduce((sum, v) => sum + (Number(v) || 0), 0);

        const maxDoublesPerDay = 2;
        while (totalRemainingDoubles() > 0) {
            const byFewest = days.slice().sort((a, b) => countDoublesForDay(a) - countDoublesForDay(b));
            let placed = false;

            for (let d = 0; d < byFewest.length; d += 1) {
                const day = byFewest[d];
                if (countDoublesForDay(day) >= maxDoublesPerDay) continue;
                if (tryPlaceBalancedDouble(day)) {
                    placed = true;
                    break;
                }
            }

            if (!placed) break;
        }

        // Strict: ENG and B/MAT must appear every day (when feasible).
        days.forEach((day) => {
            coreCodes.forEach((core) => {
                if (dayHasSubject(day, core)) return;

                if ((remainingDoubles[core] || 0) > 0 && countDoublesForDay(day) < maxDoublesPerDay) {
                    if (tryPlaceCoreDouble(day, core)) return;
                }

                if ((remainingSingles[core] || 0) > 0) {
                    if (tryPlaceCoreSingle(day, core)) return;
                }

                generationWarnings.value.push(`Could not place ${core} for ${classLabel}${streamLabel ? ` ${streamLabel}` : ''} on ${day}.`);
            });
        });

        // Fallback: ensure remaining required doubles/singles are placed when feasible.
        Object.keys(remainingDoubles).forEach((code) => {
            while ((remainingDoubles[code] || 0) > 0) {
                let placed = false;
                for (let d = 0; d < days.length && !placed; d += 1) {
                    const day = days[d];
                    const starts = allDoubleStarts(day);
                    for (let s = 0; s < starts.length; s += 1) {
                        const i = starts[s];
                        if (!canPlaceDoubleHere(day, i, code)) continue;
                        placeDouble(day, i, code);
                        remainingDoubles[code] -= 1;
                        placed = true;
                        break;
                    }
                }
                if (!placed) {
                    generationWarnings.value.push(`Could not place all required double periods for ${code} in ${classLabel}${streamLabel ? ` ${streamLabel}` : ''}.`);
                    break;
                }
            }
        });

        Object.keys(remainingSingles).forEach((code) => {
            while ((remainingSingles[code] || 0) > 0) {
                let placed = false;
                for (let d = 0; d < days.length && !placed; d += 1) {
                    const day = days[d];
                    // For core subjects, prefer slot 4 first.
                    const preferred = (code === 'ENG' || code === 'B/MAT') ? [4, 0, 1, 2, 3, 5, 6, 7, 8] : [0, 1, 2, 3, 4, 5, 6, 7, 8];
                    for (let p = 0; p < preferred.length; p += 1) {
                        const i = preferred[p];
                        if (!canPlaceSingleHere(day, i, code)) continue;
                        placeSingle(day, i, code);
                        remainingSingles[code] -= 1;
                        placed = true;
                        break;
                    }
                }
                if (!placed) {
                    generationWarnings.value.push(`Could not place all required single periods for ${code} in ${classLabel}${streamLabel ? ` ${streamLabel}` : ''}.`);
                    break;
                }
            }
        });

        // 2) Place required singles
        days.forEach((day) => {
            for (let i = 0; i < 9; i += 1) {
                if (!isTeachableLessonSlot(day, i)) continue;
                if (rowByDay[day].slots[i] !== null) continue;

                const candidates = Object.keys(remainingSingles)
                    .filter((code) => (remainingSingles[code] || 0) > 0)
                    .filter((code) => canPlaceSingleHere(day, i, code));

                if (!candidates.length) continue;

                // balance: pick subject with highest remaining singles
                candidates.sort((a, b) => (remainingSingles[b] || 0) - (remainingSingles[a] || 0));
                const code = candidates[0];
                placeSingle(day, i, code);
                remainingSingles[code] -= 1;
            }
        });

        // 3) Fill remaining teachable slots balanced (default to PS if available)
        const pickBalanced = (day, i) => {
            const prevCode = i > 0 && ![4, 7].includes(i) ? slotSubject(day, i - 1) : '';
            const candidates = uniqueCodes
                .filter((code) => canPlaceSingleHere(day, i, code))
                .filter((code) => !prevCode || prevCode !== normalizeCode(code));
            if (!candidates.length) return psCode || '';

            candidates.sort((a, b) => (weeklyCount[a] || 0) - (weeklyCount[b] || 0));
            return candidates[0] || psCode || '';
        };

        days.forEach((day) => {
            for (let i = 0; i < 9; i += 1) {
                if (!isTeachableLessonSlot(day, i)) continue;
                if (rowByDay[day].slots[i] !== null) continue;
                const picked = pickBalanced(day, i);
                if (!picked) continue;
                placeSingle(day, i, picked);
            }
        });
    });

    schedule.value = normalizeSchedule(next);
    saveScheduleToStorage();
};

const assignSubjectToCell = (day, rowIndex, slotIndex, subjectCode) => {
    const d = String(day);
    const r = Number(rowIndex);
    const s = Number(slotIndex);
    const code = String(subjectCode || '').trim();

    if (code) {
        const allowed = new Set((assignedSubjectCodes.value || []).map((c) => String(c).trim().toUpperCase()));
        if (!allowed.has(code.toUpperCase())) {
            dropFeedback.value = { type: 'error', message: 'This subject is not assigned. Use Assign to enable it.' };
            return false;
        }
    }

    if (!isDraggableSlot(d, s)) return false;
    const row = schedule.value?.[d]?.[r];
    if (!row) return false;

    const normalizeDayCode = (v) => String(v || '').trim().toUpperCase();
    const codeKey = normalizeDayCode(code);
    if (codeKey && codeKey !== 'PS') {
        const indices = (Array.isArray(row?.slots) ? row.slots : [])
            .map((cell, idx) => ({ idx, subject: normalizeDayCode(cell?.subject) }))
            .filter(({ subject }) => subject === codeKey)
            .map(({ idx }) => idx)
            .filter((idx) => idx !== s);

        if (indices.length >= 2) {
            dropFeedback.value = { type: 'error', message: 'This subject already appears twice today (max is 2).' };
            return false;
        }


        if (indices.length === 1) {
            const existing = indices[0];
            const adjacent = Math.abs(existing - s) === 1;
            if (!adjacent) {
                dropFeedback.value = { type: 'error', message: 'This subject can only appear twice in a day as a double period (place it next to the first one).' };
                return false;
            }
        }
    }


    const classId = Number(row?.school_class_id || row?.id || row?.class_id || 0) || null;
    if (code && !isSubjectAllowedInSlot(classId, code, s)) {
        dropFeedback.value = { type: 'error', message: 'This subject is not allowed in this period.' };
        return false;
    }
    if (code && wouldCauseTeacherClash(d, s, r, code)) {
        dropFeedback.value = { type: 'error', message: 'Teacher clash detected for this period.' };
        return false;
    }


    dropFeedback.value = { type: '', message: '' };


    const t = code ? pickTeacherInitialForSlot(classId, code, d, s) : '';
    row.slots[s] = code ? { subject: code, teacher: t, teacher_initials: t, teacher_name: '' } : null;
    saveScheduleToStorage();
    return true;
};

const onDragStart = (day, rowIndex, slotIndex) => {
    draggedCell.value = { day, rowIndex, slotIndex };
};

const onSubjectDragStart = (code) => {
    draggedSubjectCode.value = String(code || '').trim();
    draggedCell.value = null;
};

const closeSubjectMenu = () => {
    showSubjectMenu.value = false;
    subjectMenuSearch.value = '';
    subjectMenuTarget.value = { day: '', rowIndex: -1, slotIndex: -1 };
};

const openSubjectMenu = (evt, day, rowIndex, slotIndex) => {
    if (!isDraggableSlot(day, slotIndex)) return;
    evt?.preventDefault?.();

    subjectMenuPos.value = { x: Number(evt?.clientX || 0), y: Number(evt?.clientY || 0) };
    subjectMenuTarget.value = { day: String(day), rowIndex: Number(rowIndex), slotIndex: Number(slotIndex) };
    showSubjectMenu.value = true;

    window.setTimeout(() => {
        const handler = () => {
            closeSubjectMenu();
            document.removeEventListener('click', handler, true);
        };
        document.addEventListener('click', handler, true);
    }, 0);
};

const wouldCauseTeacherClash = (targetDay, targetSlotIndex, targetRowIndex, subjectCode) => {
    const day = String(targetDay);
    const slotIndex = Number(targetSlotIndex);
    const code = String(subjectCode || '').trim();
    if (!code) return false;

    const targetRow = schedule.value?.[day]?.[Number(targetRowIndex)];
    const targetClassId = Number(targetRow?.school_class_id || targetRow?.id || targetRow?.class_id || targetRow?.classId || targetRow?.schoolClassId || 0) || null;
    const picked = pickTeacherInitialForSlot(targetClassId, code, day, slotIndex);
    const parts = String(picked || '').split('/').map((s) => s.trim()).filter(Boolean);
    if (!parts.length) return false;

    const rows = schedule.value?.[day] || [];
    for (let r = 0; r < rows.length; r += 1) {
        if (r === Number(targetRowIndex)) continue;
        const row = rows[r];
        const cell = row?.slots?.[slotIndex];
        const otherCode = cell?.subject ? String(cell.subject) : '';
        if (!otherCode) continue;

        const otherClassId = Number(row?.school_class_id || row?.id || row?.class_id || row?.classId || row?.schoolClassId || 0) || null;
        const otherPicked = pickTeacherInitialForSlot(otherClassId, otherCode, day, slotIndex);
        const otherParts = String(otherPicked || '').split('/').map((s) => s.trim()).filter(Boolean);
        if (!otherParts.length) continue;

        for (let i = 0; i < parts.length; i += 1) {
            if (otherParts.includes(parts[i])) return true;
        }
    }

    return false;
};

const onDrop = (day, rowIndex, slotIndex) => {
    if (draggedSubjectCode.value) {
        const ok = assignSubjectToCell(day, rowIndex, slotIndex, draggedSubjectCode.value);
        draggedSubjectCode.value = '';
        draggedCell.value = null;
        return;
    }

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
    const candidateFrom = toRow.slots[slotIndex];
    const candidateTo = tmp;

    const fromClassId = Number(fromRow?.school_class_id || fromRow?.id || fromRow?.class_id || fromRow?.classId || fromRow?.schoolClassId || 0) || null;
    const toClassId = Number(toRow?.school_class_id || toRow?.id || toRow?.class_id || toRow?.classId || toRow?.schoolClassId || 0) || null;

    const canPlaceA = !candidateFrom?.subject || isSubjectAllowedInSlot(fromClassId, candidateFrom.subject, from.slotIndex);
    const canPlaceB = !candidateTo?.subject || isSubjectAllowedInSlot(toClassId, candidateTo.subject, slotIndex);

    if (!canPlaceA || !canPlaceB) {
        draggedCell.value = null;
        return;
    }

    fromRow.slots[from.slotIndex] = candidateFrom;
    toRow.slots[slotIndex] = candidateTo;

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

                <div v-if="generationWarnings.length" class="mb-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-[10px] text-amber-900">
                    <div class="font-semibold">Limitations check:</div>
                    <div class="mt-1 max-h-24 overflow-y-auto">
                        <div v-for="(w, idx) in generationWarnings" :key="idx">{{ w }}</div>
                    </div>
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

        <!-- Teacher initials modal -->
        <div
            v-if="showTeacherInitialsModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4 py-6 sm:px-0"
            @click.self="showTeacherInitialsModal = false"
        >
            <div
                class="max-h-full w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-6 text-xs text-gray-700 shadow-2xl ring-1 ring-gray-200"
            >
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">
                            Teacher Initials Details
                        </h3>
                        <p class="mt-0.5 text-[11px] text-gray-500">
                            Orodha ya walimu wote na initials zao. Zilizotumika kwenye ratiba zitaonyesha "Used".
                        </p>
                    </div>
                    <button
                        type="button"
                        class="text-[11px] text-gray-500 hover:text-gray-700"
                        @click="showTeacherInitialsModal = false"
                    >
                        Close
                    </button>
                </div>

                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="text-[11px] text-gray-600">
                        Used initials: <span class="font-semibold">{{ usedTeacherInitials.size }}</span>
                        / Total teachers: <span class="font-semibold">{{ teacherInitialsList.length }}</span>
                    </div>
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-emerald-500 focus:ring-offset-1"
                        @click="downloadTeacherInitials"
                    >
                        Download (CSV)
                    </button>
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="min-w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="border-b border-gray-200 px-3 py-2">Initials</th>
                                <th class="border-b border-gray-200 px-3 py-2">Teacher</th>
                                <th class="border-b border-gray-200 px-3 py-2">Used</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!teacherInitialsList.length">
                                <td colspan="3" class="px-3 py-3 text-center text-[11px] text-gray-500">
                                    No teachers found.
                                </td>
                            </tr>
                            <tr v-for="t in teacherInitialsList" :key="t.id" class="hover:bg-gray-50">
                                <td class="border-b border-gray-100 px-3 py-2 font-semibold text-gray-800">
                                    {{ t.initials }}
                                </td>
                                <td class="border-b border-gray-100 px-3 py-2">
                                    {{ t.simple_name || t.name }}
                                </td>
                                <td class="border-b border-gray-100 px-3 py-2">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                        :class="t.used ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700'"
                                    >
                                        {{ t.used ? 'Used' : 'Not used' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Assign subjects modal -->
        <div
            v-if="showAssignSubjectsModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4 py-6 sm:px-0"
            @click.self="showAssignSubjectsModal = false"
        >
            <div class="max-h-full w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-6 text-xs text-gray-700 shadow-2xl ring-1 ring-gray-200">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-semibold text-gray-800">Assign Subjects</div>
                        <div class="text-[11px] text-gray-500">Chagua masomo yatakayopatikana kwenye ratiba (palette).</div>
                    </div>
                    <button
                        type="button"
                        class="text-[11px] text-gray-500 hover:text-gray-700"
                        @click="showAssignSubjectsModal = false"
                    >
                        Close
                    </button>
                </div>

                <div class="mb-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <input
                        v-model="assignSubjectsSearch"
                        type="text"
                        placeholder="Search subject..."
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                    />
                    <div class="flex items-center justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-white px-3 py-2 text-[11px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                            @click="assignedSubjectCodes = allSubjectCodes.slice()"
                        >
                            Select all
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-white px-3 py-2 text-[11px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                            @click="assignedSubjectCodes = []"
                        >
                            Clear
                        </button>
                    </div>
                </div>

                <div class="max-h-[55vh] overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <div v-if="!filteredAssignSubjectCodes.length" class="py-6 text-center text-[11px] text-gray-500">
                        No subjects found.
                    </div>
                    <div v-else class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <label
                            v-for="code in filteredAssignSubjectCodes"
                            :key="code"
                            class="flex items-center gap-2 rounded-md bg-white px-3 py-2 text-[11px] ring-1 ring-gray-200"
                        >
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                :checked="assignedSubjectCodes.includes(code)"
                                @change="(e) => {
                                    if (e.target.checked) {
                                        if (!assignedSubjectCodes.includes(code)) assignedSubjectCodes = assignedSubjectCodes.concat([code]);
                                    } else {
                                        assignedSubjectCodes = assignedSubjectCodes.filter((c) => c !== code);
                                    }
                                }"
                            />
                            <span class="font-semibold text-gray-800">{{ code }}</span>
                        </label>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-gray-100 px-3 py-2 text-[11px] font-medium text-gray-700 hover:bg-gray-200"
                        @click="showAssignSubjectsModal = false"
                    >
                        Done
                    </button>
                </div>
            </div>
        </div>

        <div
            v-if="showSubjectPalette"
            class="fixed inset-0 z-50 pointer-events-none"
        >
            <div class="absolute inset-y-0 right-0 flex w-[340px] max-w-[90vw] pointer-events-auto">
                <div class="flex h-full w-full flex-col overflow-hidden rounded-l-2xl bg-white shadow-2xl ring-1 ring-gray-200">
                    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Subjects</h3>
                            <p class="text-[11px] text-gray-500">Drag and drop into the timetable.</p>
                        </div>
                        <button
                            type="button"
                            class="text-[11px] text-gray-500 hover:text-gray-700"
                            @click="closeSubjectsPopup"
                        >
                            Close
                        </button>
                    </div>

                    <div class="p-4">
                        <input
                            v-model="subjectPaletteSearch"
                            type="text"
                            placeholder="Search subject..."
                            class="mb-2 w-full rounded-md border border-gray-300 px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        />
                        <div class="mb-3 text-[11px] text-gray-500">
                            Filter: <span class="font-semibold">{{ selectedClass }}</span>
                            <span v-if="selectedStream !== 'ALL'"> · Stream <span class="font-semibold">{{ selectedStream }}</span></span>
                        </div>

                        <div class="flex-1 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-2">
                            <div v-if="!filteredSubjectPaletteCodes.length" class="px-2 py-6 text-center text-xs text-gray-500">
                                No subjects found for this class/stream.
                            </div>
                            <div v-else class="flex flex-wrap gap-2">
                                <div
                                    v-for="code in filteredSubjectPaletteCodes"
                                    :key="code"
                                    draggable="true"
                                    class="cursor-grab select-none rounded-md bg-white px-3 py-2 text-xs font-semibold text-gray-800 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50 active:cursor-grabbing"
                                    @dragstart="onSubjectDragStart(code)"
                                >
                                    {{ code }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                            class="rounded-md bg-amber-50 px-2 py-1 text-[10px] font-medium text-amber-800 ring-1 ring-amber-100 hover:bg-amber-100"
                            :disabled="!generationWarnings.length"
                            @click="showResolveModal = true"
                        >
                            Resolve ({{ generationWarnings.length }})
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-medium text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                            @click="regenerateSampleTimetable"
                        >
                            Regenerate Sample Timetable
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-white px-2 py-1 text-[10px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                            @click="openSubjectsPopup"
                        >
                            Subjects
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-white px-2 py-1 text-[10px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                            @click="showAssignSubjectsModal = true"
                        >
                            Assign
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-emerald-600 px-2 py-1 text-[10px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                            :disabled="form.processing"
                            @click="saveTimetable"
                        >
                            {{ form.processing ? 'Saving...' : 'Save' }}
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-white px-2 py-1 text-[10px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                            @click="showTeacherInitialsModal = true"
                        >
                            Teacher Initials Details
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-white px-2 py-1 text-[10px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                            @click="printTimetable"
                        >
                            Print
                        </button>
                    </div>
                </div>

                <div v-if="dropFeedback.message" class="mb-3 rounded-md border px-3 py-2 text-[11px]"
                    :class="dropFeedback.type === 'error' ? 'border-red-200 bg-red-50 text-red-900' : 'border-emerald-200 bg-emerald-50 text-emerald-900'"
                >
                    {{ dropFeedback.message }}
                </div>

                <div v-if="generationWarnings.length" class="mb-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-[10px] text-amber-900">
                    <div class="font-semibold">Limitations check:</div>
                    <div class="mt-1 max-h-24 overflow-y-auto">
                        <div v-for="(w, idx) in generationWarnings" :key="idx">{{ w }}</div>
                    </div>
                </div>

                <div
                    id="class-timetable-preview"
                    :key="generationNonce"
                    class="overflow-hidden rounded-lg border border-gray-300 bg-white text-[11px] text-gray-800"
                    @contextmenu.prevent
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
                        <table class="min-w-full border-collapse text-[10px] leading-tight">
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
                                            <template v-if="slot && slot.subject">
                                                <div>
                                                    {{ slot.subject }}
                                                    <span
                                                        v-if="row.slots[index + 1]?.subject && row.slots[index + 1].subject === slot.subject"
                                                        class="ml-1 rounded bg-emerald-200 px-1 text-[8px] font-bold text-emerald-900"
                                                    >
                                                        D
                                                    </span>
                                                </div>
                                                <div class="break-words text-[9px] font-semibold leading-tight text-gray-700">{{ slot.teacher }}</div>
                                            </template>
                                            <template v-else>
                                                <div>&nbsp;</div>
                                            </template>
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
                                                <template v-if="slot && slot.subject">
                                                    <div>
                                                        {{ slot.subject }}
                                                        <span
                                                            v-if="row.slots[4 + index + 1]?.subject && row.slots[4 + index + 1].subject === slot.subject"
                                                            class="ml-1 rounded bg-indigo-200 px-1 text-[8px] font-bold text-indigo-900"
                                                        >
                                                            D
                                                        </span>
                                                    </div>
                                                    <div class="break-words text-[9px] font-semibold leading-tight text-gray-700">{{ slot.teacher }}</div>
                                                </template>
                                                <template v-else>
                                                    <div>&nbsp;</div>
                                                </template>
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
                                                <template v-if="slot && slot.subject">
                                                    <div>
                                                        {{ slot.subject }}
                                                        <span
                                                            v-if="row.slots[7 + index + 1]?.subject && row.slots[7 + index + 1].subject === slot.subject"
                                                            class="ml-1 rounded bg-slate-200 px-1 text-[8px] font-bold text-slate-900"
                                                        >
                                                            D
                                                        </span>
                                                    </div>
                                                    <div class="break-words text-[9px] font-semibold leading-tight text-gray-700">{{ slot.teacher }}</div>
                                                </template>
                                                <template v-else>
                                                    <div class="font-semibold text-gray-400">&nbsp;</div>
                                                </template>
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
                                :disabled="form.processing"
                                @click="saveTimetable"
                            >
                                {{ form.processing ? 'Saving...' : 'Generate & Save Timetable' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Limitations modal -->
            <div
                v-if="false && showLimitationsModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4 py-6 sm:px-0"
                @click.self="showLimitationsModal = false"
            >
                <div
                    class="max-h-full w-full max-w-4xl overflow-y-auto rounded-2xl bg-white p-6 text-xs text-gray-700 shadow-2xl ring-1 ring-gray-200"
                >
                    <div class="sticky top-0 z-10 -mx-6 -mt-6 mb-3 border-b border-gray-200 bg-white/95 px-6 pt-6 pb-3 backdrop-blur">
                        <div class="flex items-center justify-between">
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

                        <div class="mt-3 flex flex-wrap items-end gap-3">
                            <div>
                                <label class="mb-1 block text-[11px] font-medium">Class</label>
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

                            <label class="flex items-center gap-2 text-[11px] text-gray-600">
                                <input
                                    v-model="applyLimitsToAllClasses"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                />
                                Apply to all classes/streams
                            </label>

                            <div v-if="limitsLoading" class="text-[11px] text-gray-500">Loading...</div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table v-if="limitationsMode === 'subject_only'" class="min-w-full border-collapse text-[11px]">
                            <thead>
                                <tr class="bg-gray-50 text-left">
                                    <th class="border-b border-gray-200 px-3 py-2">Subject</th>
                                    <th class="border-b border-gray-200 px-3 py-2">Session</th>
                                    <th class="border-b border-gray-200 px-3 py-2">Morning (Single)</th>
                                    <th class="border-b border-gray-200 px-3 py-2">Morning (Double)</th>
                                    <th class="border-b border-gray-200 px-3 py-2">Afternoon (Single)</th>
                                    <th class="border-b border-gray-200 px-3 py-2">Afternoon (Double)</th>
                                    <th class="border-b border-gray-200 px-3 py-2">Periods / Week</th>
                                    <th class="border-b border-gray-200 px-3 py-2">Double (80 mins)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!classSubjectRows.length">
                                    <td colspan="8" class="px-3 py-3 text-center text-[11px] text-gray-500">
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
                                        <select
                                            class="w-48 rounded-md border border-gray-300 bg-white px-2 py-1 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                            :disabled="!selectedLimitClassId"
                                            :value="selectedLimitClassSessionRules[String(row.subject_id)] || 'any'"
                                            @change="(e) => setSelectedLimitSubjectSessionRule(row.subject_id, e.target.value)"
                                        >
                                            <option value="any">Any time</option>
                                            <option value="morning">Morning only (08:00 - 10:40)</option>
                                            <option value="afternoon">Afternoon only (01:20 - 02:40)</option>
                                        </select>
                                    </td>
                                    <td class="border-b border-gray-100 px-3 py-2">
                                        <input
                                            v-model="subjectMorningSingleById[subjectLimitKey(row.subject_id)]"
                                            type="number"
                                            min="0"
                                            class="w-20 rounded-md border border-gray-300 px-2 py-1 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                            :disabled="!selectedLimitClassId"
                                        />
                                    </td>
                                    <td class="border-b border-gray-100 px-3 py-2">
                                        <input
                                            v-model="subjectMorningDoubleById[subjectLimitKey(row.subject_id)]"
                                            type="number"
                                            min="0"
                                            class="w-20 rounded-md border border-gray-300 px-2 py-1 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                            :disabled="!selectedLimitClassId"
                                        />
                                    </td>
                                    <td class="border-b border-gray-100 px-3 py-2">
                                        <input
                                            v-model="subjectAfternoonSingleById[subjectLimitKey(row.subject_id)]"
                                            type="number"
                                            min="0"
                                            class="w-20 rounded-md border border-gray-300 px-2 py-1 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                            :disabled="!selectedLimitClassId"
                                        />
                                    </td>
                                    <td class="border-b border-gray-100 px-3 py-2">
                                        <input
                                            v-model="subjectAfternoonDoubleById[subjectLimitKey(row.subject_id)]"
                                            type="number"
                                            min="0"
                                            class="w-20 rounded-md border border-gray-300 px-2 py-1 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                            :disabled="!selectedLimitClassId"
                                        />
                                    </td>
                                    <td class="border-b border-gray-100 px-3 py-2">
                                        <input
                                            v-model="subjectLimitsById[subjectLimitKey(row.subject_id)]"
                                            type="number"
                                            min="0"
                                            class="w-24 rounded-md border border-gray-300 px-2 py-1 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                            :disabled="!selectedLimitClassId"
                                        />
                                    </td>
                                    <td class="border-b border-gray-100 px-3 py-2">
                                        <input
                                            v-model="subjectDoubleById[subjectLimitKey(row.subject_id)]"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                            :disabled="!selectedLimitClassId"
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
    @page {
        size: A4 landscape;
        margin: 10mm;
    }

    body {
        background: #ffffff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    body * {
        visibility: hidden !important;
    }

    #class-timetable-preview,
    #class-timetable-preview * {
        visibility: visible !important;
    }

    #class-timetable-preview {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        overflow: visible !important;
        border: none !important;
        font-size: 11pt !important;
        line-height: 1.15 !important;
    }

    #class-timetable-preview table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    #class-timetable-preview th,
    #class-timetable-preview td {
        padding: 2px 3px !important;
    }

    #class-timetable-preview tr {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    #class-timetable-preview .overflow-x-auto,
    #class-timetable-preview .overflow-hidden {
        overflow: visible !important;
    }
}
</style>