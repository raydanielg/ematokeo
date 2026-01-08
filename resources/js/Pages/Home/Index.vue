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
    </div>
</template>
