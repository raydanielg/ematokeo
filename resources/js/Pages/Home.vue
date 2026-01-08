<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-white text-slate-900">
        <Header>
            <template #auth>
                <nav class="flex items-center gap-3">
                    <Link
                        :href="route('login')"
                        class="text-sm font-semibold text-emerald-900 hover:text-emerald-700 transition-colors"
                    >
                        Staff
                    </Link>

                    <Link
                        :href="route('login')"
                        class="text-sm font-semibold text-emerald-900 hover:text-emerald-700 transition-colors"
                    >
                        Students
                    </Link>
                </nav>
            </template>
        </Header>

        <main class="mx-auto max-w-3xl px-4 py-12">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h1 class="text-xl font-semibold text-slate-900">Welcome</h1>
            </div>
        </main>
    </div>
</template>
