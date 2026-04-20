<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-[#0038A8] via-[#0036a1] to-[#002f8d] px-4 py-8"
    >
        <div class="mb-8 text-center">
            <Link href="/" class="inline-block">
                <span class="sss-branding text-3xl text-white sm:text-4xl">
                    Social Security System
                </span>
            </Link>
        </div>

        <div
            class="w-full max-w-md"
        >
            <slot />
        </div>
    </div>
</template>
