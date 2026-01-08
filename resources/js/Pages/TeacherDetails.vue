<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    teacher: {
        type: Object,
        required: true,
    },
    classes: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head :title="`Teacher Details - ${props.teacher?.name || ''}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">Teacher Details</h2>
                    <p class="mt-1 text-sm text-gray-500">Classes and streams assigned to this teacher.</p>
                </div>
                <Link
                    :href="route('teachers.credentials')"
                    class="rounded-md bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                >
                    Back to Credentials
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="grid gap-3 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">Name</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900">{{ props.teacher.name }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">Email</div>
                        <div class="mt-1 text-xs text-gray-700">{{ props.teacher.email }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">Phone</div>
                        <div class="mt-1 text-xs text-gray-700">{{ props.teacher.phone || '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Assigned classes</h3>
                        <p class="mt-1 text-[11px] text-gray-500">Streams are shown under each base class.</p>
                    </div>
                    <div class="text-[11px] text-gray-500">Total: <span class="font-semibold text-gray-700">{{ props.classes.length }}</span></div>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    <div v-if="!props.classes.length" class="text-[11px] text-gray-500">
                        No class assignments yet.
                    </div>

                    <div
                        v-for="c in props.classes"
                        :key="c.base_class_id"
                        class="rounded-lg border border-gray-100 bg-slate-50 p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-semibold text-gray-900">{{ c.base_label }}</div>
                            <div class="text-[10px] text-gray-500">Base ID: {{ c.base_class_id }}</div>
                        </div>

                        <div class="mt-2">
                            <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">Streams</div>
                            <div v-if="c.streams && c.streams.length" class="mt-1 flex flex-wrap gap-1">
                                <span
                                    v-for="st in c.streams"
                                    :key="st.id"
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-100"
                                >
                                    {{ st.label }}
                                </span>
                            </div>
                            <div v-else class="mt-1 text-[11px] text-gray-500">No streams (base class only).</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
