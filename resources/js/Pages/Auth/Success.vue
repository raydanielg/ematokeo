<script setup>
import { Head } from '@inertiajs/vue3'
import { onMounted } from 'vue'
import AuthLayout from '@/Layouts/AuthLayout.vue'

onMounted(() => {
  // Redirect to dashboard after 3 seconds
  setTimeout(() => {
    window.location.href = '/dashboard'
  }, 3000)
})
</script>

<template>
  <Head title="Account Created Successfully" />
  <AuthLayout :compact="true">
        <div class="text-center">
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
  </AuthLayout>
</template>
