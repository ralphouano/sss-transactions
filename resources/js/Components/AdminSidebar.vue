<template>
  <aside class="w-full md:w-64 shrink-0">
    <div class="sss-surface sss-gold-accent p-4">
      <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-blue-900/70">
        Admin Navigation
      </h3>

      <nav class="space-y-1">
        <Link
          :href="route('dashboard')"
          class="block rounded-md px-3 py-2 text-sm transition-all duration-200"
          :class="isActive('dashboard') ? activeClass : inactiveClass"
        >
          Dashboard
        </Link>

        <Link
          :href="route('admin.interns.index')"
          class="block rounded-md px-3 py-2 text-sm transition-all duration-200"
          :class="isActive('admin.interns.*') ? activeClass : inactiveClass"
        >
          Manage Interns
        </Link>

        <Link
          :href="route('admin.reports.index')"
          class="block rounded-md px-3 py-2 text-sm transition-all duration-200"
          :class="isActive('admin.reports.*') ? activeClass : inactiveClass"
        >
          Transaction History
        </Link>

        <Link
          :href="route('intern.dashboard')"
          class="block rounded-md px-3 py-2 text-sm transition-all duration-200"
          :class="isActive('intern.dashboard') ? activeClass : inactiveClass"
        >
          Public Intern Form
        </Link>
      </nav>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

const activeClass = 'bg-blue-100 text-blue-900 font-semibold shadow-sm'
const inactiveClass = 'text-gray-700 hover:bg-blue-50/80 hover:text-blue-800'

const isActive = (pattern: string) => route().current(pattern)
</script>
