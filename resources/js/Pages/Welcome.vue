<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import Hero from '@/Components/Hero.vue';
import Features from '@/Components/Features.vue';
import WhyChoose from '@/Components/WhyChoose.vue';
import Faq from '@/Components/Faq.vue';
import DownloadApp from '@/Components/DownloadApp.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Welcome" />
    <div class="relative w-full bg-white text-slate-900 selection:bg-emerald-300 selection:text-emerald-950">
        <!-- Header -->
        <Header>
            <template #auth>
                <nav v-if="canLogin" class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="text-sm font-semibold text-emerald-900 hover:text-emerald-700 transition-colors"
                    >
                        Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="text-sm font-semibold text-emerald-900 hover:text-emerald-700 transition-colors"
                        >
                            Login
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-full bg-emerald-600 px-6 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-600/40 hover:bg-emerald-700 transition-all"
                        >
                            Get Started
                        </Link>
                    </template>
                </nav>
            </template>
        </Header>

        <!-- Main Content -->
        <main class="w-full">
            <!-- Hero Section -->
            <div class="w-full">
                <Hero />
            </div>

            <!-- Features Section -->
            <div class="w-full bg-white">
                <Features />
            </div>

            <!-- WhyChoose Section -->
            <div class="w-full bg-white">
                <WhyChoose />
            </div>

            <!-- FAQ Section -->
            <div class="w-full bg-gradient-to-br from-emerald-50 via-white to-emerald-50">
                <Faq />
            </div>

            <!-- Download App Section -->
            <div class="w-full">
                <DownloadApp />
            </div>
        </main>

        <!-- Footer -->
        <Footer />
    </div>
</template>
