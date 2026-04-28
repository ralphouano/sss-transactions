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
            <form @submit.prevent="openPinDialog" class="space-y-6" @focusin="handleFieldInteraction" @click.capture="handleFieldInteraction">
              <div class="space-y-2">
                <Label for="intern_id" class="text-base font-medium">Select Assistor</Label>
                <Select v-model="form.intern_id">
                  <SelectTrigger class="h-12 text-base">
                    <SelectValue placeholder="Select an assistor" />
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
                  @input="normalizeMemberNameInput"
                  required
                />
                <InputError :message="form.errors.member_name" />
              </div>

              <div class="space-y-2">
                <Label class="text-base font-medium">Transaction Types</Label>
                <div class="space-y-3 rounded-lg border border-blue-100 bg-blue-50/40 p-3">
                  <Input
                    id="transaction-search"
                    v-model="transactionSearch"
                    type="text"
                    placeholder="Type to search transaction type..."
                    class="h-11 text-base"
                  />

                  <div class="max-h-44 overflow-y-auto rounded-md border border-blue-100 bg-white">
                    <button
                      v-for="type in filteredTransactionTypes"
                      :key="type.key"
                      type="button"
                      class="flex w-full items-center justify-between border-b border-blue-50 px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-blue-50 last:border-b-0"
                      @click="toggleTransactionType(type.key)"
                    >
                      <span>{{ type.label }}</span>
                      <span v-if="isTransactionSelected(type.key)" class="text-xs font-semibold text-[#0038A8]">Selected</span>
                    </button>
                    <p v-if="filteredTransactionTypes.length === 0" class="px-3 py-2 text-sm text-slate-500">
                      No matching transaction type found.
                    </p>
                  </div>

                  <div v-if="selectedTransactionTypes.length" class="flex flex-wrap gap-2">
                    <button
                      v-for="type in selectedTransactionTypes"
                      :key="type.key"
                      type="button"
                      class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white px-3 py-1 text-xs font-medium text-blue-900"
                      @click="toggleTransactionType(type.key)"
                    >
                      {{ type.label }}
                      <span class="text-slate-400">x</span>
                    </button>
                  </div>
                </div>
                <InputError :message="form.errors.transactions" />
              </div>

              <div class="space-y-2">
                <SignaturePad
                  v-model="form.signature"
                  label="Member Signature"
                  :error="form.errors.signature"
                  :height="280"
                />
                <InputError :message="form.errors.submit_pin" />
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

    <Dialog v-model:open="pinDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Enter Submission PIN</DialogTitle>
          <DialogDescription>
            Enter the admin PIN to confirm transaction submission.
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="submitWithPin" class="space-y-4">
          <div class="space-y-2">
            <Label for="submit_pin" class="text-sm font-medium">PIN</Label>
            <Input
              id="submit_pin"
              v-model="form.submit_pin"
              type="password"
              inputmode="numeric"
              autocomplete="off"
              placeholder="Enter PIN"
              class="h-11"
              maxlength="6"
              pattern="[0-9]{1,6}"
              @input="form.submit_pin = String(form.submit_pin).replace(/[^0-9]/g, '').slice(0, 6)"
              required
            />
            <InputError :message="form.errors.submit_pin" />
          </div>

          <DialogFooter class="gap-2 sm:justify-end">
            <Button type="button" variant="outline" @click="closePinDialog">Cancel</Button>
            <Button type="submit" :disabled="form.processing" class="bg-[#003087] hover:bg-[#0b4cb8]">
              {{ form.processing ? 'Submitting...' : 'Confirm & Submit' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="privacyDialogOpen">
      <DialogContent class="max-w-3xl">
        <DialogHeader>
          <DialogTitle>Data Privacy Notice for Assisting Members at the E-Center</DialogTitle>
          <DialogDescription>
            Please review and confirm this consent notice before encoding transactions.
          </DialogDescription>
        </DialogHeader>

        <div class="max-h-[55vh] space-y-3 overflow-y-auto rounded-md border border-blue-100 bg-blue-50/30 p-3 text-sm leading-relaxed text-slate-700">
          <p>1. I authorize SSS Pagadian Branch personnel to assist me in my SSS online registration/account access and related processes.</p>
          <p>2. I certify that provided information is true and correct, and I understand liabilities for false information, misrepresentation, or fraud.</p>
          <p>3. I authorize SSS to use and verify submitted personal data for processing and legal purposes related to this application.</p>
          <p>4. I agree that collected information may be used and retained by SSS for processing purposes.</p>
          <p>5. I understand SSS will safeguard personal information under applicable laws and share data only as authorized or legally permitted.</p>
          <p>6. I understand that while SSS applies security measures, no internet/electronic storage method is absolutely secure.</p>
        </div>

        <DialogFooter class="gap-2 sm:justify-end">
          <Button type="button" variant="outline" @click="closePrivacyDialog">Close</Button>
          <Button type="button" class="bg-[#003087] hover:bg-[#0b4cb8]" @click="acceptPrivacyNotice">
            I Agree and Continue
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </PublicTransactionLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import PublicTransactionLayout from '@/Layouts/PublicTransactionLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card/index'
import { Button } from '@/Components/ui/button/index'
import { Input } from '@/Components/ui/input/index'
import { Label } from '@/Components/ui/label/index'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select/index'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog/index'
import InputError from '@/Components/InputError.vue'

interface Intern {
  id: number
  intern_name: string
}

interface TransactionType {
  id: number
  key: string
  label: string
}

const props = defineProps<{
  interns: Intern[]
  transactionTypes: TransactionType[]
}>()

const form = useForm({
  intern_id: '',
  member_name: '',
  signature: '',
  transactions: [] as string[],
  submit_pin: '',
})

const pinDialogOpen = ref(false)
const privacyDialogOpen = ref(true)
const privacyAccepted = ref(false)
const transactionSearch = ref('')

const selectedTransactionTypes = computed(() => props.transactionTypes.filter((type) => form.transactions.includes(type.key)))
const filteredTransactionTypes = computed(() => {
  const query = transactionSearch.value.trim().toLowerCase()
  if (!query) return props.transactionTypes

  return props.transactionTypes.filter((type) => {
    return type.label.toLowerCase().includes(query) || type.key.toLowerCase().includes(query)
  })
})

const isTransactionSelected = (key: string) => form.transactions.includes(key)
const toggleTransactionType = (key: string) => {
  if (isTransactionSelected(key)) {
    form.transactions = form.transactions.filter((item) => item !== key)
    return
  }

  form.transactions.push(key)
  transactionSearch.value = ''
}

const normalizeName = (value: string) => value
  .toLowerCase()
  .replace(/\s+/g, ' ')
  .trimStart()
  .replace(/\b\w/g, (char) => char.toUpperCase())

const normalizeMemberNameInput = () => {
  form.member_name = normalizeName(String(form.member_name ?? ''))
}

const acceptPrivacyNotice = () => {
  privacyAccepted.value = true
  privacyDialogOpen.value = false
}

const closePrivacyDialog = () => {
  privacyDialogOpen.value = false
}

const handleFieldInteraction = () => {
  if (!privacyAccepted.value) {
    privacyDialogOpen.value = true
  }
}

const openPinDialog = () => {
  if (!privacyAccepted.value) {
    privacyDialogOpen.value = true
    return
  }
  form.clearErrors('submit_pin')
  form.submit_pin = ''
  pinDialogOpen.value = true
}

const closePinDialog = () => {
  pinDialogOpen.value = false
  form.submit_pin = ''
}

const submitWithPin = () => {
  form.post(route('intern.record'), {
    onSuccess: () => {
      form.reset()
      pinDialogOpen.value = false
    },
    onFinish: () => {
      form.submit_pin = ''
    },
  })
}
</script>
