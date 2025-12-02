<script setup lang="ts">
import { ref } from 'vue'

const email = ref('')
const subscribeMessage = ref('')

const handleSubscribe = () => {
  if (email.value) {
    subscribeMessage.value = 'Asante! Umesajiliwa kwa updates.'
    email.value = ''
    setTimeout(() => {
      subscribeMessage.value = ''
    }, 3000)
  }
}
</script>

<template>
  <footer class="bg-slate-900 text-slate-100">
    <!-- Main Footer Content -->
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-5">
        <!-- Column 1: About -->
        <div class="lg:col-span-1">
          <h3 class="text-lg font-bold text-white mb-4">Matokeo</h3>
          <p class="text-sm text-slate-400 leading-relaxed mb-4">
            Mfumo wa kusimamia matokeo ya mitihani kwa shule za Tanzania. Regional & District examinations management.
          </p>
          <div class="space-y-2 text-sm text-slate-400">
            <p><strong class="text-white">P.O. Box 000</strong>, Dar es Salaam</p>
            <p><strong class="text-white">Phone:</strong> 0712 000 000</p>
            <p><strong class="text-white">Email:</strong> info@matokeo.tz</p>
          </div>
        </div>

        <!-- Column 2: Navigation Links -->
        <div>
          <h4 class="text-base font-bold text-white mb-4">Navigation</h4>
          <ul class="space-y-2 text-sm">
            <li>
              <a
                href="/"
                class="text-slate-400 hover:text-emerald-400 transition-colors duration-300 inline-flex items-center gap-2 group"
              >
                Home
                <span class="group-hover:translate-x-1 transition-transform duration-300">›</span>
              </a>
            </li>
            <li>
              <a
                href="/about"
                class="text-slate-400 hover:text-emerald-400 transition-colors duration-300 inline-flex items-center gap-2 group"
              >
                About
                <span class="group-hover:translate-x-1 transition-transform duration-300">›</span>
              </a>
            </li>
            <li>
              <a
                href="/blog"
                class="text-slate-400 hover:text-emerald-400 transition-colors duration-300 inline-flex items-center gap-2 group"
              >
                Blog
                <span class="group-hover:translate-x-1 transition-transform duration-300">›</span>
              </a>
            </li>
            <li>
              <a
                href="/contact"
                class="text-slate-400 hover:text-emerald-400 transition-colors duration-300 inline-flex items-center gap-2 group"
              >
                Contact
                <span class="group-hover:translate-x-1 transition-transform duration-300">›</span>
              </a>
            </li>
          </ul>
        </div>

        <!-- Column 3: Systems -->
        <div>
          <h4 class="text-base font-bold text-white mb-4">Systems</h4>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="text-slate-400 hover:text-emerald-400 transition-colors duration-300 inline-flex items-center gap-2 group">Staff Portal <span class="group-hover:translate-x-1 transition-transform duration-300">›</span></a></li>
            <li><a href="#" class="text-slate-400 hover:text-emerald-400 transition-colors duration-300 inline-flex items-center gap-2 group">PREM <span class="group-hover:translate-x-1 transition-transform duration-300">›</span></a></li>
            <li><a href="#" class="text-slate-400 hover:text-emerald-400 transition-colors duration-300 inline-flex items-center gap-2 group">PreMS <span class="group-hover:translate-x-1 transition-transform duration-300">›</span></a></li>
            <li><a href="#" class="text-slate-400 hover:text-emerald-400 transition-colors duration-300 inline-flex items-center gap-2 group">eServices <span class="group-hover:translate-x-1 transition-transform duration-300">›</span></a></li>
            <li><a href="#" class="text-slate-400 hover:text-emerald-400 transition-colors duration-300 inline-flex items-center gap-2 group">Teachers' Registration <span class="group-hover:translate-x-1 transition-transform duration-300">›</span></a></li>
          </ul>
        </div>

        <!-- Column 4: Working Hours -->
        <div>
          <h4 class="text-base font-bold text-white mb-4">Working Hours</h4>
          <div class="space-y-3 text-sm">
            <div>
              <p class="text-slate-400">Monday to Friday</p>
              <p class="text-white font-semibold">07:30 - 16:30</p>
            </div>
            <div>
              <p class="text-slate-400">Saturday to Sunday</p>
              <p class="text-white font-semibold">Closed</p>
            </div>
            <div class="pt-2 border-t border-slate-700">
              <p class="text-slate-400 text-xs">Support available 24/7 via email</p>
            </div>
          </div>
        </div>

        <!-- Column 5: Newsletter -->
        <div>
          <h4 class="text-base font-bold text-white mb-4">Subscribe</h4>
          <p class="text-sm text-slate-400 mb-4">
            Pata updates za Matokeo kwa email yako.
          </p>
          <form @submit.prevent="handleSubscribe" class="space-y-2">
            <input
              v-model="email"
              type="email"
              placeholder="Email yako"
              required
              class="w-full px-4 py-2 rounded-lg bg-slate-800 text-white placeholder-slate-500 border border-slate-700 focus:border-emerald-500 focus:outline-none transition-colors duration-300"
            />
            <button
              type="submit"
              class="w-full px-4 py-2 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 transition-colors duration-300 transform hover:scale-105"
            >
              Subscribe
            </button>
            <p v-if="subscribeMessage" class="text-xs text-emerald-400 animate-pulse">
              {{ subscribeMessage }}
            </p>
          </form>
        </div>
      </div>

      <!-- Divider -->
      <div class="border-t border-slate-700 my-8"></div>

      <!-- Bottom Footer -->
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-sm text-slate-400">
          &copy; {{ new Date().getFullYear() }} Matokeo - Results Management System. All rights reserved.
        </p>

        <!-- Social Media Links -->
        <div class="flex gap-6">
          <!-- Facebook -->
          <a href="#" title="Facebook" class="text-slate-400 hover:text-emerald-400 transition-all duration-300 transform hover:scale-125 hover:-translate-y-1">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
          </a>
          <!-- Twitter -->
          <a href="#" title="Twitter" class="text-slate-400 hover:text-emerald-400 transition-all duration-300 transform hover:scale-125 hover:-translate-y-1">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
            </svg>
          </a>
          <!-- GitHub -->
          <a href="#" title="GitHub" class="text-slate-400 hover:text-emerald-400 transition-all duration-300 transform hover:scale-125 hover:-translate-y-1">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
            </svg>
          </a>
          <!-- LinkedIn -->
          <a href="#" title="LinkedIn" class="text-slate-400 hover:text-emerald-400 transition-all duration-300 transform hover:scale-125 hover:-translate-y-1">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.475-2.236-1.986-2.236-1.081 0-1.722.722-2.004 1.418-.103.249-.129.597-.129.946v5.441h-3.554s.05-8.81 0-9.728h3.554v1.375c.427-.659 1.191-1.595 2.897-1.595 2.117 0 3.704 1.385 3.704 4.362v5.586zM5.337 9.433c-1.144 0-1.915-.758-1.915-1.708 0-.951.77-1.708 1.954-1.708 1.185 0 1.915.757 1.915 1.708 0 .95-.73 1.708-1.954 1.708zm1.946 11.019H3.391V9.724h3.892v10.728zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0z"/>
            </svg>
          </a>
        </div>
      </div>
    </div>
  </footer>
</template>
