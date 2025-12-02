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
  { value: 'General', label: 'General Inquiry' },
  { value: 'Technical', label: 'Technical Issue' },
  { value: 'Support', label: 'Support' },
  { value: 'Feedback', label: 'Feedback' },
  { value: 'Partnership', label: 'Partnership' },
]

const contactInfo = [
  {
    icon: 'LocationIcon',
    title: 'Address',
    content: 'P.O. Box 000, Dar es Salaam, Tanzania',
  },
  {
    icon: 'PhoneIcon',
    title: 'Phone',
    content: '+255 712 000 000',
  },
  {
    icon: 'EmailIcon',
    title: 'Email',
    content: 'info@matokeo.tz',
  },
  {
    icon: 'ClockIcon',
    title: 'Working Hours',
    content: 'Monday - Friday: 07:30 - 16:30',
  },
]

const handleSubmit = async () => {
  isSubmitting.value = true
  try {
    const response = await axios.post('/api/contact', formData.value)
    submitted.value = true
    formData.value = {
      name: '',
      email: '',
      phone: '',
      category: 'General',
      subject: '',
      message: '',
    }
  } catch (error) {
    console.error(error)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <!-- ... rest of the code remains the same ... -->
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
          <h1 class="text-5xl sm:text-6xl font-bold text-emerald-950 mb-6">Contact Us</h1>
          <p class="text-xl text-emerald-800/80 max-w-3xl mx-auto leading-relaxed">
            Have a question or issue? We're here to help. Contact us in any way that works best for you.
          </p>
        </div>
      </section>

      <!-- Contact Info Cards -->
      <section class="py-16 bg-white">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 max-w-6xl">
          <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
            <div v-for="info in contactInfo" :key="info.title" class="rounded-2xl bg-white shadow-md p-8 text-center border border-emerald-200 hover:shadow-lg transition-shadow relative">
              <div class="absolute -top-4 -right-4 w-12 h-12 bg-emerald-200/40 rounded-full blur-3xl"></div>
              <div class="text-4xl mb-4">
                <svg v-if="info.icon === 'LocationIcon'" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827-2.827l-1.94-6.784a2 2 0 012.827-2.827l6.784 1.94a20.02 20.02 0 012.622 4.97" />
                </svg>
                <svg v-else-if="info.icon === 'PhoneIcon'" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.716 3 6V5z" />
                </svg>
                <svg v-else-if="info.icon === 'EmailIcon'" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <svg v-else-if="info.icon === 'ClockIcon'" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
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
            <h2 class="text-3xl font-bold text-emerald-950 mb-8">Send a Message</h2>

            <!-- Success Message -->
            <div v-if="submitted" class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200">
              <p class="text-emerald-800 font-semibold flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Thank you! Your message has been received. We'll get back to you soon.
              </p>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Name -->
              <div>
                <label class="block text-sm font-semibold text-emerald-950 mb-2">Your Name</label>
                <input
                  v-model="formData.name"
                  type="text"
                  required
                  class="w-full px-4 py-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all"
                  placeholder="Your full name"
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
                <label class="block text-sm font-semibold text-emerald-950 mb-2">Phone</label>
                <input
                  v-model="formData.phone"
                  type="tel"
                  class="w-full px-4 py-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all"
                  placeholder="+255 712 000 000"
                />
              </div>

              <!-- Category -->
              <div>
                <label class="block text-sm font-semibold text-emerald-950 mb-2">Category</label>
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
                <label class="block text-sm font-semibold text-emerald-950 mb-2">Subject</label>
                <input
                  v-model="formData.subject"
                  type="text"
                  required
                  class="w-full px-4 py-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all"
                  placeholder="Subject of your message"
                />
              </div>

              <!-- Message -->
              <div>
                <label class="block text-sm font-semibold text-emerald-950 mb-2">Message</label>
                <textarea
                  v-model="formData.message"
                  required
                  rows="6"
                  class="w-full px-4 py-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all resize-none"
                  placeholder="Write your message here..."
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
                <span v-if="isSubmitting">Sending...</span>
                <span v-else>Send Message</span>
              </button>
            </form>
          </div>
        </div>
      </section>

      <!-- FAQ Section -->
      <section class="py-16 bg-white">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 max-w-3xl">
          <h2 class="text-3xl font-bold text-emerald-950 mb-12 text-center">Frequently Asked Questions</h2>
          <div class="space-y-4">
            <div v-for="(faq, idx) in [
              { q: 'Where is Matokeo used?', a: 'Matokeo is used by all schools in Tanzania to manage exam results.' },
              { q: 'What is the price of Matokeo?', a: 'Please contact us for pricing information.' },
              { q: 'Is there a mobile app?', a: 'Yes, Matokeo has apps for iOS and Android.' },
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
