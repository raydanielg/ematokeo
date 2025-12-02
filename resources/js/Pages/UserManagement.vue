<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    schools: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: '',
    email: '',
    role: '',
    school_id: '',
});

const submit = () => {
    form.post(route('settings.user-management.store'));
};

const showDetailModal = ref(false);
const detailUserId = ref(null);

const showToggleModal = ref(false);
const toggleUserId = ref(null);

const userMap = computed(() => {
    const map = {};
    (props.users || []).forEach((u) => {
        map[u.id] = u;
    });
    return map;
});

const schoolMap = computed(() => {
    const map = {};
    (props.schools || []).forEach((s) => {
        map[s.id] = s;
    });
    return map;
});

const openDetails = (userId) => {
    detailUserId.value = userId;
    showDetailModal.value = true;
};

const openToggleConfirm = (userId) => {
    toggleUserId.value = userId;
    showToggleModal.value = true;
};

const confirmToggleUser = () => {
    if (!toggleUserId.value) return;
    const id = toggleUserId.value;
    showToggleModal.value = false;
    router.post(route('settings.user-management.toggle', id));
};
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        User Management
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage system users, roles and access status. (Permissions are placeholders for now.)
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-gray-700">
                        Add User
                    </p>
                </div>
                <form class="grid gap-3 text-xs md:grid-cols-4" @submit.prevent="submit">
                    <div class="md:col-span-1">
                        <label class="mb-1 block text-[11px] font-medium">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="Full name"
                        />
                    </div>
                    <div class="md:col-span-1">
                        <label class="mb-1 block text-[11px] font-medium">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="user@example.com"
                        />
                    </div>
                    <div class="md:col-span-1">
                        <label class="mb-1 block text-[11px] font-medium">Role</label>
                        <select
                            v-model="form.role"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="">Select role</option>
                            <option value="admin">Admin</option>
                            <option value="teacher">Teacher</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="md:col-span-1">
                        <label class="mb-1 block text-[11px] font-medium">School (optional)</label>
                        <select
                            v-model="form.school_id"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="">No school selected</option>
                            <option
                                v-for="school in schools"
                                :key="school.id"
                                :value="school.id"
                            >
                                {{ school.name }}
                            </option>
                        </select>
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                            :disabled="form.processing"
                        >
                            Save User
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-gray-700">
                        Users ({{ users.length }})
                    </p>
                    <div class="flex items-center gap-2 text-[11px] text-gray-500">
                        <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span>Active</span>
                        <span class="inline-flex h-2 w-2 rounded-full bg-gray-300"></span>
                        <span>Disabled</span>
                    </div>
                </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">Name</th>
                            <th class="px-3 py-2">Username / Email</th>
                            <th class="px-3 py-2">Role</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="users.length === 0">
                            <td colspan="5" class="px-3 py-4 text-center text-xs text-gray-500">
                                No users found yet.
                            </td>
                        </tr>
                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="border-b border-gray-100 text-xs text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top">
                                {{ user.name }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-col">
                                    <span class="text-gray-800">{{ user.username || user.email }}</span>
                                    <span class="text-[10px] text-gray-500">{{ user.email }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1"
                                    :class="user.role === 'admin' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-gray-50 text-gray-600 ring-gray-200'"
                                >
                                    {{ user.role || 'User' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium ring-1"
                                    :class="user.is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-gray-50 text-gray-600 ring-gray-200'"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        :class="user.is_active ? 'bg-emerald-500' : 'bg-gray-400'"
                                    ></span>
                                    {{ user.is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="rounded-md bg-blue-50 px-2 py-1 text-[11px] font-medium text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100"
                                        @click="openDetails(user.id)"
                                    >
                                        Details
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-gray-50 px-2 py-1 text-[11px] font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                                        @click="openToggleConfirm(user.id)"
                                    >
                                        {{ user.is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            </div>
        </div>

        <!-- Detail modal -->
        <div
            v-if="showDetailModal"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 sm:px-0"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-5 text-xs text-gray-700 shadow-lg ring-1 ring-gray-200">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">
                            User Details
                        </h3>
                        <p class="mt-0.5 text-[11px] text-gray-500">
                            Basic information for this account.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="text-[11px] text-gray-500 hover:text-gray-700"
                        @click="showDetailModal = false"
                    >
                        Close
                    </button>
                </div>

                <div v-if="detailUserId && userMap[detailUserId]" class="space-y-2 text-[11px]">
                    <div>
                        <span class="font-medium text-gray-700">Name:</span>
                        <span class="ml-1 text-gray-800">{{ userMap[detailUserId].name }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Email:</span>
                        <span class="ml-1 text-gray-800">{{ userMap[detailUserId].email }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Role:</span>
                        <span class="ml-1 text-gray-800">{{ userMap[detailUserId].role || 'User' }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Status:</span>
                        <span class="ml-1" :class="userMap[detailUserId].is_active ? 'text-emerald-700' : 'text-gray-600'">
                            {{ userMap[detailUserId].is_active ? 'Active' : 'Disabled' }}
                        </span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">School:</span>
                        <span class="ml-1 text-gray-800">
                            {{ userMap[detailUserId].school_id ? (schoolMap[userMap[detailUserId].school_id]?.name || 'Unknown') : 'Not assigned' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enable / Disable confirm modal -->
        <div
            v-if="showToggleModal && toggleUserId && userMap[toggleUserId]"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 sm:px-0"
        >
            <div class="w-full max-w-sm rounded-xl bg-white p-5 text-xs text-gray-700 shadow-lg ring-1 ring-gray-200">
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-gray-800">
                        {{ userMap[toggleUserId].is_active ? 'Disable User' : 'Enable User' }}
                    </h3>
                    <p class="mt-0.5 text-[11px] text-gray-500">
                        Are you sure you want to
                        {{ userMap[toggleUserId].is_active ? 'disable' : 'enable' }}
                        this account?
                    </p>
                </div>
                <div class="mb-3 rounded-md bg-gray-50 px-3 py-2 text-[11px] text-gray-700">
                    <p class="font-semibold text-gray-800">
                        {{ userMap[toggleUserId].name }}
                    </p>
                    <p class="text-[10px] text-gray-500">
                        {{ userMap[toggleUserId].email }}
                    </p>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-gray-100 px-3 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-gray-200"
                        @click="showToggleModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                        @click="confirmToggleUser"
                    >
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
