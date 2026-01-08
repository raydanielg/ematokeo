<script setup>
import { Head } from '@inertiajs/vue3';
import Header from './Header.vue';
import Hero from './Hero.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    school: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-white text-slate-900">
        <Header :school="school" />

        <Hero />

        <main class="mx-auto max-w-3xl px-4 py-12">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h1 class="text-xl font-semibold text-slate-900">Welcoming</h1>

                <div class="mt-4">
                    <input
                        type="text"
                        value="welcoming"
                        readonly
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                    />
                </div>
            </div>
        </main>
    </div>
</template>
