<script setup>
import { Head } from '@inertiajs/vue3'
import { onMounted } from 'vue'
import Header from '@/Components/Header.vue'
import Footer from '@/Components/Footer.vue'

onMounted(() => {
  // Redirect to dashboard after 3 seconds
  setTimeout(() => {
    window.location.href = '/dashboard'
  }, 3000)
})
</script>

<template>
  <Head title="Account Created Successfully" />
  <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-emerald-50 flex flex-col">
    <!-- Header -->
    <Header>
      <template #auth>
        <nav class="flex items-center gap-3">
          <!-- Empty for success page -->
        </nav>
      </template>
    </Header>

    <main class="flex-1 w-full flex items-center justify-center px-4 py-12">
      <div class="w-full max-w-md">
        <div class="rounded-3xl bg-white/80 backdrop-blur-xl border border-emerald-200/60 shadow-2xl p-8 sm:p-12 text-center">
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
          <p class="text-emerald-800/70 mb-8">Your account has been successfully created. Welcome to Matokeo!</p>

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
    </main>

    <!-- Footer -->
    <Footer />
  </div>
</template>
