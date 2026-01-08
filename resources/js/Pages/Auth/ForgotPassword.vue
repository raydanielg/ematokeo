<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineProps({
  status: String,
})

const form = useForm({
  email: '',
})

const submit = () => {
  form.post(route('password.email'))
}
</script>

<template>
  <Head title="Forgot Password" />
  <AuthLayout :compact="true">
    <h1 class="text-xl font-bold text-slate-900">Forgot password?</h1>
    <p class="mt-1 text-sm text-slate-600">Enter your email and we'll send you a reset link.</p>

    <div class="mb-6 flex items-center justify-between">
      <Link :href="route('login')" class="text-sm font-semibold text-emerald-900 hover:text-emerald-700">Login</Link>
      <Link :href="route('register')" class="rounded-full bg-emerald-600 px-6 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-700 transition-all">Get Started</Link>
    </div>

          <!-- Status Message -->
          <div v-if="status" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ status }}
          </div>

          <form @submit.prevent="submit" class="space-y-6">
            <!-- Email -->
            <div>
              <label class="block text-xs font-semibold text-slate-700">Email</label>
              <div class="relative">
                <i class="material-icons absolute left-4 top-3.5 text-emerald-600">email</i>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  autofocus
                  class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                  placeholder="your@email.com"
                />
              </div>
              <div v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</div>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="form.processing"
              class="mt-2 h-10 w-full rounded-lg bg-emerald-700 text-sm font-semibold text-white hover:bg-emerald-800 disabled:opacity-50 flex items-center justify-center gap-2"
            >
              <svg v-if="!form.processing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              <span v-if="form.processing">Sending...</span>
              <span v-else>Email Password Reset Link</span>
            </button>

            <!-- Back to Login Link -->
            <p class="text-center text-emerald-800/70">
              Remember your password?
              <Link :href="route('login')" class="font-semibold text-emerald-700 hover:text-emerald-900 transition-colors">Back to login</Link>
            </p>
          </form>
  </AuthLayout>
</template>
