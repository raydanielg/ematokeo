<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineProps({
  canLogin: Boolean,
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)
const showSuccess = ref(false)

const form = useForm({
  name: '',
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('register'), {
    onSuccess: () => {
      showSuccess.value = true
      setTimeout(() => {
        window.location.href = '/dashboard'
      }, 3000)
    },
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <Head title="Register" />
  <AuthLayout :compact="true">
    <h1 class="text-xl font-bold text-slate-900">Create account</h1>
    <p class="mt-1 text-sm text-slate-600">Create your e-matokeo account to continue.</p>

    <div v-if="canLogin" class="mt-4 text-right">
      <Link :href="route('login')" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">Back to login</Link>
    </div>

    <form @submit.prevent="submit" class="mt-4 space-y-4">
      <div>
        <label class="block text-xs font-semibold text-slate-700">Full name</label>
        <input
          v-model="form.name"
          type="text"
          required
          class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100"
          placeholder="Your full name"
        />
        <div v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700">Username</label>
        <input
          v-model="form.username"
          type="text"
          required
          class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100"
          placeholder="Choose a username"
        />
        <div v-if="form.errors.username" class="mt-1 text-xs text-red-600">{{ form.errors.username }}</div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700">Email</label>
        <input
          v-model="form.email"
          type="email"
          required
          class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100"
          placeholder="you@example.com"
        />
        <div v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700">Password</label>
        <div class="relative mt-1">
          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            required
            class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 pr-10 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100"
            placeholder="Create a password"
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

      <div>
        <label class="block text-xs font-semibold text-slate-700">Confirm password</label>
        <div class="relative mt-1">
          <input
            v-model="form.password_confirmation"
            :type="showConfirmPassword ? 'text' : 'password'"
            required
            class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 pr-10 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100"
            placeholder="Confirm your password"
          />
          <button
            type="button"
            @click="showConfirmPassword = !showConfirmPassword"
            class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-slate-500 hover:text-slate-700"
          >
            <span class="text-xs">{{ showConfirmPassword ? 'Hide' : 'Show' }}</span>
          </button>
        </div>
        <div v-if="form.errors.password_confirmation" class="mt-1 text-xs text-red-600">{{ form.errors.password_confirmation }}</div>
      </div>

      <button
        type="submit"
        :disabled="form.processing"
        class="mt-2 h-10 w-full rounded-lg bg-emerald-700 text-sm font-semibold text-white hover:bg-emerald-800 disabled:opacity-50"
      >
        <span v-if="form.processing">Creating account...</span>
        <span v-else>Create account</span>
      </button>

      <p class="pt-2 text-center text-xs text-slate-600">
        Already have an account?
        <Link :href="route('login')" class="font-semibold text-emerald-700 hover:text-emerald-900">Sign in</Link>
      </p>
    </form>
  </AuthLayout>

    <!-- Success Modal -->
    <div v-if="showSuccess" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 sm:p-12 text-center animate-in fade-in zoom-in-95 duration-300">
        <!-- Success Animation Container -->
        <div class="mb-8 flex justify-center">
          <div class="relative w-32 h-32">
            <!-- Animated Circle -->
            <div class="absolute inset-0 rounded-full border-4 border-emerald-200 animate-pulse"></div>
            
            <!-- Checkmark -->
            <div class="absolute inset-0 flex items-center justify-center">
              <svg class="w-20 h-20 text-emerald-600 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Success Message -->
        <h2 class="text-3xl font-bold text-emerald-950 mb-2">Account Created!</h2>
        <p class="text-emerald-800/70 mb-8">Welcome to Matokeo! Your account has been successfully created.</p>

        <!-- Redirect Info -->
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6">
          <p class="text-sm text-emerald-800">
            <span class="font-semibold">Redirecting to dashboard...</span><br>
            You will be redirected in a few seconds.
          </p>
        </div>

        <!-- Loading Animation -->
        <div class="flex justify-center gap-2">
          <div class="w-2 h-2 rounded-full bg-emerald-600 animate-bounce" style="animation-delay: 0s;"></div>
          <div class="w-2 h-2 rounded-full bg-emerald-600 animate-bounce" style="animation-delay: 0.2s;"></div>
          <div class="w-2 h-2 rounded-full bg-emerald-600 animate-bounce" style="animation-delay: 0.4s;"></div>
        </div>
      </div>
    </div>

</template>
