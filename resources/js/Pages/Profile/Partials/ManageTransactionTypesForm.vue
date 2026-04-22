<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';

type TransactionType = {
    id: number;
    key: string;
    label: string;
    is_active: boolean;
    sort_order: number;
};

const props = defineProps<{
    transactionTypes: TransactionType[];
}>();

const createForm = useForm({
    label: '',
    is_active: true,
});

const editForms = reactive(
    Object.fromEntries(
        props.transactionTypes.map((type) => [
            type.id,
            useForm({
                label: type.label,
                is_active: type.is_active,
                sort_order: type.sort_order,
            }),
        ]),
    ) as Record<number, ReturnType<typeof useForm>>,
);

const submitCreate = () => {
    createForm.post(route('profile.transaction-types.store'), {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
};

const submitUpdate = (typeId: number) => {
    editForms[typeId].patch(route('profile.transaction-types.update', typeId), {
        preserveScroll: true,
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-semibold text-[#0038A8]">
                Transaction Type Settings
            </h2>
            <p class="mt-1 text-sm text-slate-600">
                Add new transaction types and update existing labels/order without code changes.
            </p>
        </header>

        <form @submit.prevent="submitCreate" class="mt-4 rounded-lg border border-blue-100 bg-blue-50/40 p-3">
            <h3 class="mb-3 text-sm font-semibold text-[#0038A8]">Add New Transaction Type</h3>
            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_auto_auto] md:items-end">
                <div>
                    <InputLabel for="new-label" value="Display Label" class="text-[#0038A8]" />
                    <TextInput
                        id="new-label"
                        v-model="createForm.label"
                        class="mt-1 block w-full rounded-md border-blue-200 focus:border-[#0038A8] focus:ring-[#0038A8]"
                        placeholder="Example Type"
                        required
                    />
                    <InputError class="mt-2" :message="createForm.errors.label" />
                </div>
                <div class="flex items-center pb-1">
                    <label class="flex items-center">
                        <Checkbox name="is_active" v-model:checked="createForm.is_active" />
                        <span class="ms-2 text-sm text-slate-700">Active</span>
                    </label>
                </div>
                <PrimaryButton
                    class="h-10 justify-center rounded-lg border border-[#f5c542] bg-gradient-to-r from-[#0038A8] to-[#00339a] px-4 text-sm font-semibold text-white shadow-md transition hover:from-[#0037a3] hover:to-[#002f8d] focus:ring-[#0038A8]"
                    :disabled="createForm.processing"
                >
                    Add
                </PrimaryButton>
            </div>
            <InputError class="mt-2" :message="createForm.errors.label" />
        </form>

        <div class="mt-4 overflow-x-auto rounded-lg border border-blue-100 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-blue-50/80 text-left text-xs uppercase tracking-wide text-blue-900/80">
                    <tr>
                        <th class="px-3 py-2 font-semibold">Label</th>
                        <th class="px-3 py-2 font-semibold">Order</th>
                        <th class="px-3 py-2 font-semibold">Active</th>
                        <th class="px-3 py-2 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="type in props.transactionTypes"
                        :key="type.id"
                        class="border-t border-blue-50 align-top"
                    >
                        <td class="px-3 py-2">
                            <TextInput
                                :id="`label-${type.id}`"
                                v-model="editForms[type.id].label"
                                class="block w-full rounded-md border-blue-200 text-sm focus:border-[#0038A8] focus:ring-[#0038A8]"
                                required
                            />
                            <InputError class="mt-1" :message="editForms[type.id].errors.label" />
                        </td>
                        <td class="px-3 py-2">
                            <TextInput
                                :id="`sort-${type.id}`"
                                v-model="editForms[type.id].sort_order"
                                type="number"
                                class="block w-24 rounded-md border-blue-200 text-sm focus:border-[#0038A8] focus:ring-[#0038A8]"
                                min="1"
                                required
                            />
                            <InputError class="mt-1" :message="editForms[type.id].errors.sort_order" />
                        </td>
                        <td class="px-3 py-2">
                            <label class="flex items-center pt-2">
                                <Checkbox :name="`active-${type.id}`" v-model:checked="editForms[type.id].is_active" />
                                <span class="ms-2 text-xs text-slate-700">Yes</span>
                            </label>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <PrimaryButton
                                class="justify-center rounded-lg border border-[#f5c542] bg-gradient-to-r from-[#0038A8] to-[#00339a] px-3 text-xs font-semibold text-white shadow-md transition hover:from-[#0037a3] hover:to-[#002f8d] focus:ring-[#0038A8]"
                                :disabled="editForms[type.id].processing"
                                @click.prevent="submitUpdate(type.id)"
                            >
                                Save
                            </PrimaryButton>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

