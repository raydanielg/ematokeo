<script setup lang="ts">
import { ref } from 'vue'

const qrCode = ref('/images/9.svg')
</script>

<style scoped>
@keyframes scan {
  0% {
    top: 0;
  }
  100% {
    top: 100%;
  }
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-scan {
  animation: scan 3s infinite;
}

.animate-fade-in {
  animation: fade-in 0.8s ease-out forwards;
  opacity: 0;
}
</style>

<template>
  <section class="relative bg-gradient-to-r from-emerald-600 via-emerald-500 to-emerald-600 py-20 sm:py-24 overflow-hidden">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-0 left-1/4 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
      <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
    </div>

    <div class="relative z-10 mx-auto w-full px-4 sm:px-6 lg:px-8">
      <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
        <!-- Left: Content -->
        <div class="text-white">
          <h2 class="text-4xl sm:text-5xl font-bold mb-4 animate-fade-in">
            Pakua Matokeo App
          </h2>
          <p class="text-lg text-white/90 mb-6 leading-relaxed animate-fade-in" style="animation-delay: 0.1s">
            Wanafunzi, wazazi, na waliimu wanaweza kupata matokeo kwa haraka kwa simu yao. Pakua app sasa na jifunze matokeo yako kwa dakika chache.
          </p>
          <p class="text-base text-white/80 mb-8 animate-fade-in" style="animation-delay: 0.2s">
            Inayopatikana kwa iOS na Android. Haraka, salama, na rahisi kutumia.
          </p>

          <!-- Download Buttons -->
          <div class="flex flex-wrap gap-4 animate-fade-in" style="animation-delay: 0.3s">
            <!-- Play Store Button -->
            <a
              href="https://play.google.com/store"
              target="_blank"
              rel="noopener noreferrer"
              class="group inline-flex items-center gap-3 bg-white rounded-full px-6 py-3 font-semibold text-emerald-600 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1"
            >
              <svg class="w-6 h-6 group-hover:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M3 13.5h8V3H3m8.5 0v10h8.5V3M3 21h8v-7.5H3M12.5 13.5H21V21h-8.5z"/>
              </svg>
              <div class="text-left">
                <p class="text-xs text-emerald-600/80">GET IT ON</p>
                <p class="text-sm font-bold">Google Play</p>
              </div>
            </a>

            <!-- App Store Button -->
            <a
              href="https://apps.apple.com"
              target="_blank"
              rel="noopener noreferrer"
              class="group inline-flex items-center gap-3 bg-white rounded-full px-6 py-3 font-semibold text-emerald-600 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1"
            >
              <svg class="w-6 h-6 group-hover:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.05 13.5c-.73 0-1.38.35-1.8.88-.42.53-.66 1.25-.66 2.02 0 .77.24 1.49.66 2.02.42.53 1.07.88 1.8.88.73 0 1.38-.35 1.8-.88.42-.53.66-1.25.66-2.02 0-.77-.24-1.49-.66-2.02-.42-.53-1.07-.88-1.8-.88zm-11 0c-.73 0-1.38.35-1.8.88C3.83 14.91 3.59 15.63 3.59 16.4c0 .77.24 1.49.66 2.02.42.53 1.07.88 1.8.88.73 0 1.38-.35 1.8-.88.42-.53.66-1.25.66-2.02 0-.77-.24-1.49-.66-2.02-.42-.53-1.07-.88-1.8-.88z"/>
              </svg>
              <div class="text-left">
                <p class="text-xs text-emerald-600/80">DOWNLOAD ON</p>
                <p class="text-sm font-bold">App Store</p>
              </div>
            </a>
          </div>
        </div>

        <!-- Right: QR Code -->
        <div class="flex justify-center animate-fade-in" style="animation-delay: 0.4s">
          <div class="relative group">
            <!-- Glow effect -->
            <div class="absolute inset-0 bg-white/20 rounded-3xl blur-2xl group-hover:bg-white/30 transition-all duration-300"></div>

            <!-- QR Code Card -->
            <div class="relative bg-white rounded-3xl p-8 shadow-2xl transform group-hover:scale-105 transition-transform duration-300">
              <!-- QR Code Image -->
              <div class="bg-white rounded-2xl p-6 mb-4 flex items-center justify-center min-h-64">
                <img
                  :src="qrCode"
                  alt="QR Code"
                  class="w-full h-full object-contain"
                />
              </div>

              <!-- Scan Text -->
              <p class="text-center text-emerald-600 font-semibold text-sm mt-4">
                SCAN HERE
              </p>

              <!-- Animated scan lines -->
              <div class="absolute inset-0 rounded-3xl overflow-hidden pointer-events-none">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-white/50 to-transparent animate-scan"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
