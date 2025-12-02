<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    school: {
        type: Object,
        default: () => ({}),
    },
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
];

const isHoliday = (monthIndex, day) => {
    const month = monthIndex + 1;
    return tzHolidays.find((h) => h.month === month && h.day === day) || null;
};

const buildYearMonths = () => {
    return monthNames.map((name, index) => {
        const firstDay = new Date(year, index, 1);
        let startIndex = firstDay.getDay() - 1;
        if (startIndex < 0) startIndex = 6;

        const daysInMonth = new Date(year, index + 1, 0).getDate();
        const weeks = [];
        let currentDay = 1 - startIndex;

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

const printPage = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Calendar Preview ${year}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Calendar Preview
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Print-friendly view of the school calendar and events.
                    </p>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                    @click="printPage"
                >
                    Print
                </button>
            </div>
        </template>

        <div id="calendar-preview" class="rounded-xl bg-white p-6 text-[11px] text-gray-900 shadow-sm ring-1 ring-gray-200">
            <!-- Header with logo and title (simple official report style) -->
            <div class="mb-4 border-b border-gray-200 pb-3 text-center">
                <div class="mb-2 flex items-center justify-center">
                    <img
                        src="/images/emblem.png"
                        alt="National Emblem"
                        class="h-12 w-12 object-contain"
                    />
                </div>
                <div class="text-xs">
                    <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">
                        {{ school?.ministry_name || 'MINISTRY OF EDUCATION, SCIENCE AND TECHNOLOGY' }}
                    </div>
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-800">
                        {{ school?.name || 'SCHOOL NAME' }}
                    </div>
                    <div class="text-[10px] text-gray-500">
                        OFFICIAL SCHOOL CALENDAR - {{ year }}
                    </div>
                </div>

                <div class="mt-2 text-[10px] text-gray-500">
                    Generated on: {{ new Date().toLocaleDateString() }}
                </div>
            </div>

            <!-- Year grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 year-grid-print">
                <div
                    v-for="month in months"
                    :key="month.index"
                    class="rounded-lg border border-gray-200 bg-slate-50 p-3"
                >
                    <div class="mb-2 flex items-center justify-between text-[12px] font-semibold text-gray-800">
                        <span>{{ month.name }}</span>
                        <span class="text-[10px] font-normal text-gray-500">{{ year }}</span>
                    </div>

                    <div class="grid grid-cols-7 text-center text-[10px] font-semibold text-gray-500">
                        <div v-for="d in weekDays" :key="d" class="py-1">
                            {{ d }}
                        </div>
                    </div>

                    <div class="mt-1 grid grid-cols-7 text-center text-[10px]">
                        <template v-for="(week, wi) in month.weeks" :key="wi">
                            <div
                                v-for="(day, di) in week"
                                :key="`${wi}-${di}`"
                                class="flex h-6 items-center justify-center rounded border border-transparent text-gray-700"
                                :class="{
                                    'text-gray-300': day === null,
                                    'bg-emerald-50 border-emerald-300 text-emerald-900 font-semibold': day !== null && isHoliday(month.index, day),
                                }"
                            >
                                <span v-if="day !== null">{{ day }}</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Holidays + events -->
            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-slate-50 p-3">
                    <h3 class="mb-1 text-[12px] font-semibold text-gray-800">
                        Tanzania Public Holidays (Fixed Dates)
                    </h3>
                    <ul class="grid gap-1 sm:grid-cols-2">
                        <li
                            v-for="h in tzHolidays"
                            :key="`${h.month}-${h.day}`"
                            class="flex items-center gap-2"
                        >
                            <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span>{{ h.day }} {{ monthNames[h.month - 1] }} - {{ h.name }}</span>
                        </li>
                    </ul>
                </div>
                <div class="rounded-lg border border-gray-200 bg-slate-50 p-3">
                    <h3 class="mb-1 text-[12px] font-semibold text-gray-800">
                        School Events
                    </h3>
                    <div v-if="!events || events.length === 0" class="text-[10px] text-gray-500">
                        No school events captured.
                    </div>
                    <ul v-else class="mt-1 space-y-1">
                        <li
                            v-for="event in events"
                            :key="event.id"
                            class="flex items-start gap-2"
                        >
                            <div class="mt-0.5 h-2 w-2 rounded-full bg-emerald-500"></div>
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
    </AuthenticatedLayout>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }

    #calendar-preview,
    #calendar-preview * {
        visibility: visible;
    }

    #calendar-preview {
        position: absolute;
        inset: 0;
        width: 100%;
        margin: 0;
        padding: 0.5cm;
        box-shadow: none;
        border: none;
    }

    #calendar-preview .year-grid-print {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.5rem;
    }
}
</style>
