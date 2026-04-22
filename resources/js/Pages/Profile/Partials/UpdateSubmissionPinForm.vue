<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';

defineProps<{
    submissionPinConfigured: boolean;
}>();

const form = useForm({
    pin: '',
    pin_confirmation: '',
});

const submit = () => {
    form.patch(route('profile.submission-pin.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-semibold text-[#0038A8]">
                Submission PIN Settings
            </h2>
            <p class="mt-1 text-sm text-slate-600">
                Set the PIN required before submitting a public transaction entry.
            </p>
            <p class="mt-1 text-xs text-slate-500">
                Status:
                <span class="font-semibold text-[#0038A8]">
                    {{ submissionPinConfigured ? 'Configured' : 'Not Configured' }}
                </span>
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-4">
            <div>
                <InputLabel for="pin" value="New PIN (numbers only)" class="text-[#0038A8]" />
                <TextInput
                    id="pin"
                    v-model="form.pin"
                    type="password"
                    inputmode="numeric"
                    class="mt-1 block w-full rounded-md border-blue-200 focus:border-[#0038A8] focus:ring-[#0038A8]"
                    required
                />
                <InputError class="mt-2" :message="form.errors.pin" />
            </div>

            <div>
                <InputLabel for="pin_confirmation" value="Confirm PIN" class="text-[#0038A8]" />
                <TextInput
                    id="pin_confirmation"
                    v-model="form.pin_confirmation"
                    type="password"
                    inputmode="numeric"
                    class="mt-1 block w-full rounded-md border-blue-200 focus:border-[#0038A8] focus:ring-[#0038A8]"
                    required
                />
            </div>

            <PrimaryButton
                :disabled="form.processing"
                class="justify-center rounded-lg border border-[#f5c542] bg-gradient-to-r from-[#0038A8] to-[#00339a] text-sm font-semibold text-white shadow-md transition hover:from-[#0037a3] hover:to-[#002f8d] focus:ring-[#0038A8]"
            >
                Save PIN
            </PrimaryButton>
        </form>
    </section>
</template>

