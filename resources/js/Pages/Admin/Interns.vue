<template>
  <Head title="Manage Interns" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-blue-950 leading-tight">Manage Interns</h2>
    </template>

    <div class="py-10">
      <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:px-8">
        <AdminSidebar />

        <Card class="sss-surface sss-gold-accent flex-1 border-blue-200">
          <CardHeader>
            <CardTitle class="text-blue-900">Intern List</CardTitle>
            <CardDescription>Add, edit, and remove intern names</CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="createIntern" class="mb-6 rounded-lg border border-blue-100 bg-blue-50/50 p-4">
              <div class="flex flex-col gap-3 md:flex-row md:items-end">
                <div class="flex-1 space-y-2">
                  <Label for="new_intern_name">New Intern Name</Label>
                  <Input
                    id="new_intern_name"
                    v-model="createForm.intern_name"
                    placeholder="Enter intern name"
                    required
                  />
                  <InputError :message="createForm.errors.intern_name" />
                </div>
                <Button type="submit" :disabled="createForm.processing" class="bg-[#003087] hover:bg-[#0b4cb8] transition-all duration-200">
                  Add Intern
                </Button>
              </div>
            </form>

            <Table class="rounded-lg overflow-hidden">
              <TableHeader>
                <TableRow class="bg-blue-50/70">
                  <TableHead>ID</TableHead>
                  <TableHead>Intern Name</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="intern in interns" :key="intern.id" class="transition-colors hover:bg-blue-50/60">
                  <TableCell>{{ intern.id }}</TableCell>
                  <TableCell>{{ intern.intern_name || 'N/A' }}</TableCell>
                  <TableCell class="space-x-2">
                    <Button size="sm" class="bg-[#003087] hover:bg-[#0b4cb8] transition-all duration-200" @click="openEditDialog(intern)">Edit</Button>
                    <Button size="sm" variant="destructive" :disabled="deletingId === intern.id" @click="deleteIntern(intern)">
                      {{ deletingId === intern.id ? 'Removing...' : 'Remove' }}
                    </Button>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </div>
    </div>

    <Dialog v-model:open="dialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Edit Intern</DialogTitle>
          <DialogDescription>Update intern name</DialogDescription>
        </DialogHeader>
        <form @submit.prevent="updateIntern" class="space-y-4">
          <div class="space-y-2">
            <Label for="intern_name">Intern Name</Label>
            <Input id="intern_name" v-model="editForm.intern_name" required />
            <InputError :message="editForm.errors.intern_name" />
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="dialogOpen = false">Cancel</Button>
            <Button type="submit" :disabled="editForm.processing">Save</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card/index'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table/index'
import { Button } from '@/Components/ui/button/index'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog/index'
import { Input } from '@/Components/ui/input/index'
import { Label } from '@/Components/ui/label/index'
import InputError from '@/Components/InputError.vue'
import AdminSidebar from '@/Components/AdminSidebar.vue'
import { confirmAction } from '@/lib/swal'

interface Intern {
  id: number
  intern_name: string | null
}

const props = defineProps<{
  interns: Intern[]
}>()

const dialogOpen = ref(false)
const selectedIntern = ref<Intern | null>(null)
const deletingId = ref<number | null>(null)

const createForm = useForm({
  intern_name: '',
})

const editForm = useForm({
  intern_name: '',
})

const createIntern = () => {
  createForm.post(route('admin.interns.store'), {
    onSuccess: () => {
      createForm.reset()
    },
  })
}

const openEditDialog = (intern: Intern) => {
  selectedIntern.value = intern
  editForm.intern_name = intern.intern_name || ''
  dialogOpen.value = true
}

const updateIntern = () => {
  if (!selectedIntern.value) return
  
  editForm.patch(route('admin.interns.update', selectedIntern.value.id), {
    onSuccess: () => {
      dialogOpen.value = false
      editForm.reset()
    },
  })
}

const deleteIntern = async (intern: Intern) => {
  const result = await confirmAction(
    'Remove intern?',
    `${intern.intern_name ?? 'This intern'} will be removed from the list.`,
  )

  if (!result.isConfirmed) return

  deletingId.value = intern.id
  editForm.delete(route('admin.interns.destroy', intern.id), {
    preserveScroll: true,
    onFinish: () => {
      deletingId.value = null
    },
  })
}
</script>
