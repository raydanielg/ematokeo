<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const mobileOpen = ref(false);

defineProps({
    school: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <header>
        <div class="bg-primary-700 text-white">
            <div class="mx-auto flex max-w-screen-xl flex-wrap items-center justify-between gap-2 px-4 py-1.5 text-[12px] lg:px-6">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span v-if="school?.email" class="inline-flex items-center gap-2">
                        <span class="material-icons text-[16px]">mail</span>
                        <span class="lowercase">{{ school.email }}</span>
                    </span>
                    <span v-if="school?.head_teacher_phone" class="inline-flex items-center gap-2">
                        <span class="material-icons text-[16px]">call</span>
                        <span>{{ school.head_teacher_phone }}</span>
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <a href="#" class="text-white/90 hover:text-white transition-colors" aria-label="Facebook">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 12a10 10 0 10-11.5 9.9v-7H8v-3h2.5V9.5A3.5 3.5 0 0114.25 6H17v3h-2c-.8 0-1 .4-1 1v2h3l-.5 3H14v7A10 10 0 0022 12z" />
                        </svg>
                    </a>
                    <a href="#" class="text-white/90 hover:text-white transition-colors" aria-label="Instagram">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm5 6a4 4 0 100 8 4 4 0 000-8zm6-1a1 1 0 10-2 0 1 1 0 002 0z" />
                        </svg>
                    </a>
                    <a href="#" class="text-white/90 hover:text-white transition-colors" aria-label="X">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18.9 2H22l-6.8 7.8L23 22h-6.4l-5-6.2L6.1 22H3l7.3-8.4L1 2h6.6l4.5 5.6L18.9 2z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <nav class="bg-white border-gray-200 px-4 lg:px-6 py-2.5 dark:bg-gray-800">
            <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl">
                <a href="#" class="flex items-center gap-3">
                    <img
                        src="/logo.png"
                        alt="Angelina Mabula Secondary School Logo"
                        class="h-10 w-10 rounded-full object-contain bg-white p-1 shadow-sm ring-1 ring-gray-200 animate-float"
                    />
                    <div>
                        <span class="block text-sm font-semibold whitespace-nowrap text-gray-900 dark:text-white">
                            Angelina Mabula Secondary School
                        </span>
                        <span class="block text-[11px] text-gray-500 dark:text-gray-300">eMatokeo</span>
                    </div>
                </a>

                <div class="hidden items-center justify-between w-full lg:flex lg:w-auto lg:order-1">
                    <ul class="flex flex-col mt-4 font-medium lg:flex-row lg:space-x-8 lg:mt-0">
                        <li>
                            <a
                                href="#"
                                class="block py-2 pr-4 pl-3 text-primary-700 lg:p-0 dark:text-white"
                                aria-current="page"
                            >
                                Home
                            </a>
                        </li>
                        <li>
                            <Link
                                :href="route('gallery.index')"
                                class="block py-2 pr-4 pl-3 text-gray-700 hover:text-primary-700 lg:p-0 dark:text-gray-300 lg:dark:hover:text-white"
                            >
                                Gallery
                            </Link>
                        </li>
                        <li>
                            <a
                                href="#"
                                class="block py-2 pr-4 pl-3 text-gray-700 hover:text-primary-700 lg:p-0 dark:text-gray-300 lg:dark:hover:text-white"
                            >
                                Application
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="flex items-center lg:order-2">
                    <Link
                        :href="route('login')"
                        class="text-gray-800 dark:text-white hover:bg-gray-50 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 mr-2 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800 transition-colors"
                    >
                        Staff
                    </Link>
                    <Link
                        :href="route('login')"
                        class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 mr-2 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800 transition-colors"
                    >
                        Students
                    </Link>

                    <button
                        type="button"
                        class="inline-flex items-center p-2 ml-1 text-sm text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                        aria-controls="mobile-menu-2"
                        :aria-expanded="mobileOpen ? 'true' : 'false'"
                        @click="mobileOpen = !mobileOpen"
                    >
                        <span class="sr-only">Open main menu</span>
                        <svg v-if="!mobileOpen" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path
                                fill-rule="evenodd"
                                d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                        <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path
                                fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                    </button>
                </div>

                <div
                    :class="[mobileOpen ? 'flex' : 'hidden', 'justify-between items-center w-full lg:hidden lg:w-auto lg:order-1']"
                    id="mobile-menu-2"
                >
                    <ul class="flex flex-col mt-4 font-medium w-full">
                        <li>
                            <a
                                href="#"
                                class="block py-2 pr-4 pl-3 text-gray-700 border-b border-gray-100 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700 dark:border-gray-700"
                            >
                                Home
                            </a>
                        </li>
                        <li>
                            <Link
                                :href="route('gallery.index')"
                                class="block py-2 pr-4 pl-3 text-gray-700 border-b border-gray-100 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700 dark:border-gray-700"
                            >
                                Gallery
                            </Link>
                        </li>
                        <li>
                            <a
                                href="#"
                                class="block py-2 pr-4 pl-3 text-gray-700 border-b border-gray-100 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700 dark:border-gray-700"
                            >
                                Application
                            </a>
                        </li>
                        <li>
                            <Link
                                :href="route('login')"
                                class="block py-2 pr-4 pl-3 text-gray-700 border-b border-gray-100 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700 dark:border-gray-700"
                            >
                                Staff
                            </Link>
                        </li>
                        <li>
                            <Link
                                :href="route('login')"
                                class="block py-2 pr-4 pl-3 text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700"
                            >
                                Students
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
</template>
