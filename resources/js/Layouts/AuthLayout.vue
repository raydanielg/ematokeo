<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  compact: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <div class="min-h-screen grid md:grid-cols-2">
    <!-- Left: Auth panel -->
    <div class="bg-white">
      <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full" :class="compact ? 'max-w-md' : 'max-w-lg'">
          <div class="mb-6 flex items-center justify-between">
            <Link :href="route('login')" class="inline-flex items-center gap-3">
              <img
                src="/e.png"
                alt="e-matokeo logo"
                class="h-9 w-9 rounded-full bg-white shadow-md"
              />
              <span class="text-sm font-semibold text-black">e-matokeo</span>
            </Link>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6 sm:p-8">
            <slot />
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Promo panel -->
    <div class="hidden md:block bg-gradient-to-br from-emerald-800 via-emerald-700 to-lime-500 text-white">
      <div class="min-h-screen flex items-center px-10 lg:px-14">
        <div class="max-w-xl">
          <slot name="promo">
            <div class="flex items-center gap-3 mb-6">
              <div class="h-8 w-8 rounded-full bg-white/20 flex items-center justify-center font-bold">M</div>
              <span class="text-sm font-semibold">e-matokeo</span>
            </div>
            <h2 class="text-3xl lg:text-4xl font-bold leading-tight mb-4">
              Explore powerful electronic<br />
              marking &amp; results management.
            </h2>
            <p class="text-white/80 leading-relaxed mb-8">
              Centralize exam marking, approvals, and publishing in one modern platform.
              Designed for institutions that care about accuracy, speed, and a great experience.
            </p>
            <div class="flex items-center gap-3 text-sm text-white/80">
              <div class="flex -space-x-2">
                <div class="h-8 w-8 rounded-full bg-white/15 border border-white/20 flex items-center justify-center text-xs">EM</div>
                <div class="h-8 w-8 rounded-full bg-white/15 border border-white/20 flex items-center justify-center text-xs">AC</div>
                <div class="h-8 w-8 rounded-full bg-white/15 border border-white/20 flex items-center justify-center text-xs">TZ</div>
              </div>
              <div>
                <div class="font-semibold text-white">Trusted by institutions</div>
                <div class="text-white/70 text-xs">Secure, audited, and optimized for results.</div>
              </div>
            </div>
          </slot>
        </div>
      </div>
    </div>
  </div>
</template>
