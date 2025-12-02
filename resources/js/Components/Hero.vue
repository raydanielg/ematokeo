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

      <!-- Phone mockup with Matokeo dashboard -->
      <div class="mt-16 w-full max-w-md relative">
        <!-- Phone frame -->
        <div class="rounded-3xl border-8 border-emerald-900 bg-black p-3 shadow-2xl">
          <!-- Notch -->
          <div class="absolute top-0 left-1/2 transform -translate-x-1/2 w-40 h-7 bg-black rounded-b-3xl z-20"></div>

          <!-- Screen content -->
          <div class="rounded-2xl bg-emerald-50 h-96 overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-3 text-white">
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold">9:41</span>
                <div class="flex gap-1">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M17.778 8.222c-4.296-4.296-11.26-4.296-15.556 0A1 1 0 01.808 6.808c5.076-5.077 13.308-5.077 18.384 0a1 1 0 01-1.414 1.414zM14.95 11.05a7 7 0 00-9.9 0 1 1 0 00-1.414-1.414 9 9 0 0112.728 0 1 1 0 00-1.414 1.414zM12.12 13.88a3 3 0 00-4.242 0 1 1 0 00-1.415-1.415 5 5 0 017.072 0 1 1 0 00-1.415 1.415zM9 16a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                </div>
              </div>
              <div class="text-sm font-semibold">Good Morning 👋</div>
              <div class="text-xs opacity-90">Matokeo Dashboard</div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 px-4 py-2 bg-emerald-100">
              <button class="px-3 py-1 bg-emerald-600 text-white text-xs font-semibold rounded-full">Today</button>
              <button class="px-3 py-1 bg-white text-emerald-900 text-xs font-semibold rounded-full">This Week</button>
              <button class="px-3 py-1 bg-white text-emerald-900 text-xs font-semibold rounded-full">This Month</button>
            </div>

            <!-- Stats Cards -->
            <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3">
              <!-- Results Summary -->
              <div class="bg-white rounded-lg p-3 border border-emerald-200">
                <div class="text-xs font-semibold text-emerald-900 mb-2">Results Summary</div>
                <div class="grid grid-cols-3 gap-2 text-center">
                  <div>
                    <div class="text-xs text-emerald-700">Total Results</div>
                    <div class="text-lg font-bold text-emerald-600">2,450</div>
                  </div>
                  <div>
                    <div class="text-xs text-emerald-700">Classes</div>
                    <div class="text-lg font-bold text-emerald-600">45</div>
                  </div>
                  <div>
                    <div class="text-xs text-emerald-700">Students</div>
                    <div class="text-lg font-bold text-emerald-600">1,890</div>
                  </div>
                </div>
              </div>

              <!-- Features Grid -->
              <div class="grid grid-cols-3 gap-2">
                <div class="bg-white rounded-lg p-2 text-center border border-emerald-200">
                  <svg class="w-5 h-5 mx-auto text-emerald-600 mb-1" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                  <div class="text-xs font-semibold text-emerald-900">Analytics</div>
                </div>
                <div class="bg-white rounded-lg p-2 text-center border border-emerald-200">
                  <svg class="w-5 h-5 mx-auto text-emerald-600 mb-1" fill="currentColor" viewBox="0 0 20 20"><path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z"/></svg>
                  <div class="text-xs font-semibold text-emerald-900">Chat</div>
                </div>
                <div class="bg-white rounded-lg p-2 text-center border border-emerald-200">
                  <svg class="w-5 h-5 mx-auto text-emerald-600 mb-1" fill="currentColor" viewBox="0 0 20 20"><path d="M3 1a1 1 0 011-1h12a1 1 0 011 1H3zm0 4a1 1 0 011-1h12a1 1 0 011 1H3zm0 4a1 1 0 011-1h12a1 1 0 011 1H3zm0 4a1 1 0 011-1h12a1 1 0 011 1H3z"/></svg>
                  <div class="text-xs font-semibold text-emerald-900">Mobile App</div>
                </div>
              </div>
            </div>

            <!-- Bottom Navigation -->
            <div class="flex justify-around items-center border-t border-emerald-200 bg-white px-2 py-2">
              <button class="flex flex-col items-center gap-1 text-emerald-600">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
                <span class="text-xs font-semibold">Home</span>
              </button>
              <button class="flex flex-col items-center gap-1 text-emerald-400">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v6h16V5a2 2 0 00-2-2H5z"/></svg>
                <span class="text-xs">Results</span>
              </button>
              <button class="flex flex-col items-center gap-1 text-emerald-400">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                <span class="text-xs">Stats</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
