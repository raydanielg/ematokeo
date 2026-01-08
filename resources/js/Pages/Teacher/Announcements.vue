<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    announcements: {
        type: Array,
        default: () => [],
    },
});

const count = computed(() => (Array.isArray(props.announcements) ? props.announcements.length : 0));

const formatDate = (iso) => {
    if (!iso) return '';
    try {
        const d = new Date(iso);
        return d.toLocaleString();
    } catch (e) {
        return String(iso);
    }
};
</script>

<template>
    <Head title="Announcements" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">Announcements</h2>
                <p class="mt-1 text-sm text-gray-500">School announcements shared with teachers.</p>
            </div>
        </template>

        <div class="space-y-6">
            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center justify-between gap-4">
                    <div class="text-sm text-gray-700">
                        Total: <span class="font-semibold text-gray-900">{{ count }}</span>
                    </div>
                    <div class="text-xs text-gray-500">
                        Only announcements for your school (audience: all/teachers)
                    </div>
                </div>
            </div>

            <div v-if="!announcements || announcements.length === 0" class="rounded-lg bg-white p-6 text-sm text-gray-600 shadow-sm ring-1 ring-gray-100">
                No announcements published yet.
            </div>

            <div v-else class="grid gap-4 lg:grid-cols-2">
                <article
                    v-for="a in announcements"
                    :key="a.id"
                    class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">{{ a.title }}</h3>
                            <p class="mt-1 text-xs text-gray-500">
                                <span v-if="a.published_at">Published: {{ formatDate(a.published_at) }}</span>
                                <span v-else>Created: {{ formatDate(a.created_at) }}</span>
                                <span class="ml-2 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                    {{ a.audience }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 whitespace-pre-line text-sm text-gray-700">
                        {{ a.body }}
                    </div>
                </article>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
