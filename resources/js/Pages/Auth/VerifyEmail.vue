<script setup>
import { computed } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <AuthLayout :compact="true">
        <Head title="Email Verification" />

        <h1 class="text-xl font-bold text-slate-900">Verify your email</h1>
        <p class="mt-1 text-sm text-slate-600 mb-6">
            Thanks for signing up! Please verify your email address by clicking on the link we emailed to you.
            If you didn't receive the email, we can send another.
        </p>

        <div
            class="mb-6 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl p-4"
            v-if="verificationLinkSent"
        >
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    class="h-10 rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Resend Verification Email
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 transition-colors"
                >
                    Log Out
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
