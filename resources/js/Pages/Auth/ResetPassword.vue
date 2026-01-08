<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout :compact="true">
        <Head title="Reset Password" />

        <h1 class="text-xl font-bold text-slate-900">Reset password</h1>
        <p class="mt-1 text-sm text-slate-600">Choose a new password for your account.</p>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Email" class="text-slate-700 text-xs font-semibold" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full h-10 rounded-lg border-slate-200 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-400 focus:ring-indigo-100"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" class="text-slate-700 text-xs font-semibold" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full h-10 rounded-lg border-slate-200 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-400 focus:ring-indigo-100"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                    class="text-slate-700 text-xs font-semibold"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full h-10 rounded-lg border-slate-200 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-400 focus:ring-indigo-100"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="flex items-center justify-end">
                <PrimaryButton
                    class="h-10 rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Reset Password
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>
