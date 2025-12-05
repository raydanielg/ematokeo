<script setup lang="ts">
import { computed, ref } from 'vue'

interface NavItem {
  label: string
  href?: string
  target?: string
  children?: NavItem[]
}

const openDropdown = ref<string | null>(null)
const mobileMenuOpen = ref(false)

const navItems = computed<NavItem[]>(() => [
  { label: 'Home', href: '/' },
  { label: 'About Us', href: '/about' },
  { label: 'Blog', href: '/blog' },
  { label: 'Publications', href: '/publications' },
  { label: 'Contact', href: '/contact' },
  {
    label: 'Resources',
    children: [
      { label: 'Teaching Resources', href: '/resources/teaching' },
      { label: 'Student Resources', href: '/resources/student' },
      { label: 'Documents', href: '/resources/documents' },
      { label: 'Results', href: '/resources/results' }
    ]
  }
])

const toggleDropdown = (label: string) => {
  openDropdown.value = openDropdown.value === label ? null : label
}

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}
</script>

<template>
  <header class="sticky top-0 z-20 border-b border-emerald-200 bg-emerald-50/95 backdrop-blur-sm">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
      <!-- Left: Logo -->
      <div class="flex items-center gap-2 flex-shrink-0">
        <img
          src="/e.png"
          alt="Matokeo logo"
          class="h-9 w-9 rounded-full border border-emerald-500/40 object-cover shadow-sm shadow-emerald-400/40"
        />
        <span class="text-base font-semibold text-emerald-950">Matokeo</span>
      </div>

      <!-- Center: Navigation Menu (Desktop) -->
      <nav class="hidden flex-1 items-center justify-center gap-8 sm:flex">
        <template v-for="item in navItems" :key="item.label">
          <!-- Regular nav item -->
          <a
            v-if="!item.children"
            :href="item.href"
            :target="item.target"
            class="text-sm font-medium text-emerald-900 transition hover:text-emerald-600"
          >
            {{ item.label }}
          </a>

          <!-- Nav item with badge -->
          <a
            v-else-if="!item.children && item.badge"
            :href="item.href"
            :target="item.target"
            class="text-sm font-medium text-emerald-900 transition hover:text-emerald-600 relative"
          >
            {{ item.label }}
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">{{ item.badge }}</span>
          </a>

          <!-- Dropdown nav item -->
          <div v-else class="relative group">
            <button
              @click="toggleDropdown(item.label)"
              class="text-sm font-medium text-emerald-900 transition hover:text-emerald-600 flex items-center gap-1"
            >
              {{ item.label }}
              <svg
                class="w-4 h-4 transition transform group-hover:rotate-180"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
              </svg>
            </button>

            <!-- Dropdown menu -->
            <div
              class="absolute left-0 mt-0 w-48 bg-white border border-emerald-200 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50"
            >
              <a
                v-for="child in item.children"
                :key="child.label"
                :href="child.href"
                class="block px-4 py-2 text-sm text-emerald-900 hover:bg-emerald-50 hover:text-emerald-700 first:rounded-t-lg last:rounded-b-lg transition"
              >
                {{ child.label }}
              </a>
            </div>
          </div>
        </template>
      </nav>

      <!-- Mobile Menu Button -->
      <button
        @click="toggleMobileMenu"
        class="sm:hidden flex items-center justify-center w-10 h-10 rounded-lg hover:bg-emerald-100 transition-colors"
      >
        <svg
          class="w-6 h-6 text-emerald-900 transition-transform duration-300"
          :class="{ 'rotate-90': mobileMenuOpen }"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16"
          />
        </svg>
      </button>

      <!-- Right: Auth Slot (Login / Get Started) -->
      <div class="hidden sm:flex items-center gap-3 flex-shrink-0">
        <slot name="auth"></slot>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div
      v-if="mobileMenuOpen"
      class="sm:hidden bg-white border-t border-emerald-200 animate-in fade-in slide-in-from-top-2 duration-200"
    >
      <nav class="flex flex-col px-4 py-3 space-y-2">
        <template v-for="item in navItems" :key="item.label">
          <!-- Regular nav item -->
          <a
            v-if="!item.children"
            :href="item.href"
            :target="item.target"
            class="px-3 py-2 text-sm font-medium text-emerald-900 hover:bg-emerald-50 rounded-lg transition"
            @click="mobileMenuOpen = false"
          >
            {{ item.label }}
          </a>

          <!-- Dropdown nav item -->
          <div v-else class="space-y-1">
            <button
              @click="toggleDropdown(item.label)"
              class="w-full text-left px-3 py-2 text-sm font-medium text-emerald-900 hover:bg-emerald-50 rounded-lg transition flex items-center justify-between"
            >
              {{ item.label }}
              <svg
                class="w-4 h-4 transition transform"
                :class="{ 'rotate-180': openDropdown === item.label }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
              </svg>
            </button>

            <!-- Dropdown items -->
            <div v-if="openDropdown === item.label" class="pl-4 space-y-1">
              <a
                v-for="child in item.children"
                :key="child.label"
                :href="child.href"
                class="block px-3 py-2 text-sm text-emerald-700 hover:bg-emerald-50 rounded-lg transition"
                @click="mobileMenuOpen = false"
              >
                {{ child.label }}
              </a>
            </div>
          </div>
        </template>
      </nav>

      <!-- Mobile Auth -->
      <div class="px-4 py-3 border-t border-emerald-200 flex gap-3">
        <slot name="auth"></slot>
      </div>
    </div>

  </header>
</template>
