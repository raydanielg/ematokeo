<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Header from '@/Components/Header.vue'
import Footer from '@/Components/Footer.vue'
import { ref } from 'vue'

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
})

const formData = ref({
  name: '',
  email: '',
  phone: '',
  category: 'General',
  subject: '',
  message: '',
})

const submitted = ref(false)
const isSubmitting = ref(false)

const categories = [
  { value: 'General', label: 'Swali la Jumla' },
  { value: 'Technical', label: 'Tatizo la Kiufundi' },
  { value: 'Support', label: 'Msaada' },
  { value: 'Feedback', label: 'Maoni' },
  { value: 'Partnership', label: 'Ushirikiano' },
]

const handleSubmit = async () => {
  isSubmitting.value = true
  // Simulate form submission
  setTimeout(() => {
    submitted.value = true
    isSubmitting.value = false
    // Reset form after 3 seconds
    setTimeout(() => {
      formData.value = {
        name: '',
        email: '',
        phone: '',
        category: 'General',
        subject: '',
        message: '',
      }
      submitted.value = false
    }, 3000)
  }, 1000)
}

const contactInfo = [
  {
    icon: '📍',
    title: 'Mahali Pazuri',
    content: 'P.O. Box 000, Dar es Salaam, Tanzania',
  },
  {
    icon: '📞',
    title: 'Simu',
    content: '+255 712 000 000',
  },
  {
    icon: '✉️',
    title: 'Email',
    content: 'info@matokeo.tz',
  },
  {
    icon: '🕐',
    title: 'Saa za Kazi',
    content: 'Jumatatu - Ijumaa: 07:30 - 16:30',
  },
]
</script>

<template>
  <Head title="Contact Us" />
  <div class="bg-white text-slate-900 min-h-screen flex flex-col">
    <!-- Header -->
    <Header>
      <template #auth>
        <nav v-if="canLogin" class="flex items-center gap-3">
          <Link
            v-if="$page.props.auth.user"
            :href="route('dashboard')"
            class="text-sm font-semibold text-emerald-900 hover:text-emerald-700 transition-colors"
          >
            Dashboard
          </Link>
          <template v-else>
            <Link :href="route('login')" class="text-sm font-semibold text-emerald-900 hover:text-emerald-700">Login</Link>
            <Link v-if="canRegister" :href="route('register')" class="rounded-full bg-emerald-600 px-6 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-700 transition-all">Get Started</Link>
          </template>
        </nav>
      </template>
    </Header>

    <main class="flex-1 w-full">
      <!-- Hero Section -->
      <section class="relative bg-gradient-to-br from-emerald-50 via-emerald-100 to-emerald-50 py-20 sm:py-32 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
          <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-200/40 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
          <div class="absolute bottom-0 left-0 w-96 h-96 bg-emerald-300/30 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>
        </div>

        <div class="relative z-10 mx-auto w-full px-4 sm:px-6 lg:px-8 text-center">
          <h1 class="text-5xl sm:text-6xl font-bold text-emerald-950 mb-6">Wasiliana Nasi</h1>
          <p class="text-xl text-emerald-800/80 max-w-3xl mx-auto leading-relaxed">
            Tuna swali au tatizo? Tupo hapa kusaidia. Wasiliana nasi kwa njia yoyote inayofaa kwako.
          </p>
        </div>
      </section>

      <!-- Contact Info Cards -->
      <section class="py-16 bg-white">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 max-w-6xl">
          <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
            <div v-for="info in contactInfo" :key="info.title" class="rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 p-8 text-center border border-emerald-200 hover:shadow-lg transition-shadow">
              <div class="text-4xl mb-4">{{ info.icon }}</div>
              <h3 class="text-lg font-bold text-emerald-950 mb-2">{{ info.title }}</h3>
              <p class="text-sm text-emerald-800/80">{{ info.content }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Contact Form Section -->
      <section class="py-16 bg-gradient-to-br from-emerald-50 to-white">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 max-w-2xl">
          <div class="rounded-3xl bg-white shadow-xl border border-emerald-100 p-8 sm:p-12">
            <h2 class="text-3xl font-bold text-emerald-950 mb-8">Tuma Ujumbe</h2>

            <!-- Success Message -->
            <div v-if="submitted" class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200">
              <p class="text-emerald-800 font-semibold flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Asante! Ujumbe wako umepokewa. Tutakujibu haraka.
              </p>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Name -->
              <div>
                <label class="block text-sm font-semibold text-emerald-950 mb-2">Jina Lako</label>
                <input
                  v-model="formData.name"
                  type="text"
                  required
                  class="w-full px-4 py-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all"
                  placeholder="Jina lako kamili"
                />
              </div>

              <!-- Email -->
              <div>
                <label class="block text-sm font-semibold text-emerald-950 mb-2">Email</label>
                <input
                  v-model="formData.email"
                  type="email"
                  required
                  class="w-full px-4 py-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all"
                  placeholder="email@example.com"
                />
              </div>

              <!-- Phone -->
              <div>
                <label class="block text-sm font-semibold text-emerald-950 mb-2">Simu</label>
                <input
                  v-model="formData.phone"
                  type="tel"
                  class="w-full px-4 py-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all"
                  placeholder="+255 712 000 000"
                />
              </div>

              <!-- Category -->
              <div>
                <label class="block text-sm font-semibold text-emerald-950 mb-2">Kategoria</label>
                <select
                  v-model="formData.category"
                  class="w-full px-4 py-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all"
                >
                  <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                    {{ cat.label }}
                  </option>
                </select>
              </div>

              <!-- Subject -->
              <div>
                <label class="block text-sm font-semibold text-emerald-950 mb-2">Mada</label>
                <input
                  v-model="formData.subject"
                  type="text"
                  required
                  class="w-full px-4 py-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all"
                  placeholder="Mada ya ujumbe wako"
                />
              </div>

              <!-- Message -->
              <div>
                <label class="block text-sm font-semibold text-emerald-950 mb-2">Ujumbe</label>
                <textarea
                  v-model="formData.message"
                  required
                  rows="6"
                  class="w-full px-4 py-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all resize-none"
                  placeholder="Andika ujumbe wako hapa..."
                ></textarea>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="isSubmitting"
                class="w-full px-6 py-3 rounded-full bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              >
                <svg v-if="!isSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <span v-if="isSubmitting">Inatuma...</span>
                <span v-else>Tuma Ujumbe</span>
              </button>
            </form>
          </div>
        </div>
      </section>

      <!-- FAQ Section -->
      <section class="py-16 bg-white">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 max-w-3xl">
          <h2 class="text-3xl font-bold text-emerald-950 mb-12 text-center">Maswali Yanayoulizwa Mara Kwa Mara</h2>
          <div class="space-y-4">
            <div v-for="(faq, idx) in [
              { q: 'Matokeo inatumiwa wapi?', a: 'Matokeo inatumiwa na shule zote za Tanzania kwa kusimamia matokeo ya mitihani.' },
              { q: 'Ni bei gani ya Matokeo?', a: 'Tafadhali wasiliana nasi kwa taarifa za bei.' },
              { q: 'Kuna programu ya simu?', a: 'Ndiyo, Matokeo ina programu kwa iOS na Android.' },
            ]" :key="idx" class="rounded-lg border border-emerald-100 bg-emerald-50/50 p-6 hover:shadow-md transition-shadow">
              <h3 class="font-semibold text-emerald-950 mb-2">{{ faq.q }}</h3>
              <p class="text-emerald-800/80 text-sm">{{ faq.a }}</p>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <Footer />
  </div>
</template>
