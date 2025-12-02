<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    hostels: {
        type: Array,
        default: () => [],
    },
    rooms: {
        type: Array,
        default: () => [],
    },
});

const hostelName = ref('');
const hostelType = ref('mixed');

const showAddModal = ref(false);
const showAddHostelModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);

const form = useForm({
    hostel_id: '',
    name: '',
    capacity: '',
    occupied: '',
    available: '',
    notes: '',
});

const hostelForm = useForm({
    name: '',
    type: 'mixed',
    description: '',
});

const editForm = useForm({
    id: null,
    hostel_id: '',
    name: '',
    capacity: '',
    occupied: '',
    available: '',
    notes: '',
});

const roomBeingDeleted = ref(null);

const totalBeds = computed(() =>
    (props.rooms || []).reduce((sum, room) => sum + (Number(room.capacity) || 0), 0),
);

const totalOccupied = computed(() =>
    (props.rooms || []).reduce((sum, room) => sum + (Number(room.occupied) || 0), 0),
);

const totalAvailable = computed(() => {
    const available = (props.rooms || []).reduce(
        (sum, room) => sum + (Number(room.available) || 0),
        0,
    );
    if (available) return available;
    return totalBeds.value - totalOccupied.value;
});

const openAddModal = () => {
    const firstHostel = (props.hostels || [])[0];
    form.hostel_id = firstHostel ? String(firstHostel.id) : '';
    form.name = '';
    form.capacity = '';
    form.occupied = '';
    form.available = '';
    form.notes = '';
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
};

const submitRoom = () => {
    form.post(route('hostel-rooms-beds.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
        },
    });
};

const openEditModal = (room) => {
    editForm.id = room.id;
    editForm.hostel_id = room.hostel_id ? String(room.hostel_id) : '';
    editForm.name = room.name || '';
    editForm.capacity = room.capacity ?? '';
    editForm.occupied = room.occupied ?? '';
    editForm.available = room.available ?? '';
    editForm.notes = room.notes || '';
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
};

const submitEdit = () => {
    if (!editForm.id) return;
    editForm.put(route('hostel-rooms-beds.update', editForm.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
};

const openDeleteModal = (room) => {
    roomBeingDeleted.value = room;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    roomBeingDeleted.value = null;
};

const confirmDelete = () => {
    if (!roomBeingDeleted.value) return;
    router.delete(route('hostel-rooms-beds.destroy', roomBeingDeleted.value.id), {
        preserveScroll: true,
        onFinish: () => {
            closeDeleteModal();
        },
    });
};

const openAddHostelModal = () => {
    hostelForm.name = '';
    hostelForm.type = 'mixed';
    hostelForm.description = '';
    showAddHostelModal.value = true;
};

const closeAddHostelModal = () => {
    showAddHostelModal.value = false;
};

const submitHostel = () => {
    hostelForm.post(route('hostels.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddHostelModal.value = false;
        },
    });
};
</script>

<template>
    <Head title="Hostel Rooms & Beds" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Rooms & Beds
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        View hostel rooms, bed capacity, and occupancy status.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-[11px] font-semibold text-emerald-700 shadow-sm ring-1 ring-emerald-200 hover:bg-emerald-50"
                        @click="openAddHostelModal"
                    >
                        + Add Hostel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                        @click="openAddModal"
                    >
                        + Add Room
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div class="grid gap-3 md:grid-cols-3">
                <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100 md:col-span-2">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex-1 min-w-[200px]">
                            <label class="text-[11px] font-medium text-gray-600">Hostel name</label>
                            <input
                                v-model="hostelName"
                                type="text"
                                class="mt-1 w-full rounded-md border border-dashed border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="e.g. Boys Hostel A, Girls Hostel B (display only for now)"
                            />
                        </div>
                        <div class="min-w-[160px]">
                            <label class="text-[11px] font-medium text-gray-600">Hostel type</label>
                            <select
                                v-model="hostelType"
                                class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-[11px] text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option value="boys">Boys</option>
                                <option value="girls">Girls</option>
                                <option value="mixed">Mixed</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl bg-white px-4 py-3 text-[11px] text-gray-700 shadow-sm ring-1 ring-gray-100">
                    <p class="text-[11px] font-semibold text-gray-600">Quick summary</p>
                    <p class="mt-1 text-[11px] text-gray-500">
                        Total beds and occupancy based on the rooms listed below.
                    </p>
                    <div class="mt-2 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Total beds</span>
                            <span class="font-semibold text-gray-900">{{ totalBeds }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Occupied</span>
                            <span class="font-semibold text-emerald-700">{{ totalOccupied }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Available</span>
                            <span class="font-semibold text-amber-700">{{ totalAvailable }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <table class="min-w-full border-collapse text-left text-[11px]">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">#</th>
                            <th class="px-3 py-2">Hostel</th>
                            <th class="px-3 py-2">Room</th>
                            <th class="px-3 py-2 text-right">Beds</th>
                            <th class="px-3 py-2 text-right">Occupied</th>
                            <th class="px-3 py-2 text-right">Available</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!rooms.length">
                            <td colspan="6" class="px-3 py-6 text-center text-[11px] text-gray-400">
                                No rooms have been configured yet.
                            </td>
                        </tr>
                        <tr
                            v-for="(room, index) in rooms"
                            :key="room.id || index"
                            class="border-b border-gray-100 text-[11px] text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top">{{ index + 1 }}</td>
                            <td class="px-3 py-2 align-top">{{ room.hostel_name || '-' }}</td>
                            <td class="px-3 py-2 align-top">{{ room.name || '-' }}</td>
                            <td class="px-3 py-2 align-top text-right">{{ room.capacity || 0 }}</td>
                            <td class="px-3 py-2 align-top text-right">{{ room.occupied || 0 }}</td>
                            <td class="px-3 py-2 align-top text-right">{{ room.available || 0 }}</td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex items-center justify-end gap-1 text-gray-500">
                                    <button
                                        type="button"
                                        class="inline-flex h-6 w-6 items-center justify-center rounded-full hover:bg-emerald-50 hover:text-emerald-700"
                                        title="Edit"
                                        @click="openEditModal(room)"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-1-1v2m-6 6h.01M7 7h.01M17 7h.01M7 17h.01M17 17h.01M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-6 w-6 items-center justify-center rounded-full hover:bg-rose-50 hover:text-rose-700"
                                        title="Delete"
                                        @click="openDeleteModal(room)"
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
    </AuthenticatedLayout>

    <!-- Add room modal -->
    <div
        v-if="showAddModal"
        class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-sm"
    >
        <div class="w-full max-w-xl rounded-2xl bg-white p-5 text-xs text-gray-700 shadow-2xl">
            <div class="mb-3 flex items-start justify-between gap-4 border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-sm font-semibold text-emerald-800">
                        Add Hostel Room
                    </h3>
                    <p class="mt-1 text-[11px] text-gray-500">
                        Fill in details for this room. Saving to the database will be wired later.
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
                <div class="md:col-span-2">
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
                    <label class="text-[11px] font-medium text-emerald-700">Room name / number</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. Room 1, Dorm B-2"
                    />
                </div>
                <div>
                    <label class="text-[11px] font-medium text-gray-600">Total beds (capacity)</label>
                    <input
                        v-model="form.capacity"
                        type="number"
                        min="0"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. 8"
                    />
                </div>
                <div>
                    <label class="text-[11px] font-medium text-gray-600">Currently occupied</label>
                    <input
                        v-model="form.occupied"
                        type="number"
                        min="0"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. 6"
                    />
                </div>
                <div>
                    <label class="text-[11px] font-medium text-gray-600">Available beds</label>
                    <input
                        v-model="form.available"
                        type="number"
                        min="0"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. 2"
                    />
                </div>
                <div>
                    <label class="text-[11px] font-medium text-gray-600">Notes (optional)</label>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        class="mt-1 w-full resize-none rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="Any special notes about this room (e.g. prefects, special needs)."
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
                    :disabled="form.processing || !form.hostel_id || !form.name || !form.capacity"
                    @click="submitRoom"
                >
                    Save
                </button>
            </div>
        </div>
    </div>

    <!-- Edit room modal -->
    <div
        v-if="showEditModal"
        class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-sm"
    >
        <div class="w-full max-w-xl rounded-2xl bg-white p-5 text-xs text-gray-700 shadow-2xl">
            <div class="mb-3 flex items-start justify-between gap-4 border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-sm font-semibold text-emerald-800">
                        Edit Hostel Room
                    </h3>
                    <p class="mt-1 text-[11px] text-gray-500">
                        Update details for this room.
                    </p>
                </div>
                <button
                    type="button"
                    class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[11px] font-bold text-gray-600 hover:bg-gray-200"
                    @click="closeEditModal"
                >
                    ×
                </button>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="text-[11px] font-medium text-emerald-700">Hostel</label>
                    <select
                        v-model="editForm.hostel_id"
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
                    <label class="text-[11px] font-medium text-emerald-700">Room name / number</label>
                    <input
                        v-model="editForm.name"
                        type="text"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. Room 1, Dorm B-2"
                    />
                </div>
                <div>
                    <label class="text-[11px] font-medium text-gray-600">Total beds (capacity)</label>
                    <input
                        v-model="editForm.capacity"
                        type="number"
                        min="0"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. 8"
                    />
                </div>
                <div>
                    <label class="text-[11px] font-medium text-gray-600">Currently occupied</label>
                    <input
                        v-model="editForm.occupied"
                        type="number"
                        min="0"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. 6"
                    />
                </div>
                <div>
                    <label class="text-[11px] font-medium text-gray-600">Available beds</label>
                    <input
                        v-model="editForm.available"
                        type="number"
                        min="0"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. 2"
                    />
                </div>
                <div>
                    <label class="text-[11px] font-medium text-gray-600">Notes (optional)</label>
                    <textarea
                        v-model="editForm.notes"
                        rows="2"
                        class="mt-1 w-full resize-none rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="Any special notes about this room (e.g. prefects, special needs)."
                    ></textarea>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2 text-[11px]">
                <button
                    type="button"
                    class="rounded-md bg-gray-50 px-3 py-1.5 font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                    @click="closeEditModal"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    class="rounded-md bg-emerald-600 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="editForm.processing || !editForm.hostel_id || !editForm.name || !editForm.capacity"
                    @click="submitEdit"
                >
                    Save
                </button>
            </div>
        </div>
    </div>

    <!-- Delete room confirmation modal -->
    <div
        v-if="showDeleteModal && roomBeingDeleted"
        class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-sm"
    >
        <div class="w-full max-w-sm rounded-2xl bg-white p-5 text-xs text-gray-700 shadow-2xl">
            <div class="mb-3 flex items-start justify-between gap-4 border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-sm font-semibold text-rose-700">
                        Thibitisha kufuta chumba
                    </h3>
                    <p class="mt-1 text-[11px] text-gray-500">
                        Una uhakika unataka kufuta chumba hiki cha hostel?
                        Hatua hii haiwezi kurudishwa nyuma.
                    </p>
                </div>
                <button
                    type="button"
                    class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[11px] font-bold text-gray-600 hover:bg-gray-200"
                    @click="closeDeleteModal"
                >
                    ×
                </button>
            </div>

            <div class="mb-4 rounded-md bg-rose-50 px-3 py-2 text-[11px] text-rose-700">
                <p>
                    <span class="font-semibold">Hostel:</span>
                    <span class="ml-1">{{ roomBeingDeleted.hostel_name || '-' }}</span>
                </p>
                <p class="mt-0.5">
                    <span class="font-semibold">Chumba:</span>
                    <span class="ml-1">{{ roomBeingDeleted.name || '-' }}</span>
                </p>
            </div>

            <div class="mt-2 flex justify-end gap-2 text-[11px]">
                <button
                    type="button"
                    class="rounded-md bg-gray-50 px-3 py-1.5 font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                    @click="closeDeleteModal"
                >
                    Ghairi
                </button>
                <button
                    type="button"
                    class="rounded-md bg-rose-600 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                    @click="confirmDelete"
                >
                    Ndio, futa
                </button>
            </div>
        </div>
    </div>

    <!-- Add hostel modal -->
    <div
        v-if="showAddHostelModal"
        class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-sm"
    >
        <div class="w-full max-w-xl rounded-2xl bg-white p-5 text-xs text-gray-700 shadow-2xl">
            <div class="mb-3 flex items-start justify-between gap-4 border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-sm font-semibold text-emerald-800">
                        Add Hostel
                    </h3>
                    <p class="mt-1 text-[11px] text-gray-500">
                        Create a new hostel with its type (boys, girls or mixed).
                    </p>
                </div>
                <button
                    type="button"
                    class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[11px] font-bold text-gray-600 hover:bg-gray-200"
                    @click="closeAddHostelModal"
                >
                    ×
                </button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="text-[11px] font-medium text-emerald-700">Hostel name</label>
                    <input
                        v-model="hostelForm.name"
                        type="text"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. Boys Hostel A"
                    />
                </div>
                <div>
                    <label class="text-[11px] font-medium text-gray-600">Hostel type</label>
                    <select
                        v-model="hostelForm.type"
                        class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                    >
                        <option value="boys">Boys</option>
                        <option value="girls">Girls</option>
                        <option value="mixed">Mixed</option>
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-medium text-gray-600">Description (optional)</label>
                    <textarea
                        v-model="hostelForm.description"
                        rows="2"
                        class="mt-1 w-full resize-none rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="Short description of this hostel."
                    ></textarea>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2 text-[11px]">
                <button
                    type="button"
                    class="rounded-md bg-gray-50 px-3 py-1.5 font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                    @click="closeAddHostelModal"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    class="rounded-md bg-emerald-600 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="hostelForm.processing || !hostelForm.name"
                    @click="submitHostel"
                >
                    Save
                </button>
            </div>
        </div>
    </div>
</template>
