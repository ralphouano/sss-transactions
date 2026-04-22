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

        <form @submit.prevent="submitCreate" class="mt-6 rounded-lg border border-blue-100 bg-blue-50/40 p-4">
            <h3 class="mb-3 text-sm font-semibold text-[#0038A8]">Add New Transaction Type</h3>
            <div class="grid gap-4 md:grid-cols-2">
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
                <div class="flex items-end">
                    <label class="flex items-center">
                        <Checkbox name="is_active" v-model:checked="createForm.is_active" />
                        <span class="ms-2 text-sm text-slate-700">Active</span>
                    </label>
                </div>
            </div>
            <PrimaryButton
                class="mt-4 justify-center rounded-lg border border-[#f5c542] bg-gradient-to-r from-[#0038A8] to-[#00339a] text-sm font-semibold text-white shadow-md transition hover:from-[#0037a3] hover:to-[#002f8d] focus:ring-[#0038A8]"
                :disabled="createForm.processing"
            >
                Add Transaction Type
            </PrimaryButton>
        </form>

        <div class="mt-6 space-y-3">
            <div
                v-for="type in props.transactionTypes"
                :key="type.id"
                class="rounded-lg border border-blue-100 bg-white p-4"
            >
                <form
                    @submit.prevent="submitUpdate(type.id)"
                    class="grid gap-3 md:grid-cols-4"
                >
                    <div class="md:col-span-2">
                        <InputLabel :for="`label-${type.id}`" value="Label" class="text-[#0038A8]" />
                        <TextInput
                            :id="`label-${type.id}`"
                            v-model="editForms[type.id].label"
                            class="mt-1 block w-full rounded-md border-blue-200 focus:border-[#0038A8] focus:ring-[#0038A8]"
                            required
                        />
                        <InputError class="mt-2" :message="editForms[type.id].errors.label" />
                    </div>
                    <div>
                        <InputLabel :for="`sort-${type.id}`" value="Sort Order" class="text-[#0038A8]" />
                        <TextInput
                            :id="`sort-${type.id}`"
                            v-model="editForms[type.id].sort_order"
                            type="number"
                            class="mt-1 block w-full rounded-md border-blue-200 focus:border-[#0038A8] focus:ring-[#0038A8]"
                            min="1"
                            required
                        />
                        <InputError class="mt-2" :message="editForms[type.id].errors.sort_order" />
                    </div>
                    <div class="flex items-end justify-between gap-2">
                        <label class="mb-2 flex items-center">
                            <Checkbox :name="`active-${type.id}`" v-model:checked="editForms[type.id].is_active" />
                            <span class="ms-2 text-sm text-slate-700">Active</span>
                        </label>
                        <PrimaryButton
                            class="justify-center rounded-lg border border-[#f5c542] bg-gradient-to-r from-[#0038A8] to-[#00339a] text-xs font-semibold text-white shadow-md transition hover:from-[#0037a3] hover:to-[#002f8d] focus:ring-[#0038A8]"
                            :disabled="editForms[type.id].processing"
                        >
                            Save
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>

