<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const currentSlide = ref(0)
const autoRotateInterval = ref(null)

const screenshots = [
  { name: 'Dashboard Overview', image: '/images/screenshot/dashbaordoverview.png' },
  { name: 'All Students', image: '/images/screenshot/Allstduents.png' },
  { name: 'Marks Entry', image: '/images/screenshot/marksenters.png' },
  { name: 'School Calendar', image: '/images/screenshot/schoolcalendar.png' },
  { name: 'School Performance Report', image: '/images/screenshot/schoolperfoance reotpt.png' },
  { name: 'Student Promotion', image: '/images/screenshot/stdeuntpromotion.png' },
  { name: 'Student Reports', image: '/images/screenshot/studentreports.png' },
  { name: 'Timetables', image: '/images/screenshot/timetablses.png' },
]

const nextSlide = () => {
  currentSlide.value = (currentSlide.value + 1) % screenshots.length
}

const prevSlide = () => {
  currentSlide.value = (currentSlide.value - 1 + screenshots.length) % screenshots.length
}

const goToSlide = (index) => {
  currentSlide.value = index
}

const startAutoRotate = () => {
  autoRotateInterval.value = setInterval(() => {
    nextSlide()
  }, 5000) // Change slide every 5 seconds
}

const stopAutoRotate = () => {
  if (autoRotateInterval.value) {
    clearInterval(autoRotateInterval.value)
  }
}

onMounted(() => {
  startAutoRotate()
})

onUnmounted(() => {
  stopAutoRotate()
})
</script>

<template>
  <section class="relative min-h-screen bg-gradient-to-br from-emerald-50 via-emerald-100 to-emerald-50 overflow-hidden">
    <!-- Geometric shapes background (left side) -->
    <div class="absolute left-0 top-1/4 w-96 h-96 bg-emerald-200/40 rounded-full blur-3xl -translate-x-1/2"></div>
    <div class="absolute left-20 top-1/3 w-72 h-72 bg-emerald-300/30 rounded-full blur-2xl"></div>

    <div class="relative z-10 mx-auto w-full px-4 sm:px-6 lg:px-8 py-20 flex flex-col items-center justify-center min-h-screen">
      <!-- Badge -->
      <div class="inline-flex items-center gap-2 rounded-full bg-white/60 px-4 py-2 mb-8 border border-emerald-200/50 backdrop-blur-sm">
        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
          <path d="M10.5 1.5H5.75A2.25 2.25 0 003.5 3.75v12.5A2.25 2.25 0 005.75 18.5h8.5a2.25 2.25 0 002.25-2.25V10" stroke="currentColor" stroke-width="1.5" fill="none"/>
        </svg>
        <span class="text-sm font-semibold text-emerald-900">Education Management System</span>
      </div>

      <!-- Main Heading -->
      <h1 class="text-center text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-emerald-950 mb-6 max-w-4xl">
        Manage <span class="text-emerald-600">Matokeo</span> Into Your Powerful
        <br />
        <span class="text-emerald-600">School System</span>
      </h1>

      <!-- Description -->
      <p class="text-center text-lg text-emerald-800/80 mb-8 max-w-2xl leading-relaxed">
        Accept results, manage classes, streams and grow your school directly through Matokeo - no app downloads required for your students.
      </p>

      <!-- CTA Button -->
      <button class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-8 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-600/40 hover:bg-emerald-700 transition">
        Get Started Today
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
      </button>

      <!-- Screenshot Carousel -->
      <div class="mt-16 w-full max-w-5xl relative group" @mouseenter="stopAutoRotate" @mouseleave="startAutoRotate">
        <!-- Main Carousel Container -->
        <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-white border-4 border-emerald-200">
          <!-- Carousel Images -->
          <div class="relative w-full h-96 md:h-[500px] lg:h-[600px] overflow-hidden">
            <div class="relative w-full h-full">
              <img
                v-for="(screenshot, index) in screenshots"
                :key="index"
                :src="screenshot.image"
                :alt="screenshot.name"
                class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500"
                :style="{ opacity: currentSlide === index ? 1 : 0 }"
              />
            </div>
          </div>

          <!-- Screenshot Name Overlay -->
          <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent px-6 py-4">
            <p class="text-white font-semibold text-lg">{{ screenshots[currentSlide].name }}</p>
          </div>

          <!-- Previous Button -->
          <button
            @click="prevSlide"
            class="absolute left-4 top-1/2 -translate-y-1/2 z-20 bg-white/80 hover:bg-white text-emerald-600 rounded-full p-3 transition-all opacity-0 group-hover:opacity-100 shadow-lg"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>

          <!-- Next Button -->
          <button
            @click="nextSlide"
            class="absolute right-4 top-1/2 -translate-y-1/2 z-20 bg-white/80 hover:bg-white text-emerald-600 rounded-full p-3 transition-all opacity-0 group-hover:opacity-100 shadow-lg"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <!-- Dot Indicators -->
        <div class="flex justify-center gap-2 mt-6">
          <button
            v-for="(_, index) in screenshots"
            :key="index"
            @click="goToSlide(index)"
            class="w-3 h-3 rounded-full transition-all"
            :class="currentSlide === index ? 'bg-emerald-600 w-8' : 'bg-emerald-300 hover:bg-emerald-400'"
          />
        </div>

        <!-- Slide Counter -->
        <div class="text-center mt-4 text-emerald-700 font-semibold">
          {{ currentSlide + 1 }} / {{ screenshots.length }}
        </div>
      </div>
    </div>
  </section>
</template>
