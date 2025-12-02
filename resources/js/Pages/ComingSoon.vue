<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    title: { type: String, required: true },
    description: {
        type: String,
        default: 'This section is under construction. Features will be available soon.',
    },
});
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                    {{ title }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ description }}
                </p>
            </div>
        </template>

        <div class="flex min-h-[320px] items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="text-center">
                <p class="text-sm font-medium uppercase tracking-wide text-emerald-500">
                    Coming soon
                </p>
                <p class="mt-2 text-lg font-semibold text-gray-800">
                    We are still working on this section.
                </p>
                <p class="mt-1 max-w-md text-sm text-gray-500">
                    You will be able to manage detailed data and analytics for this area here once it is fully implemented.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
