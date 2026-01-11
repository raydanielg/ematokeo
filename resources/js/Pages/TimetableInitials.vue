<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    schoolName: {
        type: String,
        default: 'School',
    },
    selectedYear: {
        type: String,
        default: '',
    },
    years: {
        type: Array,
        default: () => [],
    },
    teachers: {
        type: Array,
        default: () => [],
    },
});

const year = ref(String(props.selectedYear || ''));

watch(() => props.selectedYear, (v) => {
    if (!year.value) year.value = String(v || '');
});

watch(year, (v) => {
    const y = String(v || '').trim();
    if (!y) return;

    router.get(route('timetables.initials'), { year: y }, { preserveScroll: true, preserveState: true });
});

const teacherRows = computed(() => {
    return (props.teachers || [])
        .map((t) => {
            const subjects = (t?.subjects || [])
                .map((s) => {
                    const code = String(s?.subject_code || '').trim();
                    const name = String(s?.subject_name || '').trim();
                    return code && name ? `${code} - ${name}` : (code || name || '');
                })
                .filter(Boolean);

            return {
                id: Number(t?.id || 0),
                initials: String(t?.initials || '').trim(),
                name: String(t?.name || '').trim(),
                subjects,
            };
        })
        .filter((t) => t.id && (t.initials || t.name));
});

const printPage = () => {
    window.print();
};
</script>

<template>
    <Head title="Teacher Initials" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Teacher Initials
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Orodha ya initials za walimu na masomo yao kwa mwaka wa masomo.
                    </p>
                </div>

                <div class="flex items-center gap-2 print:hidden">
                    <select
                        v-model="year"
                        class="rounded-md border border-gray-300 bg-white px-2 py-2 text-xs text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                    >
                        <option v-for="y in years" :key="y" :value="y">
                            {{ y }}
                        </option>
                    </select>

                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                        @click="printPage"
                    >
                        Print
                    </button>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-5xl space-y-4">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <div class="text-center">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-800">
                        {{ schoolName }}
                    </div>
                    <div class="mt-1 text-sm font-semibold uppercase tracking-wide text-emerald-800">
                        ORODHA YA INITIALS ZA WALIMU
                    </div>
                    <div class="mt-1 text-[11px] text-gray-600">
                        Hizi ni initials za walimu wa shule hii kwa mwaka wa masomo
                        <span class="font-semibold">{{ year || selectedYear }}</span>.
                        Tumia orodha hii kuandika initials kwenye ratiba za vipindi.
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
                    <div class="text-sm font-semibold text-gray-800">
                        Walimu ({{ teacherRows.length }})
                    </div>
                    <div class="text-[11px] text-gray-500 print:hidden">
                        Tip: Bonyeza Print kupata nakala ya kuchapisha.
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-xs">
                        <thead>
                            <tr class="bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                                <th class="px-4 py-2">Initials</th>
                                <th class="px-4 py-2">Teacher</th>
                                <th class="px-4 py-2">Subjects</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="teacherRows.length === 0">
                                <td colspan="3" class="px-4 py-6 text-center text-xs text-gray-500">
                                    Hakuna taarifa za walimu/masomo zilizopatikana.
                                </td>
                            </tr>
                            <tr
                                v-for="t in teacherRows"
                                :key="t.id"
                                class="border-t border-gray-100 align-top hover:bg-gray-50"
                            >
                                <td class="px-4 py-2">
                                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-800 ring-1 ring-emerald-100">
                                        {{ t.initials || '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 font-medium text-gray-900">
                                    {{ t.name || '—' }}
                                </td>
                                <td class="px-4 py-2">
                                    <div v-if="t.subjects.length" class="flex flex-wrap gap-1">
                                        <span
                                            v-for="s in t.subjects"
                                            :key="`${t.id}-${s}`"
                                            class="rounded bg-slate-50 px-2 py-0.5 text-[11px] text-slate-700 ring-1 ring-slate-100"
                                        >
                                            {{ s }}
                                        </span>
                                    </div>
                                    <span v-else class="text-gray-500">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl bg-white p-4 text-[11px] text-gray-600 shadow-sm ring-1 ring-gray-100">
                <div class="font-semibold text-gray-800">Maelezo</div>
                <div class="mt-1">
                    Initials zinatengenezwa kutoka herufi za mwanzo za majina ya mwalimu (mfano: "John Paul" = "JP").
                    Kama kuna walimu wenye initials zinazofanana, ni vizuri kurekebisha majina yao ili initials zitofautiane.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
