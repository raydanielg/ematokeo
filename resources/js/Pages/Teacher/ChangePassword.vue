<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('teacher.change-password.update'));
};
</script>

<template>
    <Head title="Change Password" />

    <div class="min-h-screen bg-white">
        <div class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-6 py-10">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-slate-900">Change Password</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Please set a new password to continue.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">New Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        autocomplete="new-password"
                        required
                    />
                    <div v-if="form.errors.password" class="mt-1 text-xs text-red-600">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Confirm Password</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        autocomplete="new-password"
                        required
                    />
                    <div v-if="form.errors.password_confirmation" class="mt-1 text-xs text-red-600">
                        {{ form.errors.password_confirmation }}
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60"
                >
                    Save Password
                </button>
            </form>
        </div>
    </div>
</template>
