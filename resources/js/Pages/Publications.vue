<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Header from '@/Components/Header.vue'
import Footer from '@/Components/Footer.vue'
import { ref } from 'vue'

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
})

const publications = ref([
  {
    name: 'Approved Exam Formats',
    files: [
      { name: 'Form Four Format.pdf', path: '/pdf/form_four_format.pdf' },
      { name: 'Form Two Format.pdf', path: '/pdf/form_two_format.pdf' },
    ],
  },
  {
    name: 'Approved Exam Guides',
    files: [
      { name: 'Exam Guide 2025.pdf', path: '/pdf/exam_guide_2025.pdf' },
    ],
  },
  {
    name: 'CA Guidelines',
    files: [
      { name: 'CA Guidelines.pdf', path: '/pdf/ca_guidelines.pdf' },
    ],
  },
])

const openFolder = ref(null)

const toggleFolder = (name) => {
  openFolder.value = openFolder.value === name ? null : name
}
</script>

<template>
  <Head title="Publications" />
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

    <main class="flex-1 w-full px-4 sm:px-6 lg:px-8 py-16 bg-gradient-to-br from-emerald-50 via-white to-emerald-50">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold text-emerald-950 mb-10">Publications</h1>
        <div class="space-y-4">
          <div
            v-for="folder in publications"
            :key="folder.name"
            class="rounded-2xl bg-white border border-emerald-100 shadow-sm overflow-hidden"
          >
            <!-- Folder Header -->
            <button
              @click="toggleFolder(folder.name)"
              class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-emerald-50 transition-colors"
            >
              <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M3 4a2 2 0 012-2h5l2 2h9a2 2 0 012 2v2H3V4z"/><path d="M3 9h18v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                </svg>
                <span class="font-semibold text-emerald-900">{{ folder.name }}</span>
              </div>
              <svg class="w-5 h-5 text-emerald-600 transform transition-transform" :class="{ 'rotate-90': openFolder === folder.name }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>

            <!-- Files List -->
            <div v-if="openFolder === folder.name" class="border-t border-emerald-100 bg-emerald-50/50">
              <div v-for="file in folder.files" :key="file.name" class="flex items-center justify-between px-8 py-3 hover:bg-emerald-50">
                <span class="text-sm text-emerald-800">{{ file.name }}</span>
                <a :href="file.path" download class="text-emerald-600 hover:text-emerald-800">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                  </svg>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <Footer />
  </div>
</template>
