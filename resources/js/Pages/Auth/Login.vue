<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineProps({
  canRegister: Boolean,
  status: String,
})

const showPassword = ref(false)
const showSuccess = ref(false)

const form = useForm({
  login: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post(route('login'), {
    onSuccess: () => {
      // Show a brief success state; actual redirect is handled by backend
      showSuccess.value = true
    },
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Login" />
  <AuthLayout :compact="true">
    <h1 class="text-xl font-bold text-slate-900">Welcome back</h1>
    <p class="mt-1 text-sm text-slate-600">Sign in to continue to e-matokeo | Electronic marking system.</p>

    <div v-if="status" class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
      {{ status }}
    </div>

    <div class="mt-5 grid grid-cols-2 gap-3">
      <button type="button" class="h-10 rounded-lg border border-emerald-200 bg-white text-sm font-semibold text-slate-800 hover:bg-emerald-50">
        Google
      </button>
      <button type="button" class="h-10 rounded-lg border border-emerald-200 bg-white text-sm font-semibold text-black hover:bg-emerald-50">
        Apple
      </button>
    </div>

    <div class="my-5 flex items-center gap-3">
      <div class="h-px flex-1 bg-slate-200"></div>
      <div class="text-xs text-slate-500">or</div>
      <div class="h-px flex-1 bg-slate-200"></div>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-xs font-semibold text-slate-700">Email</label>
        <input
          v-model="form.login"
          type="text"
          required
          autofocus
          class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100"
          placeholder="you@example.com"
        />
        <div v-if="form.errors.login" class="mt-1 text-xs text-red-600">{{ form.errors.login }}</div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700">Password</label>
        <div class="relative mt-1">
          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            required
            class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 pr-10 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100"
            placeholder="Enter your password"
          />
          <button
            type="button"
            @click="showPassword = !showPassword"
            class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-slate-500 hover:text-slate-700"
          >
            <span class="text-xs">{{ showPassword ? 'Hide' : 'Show' }}</span>
          </button>
        </div>
        <div v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</div>
      </div>

      <div class="flex items-center justify-between pt-1">
        <label class="inline-flex items-center gap-2 text-xs text-slate-600">
          <input
            :checked="form.remember"
            @change="form.remember = $event.target.checked"
            type="checkbox"
            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
          />
          Remember me
        </label>
        <Link :href="route('password.request')" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">Forgot password?</Link>
      </div>

      <button
        type="submit"
        :disabled="form.processing"
        class="mt-2 h-10 w-full rounded-lg bg-emerald-700 text-sm font-semibold text-white hover:bg-emerald-800 disabled:opacity-50"
      >
        <span v-if="form.processing">Signing in...</span>
        <span v-else>Sign in to your account</span>
      </button>

      <p class="pt-2 text-center text-xs text-slate-600">
        Don't have an account?
        <Link v-if="canRegister" :href="route('register')" class="font-semibold text-emerald-700 hover:text-emerald-900">Sign up</Link>
      </p>
    </form>
  </AuthLayout>

  <div v-if="showSuccess" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 sm:p-12 text-center animate-in fade-in zoom-in-95 duration-300">
      <div class="mb-8 flex justify-center">
        <div class="relative w-32 h-32">
          <div class="absolute inset-0 rounded-full border-4 border-emerald-200 animate-pulse"></div>

          <div class="absolute inset-0 flex items-center justify-center">
            <svg class="w-20 h-20 text-emerald-600 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <h2 class="text-3xl font-bold text-emerald-950 mb-2">Welcome Back!</h2>
      <p class="text-emerald-800/70 mb-8">You have successfully logged in to your account.</p>

      <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6">
        <p class="text-sm text-emerald-800">
          <span class="font-semibold">Redirecting to dashboard...</span><br>
          You will be redirected in a few seconds.
        </p>
      </div>

      <div class="flex justify-center gap-2">
        <div class="w-2 h-2 rounded-full bg-emerald-600 animate-bounce" style="animation-delay: 0s;"></div>
        <div class="w-2 h-2 rounded-full bg-emerald-600 animate-bounce" style="animation-delay: 0.2s;"></div>
        <div class="w-2 h-2 rounded-full bg-emerald-600 animate-bounce" style="animation-delay: 0.4s;"></div>
      </div>
    </div>
  </div>
</template>
