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

const blogId = ref(1)
const liked = ref(false)
const commentText = ref('')

// Sample blog data - in real app, this would come from backend
const blog = ref({
  id: 1,
  title: 'Jinsi ya Kusimamia Matokeo ya Mitihani',
  content: `Matokeo ni mfumo kamili wa kusimamia matokeo ya mitihani. Unaweza kuingiza matokeo kwa haraka, kuangalia ripoti za kina, na kusambaza matokeo kwa wanafunzi.

Mfumo huu umebunwa kwa mahitaji ya shule za Tanzania, ikiwemo kusimamia mitihani ya mikoa na wilaya.

Kwa nini Matokeo?
- Rahisi kutumia
- Usalama wa juu
- Ripoti za kina
- Programu ya simu
- Msaada 24/7

Jinsi ya kuanza:
1. Jisajili kwenye Matokeo
2. Ongeza shule yako
3. Ongeza wanafunzi
4. Ongeza mitihani
5. Ingiza matokeo
6. Sambaza matokeo kwa wanafunzi

Matokeo inatumiwa na shule zote za Tanzania na inakamatia matokeo kwa usahihi na usalama.`,
  image: '/images/PXL_20250502_075446082.RAW-01.COVER.jpg',
  author: 'John Mwangi',
  date: '2025-12-02',
  readTime: '5 min',
  likes: 24,
  shares: 8,
  comments: 3,
  category: 'Tutorial'
})

const comments = ref([
  {
    id: 1,
    author: 'Jane Kipchoge',
    text: 'Asante kwa maelezo mazuri! Hii ilinisaidia sana.',
    date: '2025-12-02',
    likes: 5
  },
  {
    id: 2,
    author: 'Peter Kamau',
    text: 'Ninataka kujua zaidi kuhusu usalama wa data.',
    date: '2025-12-01',
    likes: 2
  }
])

const toggleLike = () => {
  liked.value = !liked.value
  blog.value.likes += liked.value ? 1 : -1
}

const addComment = () => {
  if (commentText.value.trim()) {
    comments.value.unshift({
      id: comments.value.length + 1,
      author: 'You',
      text: commentText.value,
      date: new Date().toISOString().split('T')[0],
      likes: 0
    })
    commentText.value = ''
  }
}

const relatedPosts = ref([
  {
    id: 2,
    title: 'Usalama wa Data kwenye Matokeo',
    image: '/images/PXL_20250502_075446082.RAW-01.COVER.jpg',
    views: 156,
    likes: 18
  },
  {
    id: 3,
    title: 'Programu ya Simu ya Matokeo',
    image: '/images/PXL_20250502_075446082.RAW-01.COVER.jpg',
    views: 234,
    likes: 32
  },
  {
    id: 4,
    title: 'Kuanzisha Shule Yako kwenye Matokeo',
    image: '/images/PXL_20250502_075446082.RAW-01.COVER.jpg',
    views: 189,
    likes: 28
  }
])

const mostViewed = ref([
  { id: 3, title: 'Programu ya Simu ya Matokeo', views: 234 },
  { id: 4, title: 'Kuanzisha Shule Yako kwenye Matokeo', views: 189 },
  { id: 2, title: 'Usalama wa Data kwenye Matokeo', views: 156 },
  { id: 1, title: 'Jinsi ya Kusimamia Matokeo ya Mitihani', views: 142 }
])
</script>

<template>
  <Head :title="blog.title" />
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
      <!-- Blog Header -->
      <section class="py-12 bg-gradient-to-br from-emerald-50 to-white border-b border-emerald-100">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 max-w-3xl">
          <Link href="/blog" class="inline-flex items-center gap-2 text-emerald-600 hover:text-emerald-700 mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Rudi kwenye Blog
          </Link>

          <div class="inline-flex items-center rounded-full bg-emerald-100 px-4 py-2 mb-4">
            <span class="text-sm font-semibold text-emerald-700">{{ blog.category }}</span>
          </div>

          <h1 class="text-4xl sm:text-5xl font-bold text-emerald-950 mb-4">
            {{ blog.title }}
          </h1>

          <div class="flex flex-wrap items-center gap-6 text-sm text-emerald-800/80">
            <span class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              {{ new Date(blog.date).toLocaleDateString('sw-TZ') }}
            </span>
            <span class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ blog.readTime }}
            </span>
            <span class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ blog.author }}
            </span>
          </div>
        </div>
      </section>

      <!-- Blog Image -->
      <section class="py-8 bg-white">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 max-w-3xl">
          <img
            :src="blog.image"
            :alt="blog.title"
            class="w-full h-96 object-cover rounded-2xl shadow-lg"
          />
        </div>
      </section>

      <!-- Blog Content -->
      <section class="py-12 bg-white">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 max-w-3xl">
          <div class="prose prose-emerald max-w-none">
            <p v-for="(paragraph, idx) in blog.content.split('\n\n')" :key="idx" class="text-lg text-emerald-900/80 leading-relaxed mb-6 whitespace-pre-wrap">
              {{ paragraph }}
            </p>
          </div>
        </div>
      </section>

      <!-- Engagement Section -->
      <section class="py-8 bg-emerald-50 border-y border-emerald-100">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 max-w-3xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-6">
              <button
                @click="toggleLike"
                class="flex items-center gap-2 px-4 py-2 rounded-full transition-all"
                :class="liked ? 'bg-red-100 text-red-600' : 'bg-white text-emerald-700 hover:bg-emerald-100'"
              >
                <svg class="w-5 h-5" :fill="liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 20 20">
                  <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                </svg>
                {{ blog.likes }}
              </button>

              <button class="flex items-center gap-2 px-4 py-2 rounded-full bg-white text-emerald-700 hover:bg-emerald-100 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.589 12.938 10 12.502 10 12c0-.502-.411-.938-1.316-1.342m0 2.684a3 3 0 110-2.684m9.108-3.342c.589.591 1.216 1.141 1.316 2.026.1.884-.227 1.32-1.316 1.932m0-2.958a9.776 9.776 0 00-1.316-2.026m0 2.684a3 3 0 110-2.684"/>
                </svg>
                {{ blog.shares }}
              </button>
            </div>

            <span class="text-sm text-emerald-800/80">
              {{ blog.comments }} comments
            </span>
          </div>
        </div>
      </section>

      <!-- Comments Section -->
      <section class="py-12 bg-white">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 max-w-3xl">
          <h2 class="text-2xl font-bold text-emerald-950 mb-8">Comments ({{ comments.length }})</h2>

          <!-- Add Comment -->
          <div class="mb-8 p-6 rounded-2xl bg-emerald-50 border border-emerald-100">
            <textarea
              v-model="commentText"
              placeholder="Andika comment yako..."
              class="w-full px-4 py-3 rounded-lg border border-emerald-200 bg-white text-emerald-950 placeholder-emerald-600 focus:border-emerald-500 focus:outline-none resize-none"
              rows="4"
            ></textarea>
            <button
              @click="addComment"
              class="mt-4 px-6 py-2 rounded-full bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-colors"
            >
              Post Comment
            </button>
          </div>

          <!-- Comments List -->
          <div class="space-y-6">
            <div v-for="comment in comments" :key="comment.id" class="p-6 rounded-2xl border border-emerald-100 bg-white hover:shadow-md transition-shadow">
              <div class="flex items-start justify-between mb-3">
                <div>
                  <h3 class="font-semibold text-emerald-950">{{ comment.author }}</h3>
                  <p class="text-xs text-emerald-800/60">{{ new Date(comment.date).toLocaleDateString('sw-TZ') }}</p>
                </div>
              </div>

              <p class="text-emerald-900/80 mb-4">{{ comment.text }}</p>

              <button class="flex items-center gap-2 text-sm text-emerald-700 hover:text-emerald-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                  <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                </svg>
                {{ comment.likes }}
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Related Posts Section -->
      <section class="py-12 bg-gradient-to-br from-emerald-50 to-white">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 max-w-3xl">
          <h2 class="text-2xl font-bold text-emerald-950 mb-8">Related Posts</h2>
          <div class="grid gap-6 md:grid-cols-3">
            <Link
              v-for="post in relatedPosts"
              :key="post.id"
              :href="`/blog/${post.id}`"
              class="group rounded-xl overflow-hidden border border-emerald-100 bg-white shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-1"
            >
              <div class="relative h-40 overflow-hidden bg-emerald-100">
                <img
                  :src="post.image"
                  :alt="post.title"
                  class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-300"
                />
              </div>
              <div class="p-4">
                <h3 class="font-semibold text-emerald-950 line-clamp-2 group-hover:text-emerald-700 mb-3">
                  {{ post.title }}
                </h3>
                <div class="flex items-center justify-between text-xs text-emerald-700/80">
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    {{ post.views }}
                  </span>
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                    </svg>
                    {{ post.likes }}
                  </span>
                </div>
              </div>
            </Link>
          </div>
        </div>
      </section>

      <!-- Most Viewed Section -->
      <section class="py-12 bg-white">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 max-w-3xl">
          <h2 class="text-2xl font-bold text-emerald-950 mb-8">Most Viewed</h2>
          <div class="space-y-3">
            <Link
              v-for="(post, idx) in mostViewed"
              :key="post.id"
              :href="`/blog/${post.id}`"
              class="flex items-center gap-4 p-4 rounded-lg border border-emerald-100 bg-emerald-50/50 hover:bg-emerald-100 transition-colors group"
            >
              <div class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-600 text-white font-bold text-sm">
                {{ idx + 1 }}
              </div>
              <div class="flex-1">
                <h3 class="font-semibold text-emerald-950 group-hover:text-emerald-700 line-clamp-1">
                  {{ post.title }}
                </h3>
              </div>
              <div class="flex items-center gap-1 text-xs text-emerald-700/80 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                {{ post.views }}
              </div>
            </Link>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <Footer />
  </div>
</template>
