<template>
  <Head title="Transaction Entry" />

  <PublicTransactionLayout>
    <template #header>
      <h2 class="font-semibold text-3xl text-blue-950 leading-tight">Transaction Entry</h2>
    </template>

    <div class="py-10">
      <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <Card class="sss-surface sss-gold-accent border-blue-200">
          <CardHeader class="space-y-2">
            <CardTitle class="text-2xl font-semibold text-blue-900">Record Transaction</CardTitle>
            <CardDescription class="text-base">Fill in the member details and select transaction types</CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="submit" class="space-y-6">
              <div class="space-y-2">
                <Label for="intern_id" class="text-base font-medium">Select Intern</Label>
                <Select v-model="form.intern_id">
                  <SelectTrigger class="h-12 text-base">
                    <SelectValue placeholder="Select an intern" />
                  </SelectTrigger>
                  <SelectContent position="popper" class="text-base">
                    <SelectItem
                      v-for="intern in interns"
                      :key="intern.id"
                      :value="String(intern.id)"
                      class="py-2 text-base"
                    >
                      {{ intern.intern_name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <InputError :message="form.errors.intern_id" />
              </div>

              <div class="space-y-2">
                <Label for="member_name" class="text-base font-medium">Assisted Member Name</Label>
                <Input
                  id="member_name"
                  v-model="form.member_name"
                  type="text"
                  placeholder="Enter member name"
                  class="h-12 text-base"
                  required
                />
                <InputError :message="form.errors.member_name" />
              </div>

              <div class="space-y-2">
                <Label class="text-base font-medium">Transaction Types</Label>
                <div class="grid grid-cols-2 gap-4">
                  <label v-for="type in transactionTypes" :key="type.value" class="flex items-center space-x-2 rounded-lg border border-blue-100 bg-blue-50/50 px-3 py-2.5 transition hover:bg-blue-100/50">
                    <input
                      type="checkbox"
                      :value="type.value"
                      v-model="form.transactions"
                      class="rounded border-gray-300"
                    />
                    <span class="text-base">{{ type.label }}</span>
                  </label>
                </div>
                <InputError :message="form.errors.transactions" />
              </div>

              <div class="space-y-2">
                <SignaturePad
                  v-model="form.signature"
                  label="Member Signature"
                  :error="form.errors.signature"
                />
              </div>

              <div class="flex justify-end">
                <Button type="submit" :disabled="form.processing" class="bg-[#003087] px-6 py-3 text-base hover:bg-[#0b4cb8] transition-all duration-200">
                  {{ form.processing ? 'Recording...' : 'Record Transaction' }}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </PublicTransactionLayout>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import PublicTransactionLayout from '@/Layouts/PublicTransactionLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card/index'
import { Button } from '@/Components/ui/button/index'
import { Input } from '@/Components/ui/input/index'
import { Label } from '@/Components/ui/label/index'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select/index'
import InputError from '@/Components/InputError.vue'

interface Intern {
  id: number
  intern_name: string
}

const props = defineProps<{
  interns: Intern[]
}>()

const transactionTypes = [
  { value: 'maternity_benefit', label: 'Maternity Benefit' },
  { value: 'unemployment_benefit', label: 'Unemployment Benefit' },
  { value: 'sickness_benefit', label: 'Sickness Benefit' },
  { value: 'disability_claim', label: 'Disability Claim' },
  { value: 'retirement_claim', label: 'Retirement Claim' },
  { value: 'funeral_claim', label: 'Funeral Claim' },
  { value: 'death_claim', label: 'Death Claim' },
  { value: 'salary_loan', label: 'Salary Loan' },
  { value: 'calamity_emergency', label: 'Calamity/Emergency' },
  { value: 'pension_loan', label: 'Pension Loan' },
  { value: 'consoloan', label: 'Consoloan' },
  { value: 'mysss_card', label: 'mySSS Card' },
  { value: 'employment_history', label: 'Employment History' },
  { value: 'contribution_details', label: 'Contribution Details' },
  { value: 'generate_prn', label: 'Generate PRN' },
]

const form = useForm({
  intern_id: '',
  member_name: '',
  signature: '',
  transactions: [] as string[],
})

const submit = () => {
  form.post(route('intern.record'), {
    onSuccess: () => {
      form.reset()
    },
  })
}
</script>
