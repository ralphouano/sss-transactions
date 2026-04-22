<template>
  <Head title="Monthly Transactions" />
  <iframe
    ref="printFrame"
    class="pointer-events-none fixed -left-[9999px] top-0 h-px w-px border-0 opacity-0"
    title="print-frame"
    @load="handlePrintFrameLoad"
  />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-blue-950 leading-tight">Monthly Transactions</h2>
    </template>

    <div class="py-10">
      <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:px-8">
        <AdminSidebar />

        <Card class="sss-surface sss-gold-accent flex-1 border-blue-200">
          <CardHeader>
            <div class="flex justify-between items-center">
              <div>
                <CardTitle class="text-blue-900">Monthly Transactions</CardTitle>
                <CardDescription>Track all transactions and filter by month for report generation</CardDescription>
              </div>
              <div class="flex items-center gap-2">
                <Button type="button" variant="outline" @click="printReport">Print</Button>
                <Button class="bg-[#003087] hover:bg-[#0b4cb8] transition-all duration-200" @click="exportReport">Export XLSX</Button>
              </div>
            </div>
          </CardHeader>
          <CardContent :class="isApplyingMonth ? 'opacity-90 transition-opacity duration-150' : 'opacity-100 transition-opacity duration-150'">
            <div class="mb-4 flex flex-col gap-3 rounded-lg border border-blue-100 bg-blue-50/50 p-3 sm:flex-row sm:items-end sm:justify-between">
              <div class="space-y-1">
                <Label for="history-month" class="text-sm font-medium text-blue-900">Filter Month</Label>
                <Input id="history-month" v-model="selectedMonth" type="month" class="h-10 w-56" />
              </div>
              <div class="flex items-end gap-3">
                <div class="rounded-md border border-blue-100 bg-white px-3 py-2 text-right">
                  <div class="text-[11px] uppercase tracking-wide text-blue-700/80">Monthly Total</div>
                  <div class="text-lg font-semibold text-[#0038A8]">{{ props.monthTotalCount }}</div>
                </div>
                <Button type="button" class="bg-[#003087] hover:bg-[#0b4cb8] transition-all duration-200" @click="applyMonthFilter">Apply Month</Button>
              </div>
            </div>
            <Table class="rounded-lg overflow-hidden">
              <TableHeader>
                <TableRow class="bg-blue-50/70">
                  <TableHead>ID</TableHead>
                  <TableHead>Timestamp</TableHead>
                  <TableHead>Intern</TableHead>
                  <TableHead>Member Name</TableHead>
                  <TableHead>Transaction</TableHead>
                  <TableHead>Signature</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="transactions.length === 0">
                  <TableCell colspan="6" class="text-center text-gray-500">
                    No transactions found for the selected filter
                  </TableCell>
                </TableRow>
                <template v-else>
                  <template v-for="(transaction, index) in transactions" :key="transaction.id">
                    <TableRow v-if="shouldShowDateBoundary(index)" class="bg-blue-100/70">
                      <TableCell colspan="6" class="border-y border-blue-200 py-2.5 font-semibold text-[#0038A8]">
                        {{ formatBoundaryDate(transaction.created_at) }}
                      </TableCell>
                    </TableRow>
                    <TableRow class="transition-colors hover:bg-blue-50/60">
                      <TableCell>{{ transaction.id }}</TableCell>
                      <TableCell>{{ formatTime(transaction.created_at) }}</TableCell>
                      <TableCell>{{ transaction.intern?.intern_name || 'N/A' }}</TableCell>
                      <TableCell>{{ transaction.member_name }}</TableCell>
                      <TableCell>
                        <div class="flex gap-1 flex-wrap">
                          <Badge v-for="type in transaction.transactions" :key="type" variant="secondary">
                            {{ formatTransactionType(type) }}
                          </Badge>
                        </div>
                      </TableCell>
                      <TableCell>
                        <img
                          v-if="transaction.signature"
                          :src="transaction.signature"
                          alt="Signature"
                          class="h-12 w-auto border rounded"
                        />
                        <span v-else>N/A</span>
                      </TableCell>
                    </TableRow>
                  </template>
                </template>
              </TableBody>
            </Table>
            <div
              v-if="props.transactions.last_page > 1"
              class="mt-4 flex flex-col gap-3 border-t border-blue-100 pt-3 sm:flex-row sm:items-center sm:justify-between"
            >
              <p class="text-sm text-slate-600">
                Showing {{ props.transactions.from ?? 0 }}-{{ props.transactions.to ?? 0 }} of {{ props.transactions.total }} transactions
              </p>
              <div class="flex items-center gap-2">
                <Button
                  type="button"
                  variant="outline"
                  :disabled="props.transactions.current_page <= 1"
                  @click="goToPage(props.transactions.current_page - 1)"
                >
                  Previous
                </Button>
                <span class="text-sm font-medium text-blue-900">
                  Page {{ props.transactions.current_page }} of {{ props.transactions.last_page }}
                </span>
                <Button
                  type="button"
                  variant="outline"
                  :disabled="props.transactions.current_page >= props.transactions.last_page"
                  @click="goToPage(props.transactions.current_page + 1)"
                >
                  Next
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card/index'
import { Button } from '@/Components/ui/button/index'
import { Input } from '@/Components/ui/input/index'
import { Label } from '@/Components/ui/label/index'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table/index'
import { Badge } from '@/Components/ui/badge/index'
import AdminSidebar from '@/Components/AdminSidebar.vue'
import { formatTransactionType } from '@/lib/transactionType'

interface Transaction {
  id: number
  member_name: string
  signature: string
  transactions: string[]
  created_at: string
  intern: {
    intern_name: string
  }
}

interface PaginatedTransactions {
  data: Transaction[]
  current_page: number
  last_page: number
  total: number
  from: number | null
  to: number | null
}

const props = defineProps<{
  transactions: PaginatedTransactions
  month: string | null
  monthTotalCount: number
}>()

const transactions = computed(() => props.transactions.data)
const selectedMonth = ref(props.month ?? '')
const isApplyingMonth = ref(false)

const applyMonthFilter = () => {
  isApplyingMonth.value = true
  router.get(
    route('admin.reports.index'),
    { month: selectedMonth.value || undefined },
    {
      preserveState: false,
      preserveScroll: true,
      replace: true,
      only: ['transactions', 'month', 'monthTotalCount'],
      onFinish: () => {
        setTimeout(() => {
          isApplyingMonth.value = false
        }, 120)
      },
    },
  )
}

const printFrame = ref<HTMLIFrameElement | null>(null)

const exportReport = () => {
  window.location.href = route('admin.reports.export', { month: selectedMonth.value || undefined })
}

const printReport = () => {
  if (!printFrame.value) return
  printFrame.value.src = route('admin.reports.print', { month: selectedMonth.value || undefined })
}

const handlePrintFrameLoad = () => {
  try {
    printFrame.value?.contentWindow?.focus()
    printFrame.value?.contentWindow?.print()
  } catch {
    // PDF viewer controls the print behavior in some browsers.
  }
}

const goToPage = (page: number) => {
  if (page < 1 || page > props.transactions.last_page) return
  router.get(
    route('admin.reports.index'),
    { month: selectedMonth.value || undefined, page },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      only: ['transactions', 'month', 'monthTotalCount'],
    },
  )
}

const toDateKey = (value: string) => new Date(value).toLocaleDateString('en-CA')

const shouldShowDateBoundary = (index: number) => {
  if (index === 0) return true
  return toDateKey(transactions.value[index].created_at) !== toDateKey(transactions.value[index - 1].created_at)
}

const formatBoundaryDate = (value: string) => new Date(value).toLocaleDateString(undefined, {
  weekday: 'long',
  year: 'numeric',
  month: 'long',
  day: 'numeric',
})

const formatTime = (value: string) => new Date(value).toLocaleTimeString([], {
  hour: '2-digit',
  minute: '2-digit',
  second: '2-digit',
})

</script>
