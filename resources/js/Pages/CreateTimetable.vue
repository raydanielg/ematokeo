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
    const subjectId = subjectIdByCode.value?.[String(subjectCode)] || null;
    const rule = getSubjectSessionRule(schoolClassId, subjectId);
    const slotSession = getSlotSession(slotIndex);
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
        }));
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
    });

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

        const setSlot = (day, slotIndex, subject) => {
            const picked = pickTeacherInitialForSlot(classId, subject, day, slotIndex);
            const teacher = picked || (classHasTeacherAssignments(classId) ? '' : (teachers[subject] || ''));
            const teacherInitials = typeof teacher === 'string' ? teacher : (teacher?.initials || teacher?.name || '');
            const teacherName = typeof teacher === 'string' ? teacher : (teacher?.name || teacher?.initials || '');
            rowByDay[day].slots[slotIndex] = {
                subject,
                teacher: teacherInitials,
                teacher_initials: teacherInitials,
                teacher_name: teacherName,
            };
        };

        // 1) Coverage-first: try to place every subject at least once (respecting session rules).
        const unconstrainedSubjects = [];
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

        // 2) Place remaining subjects (any-time) at least once into remaining teachable slots.
        const remainingPositions = [...morningPositions, ...otherPositions, ...afternoonPositions];
        shuffle(remainingPositions);
        unconstrainedSubjects.filter(Boolean).forEach((code) => {
            const pos = remainingPositions.pop();
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
                if (foundIndex === -1) return;
                const swapPos = remainingPositions.splice(foundIndex, 1)[0];
                setSlot(swapPos.day, swapPos.slotIndex, code);
                return;
            }
            setSlot(pos.day, pos.slotIndex, code);
        });

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
            for (let tries = 0; tries < classSubjectCodes.length; tries += 1) {
                const candidate = classSubjectCodes[(cursor + tries) % classSubjectCodes.length];
                if (candidate && prevSubject && String(candidate) === prevSubject) continue;
                if (!classId || isSubjectAllowedInSlot(classId, candidate, pos.slotIndex)) {
                    picked = candidate;
                    cursor = (cursor + tries + 1) % classSubjectCodes.length;
                    break;
                }
            }
            if (!picked) {
                picked = classSubjectCodes[Math.floor(Math.random() * (classSubjectCodes.length || 1))] || '';
            }
            return picked;
        };

        allTeachablePositions.forEach((pos) => {
            let picked = null;
            picked = pickSubjectForPos(pos);
            setSlot(pos.day, pos.slotIndex, picked);
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
        days.forEach((day) => {
            next[day].push(rowByDay[day]);
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

watch([limitsByKey, subjectLimitsById, selectedLimitClassId, limitationsMode], () => {
    saveLimitationsDraftToStorage();
}, { deep: true });

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
                            @click="generateSampleTimetable"
                        >
                            Regenerate Sample Timetable
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-emerald-600 px-2 py-1 text-[10px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                            @click="saveTimetable"
                        >
                            Save
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
                            @click="showDetailsModal = true"
                        >
                            Open Timetable Details
                        </button>
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
                                            <div>{{ slot.subject }}</div>
                                            <div class="break-words text-[9px] font-semibold leading-tight text-gray-700">{{ slot.teacher }}</div>
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
                                                <div class="break-words text-[9px] font-semibold leading-tight text-gray-700">{{ slot.teacher }}</div>
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
                                                <div class="break-words text-[9px] font-semibold leading-tight text-gray-700">{{ slot.teacher }}</div>
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
                    </div>

                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table v-if="limitationsMode === 'subject_only'" class="min-w-full border-collapse text-[11px]">
                            <thead>
                                <tr class="bg-gray-50 text-left">
                                    <th class="border-b border-gray-200 px-3 py-2">Subject</th>
                                    <th class="border-b border-gray-200 px-3 py-2">Session</th>
                                    <th class="border-b border-gray-200 px-3 py-2">Periods / Week</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!classSubjectRows.length">
                                    <td colspan="3" class="px-3 py-3 text-center text-[11px] text-gray-500">
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
