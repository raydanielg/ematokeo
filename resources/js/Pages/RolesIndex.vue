<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    roles: {
        type: Array,
        default: () => [],
    },
});

const savingUserIds = ref(new Set());

const roleOptions = computed(() => (props.roles || []).map((r) => ({
    key: r.key,
    name: r.name,
})));

const onRoleChange = (user, ev) => {
    const value = ev?.target?.value ?? 'teacher';
    if (savingUserIds.value.has(user.id)) return;

    savingUserIds.value = new Set([...savingUserIds.value, user.id]);

    router.post(route('roles.assign'), {
        user_id: user.id,
        role: value,
    }, {
        preserveScroll: true,
        onFinish: () => {
            const next = new Set(savingUserIds.value);
            next.delete(user.id);
            savingUserIds.value = next;
        },
    });
};

const isSaving = (userId) => savingUserIds.value.has(userId);
</script>

<template>
    <Head title="Roles" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">Roles & Permissions</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Assign roles to your staff/teachers. Default role is <span class="font-semibold">Teacher</span>.
                    </p>
                </div>
            </div>
        </template>

        <div class="rounded-2xl bg-slate-50/70 p-4 ring-1 ring-slate-200">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-800">Users ({{ users.length }})</p>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Phone</th>
                                    <th class="px-4 py-3">Role</th>
                                    <th class="px-4 py-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!users.length">
                                    <td colspan="5" class="px-4 py-10 text-center">
                                        <div class="mx-auto max-w-sm">
                                            <div class="text-sm font-semibold text-gray-800">No users found</div>
                                            <div class="mt-1 text-xs text-gray-500">Add teachers/users first, then assign roles here.</div>
                                        </div>
                                    </td>
                                </tr>

                                <tr
                                    v-for="(u, idx) in users"
                                    :key="u.id"
                                    :class="[
                                        'border-b border-gray-100 text-xs text-gray-700',
                                        idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/40',
                                        'hover:bg-emerald-50/40'
                                    ]"
                                >
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ u.name }}</div>
                                        <div class="text-[10px] text-gray-500">ID: {{ u.id }}</div>
                                    </td>
                                    <td class="px-4 py-3">{{ u.email || '—' }}</td>
                                    <td class="px-4 py-3">{{ u.phone || '—' }}</td>
                                    <td class="px-4 py-3">
                                        <select
                                            class="w-full rounded-md border border-gray-300 px-2 py-1 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                            :disabled="isSaving(u.id)"
                                            :value="u.role || 'teacher'"
                                            @change="onRoleChange(u, $event)"
                                        >
                                            <option v-for="r in roleOptions" :key="r.key" :value="r.key">
                                                {{ r.name }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span
                                            v-if="isSaving(u.id)"
                                            class="inline-flex items-center rounded-full bg-amber-50 px-2 py-1 text-[10px] font-semibold text-amber-800 ring-1 ring-amber-100"
                                        >
                                            Saving...
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-800 ring-1 ring-emerald-100"
                                        >
                                            Ready
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
