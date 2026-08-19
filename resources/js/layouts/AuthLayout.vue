<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

const {
    title = '',
    description = '',
    scenic = false,
} = defineProps<{
    title?: string;
    description?: string;
    scenic?: boolean;
}>();
</script>

<template>
    <AuthLayout :title="title" :description="description" :scenic="scenic">
        <slot />
    </AuthLayout>
</template>
