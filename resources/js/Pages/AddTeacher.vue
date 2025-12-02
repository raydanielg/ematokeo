<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const form = reactive({
    name: '',
    email: '',
    phone: '',
    check_number: '',
    teaching_classes: '',
});

const saveTeacher = () => {
    router.post(route('teachers.store'), form, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Add Teacher" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Add Teacher
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Register a new teacher with full name, phone number, registration (check) number and classes they teach.
                    </p>
                </div>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <form class="space-y-3 text-xs text-gray-700" @submit.prevent="saveTeacher">
                <div>
                    <label class="mb-1 block font-medium">Full Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. John Peter Mwakalinga"
                        required
                    />
                </div>
                <div>
                    <label class="mb-1 block font-medium">Email (for login)</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="teacher@example.com"
                        required
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block font-medium">Phone Number</label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="07XXXXXXXX"
                            required
                        />
                    </div>
                    <div>
                        <label class="mb-1 block font-medium">Check / Registration No.</label>
                        <input
                            v-model="form.check_number"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="Registration number"
                            required
                        />
                    </div>
                </div>

                <div>
                    <label class="mb-1 block font-medium">Classes (one or more)</label>
                    <input
                        v-model="form.teaching_classes"
                        type="text"
                        class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="e.g. Form I A, Form II B"
                        required
                    />
                    <p class="mt-1 text-[11px] text-gray-500">
                        You can enter a comma-separated list of classes this teacher is responsible for. Later we can replace this with a class picker.
                    </p>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="$inertia.visit(route('teachers.index'))"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                    >
                        Save Teacher
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
