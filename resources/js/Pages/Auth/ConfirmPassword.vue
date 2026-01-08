<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AuthLayout :compact="true">
        <Head title="Confirm Password" />

        <h1 class="text-xl font-bold text-slate-900">Confirm password</h1>
        <p class="mt-1 text-sm text-slate-600">Please confirm your password before continuing.</p>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="password" value="Password" class="text-slate-700 text-xs font-semibold" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full h-10 rounded-lg border-slate-200 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-400 focus:ring-indigo-100"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex justify-end">
                <PrimaryButton
                    class="h-10 rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Confirm
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>
