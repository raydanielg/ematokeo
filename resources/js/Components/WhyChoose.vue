<template>
  <section class="relative bg-white py-20 sm:py-24">
    <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
      <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
        <!-- Left: Image with animation -->
        <div class="relative group">
          <div class="absolute inset-0 bg-gradient-to-br from-emerald-200 to-emerald-100 rounded-3xl blur-2xl opacity-40 group-hover:opacity-60 transition-opacity duration-300"></div>
          <div class="relative bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-3xl p-8 shadow-xl overflow-hidden">
            <!-- Image -->
            <img 
              src="/images/PXL_20250502_075446082.RAW-01.COVER.jpg" 
              alt="Matokeo system" 
              class="w-full h-full object-cover rounded-2xl transform group-hover:scale-105 transition-transform duration-300"
            />
            <!-- Badge -->
            <div class="absolute bottom-6 left-6 bg-white rounded-full px-4 py-2 shadow-lg flex items-center gap-2">
              <span class="text-2xl font-bold text-emerald-600">⚡</span>
              <div>
                <p class="text-xs font-semibold text-emerald-900">Haraka Sana</p>
                <p class="text-xs text-emerald-700">Matokeo yako</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Content -->
        <div>
          <div class="mb-8">
            <h2 class="text-4xl sm:text-5xl font-bold text-emerald-950 mb-4">
              Kwa nini Matokeo?
            </h2>
            <p class="text-lg text-emerald-800/80 leading-relaxed">
              Matokeo inatoa suluhisho kamili kwa kusimamia matokeo ya mitihani, wanafunzi, na shule. Tumeandaa mfumo huu kwa mahitaji ya shule za Tanzania.
            </p>
          </div>

          <!-- Features List -->
          <div class="space-y-4">
            <!-- Feature 1 -->
            <div class="group flex gap-4 p-4 rounded-xl hover:bg-emerald-50 transition-colors duration-300 cursor-pointer">
              <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 group-hover:bg-emerald-200 transition-colors duration-300">
                <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-emerald-950 mb-1">Rahisi na Moja kwa Moja</h3>
                <p class="text-sm text-emerald-800/80">
                  Interface yenye urahisi. Hakuna kuzunguka. Ingiza matokeo kwa haraka na salama.
                </p>
              </div>
            </div>

            <!-- Feature 2 -->
            <div class="group flex gap-4 p-4 rounded-xl hover:bg-emerald-50 transition-colors duration-300 cursor-pointer">
              <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 group-hover:bg-emerald-200 transition-colors duration-300">
                <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-emerald-950 mb-1">Usalama wa Juu</h3>
                <p class="text-sm text-emerald-800/80">
                  Matokeo yako yalindwa kwa encryption na backup. Hakuna mtu anayeweza kuona isipokuwa wewe.
                </p>
              </div>
            </div>

            <!-- Feature 3 -->
            <div class="group flex gap-4 p-4 rounded-xl hover:bg-emerald-50 transition-colors duration-300 cursor-pointer">
              <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 group-hover:bg-emerald-200 transition-colors duration-300">
                <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-emerald-950 mb-1">Ripoti za Kina</h3>
                <p class="text-sm text-emerald-800/80">
                  Tazama ripoti za kina, takwimu, na mwenendo wa shule kwa muda mrefu na kwa haraka.
                </p>
              </div>
            </div>

            <!-- Feature 4 -->
            <div class="group flex gap-4 p-4 rounded-xl hover:bg-emerald-50 transition-colors duration-300 cursor-pointer">
              <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 group-hover:bg-emerald-200 transition-colors duration-300">
                <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-emerald-950 mb-1">Programu ya Simu</h3>
                <p class="text-sm text-emerald-800/80">
                  Wanafunzi, wazazi, na waliimu wanaweza kupata matokeo kwa simu yao kwa haraka.
                </p>
              </div>
            </div>
          </div>

          <!-- CTA Button -->
          <button class="mt-8 inline-flex items-center gap-2 rounded-full bg-emerald-600 px-8 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-600/40 hover:bg-emerald-700 transition-all duration-300 transform hover:scale-105">
            Jifunze Zaidi
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
