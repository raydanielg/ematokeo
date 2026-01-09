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

const selectedLimitClassSessionRules = computed(() => {
    const classId = selectedLimitClassId.value ? Number(selectedLimitClassId.value) : null;
    if (!Number.isFinite(classId) || !classId) return {};
    return sessionRulesByClassId.value?.[String(classId)] || {};
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
    const parts = String(raw || '')
        .split('/')
        .map((s) => s.trim())
        .filter(Boolean);

    if (!parts.length) return '';
    if (parts.length === 1) return parts[0];

    // Co-teaching: show both teachers when exactly two are assigned
    if (parts.length === 2) return `${parts[0]}/${parts[1]}`;

    const d = String(day || '').toUpperCase();
    const dayIdx = days.indexOf(d);
    const seed = (Math.max(0, dayIdx) * 31) + Number(slotIndex || 0);
    return parts[seed % parts.length];
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

// schedule[day][formIndex][slotIndex] = { subject, teacher }
const schedule = ref({});
const generationWarnings = ref([]);

const showResolveModal = ref(false);
const resolveRunning = ref(false);

const sessionRulesStorageKey = computed(() => {
    const schoolId = props.school?.id ?? 'school';
    return `timetable_subject_session_rules_v1_${schoolId}`;
});

const sessionRulesByClassId = ref({});

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
    const forcedMorning = new Set(['ENG', 'B/MAT', 'CS']);
    const code = String(subjectCode || '').trim().toUpperCase();
    const slotSession = getSlotSession(slotIndex);
    if (forcedMorning.has(code)) {
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

const scheduleStorageKey = computed(() => {
    const schoolId = props.school?.id ?? 'school';
    return `timetable_schedule_v3_${schoolId}`;
});

const limitationsDraftStorageKey = computed(() => {
    const schoolId = props.school?.id ?? 'school';
    return `timetable_limitations_draft_v1_${schoolId}`;
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

const loadLimitationsDraftFromStorage = () => {
    try {
        const raw = window.localStorage.getItem(limitationsDraftStorageKey.value);
        if (!raw) return null;
        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === 'object' ? parsed : null;
    } catch {
        return null;
    }
};

const saveLimitationsDraftToStorage = () => {
    try {
        window.localStorage.setItem(limitationsDraftStorageKey.value, JSON.stringify({
            mode: limitationsMode.value,
            selected_class_id: selectedLimitClassId.value,
            limits_by_key: limitsByKey.value || {},
            subject_limits_by_id: subjectLimitsById.value || {},
            subject_double_by_id: subjectDoubleById.value || {},
            subject_morning_single_by_id: subjectMorningSingleById.value || {},
            subject_morning_double_by_id: subjectMorningDoubleById.value || {},
            subject_afternoon_single_by_id: subjectAfternoonSingleById.value || {},
            subject_afternoon_double_by_id: subjectAfternoonDoubleById.value || {},
        }));
    } catch {
        // ignore storage failures
    }
};

// Function to validate double periods
const validateDoublePeriods = () => {
    const doubleCounts = {};
    
    days.forEach((day) => {
        const rows = schedule.value[day] || [];
        rows.forEach((row) => {
            for (let i = 0; i < 8; i += 1) {
                if (!canBeDoubleStart(i)) continue;
                const current = row.slots[i];
                const next = row.slots[i + 1];
                
                if (current?.subject && next?.subject && 
                    String(current.subject) === String(next.subject)) {
                    const code = String(current.subject);
                    const classId = row.school_class_id;
                    const key = `${classId}_${code}`;
                    
                    doubleCounts[key] = (doubleCounts[key] || 0) + 1;
                }
            }
        });
    });
    
    // Check if ENG and B/MAT have 2 doubles
    Object.keys(doubleCounts).forEach((key) => {
        const [classId, code] = key.split('_');
        const subjectUpper = String(code).toUpperCase();
        
        if (subjectUpper === 'ENG' || subjectUpper === 'B/MAT') {
            if (doubleCounts[key] < 2) {
                console.warn(`${code} for class ${classId} has only ${doubleCounts[key]} double(s), expected 2`);
            }
        } else {
            if (doubleCounts[key] < 1) {
                console.warn(`${code} for class ${classId} has no double period, expected at least 1`);
            }
        }
    });
    
    return doubleCounts;
};

const generateSampleTimetable = () => {
    const next = {};
    generationWarnings.value = [];

    const codes = subjectPool.value;
    const teachers = teacherBySubject.value;

    if (!codes.length) {
        schedule.value = {};
        return;
    }

    days.forEach((day) => {
        next[day] = [];
    });

    // Track teacher usage per day+slot to prevent one teacher being scheduled in two classes at the same time.
    const teacherBusy = {};
    days.forEach((day) => {
        teacherBusy[day] = Array.from({ length: 9 }).map(() => new Set());
    });

    // Track overall teacher workload distribution across the week.
    // Goal: prefer keeping at least one free day per teacher and prefer consecutive blocks.
    const teacherDaysUsedOverall = {};
    const teacherLastPlacement = {};

    const splitTeacherInitials = (raw) => {
        return String(raw || '')
            .split('/')
            .map((s) => s.trim())
            .filter(Boolean);
    };

    const ensureTeacherDaySet = (initial) => {
        const k = String(initial || '').trim();
        if (!k) return null;
        if (!teacherDaysUsedOverall[k]) teacherDaysUsedOverall[k] = new Set();
        return teacherDaysUsedOverall[k];
    };

    const teacherScore = (initial, day, slotIndex, baseClassId) => {
        const k = String(initial || '').trim();
        if (!k) return -999999;

        const daysSet = ensureTeacherDaySet(k) || new Set();
        const daysCount = daysSet.size;
        const last = teacherLastPlacement[k] || null;

        // Prefer fewer distinct days (try to keep at least 1 free day)
        // Heavy penalty once teacher already teaches 5 days.
        let score = 0;
        score -= (daysCount * 10);
        if (daysCount >= 5) score -= 50;

        // Prefer continuing on same day
        if (last && last.day === String(day)) score += 8;

        // Prefer adjacent slot progression (teacher stays in flow)
        if (last && last.day === String(day)) {
            const diff = Math.abs(Number(slotIndex) - Number(last.slotIndex));
            if (diff === 1) score += 10;
            else if (diff === 2) score += 4;
        }

        // Prefer staying within the same base class group (streams A/B/C of same Form)
        if (last && baseClassId && last.baseClassId && Number(last.baseClassId) === Number(baseClassId)) {
            score += 4;
        }

        return score;
    };

    const chooseTeacherForSlot = (schoolClassId, subjectCode, day, slotIndex) => {
        const raw = teacherInitialsForClassSubject(schoolClassId, subjectCode);
        const parts = splitTeacherInitials(raw);

        const cls = classById.value?.[String(schoolClassId)] || null;
        const baseClassId = cls?.parent_class_id ? Number(cls.parent_class_id) : Number(schoolClassId || 0);

        // Strict mode: if this class has assignments but none found for this subject, treat as no-teacher (blocked).
        if (classHasTeacherAssignments(schoolClassId) && !parts.length) {
            return { ok: false, teacher: '', busyList: [] };
        }

        // No assigned teacher: allow placement, but cell will be blank.
        if (!parts.length) {
            return { ok: true, teacher: '', busyList: [] };
        }

        const busySet = teacherBusy[String(day)]?.[Number(slotIndex)] || new Set();

        // Exactly two teachers (co-teaching): both must be free.
        if (parts.length === 2) {
            const a = parts[0];
            const b = parts[1];
            if (busySet.has(a) || busySet.has(b)) return { ok: false, teacher: '', busyList: [] };
            // Prefer co-teaching pair that keeps workload low (use min score).
            const score = Math.min(teacherScore(a, day, slotIndex, baseClassId), teacherScore(b, day, slotIndex, baseClassId));
            return { ok: true, teacher: `${a}/${b}`, busyList: [a, b] };
        }

        // One teacher: must be free.
        if (parts.length === 1) {
            const a = parts[0];
            if (busySet.has(a)) return { ok: false, teacher: '', busyList: [] };
            return { ok: true, teacher: a, busyList: [a] };
        }

        // More than 2 teachers: pick a free teacher with best workload score.
        let best = null;
        let bestScore = -999999;
        for (let i = 0; i < parts.length; i += 1) {
            const candidate = parts[i];
            if (!candidate) continue;
            if (busySet.has(candidate)) continue;
            const sc = teacherScore(candidate, day, slotIndex, baseClassId);
            if (sc > bestScore) {
                bestScore = sc;
                best = candidate;
            }
        }

        if (best) {
            return { ok: true, teacher: best, busyList: [best] };
        }

        return { ok: false, teacher: '', busyList: [] };
    };

    // Build one row per actual class from the database so that
    // only existing streams/levels are shown in the timetable.
    timetableClasses.value.forEach((c) => {
        const classId = c?.id ? Number(c.id) : null;
        const formLabel = getClassForm(c);
        const classLabel = getClassLabel(c);
        const streamLabel = c.stream ? String(c.stream).toUpperCase().trim() : '';

        const classSubjectCodes = Array.isArray(c.subject_codes) && c.subject_codes.length
            ? c.subject_codes
            : codes;

        const rowByDay = {};
        days.forEach((day) => {
            rowByDay[day] = {
                form: formLabel,
                class_label: classLabel,
                stream: streamLabel,
                school_class_id: classId,
                class_name: c.name || `${classLabel} ${streamLabel}`.trim(),
                slots: Array.from({ length: 9 }).map(() => null),
            };
        });

        // Pre-fill non-teachable fixed slots with blanks so they don't count toward teacher coverage.
        days.forEach((day) => {
            for (let i = 0; i < 9; i += 1) {
                if (!isTeachableLessonSlot(day, i)) {
                    rowByDay[day].slots[i] = { subject: '', teacher: '', teacher_initials: '', teacher_name: '' };
                }
            }
        });

        // Full regenerate: start from empty lesson slots and fill them fresh

        // Build lists of available teachable positions by session.
        const morningPositions = [];
        const afternoonPositions = [];
        const otherPositions = [];

        days.forEach((day) => {
            for (let i = 0; i < 9; i += 1) {
                if (!isTeachableLessonSlot(day, i)) continue;
                const session = getSlotSession(i);
                const pos = { day, slotIndex: i };
                if (session === 'morning') morningPositions.push(pos);
                else if (session === 'afternoon') afternoonPositions.push(pos);
                else otherPositions.push(pos);
            }
        });

        // Simple shuffle for variation.
        const shuffle = (arr) => arr.sort(() => Math.random() - 0.5);
        shuffle(morningPositions);
        shuffle(afternoonPositions);
        shuffle(otherPositions);

        const parseInitials = (raw) => {
            const str = (raw || '').toString().trim();
            if (!str) return [];
            return str
                .split('/')
                .map((s) => s.trim())
                .filter(Boolean);
        };

        const desiredPeriodsByCode = {};
        const desiredDoubleByCode = {};
        const sessionQuotaByCode = {};
        const requiredDoubleCountByCode = {};
        const maxDoubleCountByCode = {};
        const appliesSubjectLimits = limitationsMode.value === 'subject_only'
            && selectedLimitClassId.value
            && (
                String(classId) === String(selectedLimitClassId.value)
                || String(c?.parent_class_id || '') === String(selectedLimitClassId.value)
            );

        if (appliesSubjectLimits) {
            const baseId = selectedLimitClassId.value ? Number(selectedLimitClassId.value) : null;
            const baseClass = baseId ? (classById.value?.[String(baseId)] || null) : null;
            const formNumber = getFormOrder(baseClass);
            const defaultsByCode = defaultSubjectPeriodsByFormGroup(formNumber);

            classSubjectCodes.forEach((code) => {
                const sid = subjectIdByCode.value?.[String(code)] || null;
                if (!sid) return;
                const key = subjectLimitKey(sid);
                const def = defaultsByCode?.[String(code)] ?? null;
                if (Number.isFinite(Number(def)) && Number(def) > 0) {
                    // HARD-CODE: use the default table when it contains this subject
                    desiredPeriodsByCode[String(code)] = Math.floor(Number(def));
                } else {
                    const val = subjectLimitsById.value?.[key];
                    const n = val === '' || val === null || val === undefined ? null : Number(val);
                    if (Number.isFinite(n) && n > 0) {
                        desiredPeriodsByCode[String(code)] = Math.floor(n);
                    }
                }

                if (subjectDoubleById.value?.[key]) {
                    desiredDoubleByCode[String(code)] = true;
                }

                const ms = Number(subjectMorningSingleById.value?.[key] ?? 0) || 0;
                const md = Number(subjectMorningDoubleById.value?.[key] ?? 0) || 0;
                const as = Number(subjectAfternoonSingleById.value?.[key] ?? 0) || 0;
                const ad = Number(subjectAfternoonDoubleById.value?.[key] ?? 0) || 0;

                if (ms > 0 || md > 0 || as > 0 || ad > 0) {
                    sessionQuotaByCode[String(code)] = {
                        morning_single: ms,
                        morning_double: md,
                        afternoon_single: as,
                        afternoon_double: ad,
                    };

                    // Ensure overall weekly quota exists when session quotas are used
                    const derived = (ms + as) + (2 * md) + (2 * ad);
                    if (!desiredPeriodsByCode[String(code)] || desiredPeriodsByCode[String(code)] < derived) {
                        desiredPeriodsByCode[String(code)] = derived;
                    }
                }

                // RULE 1: ENG na B/MAT - double 2, single 1 (periods = 5)
                // RULE 2: Other subjects - double 1, single 1 (periods = 3)
                const eff = Number(desiredPeriodsByCode[String(code)] || 0);
                const subjectUpper = String(code).toUpperCase();
                
                if (subjectUpper === 'ENG' || subjectUpper === 'B/MAT') {
                    // ENG na B/MAT: periods = 5, double 2, single 1
                    if (eff >= 5) {
                        requiredDoubleCountByCode[String(code)] = 2;
                        maxDoubleCountByCode[String(code)] = 2;
                        // Ensure single period remains
                        if (!sessionQuotaByCode[String(code)]) {
                            sessionQuotaByCode[String(code)] = {
                                morning_single: 1,
                                morning_double: 0,
                                afternoon_single: 0,
                                afternoon_double: 0,
                            };
                        }
                    }
                } else {
                    // Other subjects: double 1, single 1 (periods = 3)
                    if (eff >= 3) {
                        requiredDoubleCountByCode[String(code)] = 1;
                        maxDoubleCountByCode[String(code)] = 1;
                        // Ensure single period remains
                        if (!sessionQuotaByCode[String(code)]) {
                            sessionQuotaByCode[String(code)] = {
                                morning_single: 1,
                                morning_double: 0,
                                afternoon_single: 0,
                                afternoon_double: 0,
                            };
                        }
                    }
                }

                // Auto-enable doubles for subjects with required doubles
                if (requiredDoubleCountByCode[String(code)] > 0) {
                    desiredDoubleByCode[String(code)] = true;
                }
            });
        }

        const remainingNeeded = { ...desiredPeriodsByCode };
        const hasQuotas = Object.keys(remainingNeeded).length > 0;

        // Per-class per-day constraints to reduce repetition:
        // - A subject should appear at most once per day (unless it is a double)
        // - A teacher should teach a class at most once per day (either a single or a double)
        const subjectCountByDay = {};
        const teacherUsedInClassDay = {};
        days.forEach((day) => {
            subjectCountByDay[day] = {};
            teacherUsedInClassDay[day] = new Set();
        });

        const isPartOfDouble = (day, slotIndex, subject) => {
            if (!subject) return false;
            const leftAllowed = slotIndex > 0 && ![4, 7].includes(slotIndex);
            const rightAllowed = slotIndex < 8 && ![3, 6].includes(slotIndex);

            const left = leftAllowed ? rowByDay[day].slots[slotIndex - 1] : null;
            const right = rightAllowed ? rowByDay[day].slots[slotIndex + 1] : null;
            const leftSubject = left?.subject ? String(left.subject) : '';
            const rightSubject = right?.subject ? String(right.subject) : '';
            const code = String(subject);

            return (leftSubject && leftSubject === code) || (rightSubject && rightSubject === code);
        };

        const clearSlot = (day, slotIndex) => {
            const existing = rowByDay[day]?.slots?.[slotIndex];
            const busy = existing?._busy;
            const busySet = teacherBusy[String(day)]?.[Number(slotIndex)];
            if (busySet && Array.isArray(busy)) {
                busy.forEach((t) => busySet.delete(t));
            }

            // Remove day from overall set only if teacher has no other lessons that day (safe but expensive); skip removal.
            // We keep teacherDaysUsedOverall as monotonic within one generation pass.

            const teacherDaySet = teacherUsedInClassDay[String(day)];
            if (teacherDaySet && Array.isArray(busy)) {
                busy.forEach((t) => teacherDaySet.delete(t));
            }

            const subject = existing?.subject ? String(existing.subject) : '';
            if (subject) {
                const counts = subjectCountByDay[String(day)] || {};
                if (counts[subject]) {
                    counts[subject] = Math.max(0, Number(counts[subject]) - 1);
                    if (counts[subject] <= 0) delete counts[subject];
                }
            }
            rowByDay[day].slots[slotIndex] = null;
        };

        const setSlot = (day, slotIndex, subject) => {
            if (!subject) return false;

            const subjectCode = String(subject);
            const counts = subjectCountByDay[String(day)] || {};
            const alreadyToday = Number(counts[subjectCode] || 0) > 0;

            const partOfDouble = isPartOfDouble(day, slotIndex, subjectCode);

            // Prevent repeating the same subject multiple times in one day unless it forms a double.
            if (alreadyToday && !partOfDouble) {
                return false;
            }

            const picked = chooseTeacherForSlot(classId, subject, day, slotIndex);
            if (!picked.ok) return false;

            // Prevent the same teacher from appearing multiple times in the same class/day.
            // (Allow blank teacher cells when there is no assignment.)
            const teacherDaySet = teacherUsedInClassDay[String(day)] || new Set();
            if (picked.busyList && picked.busyList.length) {
                for (let i = 0; i < picked.busyList.length; i += 1) {
                    if (!teacherDaySet.has(picked.busyList[i])) continue;

                    // Allow the second cell of a double (same subject) to reuse the same teacher(s)
                    // without violating the per-day-per-class teacher rule.
                    if (!partOfDouble) return false;

                    const leftAllowed = slotIndex > 0 && ![4, 7].includes(slotIndex);
                    const rightAllowed = slotIndex < 8 && ![3, 6].includes(slotIndex);
                    const left = leftAllowed ? rowByDay[day].slots[slotIndex - 1] : null;
                    const right = rightAllowed ? rowByDay[day].slots[slotIndex + 1] : null;

                    const neighbor = (left?.subject && String(left.subject) === subjectCode) ? left
                        : ((right?.subject && String(right.subject) === subjectCode) ? right : null);

                    const neighborBusy = Array.isArray(neighbor?._busy) ? neighbor._busy : [];
                    const sameBusy = Array.isArray(picked.busyList)
                        && picked.busyList.length
                        && picked.busyList.length === neighborBusy.length
                        && picked.busyList.every((t) => neighborBusy.includes(t));

                    if (!neighbor || !sameBusy) return false;
                }
            }

            const teacherInitials = picked.teacher || (classHasTeacherAssignments(classId) ? '' : (teachers[subject] || ''));
            const teacherName = teacherInitials;

            rowByDay[day].slots[slotIndex] = {
                subject,
                teacher: teacherInitials,
                teacher_initials: teacherInitials,
                teacher_name: teacherName,
                _busy: picked.busyList || [],
            };

            // Mark teachers as busy for this day+slot.
            const busySet = teacherBusy[String(day)]?.[Number(slotIndex)];
            if (busySet && picked.busyList && picked.busyList.length) {
                picked.busyList.forEach((t) => busySet.add(t));
            }

            // Update overall workload tracking
            if (picked.busyList && picked.busyList.length) {
                picked.busyList.forEach((t) => {
                    const ds = ensureTeacherDaySet(t);
                    if (ds) ds.add(String(day));
                    teacherLastPlacement[String(t)] = {
                        day: String(day),
                        slotIndex: Number(slotIndex),
                        baseClassId: c?.parent_class_id ? Number(c.parent_class_id) : Number(classId || 0),
                    };
                });
            }

            if (teacherDaySet && picked.busyList && picked.busyList.length) {
                picked.busyList.forEach((t) => teacherDaySet.add(t));
            }

            counts[subjectCode] = Number(counts[subjectCode] || 0) + 1;
            subjectCountByDay[String(day)] = counts;
            teacherUsedInClassDay[String(day)] = teacherDaySet;

            return true;
        };

        const isEmptyTeachableSlot = (day, slotIndex) => {
            if (!isTeachableLessonSlot(day, slotIndex)) return false;
            const v = rowByDay[day].slots[slotIndex];
            return v === null;
        };

        const canBeDoubleStartInSession = (slotIndex, session) => {
            if (session === 'morning') return slotIndex >= 0 && slotIndex <= 2; // 0-1,1-2,2-3
            if (session === 'afternoon') return slotIndex === 7; // 7-8
            return false;
        };

        // Track how many doubles were successfully placed for each subject.
        const placedDoublesByCode = {};
        const bumpDoubleCount = (code) => {
            const k = String(code);
            placedDoublesByCode[k] = Number(placedDoublesByCode[k] || 0) + 1;
        };

        const tryPlaceDoubleInSession = (code, session) => {
            const candidates = [];
            const startSlots = session === 'morning' ? [0, 1, 2] : [7];

            for (let d = 0; d < days.length; d += 1) {
                const day = days[d];
                for (let s = 0; s < startSlots.length; s += 1) {
                    const i = startSlots[s];
                    if (!canBeDoubleStartInSession(i, session)) continue;
                    if (!isEmptyTeachableSlot(day, i) || !isEmptyTeachableSlot(day, i + 1)) continue;
                    if (classId && (!isSubjectAllowedInSlot(classId, code, i) || !isSubjectAllowedInSlot(classId, code, i + 1))) continue;
                    if (getSlotSession(i) !== session || getSlotSession(i + 1) !== session) continue;
                    candidates.push({ day, slotIndex: i });
                }
            }

            shuffle(candidates);

            for (let k = 0; k < candidates.length; k += 1) {
                const pos = candidates[k];
                const day = pos.day;
                const i = pos.slotIndex;

                if (!setSlot(day, i, code)) continue;
                if (!setSlot(day, i + 1, code)) {
                    clearSlot(day, i);
                    continue;
                }

                remainingNeeded[String(code)] = Math.max(0, (remainingNeeded[String(code)] || 0) - 2);
                if (remainingNeeded[String(code)] <= 0) delete remainingNeeded[String(code)];
                return true;
            }

            return false;
        };

        const tryPlaceSingleInSession = (code, session) => {
            for (let d = 0; d < days.length; d += 1) {
                const day = days[d];
                for (let i = 0; i < 9; i += 1) {
                    if (getSlotSession(i) !== session) continue;
                    if (!isEmptyTeachableSlot(day, i)) continue;
                    if (classId && !isSubjectAllowedInSlot(classId, code, i)) continue;
                    if (!setSlot(day, i, code)) continue;
                    remainingNeeded[String(code)] = Math.max(0, (remainingNeeded[String(code)] || 0) - 1);
                    if (remainingNeeded[String(code)] <= 0) delete remainingNeeded[String(code)];
                    return true;
                }
            }
            return false;
        };

        // 0) If session quotas are specified, force-place them first.
        if (hasQuotas && Object.keys(sessionQuotaByCode).length > 0) {
            Object.keys(sessionQuotaByCode).forEach((code) => {
                const q = sessionQuotaByCode[code];
                if (!q) return;

                // Place morning doubles first (for ENG and B/MAT)
                for (let k = 0; k < (q.morning_double || 0); k += 1) {
                    const ok = tryPlaceDoubleInSession(code, 'morning');
                    if (!ok) {
                        // Try afternoon as fallback for doubles
                        const ok2 = tryPlaceDoubleInSession(code, 'afternoon');
                        if (!ok2) break;
                    }
                    bumpDoubleCount(code);
                }
                
                // Place morning singles
                for (let k = 0; k < (q.morning_single || 0); k += 1) {
                    const ok = tryPlaceSingleInSession(code, 'morning');
                    if (!ok) {
                        // Try afternoon as fallback for singles
                        const ok2 = tryPlaceSingleInSession(code, 'afternoon');
                        if (!ok2) break;
                    }
                }
                
                // Place afternoon doubles
                for (let k = 0; k < (q.afternoon_double || 0); k += 1) {
                    const ok = tryPlaceDoubleInSession(code, 'afternoon');
                    if (!ok) break;
                    bumpDoubleCount(code);
                }
                
                // Place afternoon singles
                for (let k = 0; k < (q.afternoon_single || 0); k += 1) {
                    const ok = tryPlaceSingleInSession(code, 'afternoon');
                    if (!ok) break;
                }
            });
        }

        // 0b) Enforce minimum doubles per subject per week (after session quotas, before other filling)
        if (hasQuotas && Object.keys(requiredDoubleCountByCode).length > 0) {
            Object.keys(requiredDoubleCountByCode).forEach((code) => {
                const need = Number(requiredDoubleCountByCode[String(code)] || 0);
                if (need <= 0) return;

                const subjectUpper = String(code).toUpperCase();
                const isEngOrMath = subjectUpper === 'ENG' || subjectUpper === 'B/MAT';
                
                while (Number(placedDoublesByCode[String(code)] || 0) < need && Number(remainingNeeded[String(code)] || 0) >= 2) {
                    // Try morning first for ENG/B/MAT
                    if (isEngOrMath) {
                        const ok = tryPlaceDoubleInSession(code, 'morning');
                        if (!ok) {
                            // Fallback to any session
                            const ok2 = tryPlaceDoubleForCode(code);
                            if (!ok2) break;
                        }
                    } else {
                        // For other subjects, try any session
                        const ok = tryPlaceDoubleForCode(code);
                        if (!ok) break;
                    }
                    bumpDoubleCount(code);
                }
            });
        }

        const canBeDoubleStart = (slotIndex) => {
            // prevent spanning breaks: 3->4 is across break, 6->7 is across break
            return slotIndex >= 0 && slotIndex < 8 && ![3, 6].includes(slotIndex);
        };

        const tryPlaceDoubleForCode = (code) => {
            // need at least 2 remaining to form a double
            if (!remainingNeeded[String(code)] || remainingNeeded[String(code)] < 2) return false;

            const candidates = [];
            
            // Prioritize morning slots for ENG and B/MAT
            const subjectUpper = String(code).toUpperCase();
            const isEngOrMath = subjectUpper === 'ENG' || subjectUpper === 'B/MAT';
            
            for (let d = 0; d < days.length; d += 1) {
                const day = days[d];
                for (let i = 0; i < 8; i += 1) {
                    if (!canBeDoubleStart(i)) continue;
                    if (!isTeachableLessonSlot(day, i) || !isTeachableLessonSlot(day, i + 1)) continue;
                    if (rowByDay[day].slots[i] !== null) continue;
                    if (rowByDay[day].slots[i + 1] !== null) continue;
                    if (classId && (!isSubjectAllowedInSlot(classId, code, i) || !isSubjectAllowedInSlot(classId, code, i + 1))) continue;
                    
                    // Score based on session preference
                    let score = 0;
                    const session = getSlotSession(i);
                    if (isEngOrMath && session === 'morning') {
                        score += 10; // Morning preference for ENG/B/MAT
                    } else if (!isEngOrMath && session === 'morning') {
                        score += 5; // Morning preference for other subjects
                    }
                    
                    candidates.push({ day, slotIndex: i, score });
                }
            }

            // Sort by score descending
            candidates.sort((a, b) => b.score - a.score);
            
            // Try highest scored candidates first
            for (let k = 0; k < candidates.length; k += 1) {
                const pos = candidates[k];
                const day = pos.day;
                const i = pos.slotIndex;

                if (!setSlot(day, i, code)) continue;
                if (!setSlot(day, i + 1, code)) {
                    clearSlot(day, i);
                    continue;
                }

                remainingNeeded[String(code)] -= 2;
                if (remainingNeeded[String(code)] <= 0) delete remainingNeeded[String(code)];
                return true;
            }

            return false;
        };

        if (hasQuotas) {
            // Place double periods first so we can reserve consecutive slots.
            Object.keys(desiredDoubleByCode)
                .filter((code) => desiredDoubleByCode[code])
                .forEach((code) => {
                    const cap = Number(maxDoubleCountByCode[String(code)] ?? 999);
                    // keep placing while possible
                    while (
                        remainingNeeded[String(code)] >= 2
                        && Number(placedDoublesByCode[String(code)] || 0) < cap
                    ) {
                        const ok = tryPlaceDoubleForCode(code);
                        if (!ok) break;
                        bumpDoubleCount(code);
                    }
                });
        }

        // 1) Coverage-first: try to place every subject at least once (respecting session rules).
        const unconstrainedSubjects = [];
        if (!hasQuotas) {
            classSubjectCodes.forEach((code) => {
                const sid = subjectIdByCode.value?.[String(code)] || null;
                const rule = getSubjectSessionRule(classId, sid);
                if (rule === 'morning') {
                    const pos = morningPositions.pop();
                    if (pos) setSlot(pos.day, pos.slotIndex, code);
                } else if (rule === 'afternoon') {
                    const pos = afternoonPositions.pop();
                    if (pos) setSlot(pos.day, pos.slotIndex, code);
                } else {
                    unconstrainedSubjects.push(code);
                }
            });
        }

        // 2) Place remaining subjects (any-time) at least once into remaining teachable slots.
        const remainingPositions = [...morningPositions, ...otherPositions, ...afternoonPositions];
        shuffle(remainingPositions);
        if (!hasQuotas) {
            unconstrainedSubjects.filter(Boolean).forEach((code) => {
                let pos = remainingPositions.pop();
                if (!pos) return;

                if (classId && !isSubjectAllowedInSlot(classId, code, pos.slotIndex)) {
                    // try to find any other slot that fits
                    let foundIndex = -1;
                    for (let j = remainingPositions.length - 1; j >= 0; j -= 1) {
                        if (isSubjectAllowedInSlot(classId, code, remainingPositions[j].slotIndex)) {
                            foundIndex = j;
                            break;
                        }
                    }

                    if (foundIndex >= 0) {
                        // put the rejected slot back, take the compatible one
                        remainingPositions.push(pos);
                        pos = remainingPositions.splice(foundIndex, 1)[0];
                    } else {
                        return;
                    }
                }

                setSlot(pos.day, pos.slotIndex, code);
            });
        }

        // 3) Fill every remaining teachable slot using rotation, respecting session rules.
        const allTeachablePositions = [];
        days.forEach((day) => {
            for (let i = 0; i < 9; i += 1) {
                if (!isTeachableLessonSlot(day, i)) continue;
                if (rowByDay[day].slots[i] !== null) continue;
                allTeachablePositions.push({ day, slotIndex: i });
            }
        });

        // Full regenerate: randomize start point for variety.
        let cursor = Math.floor(Math.random() * (classSubjectCodes.length || 1));
        const pickSubjectForPos = (pos) => {
            // Break boundaries: slot 4 starts after first break, slot 7 starts after second break.
            const hasPrevious = pos.slotIndex > 0 && ![4, 7].includes(pos.slotIndex);
            const prev = hasPrevious ? rowByDay[pos.day].slots[pos.slotIndex - 1] : null;
            const prevSubject = prev?.subject ? String(prev.subject) : '';

            let picked = null;

            const quotaCandidates = Object.keys(remainingNeeded)
                .filter((code) => Number(remainingNeeded[code] || 0) > 0);

            const ordered = quotaCandidates.length
                ? [...quotaCandidates, ...classSubjectCodes.filter((c) => !quotaCandidates.includes(String(c)))]
                : classSubjectCodes;

            const dayCounts = subjectCountByDay[String(pos.day)] || {};

            for (let tries = 0; tries < ordered.length; tries += 1) {
                const candidate = ordered[(cursor + tries) % ordered.length];
                if (candidate && prevSubject && String(candidate) === prevSubject) continue;
                if (candidate && Number(dayCounts[String(candidate)] || 0) > 0) continue;
                if (!classId || isSubjectAllowedInSlot(classId, candidate, pos.slotIndex)) {
                    picked = candidate;
                    cursor = (cursor + tries + 1) % ordered.length;
                    break;
                }
            }

            if (picked && remainingNeeded[String(picked)] > 0) {
                remainingNeeded[String(picked)] -= 1;
                if (remainingNeeded[String(picked)] <= 0) {
                    delete remainingNeeded[String(picked)];
                }
            }

            return picked;
        };

        allTeachablePositions.forEach((pos) => {
            let picked = pickSubjectForPos(pos);
            if (!picked) {
                // Fallback: choose any subject that is allowed in this slot (session rules) and avoid immediate doubles
                const hasPrevious = pos.slotIndex > 0 && ![4, 7].includes(pos.slotIndex);
                const prev = hasPrevious ? rowByDay[pos.day].slots[pos.slotIndex - 1] : null;
                const prevSubject = prev?.subject ? String(prev.subject) : '';

                const allowed = classSubjectCodes.filter((code) => {
                    if (!code) return false;
                    if (prevSubject && String(code) === prevSubject) return false;
                    return !classId || isSubjectAllowedInSlot(classId, code, pos.slotIndex);
                });

                picked = allowed.length
                    ? allowed[Math.floor(Math.random() * allowed.length)]
                    : (classSubjectCodes[Math.floor(Math.random() * (classSubjectCodes.length || 1))] || '');

                if (picked && remainingNeeded[String(picked)] > 0) {
                    remainingNeeded[String(picked)] -= 1;
                    if (remainingNeeded[String(picked)] <= 0) {
                        delete remainingNeeded[String(picked)];
                    }
                }
            }

            // Try hard to fill the slot (setSlot can fail due to per-day repetition / teacher-day limits).
            let placed = false;
            const tryCandidates = [];
            if (picked) tryCandidates.push(String(picked));
            const quotaCandidates = Object.keys(remainingNeeded || {}).filter((c) => Number(remainingNeeded[c] || 0) > 0);
            quotaCandidates.forEach((c) => {
                if (!tryCandidates.includes(String(c))) tryCandidates.push(String(c));
            });
            (classSubjectCodes || []).forEach((c) => {
                if (!tryCandidates.includes(String(c))) tryCandidates.push(String(c));
            });

            for (let t = 0; t < tryCandidates.length; t += 1) {
                const cand = tryCandidates[t];
                if (!cand) continue;
                if (classId && !isSubjectAllowedInSlot(classId, cand, pos.slotIndex)) continue;
                if (setSlot(pos.day, pos.slotIndex, cand)) {
                    placed = true;
                    if (remainingNeeded[String(cand)] > 0) {
                        remainingNeeded[String(cand)] -= 1;
                        if (remainingNeeded[String(cand)] <= 0) delete remainingNeeded[String(cand)];
                    }
                    break;
                }
            }

            if (!placed) {
                rowByDay[pos.day].slots[pos.slotIndex] = null;
            }
        });

        // 3b) Post-process: try to ensure every teacher initials appears at least once
        // by swapping a few teachable slots to subjects that include missing initials.
        const computeUsedInitialsForClass = () => {
            const set = new Set();
            days.forEach((day) => {
                const slots = rowByDay[day].slots || [];
                slots.forEach((slot) => {
                    parseInitials(slot?.teacher_initials || slot?.teacher).forEach((i) => set.add(i));
                });
            });
            return set;
        };

        const allTeacherInitials = new Set();
        classSubjectCodes.forEach((code) => {
            const raw = teachers?.[code] || '';
            parseInitials(raw).forEach((i) => allTeacherInitials.add(i));
        });

        const usedInitials = computeUsedInitialsForClass();
        const missingInitials = Array.from(allTeacherInitials).filter((i) => !usedInitials.has(i));

        const findSubjectThatContainsInitial = (initial) => {
            for (let k = 0; k < classSubjectCodes.length; k += 1) {
                const code = classSubjectCodes[k];
                const raw = teachers?.[code] || '';
                if (parseInitials(raw).includes(initial)) return code;
            }
            return null;
        };

        const findReplaceablePosition = (subjectCode) => {
            for (let d = 0; d < days.length; d += 1) {
                const day = days[d];
                for (let i = 0; i < 9; i += 1) {
                    if (!isTeachableLessonSlot(day, i)) continue;
                    if (classId && !isSubjectAllowedInSlot(classId, subjectCode, i)) continue;
                    const current = rowByDay[day].slots[i];
                    const currentSubject = current?.subject ? String(current.subject) : '';
                    if (currentSubject === String(subjectCode)) return null; // already present

                    const prev = i > 0 && ![4, 7].includes(i) ? rowByDay[day].slots[i - 1] : null;
                    const nextSlot = i < 8 && ![3, 6].includes(i) ? rowByDay[day].slots[i + 1] : null;
                    const prevSubject = prev?.subject ? String(prev.subject) : '';
                    const nextSubject = nextSlot?.subject ? String(nextSlot.subject) : '';

                    // avoid creating doubles
                    if (prevSubject && prevSubject === String(subjectCode)) continue;
                    if (nextSubject && nextSubject === String(subjectCode)) continue;

                    return { day, slotIndex: i };
                }
            }
            return null;
        };

        missingInitials.slice(0, 15).forEach((initial) => {
            const subjectCode = findSubjectThatContainsInitial(initial);
            if (!subjectCode) return;
            const pos = findReplaceablePosition(subjectCode);
            if (!pos) return;
            setSlot(pos.day, pos.slotIndex, subjectCode);
            // keep used set roughly updated to reduce extra swaps
            parseInitials(teachers?.[subjectCode] || '').forEach((i) => usedInitials.add(i));
        });

        // 4) Push into per-day arrays
        // Validate against weekly quotas and attempt a small balancing pass.
        if (hasQuotas && Object.keys(desiredPeriodsByCode || {}).length > 0) {
            const countActual = () => {
                const counts = {};
                days.forEach((day) => {
                    for (let i = 0; i < 9; i += 1) {
                        if (!isTeachableLessonSlot(day, i)) continue;
                        const slot = rowByDay[day].slots[i];
                        const code = slot?.subject ? String(slot.subject) : '';
                        if (!code) continue;
                        counts[code] = Number(counts[code] || 0) + 1;
                    }
                });
                return counts;
            };

            const isDoubleStart = (day, slotIndex) => {
                if (!isTeachableLessonSlot(day, slotIndex)) return false;
                if (!isTeachableLessonSlot(day, slotIndex + 1)) return false;
                if (![0, 1, 2, 4, 5, 7].includes(slotIndex)) return false;
                const a = rowByDay[day].slots[slotIndex];
                const b = rowByDay[day].slots[slotIndex + 1];
                return !!(a?.subject && b?.subject && String(a.subject) === String(b.subject));
            };

            const canReplaceAt = (day, slotIndex, newCode) => {
                if (!isTeachableLessonSlot(day, slotIndex)) return false;
                if (classId && !isSubjectAllowedInSlot(classId, newCode, slotIndex)) return false;

                // Avoid breaking an existing double or creating a new double unintentionally.
                if (isDoubleStart(day, slotIndex) || isDoubleStart(day, slotIndex - 1)) return false;

                const prevAllowed = slotIndex > 0 && ![4, 7].includes(slotIndex);
                const nextAllowed = slotIndex < 8 && ![3, 6].includes(slotIndex);
                const prev = prevAllowed ? rowByDay[day].slots[slotIndex - 1] : null;
                const nextSlot = nextAllowed ? rowByDay[day].slots[slotIndex + 1] : null;
                const prevCode = prev?.subject ? String(prev.subject) : '';
                const nextCode = nextSlot?.subject ? String(nextSlot.subject) : '';
                if (prevCode && prevCode === String(newCode)) return false;
                if (nextCode && nextCode === String(newCode)) return false;
                return true;
            };

            const attemptSwapFor = (deficitCode, actualCounts, desiredCounts) => {
                const desired = Number(desiredCounts[String(deficitCode)] || 0);
                const current = Number(actualCounts[String(deficitCode)] || 0);
                if (current >= desired) return false;

                for (let d = 0; d < days.length; d += 1) {
                    const day = days[d];
                    for (let i = 0; i < 9; i += 1) {
                        if (!isTeachableLessonSlot(day, i)) continue;
                        const slot = rowByDay[day].slots[i];
                        const fromCode = slot?.subject ? String(slot.subject) : '';
                        if (!fromCode) continue;
                        if (fromCode === String(deficitCode)) continue;

                        const desiredFrom = Number(desiredCounts[String(fromCode)] || 0);
                        const actualFrom = Number(actualCounts[String(fromCode)] || 0);

                        // Only swap out from a subject that is above its desired quota.
                        if (actualFrom <= desiredFrom) continue;
                        if (!canReplaceAt(day, i, deficitCode)) continue;

                        // Swap by clearing then setting (keeps teacherBusy/subjectCountByDay consistent)
                        clearSlot(day, i);
                        const ok = setSlot(day, i, deficitCode);
                        if (!ok) {
                            // rollback
                            setSlot(day, i, fromCode);
                            continue;
                        }

                        actualCounts[String(deficitCode)] = Number(actualCounts[String(deficitCode)] || 0) + 1;
                        actualCounts[String(fromCode)] = Math.max(0, Number(actualCounts[String(fromCode)] || 0) - 1);
                        return true;
                    }
                }

                return false;
            };

            const desiredCounts = { ...desiredPeriodsByCode };
            let actualCounts = countActual();
            const codesToCheck = Object.keys(desiredCounts);

            // Try a bounded number of swaps to reduce deficits.
            let safety = 0;
            while (safety < 250) {
                safety += 1;
                let didSomething = false;
                for (let k = 0; k < codesToCheck.length; k += 1) {
                    const code = codesToCheck[k];
                    if (Number(actualCounts[String(code)] || 0) >= Number(desiredCounts[String(code)] || 0)) continue;
                    const swapped = attemptSwapFor(code, actualCounts, desiredCounts);
                    if (swapped) {
                        didSomething = true;
                    }
                }
                if (!didSomething) break;
            }

            // Final report for this class if deficits remain.
            actualCounts = countActual();
            const classKey = `${String(classLabel || c?.name || '')}${streamLabel ? ` ${streamLabel}` : ''}`.trim();
            codesToCheck.forEach((code) => {
                const desired = Number(desiredCounts[String(code)] || 0);
                const actual = Number(actualCounts[String(code)] || 0);
                if (desired > 0 && actual < desired) {
                    generationWarnings.value.push(`${classKey}: ${code} missing ${desired - actual} periods`);
                }
                if (desired > 0 && actual > desired) {
                    generationWarnings.value.push(`${classKey}: ${code} exceeds by ${actual - desired} periods`);
                }
            });
        }

        // Final fill pass: ensure no empty teachable slots (especially morning/midday).
        // If constraints make it impossible, slot remains null (UI will show PS only for afternoon).
        const computeCounts = () => {
            const counts = {};
            days.forEach((day) => {
                for (let i = 0; i < 9; i += 1) {
                    if (!isTeachableLessonSlot(day, i)) continue;
                    const s = rowByDay[day].slots[i];
                    const code = s?.subject ? String(s.subject) : '';
                    if (!code) continue;
                    counts[code] = Number(counts[code] || 0) + 1;
                }
            });
            return counts;
        };

        const actualCountsFinal = computeCounts();
        const desiredCountsFinal = { ...desiredPeriodsByCode };

        days.forEach((day) => {
            for (let i = 0; i < 9; i += 1) {
                if (!isTeachableLessonSlot(day, i)) continue;
                if (rowByDay[day].slots[i] !== null) continue;

                const deficitCandidates = Object.keys(desiredCountsFinal)
                    .filter((code) => Number(desiredCountsFinal[String(code)] || 0) > Number(actualCountsFinal[String(code)] || 0));

                const candidates = [...deficitCandidates, ...(classSubjectCodes || []).map((c) => String(c))];
                let placed = false;
                for (let k = 0; k < candidates.length; k += 1) {
                    const code = candidates[k];
                    if (!code) continue;
                    if (classId && !isSubjectAllowedInSlot(classId, code, i)) continue;
                    if (setSlot(day, i, code)) {
                        actualCountsFinal[String(code)] = Number(actualCountsFinal[String(code)] || 0) + 1;
                        placed = true;
                        break;
                    }
                }

                if (!placed) {
                    rowByDay[day].slots[i] = null;
                }
            }
        });

        days.forEach((day) => {
            next[day].push(rowByDay[day]);
        });
    });

    schedule.value = next;
    saveScheduleToStorage();
    validateDoublePeriods();
};

const regenerateSampleTimetable = async () => {
    // Ensure regenerate uses DB limitations (subject_only) even if modal is closed.
    if (limitationsMode.value === 'subject_only' && selectedLimitClassId.value) {
        await loadSubjectLimits();
    }
    generateSampleTimetable();
};

const resolveTargetClassIds = computed(() => {
    const baseId = selectedLimitClassId.value ? Number(selectedLimitClassId.value) : null;
    if (!Number.isFinite(baseId) || !baseId) return [];

    const all = Array.isArray(props.classes) ? props.classes : [];
    const streams = all.filter((c) => Number(c?.parent_class_id) === baseId).map((c) => Number(c.id)).filter((v) => Number.isFinite(v));
    if (streams.length) return streams;
    return [baseId];
});

const resolveSubjectCodesForBase = computed(() => {
    const baseId = selectedLimitClassId.value ? Number(selectedLimitClassId.value) : null;
    if (!Number.isFinite(baseId) || !baseId) return [];

    const all = Array.isArray(props.classes) ? props.classes : [];
    const base = all.find((c) => Number(c?.id) === baseId) || null;
    const streams = all.filter((c) => Number(c?.parent_class_id) === baseId);
    const codes = new Set();
    if (streams.length) {
        streams.forEach((s) => (Array.isArray(s?.subject_codes) ? s.subject_codes : []).forEach((code) => codes.add(String(code))));
    } else {
        (Array.isArray(base?.subject_codes) ? base.subject_codes : []).forEach((code) => codes.add(String(code)));
    }
    return Array.from(codes.values()).filter(Boolean);
});

const desiredPeriodsForResolve = computed(() => {
    // Uses subject_only limits currently loaded for selectedLimitClassId.
    // Falls back to hard-coded defaults (Form I&II vs Form III&IV) when missing.
    const baseId = selectedLimitClassId.value ? Number(selectedLimitClassId.value) : null;
    if (!Number.isFinite(baseId) || !baseId) return {};

    const base = classById.value?.[String(baseId)] || null;
    const formNumber = getFormOrder(base);
    const defaultsByCode = defaultSubjectPeriodsByFormGroup(formNumber);

    const desired = {};
    (resolveSubjectCodesForBase.value || []).forEach((code) => {
        const sid = subjectIdByCode.value?.[String(code)] || null;
        if (!sid) return;
        const key = subjectLimitKey(sid);
        const def = defaultsByCode?.[String(code)] ?? null;
        // HARD-CODE: if the subject exists in the default table, always use that value.
        if (Number.isFinite(Number(def)) && Number(def) > 0) {
            desired[String(code)] = Math.floor(Number(def));
            return;
        }

        const raw = subjectLimitsById.value?.[key];
        const val = raw === '' || raw === null || raw === undefined ? null : Number(raw);
        if (Number.isFinite(val) && val > 0) {
            desired[String(code)] = Math.floor(val);
        }
    });
    return desired;
});

const actualPeriodsByClassIdForResolve = computed(() => {
    const wanted = new Set((resolveTargetClassIds.value || []).map((v) => String(v)));
    const result = {};
    (resolveTargetClassIds.value || []).forEach((id) => {
        result[String(id)] = {};
    });

    days.forEach((day) => {
        const rows = schedule.value?.[day] || [];
        rows.forEach((row) => {
            const cid = row?.school_class_id ? String(row.school_class_id) : '';
            if (!wanted.has(cid)) return;
            for (let i = 0; i < 9; i += 1) {
                if (!isTeachableLessonSlot(day, i)) continue;
                const slot = row?.slots?.[i];
                const code = slot?.subject ? String(slot.subject) : '';
                if (!code) continue;
                if (!result[cid]) result[cid] = {};
                result[cid][code] = Number(result[cid][code] || 0) + 1;
            }
        });
    });

    return result;
});

const resolveReport = computed(() => {
    const desired = desiredPeriodsForResolve.value || {};
    const report = [];

    (resolveTargetClassIds.value || []).forEach((cid) => {
        const c = classById.value?.[String(cid)] || null;
        const label = `${getClassLabel(c) || c?.name || ''}${c?.stream ? ` ${String(c.stream).toUpperCase()}` : ''}`.trim();
        const actualMap = actualPeriodsByClassIdForResolve.value?.[String(cid)] || {};

        Object.keys(desired).forEach((code) => {
            const want = Number(desired[String(code)] || 0);
            const got = Number(actualMap[String(code)] || 0);
            report.push({
                class_id: Number(cid),
                class_label: label,
                subject_code: String(code),
                desired: want,
                actual: got,
                completed: want <= 0 ? true : (got >= want),
            });
        });
    });

    return report.sort((a, b) => {
        const cc = Number(a.class_id) - Number(b.class_id);
        if (cc !== 0) return cc;
        const lc = String(a.class_label || '').localeCompare(String(b.class_label || ''));
        if (lc !== 0) return lc;
        return String(a.subject_code || '').localeCompare(String(b.subject_code || ''));
    });
});

const resolveSummary = computed(() => {
    const rows = resolveReport.value || [];
    if (!rows.length) return { completed: true, missingCount: 0 };
    const notOk = rows.filter((r) => !r.completed);
    return { completed: notOk.length === 0, missingCount: notOk.length };
});

const runResolve = async () => {
    if (resolveRunning.value) return;
    resolveRunning.value = true;
    try {
        // Reload limitations and regenerate from scratch so the generator can place missing quotas.
        await regenerateSampleTimetable();
    } finally {
        resolveRunning.value = false;
    }
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
    const groups = getGroupedRows(day);
    return groups.reduce((sum, g) => sum + (g?.rows?.length || 0), 0);
};

onMounted(() => {
    sessionRulesByClassId.value = loadSessionRulesFromStorage();

    const limitationsDraft = loadLimitationsDraftFromStorage();
    if (limitationsDraft) {
        if (limitationsDraft.mode === 'teacher_subject' || limitationsDraft.mode === 'subject_only') {
            limitationsMode.value = limitationsDraft.mode;
        }
        if (limitationsDraft.selected_class_id) {
            selectedLimitClassId.value = String(limitationsDraft.selected_class_id);
        }
        if (limitationsDraft.limits_by_key && typeof limitationsDraft.limits_by_key === 'object') {
            limitsByKey.value = limitationsDraft.limits_by_key;
        }
        if (limitationsDraft.subject_limits_by_id && typeof limitationsDraft.subject_limits_by_id === 'object') {
            subjectLimitsById.value = limitationsDraft.subject_limits_by_id;
        }
        if (limitationsDraft.subject_double_by_id && typeof limitationsDraft.subject_double_by_id === 'object') {
            subjectDoubleById.value = limitationsDraft.subject_double_by_id;
        }
        if (limitationsDraft.subject_morning_single_by_id && typeof limitationsDraft.subject_morning_single_by_id === 'object') {
            subjectMorningSingleById.value = limitationsDraft.subject_morning_single_by_id;
        }
        if (limitationsDraft.subject_morning_double_by_id && typeof limitationsDraft.subject_morning_double_by_id === 'object') {
            subjectMorningDoubleById.value = limitationsDraft.subject_morning_double_by_id;
        }
        if (limitationsDraft.subject_afternoon_single_by_id && typeof limitationsDraft.subject_afternoon_single_by_id === 'object') {
            subjectAfternoonSingleById.value = limitationsDraft.subject_afternoon_single_by_id;
        }
        if (limitationsDraft.subject_afternoon_double_by_id && typeof limitationsDraft.subject_afternoon_double_by_id === 'object') {
            subjectAfternoonDoubleById.value = limitationsDraft.subject_afternoon_double_by_id;
        }
    }

    if (props.timetable?.schedule_json) {
        schedule.value = props.timetable.schedule_json;
        saveScheduleToStorage();
        return;
    }

    const stored = loadScheduleFromStorage();
    if (stored) {
        schedule.value = stored;
    } else {
        generateSampleTimetable();
    }
});

const saveTimetable = () => {
    form.schedule_json = schedule.value || {};
    form.post(route('timetables.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showDetailsModal.value = false;
            clearStoredSchedule();
        },
    });
};

const printTimetable = () => {
    window.print();
};

const showTeacherInitialsModal = ref(false);

const simpleTeacherName = (name) => {
    const s = String(name || '').trim();
    if (!s) return '';
    const parts = s.split(/\s+/).filter(Boolean);
    if (parts.length === 1) return parts[0];
    return `${parts[0]} ${parts[parts.length - 1]}`;
};

const usedTeacherInitials = computed(() => {
    const set = new Set();
    const data = schedule.value || {};
    Object.values(data).forEach((rows) => {
        (rows || []).forEach((row) => {
            (row?.slots || []).forEach((slot) => {
                const raw = (slot?.teacher_initials || slot?.teacher || '').toString().trim();
                if (!raw) return;
                raw
                    .split('/')
                    .map((s) => s.trim())
                    .filter(Boolean)
                    .forEach((i) => set.add(i));
            });
        });
    });
    return set;
});

const teacherInitialsList = computed(() => {
    const allSubjects = Array.isArray(props.subjects) ? props.subjects : [];
    const map = new Map();

    allSubjects.forEach((s) => {
        const teachers = Array.isArray(s.teachers) ? s.teachers : [];
        teachers.forEach((t) => {
            if (!t?.id) return;
            const initials = String(t.initials || '').trim() || String(t.name || '').trim();
            const name = String(t.name || '').trim();
            if (!initials && !name) return;
            if (!map.has(t.id)) {
                map.set(t.id, {
                    id: t.id,
                    initials,
                    name,
                    simple_name: simpleTeacherName(name) || name,
                    used: false,
                });
            }
        });
    });

    const usedSet = usedTeacherInitials.value;
    const list = Array.from(map.values()).map((row) => ({
        ...row,
        used: usedSet.has(row.initials),
    }));

    return list.sort((a, b) => {
        const u = Number(b.used) - Number(a.used);
        if (u !== 0) return u;
        const ic = String(a.initials || '').localeCompare(String(b.initials || ''));
        if (ic !== 0) return ic;
        return String(a.simple_name || '').localeCompare(String(b.simple_name || ''));
    });
});

const downloadTeacherInitials = () => {
    const rows = teacherInitialsList.value;
    const lines = ['Initials,Name,UsedInTimetable'];
    rows.forEach((r) => {
        const initials = String(r.initials || '').replaceAll('"', '""');
        const name = String(r.simple_name || r.name || '').replaceAll('"', '""');
        const used = r.used ? 'YES' : 'NO';
        lines.push(`"${initials}","${name}","${used}"`);
    });

    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `teacher-initials-${form.academic_year || 'timetable'}.csv`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
};

const showLimitationsModal = ref(false);
const selectedLimitClassId = ref('');
const limitsLoading = ref(false);
const limitsSaving = ref(false);
const limitsByKey = ref({});

const limitationsMode = ref('subject_only');
const subjectLimitsById = ref({});
const subjectDoubleById = ref({});
const subjectMorningSingleById = ref({});
const subjectMorningDoubleById = ref({});
const subjectAfternoonSingleById = ref({});
const subjectAfternoonDoubleById = ref({});

const suppressAutoLimitSave = ref(false);
const autoLimitSaveTimer = ref(null);

const defaultSubjectPeriodsByFormGroup = (formNumber) => {
    const form12 = {
        'B/MAT': 5,  // double 2 + single 1
        'BIO': 3,    // double 1 + single 1
        'BUS': 3,    // double 1 + single 1
        'CHE': 3,    // double 1 + single 1
        'CS': 4,     // double 1 + single 2
        'ENG': 5,    // double 2 + single 1
        'GEO': 2,    // single 2 or double 1
        'HIS': 3,    // double 1 + single 1
        'HTM': 3,    // double 1 + single 1
        'KIS': 4,    // double 1 + single 2
        'PHY': 3,    // double 1 + single 1
    };

    const form34 = {
        'B/MAT': 5,  // double 2 + single 1
        'BIO': 4,    // double 1 + single 2
        'BUS': 3,    // double 1 + single 1
        'CHE': 4,    // double 1 + single 2
        'CIV': 3,    // double 1 + single 1
        'CS': 4,     // double 1 + single 2
        'ENG': 5,    // double 2 + single 1
        'FK': 2,     // single 2 or double 1
        'GEO': 3,    // double 1 + single 1
        'HIS': 4,    // double 1 + single 2
        'HTM': 3,    // double 1 + single 1
        'KIS': 4,    // double 1 + single 2
        'LIT': 2,    // single 2 or double 1
        'PHY': 4,    // double 1 + single 2
    };

    if (formNumber === 1 || formNumber === 2) return form12;
    if (formNumber === 3 || formNumber === 4) return form34;
    return {};
};

const scheduleAutoLimitSave = () => {
    if (!showLimitationsModal.value) return;
    if (!selectedLimitClassId.value) return;
    if (limitsLoading.value || limitsSaving.value) return;
    if (suppressAutoLimitSave.value) return;

    if (autoLimitSaveTimer.value) {
        window.clearTimeout(autoLimitSaveTimer.value);
    }

    autoLimitSaveTimer.value = window.setTimeout(async () => {
        if (!showLimitationsModal.value) return;
        if (!selectedLimitClassId.value) return;
        if (limitsLoading.value || limitsSaving.value) return;

        if (limitationsMode.value === 'subject_only') {
            await saveSubjectLimits({ closeModal: false });
        } else {
            await saveLimits({ closeModal: false });
        }
    }, 600);
};

const syncSubjectPeriodsFromTeacherLimits = () => {
    if (!selectedLimitClassId.value) return;

    const next = { ...(subjectLimitsById.value || {}) };
    const totalsBySubjectId = {};

    (teacherSubjectRows.value || []).forEach((row) => {
        const subjectId = row?.subject_id ? Number(row.subject_id) : null;
        const teacherId = row?.teacher_id ? Number(row.teacher_id) : null;
        if (!subjectId || !teacherId) return;

        const key = limitKey(teacherId, subjectId);
        const raw = limitsByKey.value?.[key];
        const val = raw === '' || raw === null || raw === undefined ? 0 : Number(raw);
        if (!Number.isFinite(val) || val < 0) return;

        totalsBySubjectId[String(subjectId)] = Number(totalsBySubjectId[String(subjectId)] || 0) + val;
    });

    Object.keys(totalsBySubjectId).forEach((sid) => {
        next[subjectLimitKey(sid)] = totalsBySubjectId[sid];
    });

    subjectLimitsById.value = next;
};

watch([
    limitsByKey,
    subjectLimitsById,
    subjectDoubleById,
    subjectMorningSingleById,
    subjectMorningDoubleById,
    subjectAfternoonSingleById,
    subjectAfternoonDoubleById,
    selectedLimitClassId,
    limitationsMode,
], () => {
    saveLimitationsDraftToStorage();
}, { deep: true });

const selectedLimitClass = computed(() => {
    const selectedId = selectedLimitClassId.value ? Number(selectedLimitClassId.value) : null;
    if (!Number.isFinite(selectedId)) return null;
    const base = (props.classes || []).filter((c) => !c?.parent_class_id);
    return base.find((c) => Number(c?.id) === selectedId) || null;
});

const selectedLimitClassHasSubjects = computed(() => {
    const baseId = selectedLimitClass.value?.id ? Number(selectedLimitClass.value.id) : null;
    if (!Number.isFinite(baseId) || !baseId) return false;

    const all = Array.isArray(props.classes) ? props.classes : [];
    const streams = all.filter((c) => Number(c?.parent_class_id) === baseId);
    const codes = new Set();
    if (streams.length) {
        streams.forEach((s) => (Array.isArray(s?.subject_codes) ? s.subject_codes : []).forEach((code) => codes.add(code)));
    } else {
        (Array.isArray(selectedLimitClass.value?.subject_codes) ? selectedLimitClass.value.subject_codes : []).forEach((code) => codes.add(code));
    }
    return codes.size > 0;
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
        ? (props.classes || []).filter((c) => !c?.parent_class_id).find((c) => Number(c?.id) === selectedId)
        : null;

    const all = Array.isArray(props.classes) ? props.classes : [];
    const streams = selectedClass?.id ? all.filter((c) => Number(c?.parent_class_id) === Number(selectedClass.id)) : [];
    const codesSet = new Set();
    if (streams.length) {
        streams.forEach((s) => (Array.isArray(s?.subject_codes) ? s.subject_codes : []).forEach((code) => codesSet.add(code)));
    } else {
        (Array.isArray(selectedClass?.subject_codes) ? selectedClass.subject_codes : []).forEach((code) => codesSet.add(code));
    }
    const classSubjectCodes = Array.from(codesSet.values());

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
        ? (props.classes || []).filter((c) => !c?.parent_class_id).find((c) => Number(c?.id) === selectedId)
        : null;

    if (!selectedClass) {
        return [];
    }

    const all = Array.isArray(props.classes) ? props.classes : [];
    const streams = selectedClass?.id ? all.filter((c) => Number(c?.parent_class_id) === Number(selectedClass.id)) : [];
    const codesSet = new Set();
    if (streams.length) {
        streams.forEach((s) => (Array.isArray(s?.subject_codes) ? s.subject_codes : []).forEach((code) => codesSet.add(code)));
    } else {
        (Array.isArray(selectedClass?.subject_codes) ? selectedClass.subject_codes : []).forEach((code) => codesSet.add(code));
    }
    const codes = Array.from(codesSet.values());
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
    const base = (props.classes || []).filter((c) => !c?.parent_class_id);
    return base
        .map((c) => {
            const label = getClassLabel(c) || c.name || '';
            return {
                id: String(c.id),
                display: label,
            };
        })
        .sort((a, b) => (Number(a.id || 0) - Number(b.id || 0)));
});

const limitKey = (teacherId, subjectId) => `${teacherId}:${subjectId}`;

const subjectLimitKey = (subjectId) => `${subjectId}`;

const loadSubjectLimits = async () => {
    if (!selectedLimitClassId.value) {
        subjectLimitsById.value = {};
        subjectDoubleById.value = {};
        subjectMorningSingleById.value = {};
        subjectMorningDoubleById.value = {};
        subjectAfternoonSingleById.value = {};
        subjectAfternoonDoubleById.value = {};
        return;
    }

    limitsLoading.value = true;
    suppressAutoLimitSave.value = true;
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
        const doubleMap = {};
        const morningSingleMap = {};
        const morningDoubleMap = {};
        const afternoonSingleMap = {};
        const afternoonDoubleMap = {};
        (json?.limits || []).forEach((row) => {
            map[subjectLimitKey(row.subject_id)] = row.periods_per_week;
            doubleMap[subjectLimitKey(row.subject_id)] = !!row.is_double;
            morningSingleMap[subjectLimitKey(row.subject_id)] = Number(row.morning_single || 0);
            morningDoubleMap[subjectLimitKey(row.subject_id)] = Number(row.morning_double || 0);
            afternoonSingleMap[subjectLimitKey(row.subject_id)] = Number(row.afternoon_single || 0);
            afternoonDoubleMap[subjectLimitKey(row.subject_id)] = Number(row.afternoon_double || 0);
        });

        // Hard-coded defaults (authoritative): override DB values when defaults exist.
        const cls = classById.value?.[String(selectedLimitClassId.value)] || null;
        const formNumber = getFormOrder(cls);
        const defaults = defaultSubjectPeriodsByFormGroup(formNumber);
        Object.keys(defaults).forEach((code) => {
            const sid = subjectIdByCode.value?.[String(code)] || null;
            if (!sid) return;
            const key = subjectLimitKey(sid);

            // Override periods/week to match the default table
            map[key] = Number(defaults[code]);

            // RULE 1: ENG na B/MAT - double 2, single 1 (periods = 5)
            // RULE 2: Other subjects - double 1, single 1 (periods = 3)
            const eff = Number(defaults[code] || 0);
            const subjectUpper = String(code).toUpperCase();
            
            if (subjectUpper === 'ENG' || subjectUpper === 'B/MAT') {
                // ENG na B/MAT: periods = 5, double 2, single 1
                if (eff >= 5) {
                    doubleMap[key] = true;
                }
            } else {
                // Other subjects: double 1, single 1 (periods = 3)
                if (eff >= 3) {
                    doubleMap[key] = true;
                }
            }
        });

        subjectLimitsById.value = map;
        subjectDoubleById.value = doubleMap;
        subjectMorningSingleById.value = morningSingleMap;
        subjectMorningDoubleById.value = morningDoubleMap;
        subjectAfternoonSingleById.value = afternoonSingleMap;
        subjectAfternoonDoubleById.value = afternoonDoubleMap;
    } catch {
        subjectLimitsById.value = {};
        subjectDoubleById.value = {};
        subjectMorningSingleById.value = {};
        subjectMorningDoubleById.value = {};
        subjectAfternoonSingleById.value = {};
        subjectAfternoonDoubleById.value = {};
    } finally {
        limitsLoading.value = false;
        window.setTimeout(() => {
            suppressAutoLimitSave.value = false;
        }, 0);
    }
};

const loadLimits = async () => {
    if (!selectedLimitClassId.value) {
        limitsByKey.value = {};
        return;
    }

    limitsLoading.value = true;
    suppressAutoLimitSave.value = true;
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
        window.setTimeout(() => {
            suppressAutoLimitSave.value = false;
        }, 0);
    }
};

const saveLimits = async ({ closeModal = true } = {}) => {
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

        if (closeModal) {
            showLimitationsModal.value = false;
        }
    } finally {
        limitsSaving.value = false;
    }
};

const saveSubjectLimits = async ({ closeModal = true } = {}) => {
    if (!selectedLimitClassId.value) return;

    limitsSaving.value = true;
    try {
        const limits = classSubjectRows.value
            .map((row) => {
                const key = subjectLimitKey(row.subject_id);
                const val = subjectLimitsById.value[key];
                const isDouble = !!subjectDoubleById.value?.[key];

                const ms = Number(subjectMorningSingleById.value?.[key] ?? 0) || 0;
                const md = Number(subjectMorningDoubleById.value?.[key] ?? 0) || 0;
                const as = Number(subjectAfternoonSingleById.value?.[key] ?? 0) || 0;
                const ad = Number(subjectAfternoonDoubleById.value?.[key] ?? 0) || 0;

                // If any session quota is provided, let backend derive periods_per_week from quotas.
                const hasSessionQuotas = ms > 0 || md > 0 || as > 0 || ad > 0;
                return {
                    school_class_id: Number(selectedLimitClassId.value),
                    subject_id: row.subject_id,
                    periods_per_week: hasSessionQuotas
                        ? null
                        : (val === '' || val === null || val === undefined ? 0 : Number(val)),
                    is_double: isDouble,
                    morning_single: ms,
                    morning_double: md,
                    afternoon_single: as,
                    afternoon_double: ad,
                };
            })
            .filter((r) => r.periods_per_week === null || (Number.isFinite(r.periods_per_week) && r.periods_per_week >= 0));

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

        if (closeModal) {
            showLimitationsModal.value = false;
        }
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

watch(limitsByKey, () => {
    if (!showLimitationsModal.value) return;
    if (limitationsMode.value !== 'teacher_subject') return;
    syncSubjectPeriodsFromTeacherLimits();
}, { deep: true });

watch([
    limitsByKey,
    subjectLimitsById,
    subjectDoubleById,
    subjectMorningSingleById,
    subjectMorningDoubleById,
    subjectAfternoonSingleById,
    subjectAfternoonDoubleById,
], () => {
    scheduleAutoLimitSave();
}, { deep: true });

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
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
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
                            @click="regenerateSampleTimetable"
                        >
                            Regenerate Sample Timetable
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
                            @click="showResolveModal = true"
                        >
                            Limitations &amp; Resolve
                        </button>
                    </div>
                </div>

                <div
                    v-if="showResolveModal"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4 py-6 sm:px-0"
                    @click.self="showResolveModal = false"
                >
                    <div class="max-h-full w-full max-w-4xl overflow-y-auto rounded-2xl bg-white p-6 text-xs text-gray-700 shadow-2xl ring-1 ring-gray-200">
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800">Limitations &amp; Resolve</h3>
                                <p class="mt-0.5 text-[11px] text-gray-500">
                                    Inaonyesha Periods/Week ulivyo-set na ukaguzi wa ratiba kama imekamilika kwa kila stream.
                                </p>
                            </div>
                            <button type="button" class="text-[11px] text-gray-500 hover:text-gray-700" @click="showResolveModal = false">Close</button>
                        </div>

                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div class="text-[11px]">
                                Status:
                                <span
                                    class="ml-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="resolveSummary.completed ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900'"
                                >
                                    {{ resolveSummary.completed ? 'Completed' : `Not completed (${resolveSummary.missingCount})` }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="rounded-md bg-white px-3 py-1.5 text-[11px] font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                                    @click="loadSubjectLimits"
                                >
                                    Reload Limitations
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-60"
                                    :disabled="resolveRunning"
                                    @click="runResolve"
                                >
                                    {{ resolveRunning ? 'Resolving...' : 'Resolve' }}
                                </button>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-lg border border-gray-200">
                            <table class="min-w-full border-collapse text-[11px]">
                                <thead>
                                    <tr class="bg-gray-50 text-left">
                                        <th class="border-b border-gray-200 px-3 py-2">Class</th>
                                        <th class="border-b border-gray-200 px-3 py-2">Subject</th>
                                        <th class="border-b border-gray-200 px-3 py-2">Desired</th>
                                        <th class="border-b border-gray-200 px-3 py-2">Actual</th>
                                        <th class="border-b border-gray-200 px-3 py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!resolveReport.length">
                                        <td colspan="5" class="px-3 py-4 text-center text-[11px] text-gray-500">
                                            Select a class in Limitations first.
                                        </td>
                                    </tr>
                                    <tr v-for="(r, idx) in resolveReport" :key="idx" class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="px-3 py-2">{{ r.class_label }}</td>
                                        <td class="px-3 py-2 font-semibold">{{ r.subject_code }}</td>
                                        <td class="px-3 py-2">{{ r.desired }}</td>
                                        <td class="px-3 py-2">{{ r.actual }}</td>
                                        <td class="px-3 py-2">
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                                :class="r.completed ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900'"
                                            >
                                                {{ r.completed ? 'Completed' : 'Not completed' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div
                    id="class-timetable-preview"
                    class="overflow-hidden rounded-lg border border-gray-300 bg-white text-[11px] text-gray-800"
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
                                                    <div class="font-semibold text-gray-400">PS</div>
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
                v-if="showLimitationsModal"
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