<template>
  <Head title="Admin Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-blue-950 leading-tight">Admin Dashboard</h2>
    </template>

    <div class="py-10">
      <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:px-8">
        <AdminSidebar />

        <div class="flex-1 space-y-6">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <Card class="sss-surface sss-gold-accent border-blue-200">
              <CardHeader class="pb-2">
                <CardDescription>Transactions Today</CardDescription>
                <CardTitle class="text-3xl text-blue-900">{{ summary.today_count }}</CardTitle>
              </CardHeader>
            </Card>

            <Card class="sss-surface sss-gold-accent border-blue-200">
              <CardHeader class="pb-2">
                <CardDescription>Transactions This Month ({{ summary.month_label }})</CardDescription>
                <CardTitle class="text-3xl text-blue-900">{{ summary.month_count }}</CardTitle>
              </CardHeader>
            </Card>

            <Card class="sss-surface sss-gold-accent border-blue-200">
              <CardHeader class="pb-2">
                <CardDescription>Active Interns Today</CardDescription>
                <CardTitle class="text-3xl text-blue-900">{{ summary.today_unique_interns }}</CardTitle>
              </CardHeader>
            </Card>
          </div>

          <Card class="sss-surface sss-gold-accent border-blue-200">
            <CardHeader>
              <CardTitle class="text-blue-900">Today's Recorded Transactions</CardTitle>
              <CardDescription>{{ summary.date }}</CardDescription>
            </CardHeader>
            <CardContent>
              <Table class="rounded-lg overflow-hidden">
                <TableHeader>
                  <TableRow class="bg-blue-50/70">
                    <TableHead>Intern</TableHead>
                    <TableHead>Member</TableHead>
                    <TableHead>Types</TableHead>
                    <TableHead>Time</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-if="today_transactions.length === 0">
                    <TableCell colspan="4" class="text-center text-gray-500 py-8">
                      No transactions recorded today.
                    </TableCell>
                  </TableRow>
                  <TableRow
                    v-for="transaction in today_transactions"
                    :key="transaction.id"
                    v-else
                    class="transition-colors hover:bg-blue-50/60"
                  >
                    <TableCell>{{ transaction.intern?.intern_name || 'N/A' }}</TableCell>
                    <TableCell>{{ transaction.member_name }}</TableCell>
                    <TableCell>
                      <div class="flex flex-wrap gap-1">
                        <Badge v-for="type in transaction.transactions" :key="type" variant="secondary">
                          {{ formatTransactionType(type) }}
                        </Badge>
                      </div>
                    </TableCell>
                    <TableCell>{{ formatTime(transaction.created_at) }}</TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AdminSidebar from '@/Components/AdminSidebar.vue'
import { Head } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card/index'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table/index'
import { Badge } from '@/Components/ui/badge/index'
import { formatTransactionType } from '@/lib/transactionType'

interface Transaction {
  id: number
  member_name: string
  transactions: string[]
  created_at: string
  intern: {
    intern_name: string
  } | null
}

defineProps<{
  summary: {
    today_count: number
    month_count: number
    month_label: string
    today_unique_interns: number
    date: string
  }
  today_transactions: Transaction[]
}>()

const formatTime = (value: string) => new Date(value).toLocaleTimeString([], {
  hour: '2-digit',
  minute: '2-digit',
  second: '2-digit',
})

</script>
