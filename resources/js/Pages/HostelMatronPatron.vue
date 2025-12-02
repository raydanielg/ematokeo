<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    staff: {
        type: Array,
        default: () => [],
    },
    hostels: {
        type: Array,
        default: () => [],
    },
});

const showAddModal = ref(false);
const showSuccessOverlay = ref(false);
const showViewModal = ref(false);
const selectedStaff = ref(null);

const form = useForm({
    hostel_id: '',
    role: 'matron',
    name: '',
    phone: '',
    email: '',
    password: '',
    notes: '',
});

const openAddModal = () => {
    const firstHostel = (props.hostels || [])[0];
    form.hostel_id = firstHostel ? String(firstHostel.id) : '';
    form.role = 'matron';
    form.name = '';
    form.phone = '';
    form.email = '';
    form.password = '';
    form.notes = '';
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
};

const submitStaff = () => {
    form.post(route('hostel-matron-patron.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
            form.reset('password');
            showSuccessOverlay.value = true;
        },
    });
};

const openViewModal = (person) => {
	selectedStaff.value = person;
	showViewModal.value = true;
};

const closeViewModal = () => {
	showViewModal.value = false;
	selectedStaff.value = null;
};
</script>

<template>
    <Head title="Matron & Patron" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Matron & Patron
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage hostel matrons and patrons, including their assigned hostels.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                        @click="openAddModal"
                    >
                        + Add Matron/Patron
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div
                v-if="$page.props.flash && $page.props.flash.success"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] text-emerald-900"
            >
                <div class="flex items-start gap-2">
                    <span class="mt-0.5 text-emerald-600">✔</span>
                    <div>
                        <p class="font-semibold">Imehifadhiwa</p>
                        <p class="text-[11px] text-emerald-800">{{ $page.props.flash.success }}</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <table class="min-w-full border-collapse text-left text-[11px]">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">#</th>
                            <th class="px-3 py-2">Name</th>
                            <th class="px-3 py-2">Role</th>
                            <th class="px-3 py-2">Hostel</th>
                            <th class="px-3 py-2">Phone</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!staff.length">
                            <td colspan="6" class="px-3 py-6 text-center text-[11px] text-gray-400">
                                No matron or patron records yet.
                            </td>
                        </tr>
                        <tr
                            v-for="(person, index) in staff"
                            :key="person.id || index"
                            class="border-b border-gray-100 text-[11px] text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top">{{ index + 1 }}</td>
                            <td class="px-3 py-2 align-top">{{ person.name || '-' }}</td>
                            <td class="px-3 py-2 align-top">{{ person.role || '-' }}</td>
                            <td class="px-3 py-2 align-top">{{ person.hostel_name || '-' }}</td>
                            <td class="px-3 py-2 align-top">{{ person.phone || '-' }}</td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex items-center justify-end gap-1 text-gray-500">
                                    <button
                                        type="button"
                                        class="inline-flex h-6 w-6 items-center justify-center rounded-full hover:bg-emerald-50 hover:text-emerald-700"
                                        title="Details"
                                        @click="openViewModal(person)"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <Link
                                        :href="route('hostel-matron-patron.contract', person.id)"
                                        class="inline-flex h-6 w-6 items-center justify-center rounded-full hover:bg-indigo-50 hover:text-indigo-700"
                                        title="Mkataba"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4h10a2 2 0 012 2v11a1 1 0 01-.293.707l-3 3A1 1 0 0115 21H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                                        </svg>
                                    </Link>
                                    <button
                                        type="button"
                                        class="inline-flex h-6 w-6 items-center justify-center rounded-full hover:bg-emerald-50 hover:text-emerald-700"
                                        title="Edit"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-1-1v2m-6 6h.01M7 7h.01M17 7h.01M7 17h.01M17 17h.01M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-6 w-6 items-center justify-center rounded-full hover:bg-rose-50 hover:text-rose-700"
                                        title="Delete"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add matron/patron modal (frontend only for now) -->
        <div
            v-if="showAddModal"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-xl rounded-2xl bg-white p-5 text-xs text-gray-700 shadow-2xl">
                <div class="mb-3 flex items-start justify-between gap-4 border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-emerald-800">
                            Add Matron / Patron
                        </h3>
                        <p class="mt-1 text-[11px] text-gray-500">
                            Capture basic details for the staff responsible for a specific hostel.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[11px] font-bold text-gray-600 hover:bg-gray-200"
                        @click="closeAddModal"
                    >
                        ×
                    </button>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="text-[11px] font-medium text-emerald-700">Hostel</label>
                        <select
                            v-model="form.hostel_id"
                            class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="" disabled>Select hostel</option>
                            <option
                                v-for="hostel in hostels"
                                :key="hostel.id"
                                :value="String(hostel.id)"
                            >
                                {{ hostel.name }} ({{ hostel.type }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] font-medium text-emerald-700">Role</label>
                        <select
                            v-model="form.role"
                            class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="matron">Matron</option>
                            <option value="patron">Patron</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[11px] font-medium text-gray-600">Full name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="e.g. Jane Doe"
                        />
                    </div>
                    <div>
                        <label class="text-[11px] font-medium text-gray-600">Phone number</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="e.g. 07xx xxx xxx"
                        />
                    </div>
                    <div>
                        <label class="text-[11px] font-medium text-gray-600">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="e.g. matron@example.com"
                        />
                    </div>
                    <div>
                        <label class="text-[11px] font-medium text-gray-600">Password</label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="Temporary password"
                        />
                    </div>
                    <div>
                        <label class="text-[11px] font-medium text-gray-600">Notes (optional)</label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            class="mt-1 w-full resize-none rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="Extra information about this staff member."
                        ></textarea>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2 text-[11px]">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="closeAddModal"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="form.processing || !form.hostel_id || !form.name || !form.phone || !form.email"
                        @click="submitStaff"
                    >
                        Save
                    </button>
                </div>
            </div>
        </div>

        <!-- Success animation overlay -->
        <div
            v-if="showSuccessOverlay && $page.props.flash && $page.props.flash.success"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6 backdrop-blur-sm"
        >
            <div class="relative w-full max-w-xs rounded-2xl bg-white px-4 py-5 text-center text-xs text-gray-700 shadow-2xl">
                <button
                    type="button"
                    class="absolute right-2 top-2 inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[11px] font-bold text-gray-600 hover:bg-gray-200"
                    @click="showSuccessOverlay = false"
                >
                    ×
                </button>
                <div class="flex flex-col items-center gap-2">
                    <lottie-player
                        src="/success.json"
                        background="transparent"
                        speed="1"
                        style="width: 120px; height: 120px;"
                        autoplay
                    ></lottie-player>
                    <p class="font-semibold text-emerald-800">Imehifadhiwa kikamilifu</p>
                    <p class="text-[11px] text-gray-600">
                        {{ $page.props.flash.success }}
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
