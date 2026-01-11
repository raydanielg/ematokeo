<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
});

const today = new Date();
const year = today.getFullYear();

const monthNames = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
];

const weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

// Tanzanian public holidays (fixed dates)
const tzHolidays = [
    { month: 1, day: 1, name: 'New Year' },
    { month: 1, day: 12, name: 'Zanzibar Revolution Day' },
    { month: 4, day: 7, name: 'Karume Day' },
    { month: 4, day: 26, name: 'Union Day' },
    { month: 5, day: 1, name: 'Workers Day' },
    { month: 7, day: 7, name: 'Saba Saba Day' },
    { month: 8, day: 8, name: 'Nane Nane Day' },
    { month: 10, day: 14, name: 'Nyerere Day' },
    { month: 12, day: 9, name: 'Independence Day' },
    { month: 12, day: 25, name: 'Christmas Day' },
    { month: 12, day: 26, name: 'Boxing Day' },
    // Note: Islamic holidays (Eid) vary each year and are not fixed-date here.
];

const isHoliday = (monthIndex, day) => {
    const month = monthIndex + 1; // monthIndex is 0-based
    return tzHolidays.find((h) => h.month === month && h.day === day) || null;
};

const buildYearMonths = () => {
    return monthNames.map((name, index) => {
        const firstDay = new Date(year, index, 1);
        // JS: 0=Sun, 1=Mon ... -> make Monday=0
        let startIndex = firstDay.getDay() - 1;
        if (startIndex < 0) startIndex = 6; // Sunday

        const daysInMonth = new Date(year, index + 1, 0).getDate();
        const weeks = [];
        let currentDay = 1 - startIndex;

        // 6 rows max to be safe
        for (let w = 0; w < 6; w++) {
            const week = [];
            for (let d = 0; d < 7; d++) {
                if (currentDay < 1 || currentDay > daysInMonth) {
                    week.push(null);
                } else {
                    week.push(currentDay);
                }
                currentDay++;
            }
            weeks.push(week);
        }

        return {
            name,
            index,
            weeks,
        };
    });
};

const months = buildYearMonths();

const viewMode = ref('date'); // 'date' | 'events'

const eventFilterFrom = ref('');
const eventFilterTo = ref('');

const filteredEvents = computed(() => {
    const from = String(eventFilterFrom.value || '').trim();
    const to = String(eventFilterTo.value || '').trim();

    return (props.events || [])
        .slice()
        .filter((e) => {
            const d = String(e?.date || '').trim();
            if (!d) return false;
            if (from && d < from) return false;
            if (to && d > to) return false;
            return true;
        })
        .sort((a, b) => String(a?.date || '').localeCompare(String(b?.date || '')));
});

const isToday = (monthIndex, day) => {
    if (!day) return false;
    const now = new Date();
    return (
        now.getFullYear() === year &&
        now.getMonth() === monthIndex &&
        now.getDate() === day
    );
};

const eventForm = useForm({
    date: new Date().toISOString().slice(0, 10),
    title: '',
    description: '',
});

const submitEvent = () => {
    eventForm.post(route('calendar.events.store'));
};
</script>

<template>
    <Head title="Calendar" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        School Calendar
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Full year view with highlighted national events for Tanzania.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="inline-flex overflow-hidden rounded-md bg-white shadow-sm ring-1 ring-gray-200">
                        <button
                            type="button"
                            class="px-3 py-1.5 text-[11px] font-semibold"
                            :class="viewMode === 'date' ? 'bg-emerald-600 text-white' : 'text-gray-700 hover:bg-gray-50'"
                            @click="viewMode = 'date'"
                        >
                            By Date
                        </button>
                        <button
                            type="button"
                            class="px-3 py-1.5 text-[11px] font-semibold"
                            :class="viewMode === 'events' ? 'bg-emerald-600 text-white' : 'text-gray-700 hover:bg-gray-50'"
                            @click="viewMode = 'events'"
                        >
                            By Events
                        </button>
                    </div>
                    <a
                        :href="route('calendar.preview')"
                        class="inline-flex items-center rounded-md bg-white/90 px-3 py-1.5 text-[11px] font-semibold text-emerald-700 shadow-sm ring-1 ring-emerald-200 hover:bg-emerald-50"
                    >
                        Preview / Print
                    </a>
                </div>
            </div>
        </template>

        <div
            class="space-y-4 rounded-2xl bg-slate-900/90 p-3"
            style="background-image: url('/images/bgone.jpg'); background-size: cover; background-position: center;"
        >
            <div class="rounded-2xl bg-gradient-to-br from-emerald-900/95 via-emerald-800/95 to-emerald-950/95 p-[1px] shadow-xl backdrop-blur-sm">
                <div class="rounded-2xl bg-white/95 p-5 text-sm backdrop-blur">
                    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="text-base font-semibold text-emerald-900">
                                Tanzania School Year {{ year }}
                            </div>
                            <p class="mt-0.5 text-[11px] text-emerald-900/80">
                                Public holidays are highlighted in green. Islamic holidays are not shown because dates vary each year.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-[11px]">
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-emerald-700 ring-1 ring-emerald-100">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                National Holiday
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-2 py-0.5 text-sky-700 ring-1 ring-sky-100">
                                <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                Today
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 text-slate-600 ring-1 ring-slate-200">
                                <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                                Weekend
                            </span>
                        </div>
                    </div>

                    <div v-if="viewMode === 'events'" class="mb-4 rounded-xl bg-white p-3 text-[11px] text-gray-800 shadow-sm ring-1 ring-slate-200/80">
                        <div class="mb-2 flex flex-wrap items-end justify-between gap-2">
                            <div>
                                <div class="text-[12px] font-semibold text-gray-900">Events Preview</div>
                                <div class="mt-0.5 text-[10px] text-gray-500">Filter by date range and review all school events.</div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <div>
                                    <label class="mb-0.5 block text-[10px] font-medium text-gray-600">From</label>
                                    <input v-model="eventFilterFrom" type="date" class="rounded-md border border-gray-300 px-2 py-1 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" />
                                </div>
                                <div>
                                    <label class="mb-0.5 block text-[10px] font-medium text-gray-600">To</label>
                                    <input v-model="eventFilterTo" type="date" class="rounded-md border border-gray-300 px-2 py-1 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" />
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-left text-[11px]">
                                <thead>
                                    <tr class="bg-slate-50 text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                                        <th class="border border-slate-200 px-3 py-2">Date</th>
                                        <th class="border border-slate-200 px-3 py-2">Event</th>
                                        <th class="border border-slate-200 px-3 py-2">Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="filteredEvents.length === 0">
                                        <td colspan="3" class="border border-slate-200 px-3 py-4 text-center text-[11px] text-gray-500">
                                            No events found for the selected range.
                                        </td>
                                    </tr>
                                    <tr v-for="e in filteredEvents" :key="e.id" class="odd:bg-white even:bg-slate-50">
                                        <td class="border border-slate-200 px-3 py-2 font-semibold text-emerald-700">{{ e.date }}</td>
                                        <td class="border border-slate-200 px-3 py-2 font-semibold text-gray-900">{{ e.title }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-gray-700">{{ e.description || '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Event Center (add events) -->
                    <div class="mb-4 rounded-xl bg-slate-50/80 p-3 text-[11px] text-gray-800 ring-1 ring-slate-200/80">
                        <div class="mb-2 flex items-center justify-between">
                            <h3 class="text-[12px] font-semibold text-gray-800">
                                Event Center
                            </h3>
                            <span class="text-[10px] text-gray-500">
                                Add your school events (opening day, exams, meetings).
                            </span>
                        </div>
                        <form class="grid gap-2 md:grid-cols-4" @submit.prevent="submitEvent">
                            <div class="md:col-span-1">
                                <label class="mb-1 block text-[10px] font-medium">Date</label>
                                <input
                                    v-model="eventForm.date"
                                    type="date"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                />
                            </div>
                            <div class="md:col-span-1">
                                <label class="mb-1 block text-[10px] font-medium">Title</label>
                                <input
                                    v-model="eventForm.title"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="e.g. Midterm Exams"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-medium">Description (optional)</label>
                                <input
                                    v-model="eventForm.description"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    placeholder="Short note about the event"
                                />
                            </div>
                            <div class="md:col-span-4 flex justify-end pt-1">
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                                    :disabled="eventForm.processing"
                                >
                                    Save Event
                                </button>
                            </div>
                        </form>
                    </div>

                    <div v-if="viewMode === 'date'" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div
                            v-for="month in months"
                            :key="month.index"
                            class="rounded-xl bg-gradient-to-br from-slate-50 via-white to-slate-100 p-3 shadow-sm ring-1 ring-slate-200/80"
                        >
                            <div class="mb-2 flex items-center justify-between text-[12px] font-semibold text-gray-800">
                                <span>{{ month.name }}</span>
                                <span class="text-[10px] font-normal text-gray-500">{{ year }}</span>
                            </div>

                            <div class="grid grid-cols-7 text-center text-[10px] font-semibold text-gray-500">
                                <div
                                    v-for="(d, idx) in weekDays"
                                    :key="d"
                                    class="py-1"
                                    :class="{ 'text-rose-500': idx >= 5 }"
                                >
                                    {{ d }}
                                </div>
                            </div>

                            <div class="mt-1 grid grid-cols-7 text-center text-[10px]">
                                <template v-for="(week, wi) in month.weeks" :key="wi">
                                    <div
                                        v-for="(day, di) in week"
                                        :key="`${wi}-${di}`"
                                        class="flex h-7 items-center justify-center rounded-full border border-transparent text-gray-700 transition hover:border-emerald-300 hover:bg-emerald-50/60"
                                        :class="{
                                            'text-gray-300 hover:border-transparent hover:bg-transparent': day === null,
                                            'bg-emerald-50 border-emerald-300 text-emerald-900 font-semibold shadow-sm': day !== null && isHoliday(month.index, day),
                                            'bg-sky-100 border-sky-400 text-sky-900 font-semibold shadow-sm': day !== null && isToday(month.index, day),
                                            'text-rose-500/80': day !== null && (di === 5 || di === 6) && !isHoliday(month.index, day) && !isToday(month.index, day),
                                        }"
                                    >
                                        <span v-if="day !== null">{{ day }}</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div v-if="viewMode === 'date'" class="mt-4 grid gap-4 lg:grid-cols-2">
                        <div class="rounded-xl bg-slate-900/95 p-3 text-[11px] text-slate-100 shadow-inner ring-1 ring-slate-700/80">
                            <h3 class="mb-1 text-[12px] font-semibold text-emerald-100">
                                Tanzania Public Holidays (Fixed Dates)
                            </h3>
                            <ul class="grid gap-1 sm:grid-cols-2">
                                <li
                                    v-for="h in tzHolidays"
                                    :key="`${h.month}-${h.day}`"
                                    class="flex items-center gap-2"
                                >
                                    <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                                    <span>{{ h.day }} {{ monthNames[h.month - 1] }} - {{ h.name }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="rounded-xl bg-white/95 p-3 text-[11px] text-gray-800 shadow-sm ring-1 ring-slate-200/80">
                            <h3 class="mb-1 text-[12px] font-semibold text-gray-800">
                                School Events
                            </h3>
                            <div v-if="!props.events || props.events.length === 0" class="text-[10px] text-gray-500">
                                No school events added yet. Use the Event Center above to add your first event.
                            </div>
                            <ul v-else class="mt-1 space-y-1 max-h-40 overflow-auto pr-1">
                                <li
                                    v-for="event in props.events"
                                    :key="event.id"
                                    class="flex items-start justify-between gap-2 rounded-md bg-slate-50 px-2 py-1"
                                >
                                    <div>
                                        <div class="text-[10px] font-semibold text-emerald-700">
                                            {{ event.date }}
                                        </div>
                                        <div class="text-[11px] font-semibold text-gray-900">
                                            {{ event.title }}
                                        </div>
                                        <div v-if="event.description" class="text-[10px] text-gray-600">
                                            {{ event.description }}
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
