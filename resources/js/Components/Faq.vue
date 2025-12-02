<script setup lang="ts">
import { ref } from 'vue'

interface FaqItem {
  id: number
  question: string
  answer: string
}

const openId = ref<number | null>(null)

const faqs = ref<FaqItem[]>([
  {
    id: 1,
    question: 'Which schools can use this system?',
    answer: 'Matokeo is suitable for all primary and secondary schools. The system is built for the needs of Tanzanian schools, including managing classes, streams, exams and results accurately.'
  },
  {
    id: 2,
    question: 'Is my data secure?',
    answer: 'Yes, your data is completely secure. We use high-level encryption, automatic backups, and enterprise-grade security. No one can see your results except you.'
  },
  {
    id: 3,
    question: 'What is the price of Matokeo?',
    answer: 'Matokeo has pricing that matches the size of your school. We offer special rates for government and private schools. Contact us for a real quote.'
  },
  {
    id: 4,
    question: 'Is there a mobile app?',
    answer: 'Yes! Matokeo has mobile apps for iOS and Android. Students, parents and teachers can quickly view results from their phones.'
  },
  {
    id: 5,
    question: 'If I have problems, who helps?',
    answer: 'Our support team is ready to help 24/7. You can contact us via email, WhatsApp, or phone. We will respond quickly and professionally.'
  },
  {
    id: 6,
    question: 'Can I transfer data from another system?',
    answer: 'Yes, we can help transfer your data from another system. Our team will provide complete data migration service.'
  }
])

const toggleFaq = (id: number) => {
  openId.value = openId.value === id ? null : id
}
</script>

<template>
  <section class="relative bg-gradient-to-br from-emerald-50 via-white to-emerald-50 py-20 sm:py-24">
    <!-- Decorative background -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-20 right-0 w-96 h-96 bg-emerald-100/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
      <div class="absolute bottom-0 left-0 w-96 h-96 bg-emerald-100/20 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>
    </div>

    <div class="relative z-10 mx-auto w-full px-4 sm:px-6 lg:px-8">
      <div class="grid gap-12 lg:grid-cols-2 lg:items-start">
        <!-- Left: Image -->
        <div class="relative group hidden lg:block">
          <div class="absolute inset-0 bg-gradient-to-br from-emerald-200 to-emerald-100 rounded-3xl blur-3xl opacity-50 group-hover:opacity-70 transition-opacity duration-300"></div>
          <div class="relative bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-3xl p-8 shadow-2xl overflow-hidden">
            <img
              src="/images/PXL_20250502_075432413.RAW-01.COVER.jpg"
              alt="FAQ"
              class="w-full h-full object-cover rounded-2xl transform group-hover:scale-105 transition-transform duration-300"
            />
          </div>
        </div>

        <!-- Right: FAQ Content -->
        <div>
          <!-- Header -->
          <div class="mb-8">
            <h2 class="text-4xl sm:text-5xl font-bold text-emerald-950 mb-4">
              Frequently Asked Questions
            </h2>
            <p class="text-lg text-emerald-800/80 leading-relaxed">
              Find answers to important questions about Matokeo. If you don't have an answer, contact us.
            </p>
          </div>

          <!-- FAQ Accordion -->
          <div class="space-y-4">
            <div
              v-for="(faq, index) in faqs"
              :key="faq.id"
              class="group"
              :style="{ animationDelay: `${index * 50}ms` }"
            >
              <button
                @click="toggleFaq(faq.id)"
                class="w-full text-left"
              >
                <div class="relative bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-all duration-300 border border-emerald-100/50 hover:border-emerald-300 overflow-hidden group"
                >
                  <!-- Background gradient on hover -->
                  <div class="absolute inset-0 bg-gradient-to-r from-emerald-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                  <!-- Content -->
                  <div class="relative flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-emerald-950 group-hover:text-emerald-700 transition-colors duration-300">
                      {{ faq.question }}
                    </h3>
                    <div class="flex-shrink-0 ml-4">
                      <svg
                        class="w-6 h-6 text-emerald-600 transition-transform duration-500 ease-out"
                        :class="{ 'rotate-180': openId === faq.id }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                      </svg>
                    </div>
                  </div>
                </div>
              </button>

              <!-- Answer -->
              <transition
                enter-active-class="transition-all duration-500 ease-out"
                leave-active-class="transition-all duration-300 ease-in"
                enter-from-class="opacity-0 -translate-y-4 max-h-0"
                enter-to-class="opacity-100 translate-y-0 max-h-96"
                leave-from-class="opacity-100 translate-y-0 max-h-96"
                leave-to-class="opacity-0 -translate-y-4 max-h-0"
              >
                <div
                  v-if="openId === faq.id"
                  class="overflow-hidden"
                >
                  <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-b-2xl p-6 border border-t-0 border-emerald-100/50 border-emerald-300">
                    <p class="text-emerald-800/90 leading-relaxed text-base">
                      {{ faq.answer }}
                    </p>
                  </div>
                </div>
              </transition>
            </div>
          </div>

          <!-- CTA Section -->
          <div class="mt-8">
            <p class="text-emerald-800/80 mb-4">
              Don't have an answer to your question?
            </p>
            <button class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-8 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-600/40 hover:bg-emerald-700 transition-all duration-300 transform hover:scale-105">
              Contact Us
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
