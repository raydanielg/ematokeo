<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Header from '@/Components/Header.vue'
import Footer from '@/Components/Footer.vue'
import { ref } from 'vue'

defineProps({
  canLogin: {
    type: Boolean,
  },
  canRegister: {
    type: Boolean,
  },
})

const searchQuery = ref('')

const blogs = ref([
  {
    id: 1,
    title: 'Jinsi ya Kusimamia Matokeo ya Mitihani',
    excerpt: 'Jifunze jinsi ya kutumia Matokeo kwa kusimamia matokeo ya mitihani kwa usahihi...',
    content: 'Matokeo ni mfumo kamili wa kusimamia matokeo ya mitihani. Unaweza kuingiza matokeo kwa haraka, kuangalia ripoti za kina, na kusambaza matokeo kwa wanafunzi.',
    image: '/images/PXL_20250502_075446082.RAW-01.COVER.jpg',
    author: 'John Mwangi',
    date: '2025-12-02',
    readTime: '5 min',
    likes: 24,
    shares: 8,
    comments: 3,
    category: 'Tutorial'
  },
  {
    id: 2,
    title: 'Usalama wa Data kwenye Matokeo',
    excerpt: 'Tunajua umuhimu wa usalama wa data. Hapa tutaeleza jinsi tunavyolinda data yako...',
    content: 'Usalama wa data ni muhimu sana. Tunatumia encryption ya juu, backup otomatiki, na usalama wa kiwango cha enterprise ili kulinda data yako.',
    image: '/images/PXL_20250502_075446082.RAW-01.COVER.jpg',
    author: 'Jane Kipchoge',
    date: '2025-11-28',
    readTime: '7 min',
    likes: 18,
    shares: 5,
    comments: 2,
    category: 'Security'
  },
  {
    id: 3,
    title: 'Programu ya Simu ya Matokeo',
    excerpt: 'Matokeo ina programu ya simu kwa iOS na Android. Jifunze jinsi ya kuipakua...',
    content: 'Programu ya simu ya Matokeo inapatikana kwa iOS na Android. Wanafunzi, wazazi, na waliimu wanaweza kuangalia matokeo kwa haraka kutoka kwa simu yao.',
    image: '/images/PXL_20250502_075446082.RAW-01.COVER.jpg',
    author: 'Peter Kamau',
    date: '2025-11-25',
    readTime: '4 min',
    likes: 32,
    shares: 12,
    comments: 5,
    category: 'Mobile'
  },
  {
    id: 4,
    title: 'Kuanzisha Shule Yako kwenye Matokeo',
    excerpt: 'Hatua za kuanzisha shule yako kwenye Matokeo na kuanza kutumia mfumo...',
    content: 'Kuanzisha shule yako kwenye Matokeo ni rahisi. Fuata hatua hizi: 1. Jisajili, 2. Ongeza shule, 3. Ongeza wanafunzi, 4. Ongeza mitihani, 5. Ingiza matokeo.',
    image: '/images/PXL_20250502_075446082.RAW-01.COVER.jpg',
    author: 'John Mwangi',
    date: '2025-11-20',
    readTime: '6 min',
    likes: 28,
    shares: 10,
    comments: 4,
    category: 'Getting Started'
  }
])

const filteredBlogs = computed(() => {
  if (!searchQuery.value) return blogs.value
  return blogs.value.filter(blog =>
    blog.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    blog.excerpt.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

import { computed } from 'vue'
</script>

<template>
  <Head title="Blog" />
  <div class="relative w-full bg-white text-slate-900">
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
            <Link
              :href="route('login')"
              class="text-sm font-semibold text-emerald-900 hover:text-emerald-700 transition-colors"
            >
              Login
            </Link>

            <Link
              v-if="canRegister"
              :href="route('register')"
              class="rounded-full bg-emerald-600 px-6 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-600/40 hover:bg-emerald-700 transition-all"
            >
              Get Started
            </Link>
          </template>
        </nav>
      </template>
    </Header>

    <!-- Main Content -->
    <main class="w-full">
      <!-- Hero Section -->
      <section class="relative bg-gradient-to-br from-emerald-50 via-emerald-100 to-emerald-50 py-20 sm:py-32 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
          <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-200/40 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
          <div class="absolute bottom-0 left-0 w-96 h-96 bg-emerald-300/30 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>
        </div>

        <div class="relative z-10 mx-auto w-full px-4 sm:px-6 lg:px-8 text-center">
          <h1 class="text-5xl sm:text-6xl font-bold text-emerald-950 mb-6">
            Blog
          </h1>
          <p class="text-xl text-emerald-800/80 max-w-3xl mx-auto leading-relaxed">
            Jifunze kuhusu Matokeo, tips, tricks, na habari za mwisho kuhusu mfumo wa kusimamia matokeo
          </p>
        </div>
      </section>

      <!-- Search Section -->
      <section class="py-8 bg-white border-b border-emerald-100">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
          <div class="max-w-2xl mx-auto">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Tafuta blog..."
              class="w-full px-6 py-3 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none transition-colors"
            />
          </div>
        </div>
      </section>

      <!-- Blog List Section -->
      <section class="py-20 sm:py-24 bg-white">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
          <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-2">
            <Link
              v-for="blog in filteredBlogs"
              :key="blog.id"
              :href="`/blog/${blog.id}`"
              class="group overflow-hidden rounded-2xl border border-emerald-100/50 bg-white shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2"
            >
              <!-- Blog Image -->
              <div class="relative h-48 overflow-hidden bg-emerald-100">
                <img
                  :src="blog.image"
                  :alt="blog.title"
                  class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-300"
                />
                <div class="absolute top-4 left-4">
                  <span class="inline-flex items-center rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white">
                    {{ blog.category }}
                  </span>
                </div>
              </div>

              <!-- Blog Content -->
              <div class="p-6">
                <!-- Title -->
                <h3 class="text-xl font-bold text-emerald-950 mb-2 line-clamp-2 group-hover:text-emerald-700 transition-colors">
                  {{ blog.title }}
                </h3>

                <!-- Excerpt -->
                <p class="text-sm text-emerald-800/80 mb-4 line-clamp-2">
                  {{ blog.excerpt }}
                </p>

                <!-- Meta Info -->
                <div class="flex items-center justify-between text-xs text-emerald-700/80 mb-4 pb-4 border-b border-emerald-100">
                  <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                      </svg>
                      {{ new Date(blog.date).toLocaleDateString('sw-TZ') }}
                    </span>
                    <span class="flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      {{ blog.readTime }}
                    </span>
                  </div>
                  <span class="text-emerald-700 font-semibold">{{ blog.author }}</span>
                </div>

                <!-- Engagement Stats -->
                <div class="flex items-center justify-between text-xs text-emerald-700">
                  <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1 hover:text-emerald-600">
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                      </svg>
                      {{ blog.likes }}
                    </span>
                    <span class="flex items-center gap-1 hover:text-emerald-600">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.589 12.938 10 12.502 10 12c0-.502-.411-.938-1.316-1.342m0 2.684a3 3 0 110-2.684m9.108-3.342c.589.591 1.216 1.141 1.316 2.026.1.884-.227 1.32-1.316 1.932m0-2.958a9.776 9.776 0 00-1.316-2.026m0 2.684a3 3 0 110-2.684"/>
                      </svg>
                      {{ blog.shares }}
                    </span>
                    <span class="flex items-center gap-1 hover:text-emerald-600">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                      </svg>
                      {{ blog.comments }}
                    </span>
                  </div>
                </div>
              </div>
            </Link>
          </div>

          <!-- No Results -->
          <div v-if="filteredBlogs.length === 0" class="text-center py-12">
            <svg class="w-16 h-16 text-emerald-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-emerald-800/80 text-lg">Hakuna blog inayolingana na utafutaji wako</p>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <Footer />
  </div>
</template>
