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
          Monthly Transactions
        </Link>

        <Link
          :href="route('intern.dashboard')"
          class="block rounded-md px-3 py-2 text-sm transition-all duration-200"
          :class="isActive('intern.dashboard') ? activeClass : inactiveClass"
        >
          Public Intern Form
        </Link>
      </nav>

      <div v-if="transactionTypeCounts.length" class="mt-5 border-t border-blue-100 pt-4">
        <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-blue-900/70">
          Transaction Type Counts
        </h4>
        <div class="space-y-2">
          <div
            v-for="item in transactionTypeCounts"
            :key="item.key"
            class="flex items-center justify-between rounded-md border border-blue-100 bg-blue-50/60 px-3 py-2 text-xs"
          >
            <span class="pe-2 text-blue-900">{{ formatTransactionType(item.key) }}</span>
            <span class="rounded bg-white px-2 py-0.5 font-semibold text-blue-900">{{ item.count }}</span>
          </div>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { formatTransactionType } from '@/lib/transactionType'

type TransactionTypeCount = {
  key: string
  count: number
}

const activeClass = 'bg-blue-100 text-blue-900 font-semibold shadow-sm'
const inactiveClass = 'text-gray-700 hover:bg-blue-50/80 hover:text-blue-800'

const isActive = (pattern: string) => route().current(pattern)

const page = usePage()

const transactionTypeCounts = computed<TransactionTypeCount[]>(() => {
  const counts = page.props.adminTransactionTypeCounts
  if (!Array.isArray(counts)) return []
  return counts as TransactionTypeCount[]
})

</script>
