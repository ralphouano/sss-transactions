<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ManageTransactionTypesForm from './Partials/ManageTransactionTypesForm.vue';
import UpdateSubmissionPinForm from './Partials/UpdateSubmissionPinForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
    canManageTransactionTypes: boolean;
    transactionTypes: Array<{
        id: number;
        key: string;
        label: string;
        is_active: boolean;
        sort_order: number;
    }>;
    submissionPinConfigured: boolean;
}>();
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="sss-branding text-xl leading-tight text-[#0038A8]"
            >
                Settings
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div
                    class="sss-surface sss-gold-accent p-4 sm:rounded-lg sm:p-8"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div
                    class="sss-surface sss-gold-accent p-4 sm:rounded-lg sm:p-8"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div
                    v-if="canManageTransactionTypes"
                    class="sss-surface sss-gold-accent p-4 sm:rounded-lg sm:p-8"
                >
                    <UpdateSubmissionPinForm :submission-pin-configured="submissionPinConfigured" />
                </div>

                <div
                    v-if="canManageTransactionTypes"
                    class="sss-surface sss-gold-accent p-4 sm:rounded-lg sm:p-8"
                >
                    <ManageTransactionTypesForm :transaction-types="transactionTypes" />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
