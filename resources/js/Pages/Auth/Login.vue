<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import Header from '@/Components/Header.vue'
import Footer from '@/Components/Footer.vue'

defineProps({
  canRegister: Boolean,
  status: String,
})

const showPassword = ref(false)
const showDetails = ref(false)

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const benefits = [
  { icon: 'flash_on', title: 'Fast Access', desc: 'Quick login to your dashboard' },
  { icon: 'lock', title: 'Secure', desc: 'Protected with encryption' },
  { icon: 'smartphone', title: 'Mobile Ready', desc: 'Access from any device' },
  { icon: 'public', title: 'Global', desc: 'Available 24/7' },
]

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Login" />
  <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-emerald-50 flex flex-col">
    <!-- Header -->
    <Header>
      <template #auth>
        <nav v-if="canRegister" class="flex items-center gap-3">
          <Link :href="route('register')" class="rounded-full bg-emerald-600 px-6 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-700 transition-all">Get Started</Link>
        </nav>
      </template>
    </Header>

    <main class="flex-1 w-full flex items-center justify-center px-4 py-12">
      <div class="w-full max-w-6xl">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <!-- Left: Benefits -->
          <div class="hidden md:block">
            <h1 class="text-4xl font-bold text-emerald-950 mb-6">Welcome Back</h1>
            <p class="text-lg text-emerald-800/80 mb-8">Access your exam results and manage your account</p>
            
            <div class="space-y-4">
              <div v-for="benefit in benefits" :key="benefit.title" class="flex gap-4 p-4 rounded-xl bg-white/60 backdrop-blur-sm border border-emerald-200/50 hover:border-emerald-400 transition-all animate-pulse">
                <div class="text-3xl flex-shrink-0">
                  <i class="material-icons text-emerald-600">{{ benefit.icon }}</i>
                </div>
                <div>
                  <h3 class="font-bold text-emerald-950">{{ benefit.title }}</h3>
                  <p class="text-sm text-emerald-800/70">{{ benefit.desc }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Login Card -->
          <div class="w-full">
            <div class="rounded-3xl bg-white/80 backdrop-blur-xl border border-emerald-200/60 shadow-2xl p-8 sm:p-12 hover:shadow-3xl transition-all duration-300">
              <h2 class="text-3xl font-bold text-emerald-950 mb-2">Sign In</h2>
              <p class="text-emerald-800/70 mb-8">Enter your credentials to access your account</p>

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

                <!-- Password -->
                <div>
                  <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-semibold text-emerald-950">Password</label>
                    <Link :href="route('password.request')" class="text-sm text-emerald-600 hover:text-emerald-700 transition-colors">Forgot password?</Link>
                  </div>
                  <div class="relative">
                    <svg class="absolute left-4 top-3.5 w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <input
                      v-model="form.password"
                      :type="showPassword ? 'text' : 'password'"
                      required
                      class="w-full pl-12 pr-12 py-3 rounded-xl border border-emerald-200 bg-emerald-50/50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all"
                      placeholder="Enter password"
                    />
                    <button
                      type="button"
                      @click="showPassword = !showPassword"
                      class="absolute right-4 top-3.5 text-emerald-600 hover:text-emerald-700 transition-colors"
                    >
                      <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/>
                      </svg>
                      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </button>
                  </div>
                  <span v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</span>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                  <input
                    :checked="form.remember"
                    @change="form.remember = $event.target.checked"
                    type="checkbox"
                    class="w-4 h-4 rounded border-emerald-300 text-emerald-600 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                  />
                  <label class="ml-2 text-sm text-emerald-800">Remember me</label>
                </div>

                <!-- Info Button -->
                <button
                  type="button"
                  @click="showDetails = true"
                  class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm text-emerald-700 hover:text-emerald-900 transition-colors"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  How does login work?
                </button>

                <!-- Submit Button -->
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="w-full px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-semibold hover:from-emerald-700 hover:to-emerald-800 transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-lg hover:shadow-xl"
                >
                  <svg v-if="!form.processing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14.5a3 3 0 010 6H3"/>
                  </svg>
                  <span v-if="form.processing">Signing in...</span>
                  <span v-else>Sign In</span>
                </button>

                <!-- Register Link -->
                <p class="text-center text-emerald-800/70">
                  Don't have an account?
                  <Link v-if="canRegister" :href="route('register')" class="font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">Create one here</Link>
                </p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Details Modal -->
    <div v-if="showDetails" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto animate-in fade-in zoom-in-95 duration-300">
        <div class="sticky top-0 bg-gradient-to-r from-emerald-600 to-emerald-700 px-6 py-4 flex items-center justify-between">
          <h3 class="text-xl font-bold text-white">How Login Works</h3>
          <button
            @click="showDetails = false"
            class="text-white hover:bg-emerald-600 rounded-full p-2 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="p-6 space-y-4">
          <div class="flex gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold">1</div>
            <div>
              <h4 class="font-semibold text-emerald-950">Enter Your Email</h4>
              <p class="text-sm text-emerald-800/70">Use the email address you registered with</p>
            </div>
          </div>

          <div class="flex gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold">2</div>
            <div>
              <h4 class="font-semibold text-emerald-950">Enter Your Password</h4>
              <p class="text-sm text-emerald-800/70">Your password is encrypted for security</p>
            </div>
          </div>

          <div class="flex gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold">3</div>
            <div>
              <h4 class="font-semibold text-emerald-950">Click Sign In</h4>
              <p class="text-sm text-emerald-800/70">You'll be redirected to your dashboard</p>
            </div>
          </div>

          <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mt-6">
            <p class="text-sm text-emerald-800">
              <span class="font-semibold">Security:</span> We use industry-standard encryption to protect your login credentials.
            </p>
          </div>

          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-800">
              <span class="font-semibold">Tip:</span> Check "Remember me" to stay logged in on this device.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <Footer />
  </div>
</template>
