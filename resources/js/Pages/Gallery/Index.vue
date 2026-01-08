<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, onBeforeUnmount, ref } from 'vue';
import Header from '../Home/Header.vue';

const props = defineProps({
    school: {
        type: Object,
        default: null,
    },
    images: {
        type: Array,
        default: () => [],
    },
});

const previewOpen = ref(false);
const previewImage = ref(null);

const openPreview = (img) => {
    previewImage.value = img;
    previewOpen.value = true;
};

const closePreview = () => {
    previewOpen.value = false;
    previewImage.value = null;
};

const preventContextMenu = (e) => {
    e.preventDefault();
};

onMounted(() => {
    window.addEventListener('contextmenu', preventContextMenu);
});

onBeforeUnmount(() => {
    window.removeEventListener('contextmenu', preventContextMenu);
});
</script>

<template>
    <Head title="Gallery" />

    <div class="min-h-screen bg-white text-slate-900">
        <Header :school="school" />

        <main class="mx-auto max-w-screen-xl px-4 py-10 lg:px-6">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-900">Gallery</h1>
                <p class="mt-1 text-sm text-slate-600">Picha za shule zilizopostiwa.</p>
            </div>

            <div
                class="columns-1 gap-4 sm:columns-2 lg:columns-3"
                @dragstart.prevent
                @contextmenu.prevent
            >
                <div
                    v-for="img in images"
                    :key="img.url"
                    class="mb-4 break-inside-avoid overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="group relative cursor-zoom-in"
                        @click="openPreview(img)"
                    >
                        <img
                            :src="img.url"
                            :alt="img.name"
                            class="block w-full select-none pointer-events-none"
                            draggable="false"
                            loading="lazy"
                        />

                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/55 via-black/0 to-black/0 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                        ></div>

                        <div
                            class="pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                        >
                            <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between gap-3">
                                <div class="text-xs text-white/90">{{ img.name }}</div>
                                <div class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-medium text-white ring-1 ring-white/20">
                                    View
                                </div>
                            </div>
                        </div>

                        <div class="pointer-events-none absolute inset-0 select-none">
                            <div
                                class="absolute -rotate-12 left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 whitespace-nowrap text-white/10 text-xl font-black tracking-widest"
                            >
                                ANGELINA MABULA
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!images.length" class="rounded-2xl border border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-600">
                    Hakuna picha kwa sasa.
                </div>
            </div>
        </main>

        <div
            v-if="previewOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
            @click.self="closePreview"
            @contextmenu.prevent
        >
            <div class="relative w-full max-w-5xl overflow-hidden rounded-2xl bg-black ring-1 ring-white/10">
                <button
                    type="button"
                    class="absolute right-3 top-3 rounded-full bg-white/10 px-3 py-2 text-xs font-semibold text-white ring-1 ring-white/15 hover:bg-white/15"
                    @click="closePreview"
                >
                    Close
                </button>

                <div class="relative">
                    <img
                        v-if="previewImage"
                        :src="previewImage.url"
                        :alt="previewImage.name"
                        class="block w-full select-none"
                        draggable="false"
                        @dragstart.prevent
                        @contextmenu.prevent
                    />

                    <div class="pointer-events-none absolute inset-0 select-none">
                        <div
                            class="absolute -rotate-12 left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 whitespace-nowrap text-white/10 text-3xl font-black tracking-widest"
                        >
                            ANGELINA MABULA
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between bg-black px-4 py-3">
                    <div class="text-sm font-medium text-white/90">{{ previewImage?.name }}</div>
                    <div class="text-xs text-white/60">Saving is restricted</div>
                </div>
            </div>
        </div>
    </div>
</template>
