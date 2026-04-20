<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import sssLogo from '@/assets/sss-logo.svg';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div
            class="w-full rounded-2xl border border-blue-100 bg-white/95 p-7 shadow-xl backdrop-blur-sm"
        >
            <div class="mb-6 flex flex-col items-center text-center">
                <img :src="sssLogo" alt="SSS Office Logo" class="mb-3 h-14 w-auto" />
                <h1 class="sss-branding text-2xl text-[#0038A8] sm:text-3xl">
                    SSS Daily Transaction Logs
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    Sign in using your office account credentials.
                </p>
            </div>

            <div
                v-if="status"
                class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700"
            >
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="email" value="Email" class="text-blue-950" />

                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full rounded-md border-blue-200 focus:border-blue-500 focus:ring-blue-500"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                    />

                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="Password" class="text-blue-950" />

                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full rounded-md border-blue-200 focus:border-blue-500 focus:ring-blue-500"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />

                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ms-2 text-sm text-slate-600">Remember me</span>
                    </label>

                    <p class="text-right text-xs text-slate-600">
                        Forgot your password? Contact the developer.
                    </p>
                </div>

                <PrimaryButton
                    class="w-full justify-center rounded-lg border border-[#f5c542] bg-gradient-to-r from-[#0038A8] to-[#00339a] py-2.5 text-sm font-semibold text-white shadow-md transition hover:from-[#0037a3] hover:to-[#002f8d] focus:ring-[#0038A8]"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </form>
        </div>
    </GuestLayout>
</template>
