<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import Header from '@/Components/Header.vue'
import Footer from '@/Components/Footer.vue'

defineProps({
  canLogin: Boolean,
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)
const showDetails = ref(false)
const showSuccess = ref(false)

const form = useForm({
  name: '',
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const features = [
  { icon: 'chart-increasing', title: 'Real-time Results', desc: 'Get instant exam results' },
  { icon: 'users', title: 'Student Management', desc: 'Manage students efficiently' },
  { icon: 'chart-bar', title: 'Analytics', desc: 'Detailed performance insights' },
  { icon: 'lock', title: 'Secure', desc: 'Enterprise-grade security' },
]

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
  <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-emerald-50 flex flex-col">
    <!-- Header -->
    <Header>
      <template #auth>
        <nav v-if="canLogin" class="flex items-center gap-3">
          <Link :href="route('login')" class="text-sm font-semibold text-emerald-900 hover:text-emerald-700">Login</Link>
        </nav>
      </template>
    </Header>

    <main class="flex-1 w-full flex items-center justify-center px-3 sm:px-4 py-8 sm:py-12">
      <div class="w-full max-w-6xl">
        <div class="grid md:grid-cols-2 gap-6 md:gap-12 items-center">
          <!-- Left: Features -->
          <div class="hidden md:block">
            <h1 class="text-3xl md:text-4xl font-bold text-emerald-950 mb-6">Join Matokeo</h1>
            <p class="text-base md:text-lg text-emerald-800/80 mb-8">Start managing exam results with our powerful platform</p>
            
            <div class="space-y-4">
              <div v-for="feature in features" :key="feature.title" class="flex gap-4 p-4 rounded-xl bg-white/60 backdrop-blur-sm border border-emerald-200/50 hover:border-emerald-400 transition-all duration-300 hover:scale-105">
                <div class="text-3xl flex-shrink-0">
                  <i v-if="feature.icon === 'chart-increasing'" class="material-icons text-emerald-600">trending_up</i>
                  <i v-else-if="feature.icon === 'users'" class="material-icons text-emerald-600">people</i>
                  <i v-else-if="feature.icon === 'chart-bar'" class="material-icons text-emerald-600">bar_chart</i>
                  <i v-else-if="feature.icon === 'lock'" class="material-icons text-emerald-600">lock</i>
                </div>
                <div>
                  <h3 class="font-bold italic text-emerald-950">{{ feature.title }}</h3>
                  <p class="text-sm text-emerald-800/70">{{ feature.desc }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Register Card -->
          <div class="w-full">
            <div class="rounded-2xl sm:rounded-3xl bg-white/80 backdrop-blur-xl border border-emerald-200/60 shadow-2xl p-5 sm:p-8 md:p-12 hover:shadow-3xl transition-all duration-300">
              <h2 class="text-2xl sm:text-3xl font-bold text-emerald-950 mb-2">Create Account</h2>
              <p class="text-sm sm:text-base text-emerald-800/70 mb-6 sm:mb-8">Sign up to get started with Matokeo</p>

              <form @submit.prevent="submit" class="space-y-6">
                <!-- Name -->
                <div>
                  <label class="block text-sm font-semibold text-emerald-950 mb-2">Full Name</label>
                  <div class="relative">
                    <i class="material-icons absolute left-4 top-3.5 text-emerald-600">person</i>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      class="w-full pl-12 pr-4 py-3 rounded-xl border border-emerald-200 bg-emerald-50/50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all"
                      placeholder="Your full name"
                    />
                  </div>
                  <span v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</span>
                </div>

                <!-- Username -->
                <div>
                  <label class="block text-sm font-semibold text-emerald-950 mb-2">Username</label>
                  <div class="relative">
                    <i class="material-icons absolute left-4 top-3.5 text-emerald-600">account_circle</i>
                    <input
                      v-model="form.username"
                      type="text"
                      required
                      class="w-full pl-12 pr-4 py-3 rounded-xl border border-emerald-200 bg-emerald-50/50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all"
                      placeholder="Choose a username"
                    />
                  </div>
                  <span v-if="form.errors.username" class="text-red-500 text-sm mt-1">{{ form.errors.username }}</span>
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-sm font-semibold text-emerald-950 mb-2">Email</label>
                  <div class="relative">
                    <svg class="absolute left-4 top-3.5 w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <input
                      v-model="form.email"
                      type="email"
                      required
                      class="w-full pl-12 pr-4 py-3 rounded-xl border border-emerald-200 bg-emerald-50/50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all"
                      placeholder="your@email.com"
                    />
                  </div>
                  <span v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</span>
                </div>

                <!-- Password -->
                <div>
                  <label class="block text-sm font-semibold text-emerald-950 mb-2">Password</label>
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

                <!-- Confirm Password -->
                <div>
                  <label class="block text-sm font-semibold text-emerald-950 mb-2">Confirm Password</label>
                  <div class="relative">
                    <svg class="absolute left-4 top-3.5 w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <input
                      v-model="form.password_confirmation"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      required
                      class="w-full pl-12 pr-12 py-3 rounded-xl border border-emerald-200 bg-emerald-50/50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all"
                      placeholder="Confirm password"
                    />
                    <button
                      type="button"
                      @click="showConfirmPassword = !showConfirmPassword"
                      class="absolute right-4 top-3.5 text-emerald-600 hover:text-emerald-700 transition-colors"
                    >
                      <svg v-if="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/>
                      </svg>
                      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </button>
                  </div>
                  <span v-if="form.errors.password_confirmation" class="text-red-500 text-sm mt-1">{{ form.errors.password_confirmation }}</span>
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
                  What information do we need?
                </button>

                <!-- Submit Button -->
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="w-full px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-semibold hover:from-emerald-700 hover:to-emerald-800 transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-lg hover:shadow-xl"
                >
                  <svg v-if="!form.processing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9l-6 6m0 0l-6-6m6 6V3"/>
                  </svg>
                  <span v-if="form.processing">Creating Account...</span>
                  <span v-else>Create Account</span>
                </button>

                <!-- Login Link -->
                <p class="text-center text-emerald-800/70">
                  Already have an account?
                  <Link :href="route('login')" class="font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">Login here</Link>
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
          <h3 class="text-xl font-bold text-white">Registration Information</h3>
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
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
              <h4 class="font-semibold text-emerald-950">Full Name</h4>
              <p class="text-sm text-emerald-800/70">Your complete name for identification purposes</p>
            </div>
          </div>

          <div class="flex gap-3">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <div>
              <h4 class="font-semibold text-emerald-950">Email Address</h4>
              <p class="text-sm text-emerald-800/70">Used for account recovery and notifications</p>
            </div>
          </div>

          <div class="flex gap-3">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <div>
              <h4 class="font-semibold text-emerald-950">Secure Password</h4>
              <p class="text-sm text-emerald-800/70">Must be at least 8 characters with mixed case</p>
            </div>
          </div>

          <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mt-6">
            <p class="text-sm text-emerald-800">
              <span class="font-semibold">Privacy:</span> Your data is encrypted and secure. We never share your information with third parties.
            </p>
          </div>
        </div>
      </div>
    </div>

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

    <!-- Footer -->
    <Footer />
  </div>
</template>
