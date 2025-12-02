<template>
  <section class="relative bg-gradient-to-br from-emerald-50 via-white to-emerald-50 py-20 sm:py-24">
    <!-- Decorative background elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-100/30 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
      <div class="absolute bottom-0 left-0 w-96 h-96 bg-emerald-100/20 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>
    </div>

    <div class="relative z-10 mx-auto w-full px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="text-center mb-16">
        <h2 class="text-4xl sm:text-5xl font-bold text-emerald-950 mb-4">
          Kwa nini Matokeo?
        </h2>
        <p class="text-lg text-emerald-800/80 max-w-3xl mx-auto leading-relaxed">
          Matokeo ni mfumo kamili wa kusimamia matokeo ya mitihani, wanafunzi, na shule kwa njia rahisi na yenye ufanisi.
        </p>
      </div>

      <!-- Features Grid (3x2) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Feature 1 -->
        <div class="group relative bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border border-emerald-100/50 hover:border-emerald-300">
          <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          <div class="relative">
            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-6 group-hover:bg-emerald-200 transition-colors duration-300">
              <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h3 class="text-xl font-bold text-emerald-950 mb-3">Rahisi na Haraka</h3>
            <p class="text-emerald-800/80 leading-relaxed">
              Kuingiza na kusimamia matokeo kwa haraka bila matatizo. Interface yenye urahisi kwa wote.
            </p>
          </div>
        </div>

        <!-- Feature 2 -->
        <div class="group relative bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border border-emerald-100/50 hover:border-emerald-300">
          <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          <div class="relative">
            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-6 group-hover:bg-emerald-200 transition-colors duration-300">
              <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
              </svg>
            </div>
            <h3 class="text-xl font-bold text-emerald-950 mb-3">Kuzuia Makosa</h3>
            <p class="text-emerald-800/80 leading-relaxed">
              Mfumo unakuza makosa na kuangalia data kwa usahihi kabla ya kukamatia.
            </p>
          </div>
        </div>

        <!-- Feature 3 -->
        <div class="group relative bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border border-emerald-100/50 hover:border-emerald-300">
          <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          <div class="relative">
            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-6 group-hover:bg-emerald-200 transition-colors duration-300">
              <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h3 class="text-xl font-bold text-emerald-950 mb-3">Akaunti Salama</h3>
            <p class="text-emerald-800/80 leading-relaxed">
              Matokeo yako yalindwa kwa siri na usalama wa juu. Hakuna mtu anayeweza kuona isipokuwa wewe.
            </p>
          </div>
        </div>

        <!-- Feature 4 -->
        <div class="group relative bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border border-emerald-100/50 hover:border-emerald-300">
          <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          <div class="relative">
            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-6 group-hover:bg-emerald-200 transition-colors duration-300">
              <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
            </div>
            <h3 class="text-xl font-bold text-emerald-950 mb-3">Ripoti Kamili</h3>
            <p class="text-emerald-800/80 leading-relaxed">
              Tazama ripoti za kina kuhusu matokeo, takwimu, na mwenendo wa shule kwa muda mrefu.
            </p>
          </div>
        </div>

        <!-- Feature 5 -->
        <div class="group relative bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border border-emerald-100/50 hover:border-emerald-300">
          <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          <div class="relative">
            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-6 group-hover:bg-emerald-200 transition-colors duration-300">
              <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h3 class="text-xl font-bold text-emerald-950 mb-3">Msaada 24/7</h3>
            <p class="text-emerald-800/80 leading-relaxed">
              Timu yetu iko tayari kusaidia wakati wowote. Maswali? Tutajibu haraka na kwa upole.
            </p>
          </div>
        </div>

        <!-- Feature 6 -->
        <div class="group relative bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border border-emerald-100/50 hover:border-emerald-300">
          <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          <div class="relative">
            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-6 group-hover:bg-emerald-200 transition-colors duration-300">
              <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
            </div>
            <h3 class="text-xl font-bold text-emerald-950 mb-3">Haraka Sana</h3>
            <p class="text-emerald-800/80 leading-relaxed">
              Mfumo unafanya kazi kwa kasi. Hakuna kusubiri. Matokeo yanakuja haraka na salama.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
