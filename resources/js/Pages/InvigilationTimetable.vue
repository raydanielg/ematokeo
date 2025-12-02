<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    exams: {
        type: Array,
        default: () => [],
    },
    selectedExamId: {
        type: Number,
        default: null,
    },
    selectedExam: {
        type: Object,
        default: null,
    },
    school: {
        type: Object,
        default: null,
    },
    classes: {
        type: Array,
        default: () => [],
    },
    teachers: {
        type: Array,
        default: () => [],
    },
    selectedClassId: {
        type: Number,
        default: null,
    },
    classSubjects: {
        type: Array,
        default: () => [],
    },
    academicTeacher: {
        type: Object,
        default: null,
    },
});

const teacherScope = ref('all'); // 'all' | 'class' | 'specific'

const morningTime = ref('08:00 - 10:30');
const afternoonTime = ref('02:00 - 04:30');

const examStartTime = ref('08:00');
const examEndTime = ref('17:00');
const examDuration = ref('2:30');
const startDay = ref('MONDAY');

const invigilatorName = ref('Emmanuel Mpandula');

const specificTeacherId = ref('');
const excludedTeacherIds = ref([]);

const hasGenerated = ref(false);

const canPrint = computed(() => {
    return Boolean(morningTime.value && afternoonTime.value && props.selectedExamId && hasGenerated.value);
});

const selectedClass = computed(() => {
    if (!props.selectedClassId) return null;
    return props.classes.find((c) => String(c.id) === String(props.selectedClassId)) || null;
});

const dayNames = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY'];

const subjectLabel = (subject) => {
    if (!subject) return '';
    return subject.name || subject.subject_code || '';
};

const subjectLayout = computed(() => {
    const subjects = props.classSubjects || [];
    const rows = [];

    const startIndex = Math.max(0, dayNames.indexOf(startDay.value || 'MONDAY'));

    for (let i = 0; i < dayNames.length; i += 1) {
        const subjectIndexBase = 2 * i;
        const morning = subjects[subjectIndexBase] || null;
        const afternoon = subjects[subjectIndexBase + 1] || null;

        const dayName = dayNames[(startIndex + i) % dayNames.length];

        rows.push({
            day: dayName,
            morningLabel: subjectLabel(morning),
            afternoonLabel: subjectLabel(afternoon),
        });
    }

    return rows;
});

const teachersForLayout = computed(() => {
    let list = props.teachers || [];

    if (teacherScope.value === 'class' && selectedClass.value) {
        const className = selectedClass.value.name || '';
        list = list.filter((t) => (t.teaching_classes || '').includes(className));
    } else if (teacherScope.value === 'specific' && specificTeacherId.value) {
        list = list.filter((t) => String(t.id) === String(specificTeacherId.value));
    }

    if (excludedTeacherIds.value.length) {
        const excludedSet = new Set(excludedTeacherIds.value.map((id) => String(id)));
        list = list.filter((t) => !excludedSet.has(String(t.id)));
    }

    return list;
});

const hasTeachers = computed(() => teachersForLayout.value.length > 0);

const roomTeachers = computed(() => {
    const list = teachersForLayout.value;
    const rooms = [];
    const maxRooms = 7; // ROOM 1..7 as sample

    if (!list.length) {
        for (let i = 1; i <= maxRooms; i += 1) {
            rooms.push({ room: i, teacher: null });
        }
        return rooms;
    }

    for (let i = 0; i < maxRooms; i += 1) {
        rooms.push({
            room: i + 1,
            teacher: list[i % list.length],
        });
    }

    return rooms;
});

const generateTimetable = () => {
    hasGenerated.value = true;
};
</script>

<template>
    <Head title="Invigilation Timetable" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div
                        v-if="props.school?.logo_path"
                        class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full border border-gray-200 bg-white"
                    >
                        <img
                            :src="props.school.logo_path"
                            alt="School logo"
                            class="h-full w-full object-contain"
                        />
                    </div>
                    <div>
                        <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                            Invigilation Timetable
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Choose exam, class and teacher pool, adjust exam times, then preview before creating.
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center rounded-md px-3 py-1.5 text-[11px] font-semibold shadow-sm"
                    :class="canPrint ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-gray-200 text-gray-500 cursor-not-allowed'"
                    :disabled="!canPrint"
                    @click="canPrint && window.print()"
                >
                    Print / PDF Preview
                </button>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Exam / class / teacher scope selector -->
            <div class="flex flex-wrap items-end justify-between gap-3 text-[11px] text-gray-700">
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-700">Exam</label>
                        <select
                            class="w-60 rounded-md border border-gray-300 px-2 py-1 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            :value="props.selectedExamId || ''"
                            @change="(e) => router.get(route('timetables.invigilation'), { exam: e.target.value || null }, { preserveScroll: true })"
                        >
                            <option value="">Select exam...</option>
                            <option
                                v-for="exam in props.exams"
                                :key="exam.id"
                                :value="exam.id"
                            >
                                {{ exam.name }} ({{ exam.academic_year }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-700">Class / Form</label>
                        <select
                            :value="props.selectedClassId || ''"
                            class="w-52 rounded-md border border-gray-300 px-2 py-1 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            @change="(e) => router.get(route('timetables.invigilation'), { exam: props.selectedExamId || null, class: e.target.value || null }, { preserveScroll: true })"
                        >
                            <option value="">All classes</option>
                            <option
                                v-for="cls in props.classes"
                                :key="cls.id"
                                :value="cls.id"
                            >
                                {{ cls.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-gray-700">Teachers filter</label>
                        <div class="flex flex-col gap-1 text-[11px]">
                            <div class="flex flex-wrap gap-3">
                                <label class="inline-flex items-center gap-1">
                                    <input
                                        v-model="teacherScope"
                                        type="radio"
                                        value="all"
                                        class="h-3 w-3 text-emerald-600 focus:ring-emerald-500"
                                    />
                                    <span>All teachers</span>
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input
                                        v-model="teacherScope"
                                        type="radio"
                                        value="class"
                                        class="h-3 w-3 text-emerald-600 focus:ring-emerald-500"
                                    />
                                    <span>Teachers of selected class</span>
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input
                                        v-model="teacherScope"
                                        type="radio"
                                        value="specific"
                                        class="h-3 w-3 text-emerald-600 focus:ring-emerald-500"
                                    />
                                    <span>Specific teacher</span>
                                </label>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <div v-if="teacherScope === 'specific'">
                                    <label class="mb-1 block text-[10px] font-medium text-gray-600">Choose teacher</label>
                                    <select
                                        v-model="specificTeacherId"
                                        class="w-48 rounded-md border border-gray-300 px-2 py-1 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    >
                                        <option value="">Select teacher...</option>
                                        <option
                                            v-for="t in props.teachers"
                                            :key="t.id"
                                            :value="t.id"
                                        >
                                            {{ t.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[10px] font-medium text-gray-600">Excluded teachers</label>
                                    <select
                                        v-model="excludedTeacherIds"
                                        multiple
                                        class="min-h-[2.2rem] w-64 rounded-md border border-gray-300 px-2 py-1 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    >
                                        <option
                                            v-for="t in props.teachers"
                                            :key="`ex-` + t.id"
                                            :value="t.id"
                                        >
                                            {{ t.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-[10px] text-gray-500">
                    These options control how the invigilation timetable will be generated (exam, class and teacher pool). You must also set exam times below before printing/creating.
                </div>
            </div>

            <!-- Exam times editor -->
            <div class="flex flex-wrap items-end gap-4 text-[11px] text-gray-700">
                <div>
                    <label class="mb-1 block text-[11px] font-semibold text-gray-700">Exam start time</label>
                    <input
                        v-model="examStartTime"
                        type="text"
                        class="w-32 rounded-md border border-gray-300 px-2 py-1 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. 08:00"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold text-gray-700">Exam end time</label>
                    <input
                        v-model="examEndTime"
                        type="text"
                        class="w-32 rounded-md border border-gray-300 px-2 py-1 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. 17:00"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold text-gray-700">Exam duration</label>
                    <input
                        v-model="examDuration"
                        type="text"
                        class="w-28 rounded-md border border-gray-300 px-2 py-1 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. 02:30"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold text-gray-700">Start day</label>
                    <select
                        v-model="startDay"
                        class="w-32 rounded-md border border-gray-300 px-2 py-1 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                    >
                        <option v-for="d in dayNames" :key="d" :value="d">
                            {{ d }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold text-gray-700">Morning session time (A.M)</label>
                    <input
                        v-model="morningTime"
                        type="text"
                        class="w-40 rounded-md border border-gray-300 px-2 py-1 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. 08:00 - 10:30"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold text-gray-700">Afternoon session time (P.M)</label>
                    <input
                        v-model="afternoonTime"
                        type="text"
                        class="w-40 rounded-md border border-gray-300 px-2 py-1 text-[11px] text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. 02:00 - 04:30"
                    />
                </div>
                <div class="ml-auto flex items-end">
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                        @click="generateTimetable"
                        :disabled="!props.selectedExamId"
                    >
                        Generate timetable
                    </button>
                </div>
            </div>

            <!-- Government / school header like official timetable -->
            <div class="rounded-xl bg-white p-4 text-center text-[11px] text-gray-800 shadow-sm ring-1 ring-gray-200">
                <div class="text-[10px] font-semibold uppercase tracking-wide">
                    THE PRESIDENT'S OFFICE
                </div>
                <div class="text-[10px] font-semibold uppercase tracking-wide">
                    REGIONAL ADMINISTRATION AND LOCAL GOVERNMENT
                </div>
                <div class="mt-1 text-[11px] font-semibold uppercase tracking-wide">
                    {{ props.school?.name || 'SCHOOL NAME' }}
                </div>
                <div class="mt-0.5 text-[11px] font-medium uppercase tracking-wide text-gray-700">
                    {{ props.selectedExam?.name || 'SELECT AN EXAM TO PREVIEW INVIGILATION' }}
                </div>
                <div class="mt-1 text-[11px] font-extrabold uppercase tracking-wide text-gray-900">
                    INVIGILATION TIMETABLE
                </div>
                <div class="mt-1 text-[10px] text-gray-700">
                    INVIGILATOR:
                    <input
                        v-model="invigilatorName"
                        type="text"
                        class="ml-1 inline-block w-56 rounded-md border border-gray-300 px-1 py-0.5 text-[10px] font-semibold uppercase text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                    />
                </div>
                <div class="mt-1 text-[10px] text-gray-600">
                    <span v-if="selectedClass">
                        Class: <span class="font-semibold">{{ selectedClass.name }}</span>
                    </span>
                    <span v-else>
                        All classes
                    </span>
                    <span class="mx-1">•</span>
                    <span>
                        Teachers:
                        <span class="font-semibold">
                            {{
                                teacherScope === 'all'
                                    ? 'All teachers'
                                    : teacherScope === 'class'
                                        ? 'Teachers of selected class'
                                        : 'Specific teacher'
                            }}
                        </span>
                    </span>
                </div>
            </div>

            <!-- Main timetable styled similar to NECTA sample -->
            <div
                v-if="hasGenerated"
                class="overflow-x-auto rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-300"
            >
                <table class="min-w-full border border-black text-[11px]">
                    <thead>
                        <!-- Days / Sessions band -->
                        <tr class="bg-yellow-400 text-[11px] font-semibold uppercase tracking-wide text-black">
                            <th rowspan="3" class="border border-black px-2 py-2 align-middle">
                                DAYS /
                                <br />
                                DATE
                            </th>
                            <th colspan="4" class="border border-black px-2 py-2">
                                MORNING SESSION (A.M)
                            </th>
                            <th colspan="4" class="border border-black px-2 py-2">
                                AFTERNOON SESSION (P.M)
                            </th>
                        </tr>
                        <tr class="bg-yellow-300 text-[10px] font-semibold uppercase tracking-wide text-black">
                            <th class="border border-black px-2 py-1">TIME</th>
                            <th colspan="3" class="border border-black px-2 py-1">SUBJECT / ROOM</th>
                            <th class="border border-black px-2 py-1">TIME</th>
                            <th colspan="3" class="border border-black px-2 py-1">SUBJECT / ROOM</th>
                        </tr>
                        <tr class="bg-yellow-300 text-[10px] font-semibold uppercase tracking-wide text-black">
                            <th class="border border-black px-2 py-1">{{ morningTime || 'Set AM time' }}</th>
                            <th class="border border-black px-2 py-1">SUBJECT</th>
                            <th class="border border-black px-2 py-1">ROOM</th>
                            <th class="border border-black px-2 py-1"></th>
                            <th class="border border-black px-2 py-1">{{ afternoonTime || 'Set PM time' }}</th>
                            <th class="border border-black px-2 py-1">SUBJECT</th>
                            <th class="border border-black px-2 py-1">ROOM</th>
                            <th class="border border-black px-2 py-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- MONDAY header row: day + subjects from selected class -->
                        <tr class="bg-yellow-200 font-semibold uppercase text-gray-900">
                            <td class="border border-black px-2 py-1">MONDAY</td>
                            <td class="border border-black px-2 py-1" colspan="3">
                                {{ subjectLayout[0]?.morningLabel || 'SUBJECT 1' }}
                            </td>
                            <td class="border border-black px-2 py-1">&nbsp;</td>
                            <td class="border border-black px-2 py-1" colspan="3">
                                {{ subjectLayout[0]?.afternoonLabel || 'SUBJECT 2' }}
                            </td>
                        </tr>
                        <tr class="bg-yellow-100 text-gray-800">
                            <td class="border border-black px-2 py-1 align-top">28/07/2025</td>
                            <td colspan="3" class="border border-black px-2 py-1">
                                <div class="grid grid-cols-4 gap-1">
                                    <span class="font-semibold">ROOM</span>
                                    <span class="col-span-3 font-semibold">TEACHER</span>
                                    <template v-if="hasTeachers">
                                        <template v-for="rt in roomTeachers" :key="`mon-am-` + rt.room">
                                            <span>{{ rt.room }}</span>
                                            <span class="col-span-3">{{ rt.teacher?.name || '-' }}</span>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <span class="col-span-4 text-[10px] font-medium text-red-600">
                                            Hakuna walimu waliopatikana kwa ratiba hii (kulingana na chaguo la teachers).
                                        </span>
                                    </template>
                                </div>
                            </td>
                            <td class="border border-black px-2 py-1 align-top">&nbsp;</td>
                            <td colspan="3" class="border border-black px-2 py-1">
                                <div class="grid grid-cols-4 gap-1">
                                    <span class="font-semibold">ROOM</span>
                                    <span class="col-span-3 font-semibold">TEACHER</span>
                                    <template v-if="hasTeachers">
                                        <template v-for="rt in roomTeachers" :key="`mon-pm-` + rt.room">
                                            <span>{{ rt.room }}</span>
                                            <span class="col-span-3">{{ rt.teacher?.name || '-' }}</span>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <span class="col-span-4 text-[10px] font-medium text-red-600">
                                            Hakuna walimu waliopatikana kwa ratiba hii (kulingana na chaguo la teachers).
                                        </span>
                                    </template>
                                </div>
                            </td>
                        </tr>

                        <!-- TUESDAY header row: day + subjects from selected class -->
                        <tr class="bg-yellow-200 font-semibold uppercase text-gray-900">
                            <td class="border border-black px-2 py-1">TUESDAY</td>
                            <td class="border border-black px-2 py-1" colspan="3">
                                {{ subjectLayout[1]?.morningLabel || 'SUBJECT 3' }}
                            </td>
                            <td class="border border-black px-2 py-1">&nbsp;</td>
                            <td class="border border-black px-2 py-1" colspan="3">
                                {{ subjectLayout[1]?.afternoonLabel || 'SUBJECT 4' }}
                            </td>
                        </tr>
                        <tr class="bg-yellow-100 text-gray-800">
                            <td class="border border-black px-2 py-1 align-top">29/07/2025</td>
                            <td colspan="3" class="border border-black px-2 py-1">
                                <div class="grid grid-cols-4 gap-1">
                                    <span class="font-semibold">ROOM</span>
                                    <span class="col-span-3 font-semibold">TEACHER</span>
                                    <template v-if="hasTeachers">
                                        <template v-for="rt in roomTeachers" :key="`tue-am-` + rt.room">
                                            <span>{{ rt.room }}</span>
                                            <span class="col-span-3">{{ rt.teacher?.name || '-' }}</span>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <span class="col-span-4 text-[10px] font-medium text-red-600">
                                            Hakuna walimu waliopatikana kwa ratiba hii (kulingana na chaguo la teachers).
                                        </span>
                                    </template>
                                </div>
                            </td>
                            <td class="border border-black px-2 py-1 align-top">&nbsp;</td>
                            <td colspan="3" class="border border-black px-2 py-1">
                                <div class="grid grid-cols-4 gap-1">
                                    <span class="font-semibold">ROOM</span>
                                    <span class="col-span-3 font-semibold">TEACHER</span>
                                    <template v-if="hasTeachers">
                                        <template v-for="rt in roomTeachers" :key="`tue-pm-` + rt.room">
                                            <span>{{ rt.room }}</span>
                                            <span class="col-span-3">{{ rt.teacher?.name || '-' }}</span>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <span class="col-span-4 text-[10px] font-medium text-red-600">
                                            Hakuna walimu waliopatikana kwa ratiba hii (kulingana na chaguo la teachers).
                                        </span>
                                    </template>
                                </div>
                            </td>
                        </tr>

                        <!-- WEDNESDAY header row: day + subjects from selected class -->
                        <tr class="bg-yellow-200 font-semibold uppercase text-gray-900">
                            <td class="border border-black px-2 py-1">WEDNESDAY</td>
                            <td class="border border-black px-2 py-1" colspan="3">
                                {{ subjectLayout[2]?.morningLabel || 'SUBJECT 5' }}
                            </td>
                            <td class="border border-black px-2 py-1">&nbsp;</td>
                            <td class="border border-black px-2 py-1" colspan="3">
                                {{ subjectLayout[2]?.afternoonLabel || 'SUBJECT 6' }}
                            </td>
                        </tr>
                        <tr class="bg-yellow-100 text-gray-800">
                            <td class="border border-black px-2 py-1 align-top">30/07/2025</td>
                            <td colspan="3" class="border border-black px-2 py-1">
                                <div class="grid grid-cols-4 gap-1">
                                    <span class="font-semibold">ROOM</span>
                                    <span class="col-span-3 font-semibold">TEACHER</span>
                                    <template v-if="hasTeachers">
                                        <template v-for="rt in roomTeachers" :key="`wed-am-` + rt.room">
                                            <span>{{ rt.room }}</span>
                                            <span class="col-span-3">{{ rt.teacher?.name || '-' }}</span>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <span class="col-span-4 text-[10px] font-medium text-red-600">
                                            Hakuna walimu waliopatikana kwa ratiba hii (kulingana na chaguo la teachers).
                                        </span>
                                    </template>
                                </div>
                            </td>
                            <td class="border border-black px-2 py-1 align-top">&nbsp;</td>
                            <td colspan="3" class="border border-black px-2 py-1">
                                <div class="grid grid-cols-4 gap-1">
                                    <span class="font-semibold">ROOM</span>
                                    <span class="col-span-3 font-semibold">TEACHER</span>
                                    <template v-if="hasTeachers">
                                        <template v-for="rt in roomTeachers" :key="`wed-pm-` + rt.room">
                                            <span>{{ rt.room }}</span>
                                            <span class="col-span-3">{{ rt.teacher?.name || '-' }}</span>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <span class="col-span-4 text-[10px] font-medium text-red-600">
                                            Hakuna walimu waliopatikana kwa ratiba hii (kulingana na chaguo la teachers).
                                        </span>
                                    </template>
                                </div>
                            </td>
                        </tr>

                        <!-- THURSDAY header row: day + subjects from selected class -->
                        <tr class="bg-yellow-200 font-semibold uppercase text-gray-900">
                            <td class="border border-black px-2 py-1">THURSDAY</td>
                            <td class="border border-black px-2 py-1" colspan="3">
                                {{ subjectLayout[3]?.morningLabel || 'SUBJECT 7' }}
                            </td>
                            <td class="border border-black px-2 py-1">&nbsp;</td>
                            <td class="border border-black px-2 py-1" colspan="3">
                                {{ subjectLayout[3]?.afternoonLabel || 'SUBJECT 8' }}
                            </td>
                        </tr>
                        <tr class="bg-yellow-100 text-gray-800">
                            <td class="border border-black px-2 py-1 align-top">31/07/2025</td>
                            <td colspan="3" class="border border-black px-2 py-1">
                                <div class="grid grid-cols-4 gap-1">
                                    <span class="font-semibold">ROOM</span>
                                    <span class="col-span-3 font-semibold">TEACHER</span>
                                    <template v-if="hasTeachers">
                                        <template v-for="rt in roomTeachers" :key="`thu-am-` + rt.room">
                                            <span>{{ rt.room }}</span>
                                            <span class="col-span-3">{{ rt.teacher?.name || '-' }}</span>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <span class="col-span-4 text-[10px] font-medium text-red-600">
                                            Hakuna walimu waliopatikana kwa ratiba hii (kulingana na chaguo la teachers).
                                        </span>
                                    </template>
                                </div>
                            </td>
                            <td class="border border-black px-2 py-1 align-top">&nbsp;</td>
                            <td colspan="3" class="border border-black px-2 py-1">
                                <div class="grid grid-cols-4 gap-1">
                                    <span class="font-semibold">ROOM</span>
                                    <span class="col-span-3 font-semibold">TEACHER</span>
                                    <template v-if="hasTeachers">
                                        <template v-for="rt in roomTeachers" :key="`thu-pm-` + rt.room">
                                            <span>{{ rt.room }}</span>
                                            <span class="col-span-3">{{ rt.teacher?.name || '-' }}</span>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <span class="col-span-4 text-[10px] font-medium text-red-600">
                                            Hakuna walimu waliopatikana kwa ratiba hii (kulingana na chaguo la teachers).
                                        </span>
                                    </template>
                                </div>
                            </td>
                        </tr>

                        <!-- FRIDAY header row: day + subjects from selected class -->
                        <tr class="bg-yellow-200 font-semibold uppercase text-gray-900">
                            <td class="border border-black px-2 py-1">FRIDAY</td>
                            <td class="border border-black px-2 py-1" colspan="3">
                                {{ subjectLayout[4]?.morningLabel || 'SUBJECT 9' }}
                            </td>
                            <td class="border border-black px-2 py-1">&nbsp;</td>
                            <td class="border border-black px-2 py-1" colspan="3">
                                {{ subjectLayout[4]?.afternoonLabel || 'SUBJECT 10' }}
                            </td>
                        </tr>
                        <tr class="bg-yellow-100 text-gray-800">
                            <td class="border border-black px-2 py-1 align-top">01/08/2025</td>
                            <td colspan="3" class="border border-black px-2 py-1">
                                <div class="grid grid-cols-4 gap-1">
                                    <span class="font-semibold">ROOM</span>
                                    <span class="col-span-3 font-semibold">TEACHER</span>
                                    <template v-if="hasTeachers">
                                        <template v-for="rt in roomTeachers" :key="`fri-am-` + rt.room">
                                            <span>{{ rt.room }}</span>
                                            <span class="col-span-3">{{ rt.teacher?.name || '-' }}</span>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <span class="col-span-4 text-[10px] font-medium text-red-600">
                                            Hakuna walimu waliopatikana kwa ratiba hii (kulingana na chaguo la teachers).
                                        </span>
                                    </template>
                                </div>
                            </td>
                            <td class="border border-black px-2 py-1 align-top">&nbsp;</td>
                            <td colspan="3" class="border border-black px-2 py-1">
                                <div class="grid grid-cols-4 gap-1">
                                    <span class="font-semibold">ROOM</span>
                                    <span class="col-span-3 font-semibold">TEACHER</span>
                                    <template v-if="hasTeachers">
                                        <template v-for="rt in roomTeachers" :key="`fri-pm-` + rt.room">
                                            <span>{{ rt.room }}</span>
                                            <span class="col-span-3">{{ rt.teacher?.name || '-' }}</span>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <span class="col-span-4 text-[10px] font-medium text-red-600">
                                            Hakuna walimu waliopatikana kwa ratiba hii (kulingana na chaguo la teachers).
                                        </span>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
