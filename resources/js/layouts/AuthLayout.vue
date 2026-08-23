<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import { usePage } from '@inertiajs/vue3';

const {
    title = '',
    description = '',
    scenic = false,
} = defineProps<{
    title?: string;
    description?: string;
    scenic?: boolean;
}>();
const page = usePage();
</script>

<template>
    <AuthLayout :title="title || page.props.name" :description="description" :scenic="scenic">
        <slot />
    </AuthLayout>
</template>
