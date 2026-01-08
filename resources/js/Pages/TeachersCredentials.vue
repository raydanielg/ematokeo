<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    teachersWithVipindi: {
        type: Array,
        default: () => [],
    },
    allTeachers: {
        type: Array,
        default: () => [],
    },
    defaultPassword: {
        type: String,
        default: 'amss2025',
    },
});

const query = ref('');
const ajaxQuery = ref('');
const ajaxResults = ref([]);
const ajaxLoading = ref(false);
let ajaxTimer = null;

const norm = (v) => String(v || '').toLowerCase().trim();

const filteredVipindi = computed(() => {
    const q = norm(query.value);
    if (!q) return props.teachersWithVipindi || [];
    return (props.teachersWithVipindi || []).filter((t) => {
        return norm(t.name).includes(q) || norm(t.email).includes(q) || norm(t.check_number).includes(q) || norm(t.phone).includes(q);
    });
});

const filteredAll = computed(() => {
    const q = norm(query.value);
    if (!q) return props.allTeachers || [];
    return (props.allTeachers || []).filter((t) => {
        return norm(t.name).includes(q) || norm(t.email).includes(q) || norm(t.check_number).includes(q) || norm(t.phone).includes(q);
    });
});

const resettingId = ref(null);

const resetPassword = (teacher) => {
    if (!teacher?.id) return;
    const ok = window.confirm(`Reset password ya ${teacher.name} iwe \"${props.defaultPassword}\"?`);
    if (!ok) return;

    resettingId.value = teacher.id;
    router.post(route('teachers.credentials.reset-password', teacher.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            resettingId.value = null;
        },
    });
};

const openTeacherDetails = (teacherId) => {
    if (!teacherId) return;
    router.get(route('teachers.details', teacherId), {}, { preserveScroll: true });
};

watch(
    () => ajaxQuery.value,
    (val) => {
        const q = String(val || '').trim();
        if (ajaxTimer) {
            clearTimeout(ajaxTimer);
            ajaxTimer = null;
        }
        if (q.length < 2) {
            ajaxResults.value = [];
            ajaxLoading.value = false;
            return;
        }

        ajaxTimer = setTimeout(async () => {
            ajaxLoading.value = true;
            try {
                const res = await fetch(route('teachers.search', { q }), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                });
                const json = await res.json();
                ajaxResults.value = Array.isArray(json?.data) ? json.data : [];
            } catch {
                ajaxResults.value = [];
            } finally {
                ajaxLoading.value = false;
            }
        }, 250);
    }
);
</script>

<template>
    <Head title="Teacher Credentials" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">Teacher Credentials</h2>
                <p class="mt-1 text-sm text-gray-500">Reset passwords and view login details for teachers.</p>
            </div>
        </template>

        <div class="space-y-6">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Default Password</h3>
                        <p class="mt-1 text-[11px] text-gray-600">Kwa sasa mfumo utaweka password ya reset kuwa:</p>
                        <div class="mt-2 inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                            {{ props.defaultPassword }}
                        </div>
                    </div>

                    <div class="w-full md:w-80">
                        <label class="mb-1 block text-[11px] font-medium text-gray-700">Teacher search (AJAX)</label>
                        <div class="relative">
                            <input
                                v-model="ajaxQuery"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="Type name/email/check no..."
                            />
                            <div
                                v-if="ajaxLoading || ajaxResults.length"
                                class="absolute z-10 mt-1 w-full overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg"
                            >
                                <div v-if="ajaxLoading" class="px-3 py-2 text-[11px] text-gray-500">Searching...</div>
                                <button
                                    v-for="t in ajaxResults"
                                    :key="t.id"
                                    type="button"
                                    class="flex w-full items-start justify-between gap-2 px-3 py-2 text-left text-[11px] hover:bg-emerald-50"
                                    @click="openTeacherDetails(t.id)"
                                >
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ t.name }}</div>
                                        <div class="text-[10px] text-gray-500">{{ t.email }}</div>
                                    </div>
                                    <div class="text-[10px] text-gray-400">{{ t.check_number || '' }}</div>
                                </button>
                                <div v-if="!ajaxLoading && !ajaxResults.length" class="px-3 py-2 text-[11px] text-gray-500">No results.</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="mb-1 block text-[11px] font-medium text-gray-700">Filter tables (local)</label>
                            <input
                                v-model="query"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="Filter by name, email, phone, check no..."
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Walimu wenye vipindi tu</h3>
                        <p class="mt-1 text-[11px] text-gray-500">Hawa ni walimu waliopangiwa vipindi (waliopo kwenye timetable weekly limits).</p>
                    </div>
                    <div class="text-[11px] text-gray-500">Total: <span class="font-semibold text-gray-700">{{ filteredVipindi.length }}</span></div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-[11px]">
                        <thead>
                            <tr class="border-b bg-slate-50 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-600">
                                <th class="px-3 py-2">Name</th>
                                <th class="px-3 py-2">Email</th>
                                <th class="px-3 py-2">Phone</th>
                                <th class="px-3 py-2">Check No.</th>
                                <th class="px-3 py-2 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!filteredVipindi.length">
                                <td colspan="5" class="px-3 py-3 text-[11px] text-gray-500">Hakuna mwalimu mwenye vipindi bado.</td>
                            </tr>
                            <tr v-for="t in filteredVipindi" :key="t.id" class="border-b last:border-b-0">
                                <td class="px-3 py-2 font-medium text-gray-900">
                                    <button type="button" class="text-emerald-700 hover:underline" @click="openTeacherDetails(t.id)">
                                        {{ t.name }}
                                    </button>
                                </td>
                                <td class="px-3 py-2 text-gray-700">{{ t.email }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ t.phone || '-' }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ t.check_number || '-' }}</td>
                                <td class="px-3 py-2 text-right">
                                    <button
                                        type="button"
                                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-60"
                                        :disabled="resettingId === t.id"
                                        @click="resetPassword(t)"
                                    >
                                        {{ resettingId === t.id ? 'Resetting...' : 'Reset password' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Walimu wote (role: teacher)</h3>
                        <p class="mt-1 text-[11px] text-gray-500">Hii ni list ya walimu wote walio kwenye mfumo. Unaweza ku-reset password yao pia.</p>
                    </div>
                    <div class="text-[11px] text-gray-500">Total: <span class="font-semibold text-gray-700">{{ filteredAll.length }}</span></div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-[11px]">
                        <thead>
                            <tr class="border-b bg-slate-50 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-600">
                                <th class="px-3 py-2">Name</th>
                                <th class="px-3 py-2">Email</th>
                                <th class="px-3 py-2">Phone</th>
                                <th class="px-3 py-2">Check No.</th>
                                <th class="px-3 py-2 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!filteredAll.length">
                                <td colspan="5" class="px-3 py-3 text-[11px] text-gray-500">Hakuna walimu kwenye mfumo.</td>
                            </tr>
                            <tr v-for="t in filteredAll" :key="t.id" class="border-b last:border-b-0">
                                <td class="px-3 py-2 font-medium text-gray-900">
                                    <button type="button" class="text-emerald-700 hover:underline" @click="openTeacherDetails(t.id)">
                                        {{ t.name }}
                                    </button>
                                </td>
                                <td class="px-3 py-2 text-gray-700">{{ t.email }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ t.phone || '-' }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ t.check_number || '-' }}</td>
                                <td class="px-3 py-2 text-right">
                                    <button
                                        type="button"
                                        class="rounded-md bg-slate-900 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-60"
                                        :disabled="resettingId === t.id"
                                        @click="resetPassword(t)"
                                    >
                                        {{ resettingId === t.id ? 'Resetting...' : 'Reset password' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
