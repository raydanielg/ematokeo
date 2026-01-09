<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    timetable: {
        type: Object,
        default: () => ({}),
    },
    teacherInitials: {
        type: String,
        default: '',
    },
    periods: {
        type: Array,
        default: () => [],
    },
});

const count = computed(() => (Array.isArray(props.periods) ? props.periods.length : 0));

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

    return map[idx] || `Slot ${idx + 1}`;
};

const days = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY'];
const slotIndices = [0, 1, 2, 3, 4, 5, 6, 7, 8];

const sortedPeriods = computed(() => {
    const list = Array.isArray(props.periods) ? [...props.periods] : [];
    const dayOrder = { MONDAY: 1, TUESDAY: 2, WEDNESDAY: 3, THURSDAY: 4, FRIDAY: 5 };

    return list.sort((a, b) => {
        const da = dayOrder[String(a.day || '').toUpperCase()] || 99;
        const db = dayOrder[String(b.day || '').toUpperCase()] || 99;
        if (da !== db) return da - db;

        const sa = Number(a.slot_index || 0);
        const sb = Number(b.slot_index || 0);
        if (sa !== sb) return sa - sb;

        const ca = String(a.class_label || '').localeCompare(String(b.class_label || ''));
        if (ca !== 0) return ca;

        const streamCmp = String(a.stream || '').localeCompare(String(b.stream || ''));
        if (streamCmp !== 0) return streamCmp;

        return Number(a.slot_index || 0) - Number(b.slot_index || 0);
    });
});

const periodsByDaySlot = computed(() => {
    const out = {};
    days.forEach((d) => {
        out[d] = {};
    });

    (Array.isArray(props.periods) ? props.periods : []).forEach((p) => {
        const day = String(p?.day || '').toUpperCase();
        const slot = Number(p?.slot_index);
        if (!days.includes(day)) return;
        if (!Number.isFinite(slot)) return;

        if (!out[day][slot]) out[day][slot] = [];
        out[day][slot].push({
            subject: p?.subject || '',
            class_label: p?.class_label || '',
            stream: p?.stream || '',
        });
    });

    return out;
});
</script>

<template>
    <Head title="My Periods" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">My Periods</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Showing periods for initials: <span class="font-semibold text-gray-800">{{ teacherInitials || '—' }}</span>
                        <span v-if="timetable?.title"> · Timetable: <span class="font-semibold text-gray-800">{{ timetable.title }}</span></span>
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                    @click="router.get(route('teacher.timetables.my'))"
                >
                    Back
                </button>
            </div>
        </template>

        <div class="space-y-4">
            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="text-sm font-semibold text-gray-800">Summary</div>
                <div class="mt-1 text-sm text-gray-600">
                    Total periods found: <span class="font-semibold text-gray-800">{{ count }}</span>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 px-5 py-3">
                    <div class="text-sm font-semibold text-gray-800">Weekly Preview</div>
                    <div class="mt-1 text-xs text-gray-500">Showing only your periods (by initials) grouped by day and time.</div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-gray-600">Day</th>
                                <th
                                    v-for="i in slotIndices"
                                    :key="`hdr-${i}`"
                                    class="min-w-[140px] px-3 py-3 text-[11px] font-semibold uppercase tracking-wide text-gray-600"
                                >
                                    {{ slotTimeLabel(i) }}
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="d in days" :key="d" class="align-top hover:bg-gray-50">
                                <td class="whitespace-nowrap px-4 py-3 text-[11px] font-semibold text-gray-900">{{ d }}</td>
                                <td
                                    v-for="i in slotIndices"
                                    :key="`${d}-${i}`"
                                    class="px-3 py-2"
                                >
                                    <div v-if="periodsByDaySlot?.[d]?.[i]?.length" class="space-y-1">
                                        <div
                                            v-for="(p, idx) in periodsByDaySlot[d][i]"
                                            :key="`${d}-${i}-${idx}`"
                                            class="rounded-md bg-emerald-50 px-2 py-1 ring-1 ring-emerald-100"
                                        >
                                            <div class="text-[11px] font-semibold text-emerald-900">{{ p.subject }}</div>
                                            <div class="text-[10px] font-semibold text-gray-700">
                                                {{ p.class_label }}<span v-if="p.stream"> · {{ p.stream }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-[10px] text-gray-300">—</div>
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
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Day</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Class</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Stream</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Time</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">Subject</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!sortedPeriods.length">
                                <td colspan="5" class="px-5 py-6 text-center text-sm text-gray-500">
                                    No periods found for your initials in this timetable.
                                </td>
                            </tr>

                            <tr v-for="(p, idx) in sortedPeriods" :key="`${p.day}-${p.class_label}-${p.stream}-${p.slot_index}-${idx}`" class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-semibold text-gray-900">{{ p.day }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ p.class_label || '—' }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ p.stream || '—' }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ slotTimeLabel(p.slot_index) }}</td>
                                <td class="px-5 py-3 font-semibold text-emerald-800">{{ p.subject || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
