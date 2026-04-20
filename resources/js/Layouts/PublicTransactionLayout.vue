<script setup lang="ts">
import sssLogo from '@/assets/sss-logo.svg'
import { showErrorToast, showSuccessToast } from '@/lib/swal'
import { Link, usePage } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string | null, error?: string | null } | undefined)

watch(
  flash,
  (value) => {
    if (value?.success) showSuccessToast(value.success)
    if (value?.error) showErrorToast(value.error)
  },
  { deep: true },
)
</script>

<template>
  <div class="min-h-screen">
    <nav class="sss-nav-gradient border-b border-blue-900/30 shadow-md">
      <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <Link
          :href="route('intern.dashboard')"
          class="flex shrink-0 items-center gap-3 transition-transform duration-300 hover:scale-105"
        >
          <img :src="sssLogo" alt="SSS Logo" class="block h-10 w-auto drop-shadow-sm" />
          <span class="hidden text-lg font-semibold text-white sm:inline">SSS Daily Transaction Logs</span>
        </Link>
        <Link
          :href="route('login')"
          class="rounded-md border border-white/35 bg-white/10 px-4 py-2.5 text-base font-medium text-white transition duration-200 ease-in-out hover:bg-white/20 focus:outline-none"
        >
          Staff login
        </Link>
      </div>
    </nav>

    <header
      v-if="$slots.header"
      class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8"
    >
      <div class="sss-surface sss-gold-accent px-6 py-5">
        <slot name="header" />
      </div>
    </header>

    <main class="sss-page-enter">
      <slot />
    </main>
  </div>
</template>
