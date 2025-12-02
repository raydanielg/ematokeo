<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import Header from '@/Components/Header.vue'
import Footer from '@/Components/Footer.vue'

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
  <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-emerald-50 flex flex-col">
    <!-- Header -->
    <Header>
      <template #auth>
        <nav class="flex items-center gap-3">
          <Link :href="route('login')" class="text-sm font-semibold text-emerald-900 hover:text-emerald-700">Login</Link>
          <Link :href="route('register')" class="rounded-full bg-emerald-600 px-6 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-700 transition-all">Get Started</Link>
        </nav>
      </template>
    </Header>

    <main class="flex-1 w-full flex items-center justify-center px-4 py-12">
      <div class="w-full max-w-md">
        <div class="rounded-3xl bg-white/80 backdrop-blur-xl border border-emerald-200/60 shadow-2xl p-8 sm:p-12 hover:shadow-3xl transition-all duration-300">
          <h2 class="text-3xl font-bold text-emerald-950 mb-2">Forgot Password?</h2>
          <p class="text-emerald-800/70 mb-8">No problem. Just let us know your email address and we will email you a password reset link.</p>

          <!-- Status Message -->
          <div v-if="status" class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200">
            <p class="text-emerald-800 font-semibold">{{ status }}</p>
          </div>

          <form @submit.prevent="submit" class="space-y-6">
            <!-- Email -->
            <div>
              <label class="block text-sm font-semibold text-emerald-950 mb-2">Email Address</label>
              <div class="relative">
                <i class="material-icons absolute left-4 top-3.5 text-emerald-600">email</i>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  autofocus
                  class="w-full pl-12 pr-4 py-3 rounded-xl border border-emerald-200 bg-emerald-50/50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all"
                  placeholder="your@email.com"
                />
              </div>
              <span v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</span>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-semibold hover:from-emerald-700 hover:to-emerald-800 transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-lg hover:shadow-xl"
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
              <Link :href="route('login')" class="font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">Back to Login</Link>
            </p>
          </form>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <Footer />
  </div>
</template>
