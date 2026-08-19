<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Search, X } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    initialSearch: string;
}>();

const search = ref(props.initialSearch);

watch(
    () => props.initialSearch,
    (value) => (search.value = value),
);

function submit(): void {
    router.get('/spots', search.value ? { search: search.value } : {}, {
        preserveState: true,
        replace: true,
    });
}

function clear(): void {
    search.value = '';
    submit();
}
</script>

<template>
    <form class="relative mb-6" @submit.prevent="submit">
        <Search
            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
        />
        <Input
            v-model="search"
            type="search"
            aria-label="Plekken zoeken"
            placeholder="Zoek op naam, notitie, tag of adres…"
            class="pr-10 pl-9"
        />
        <button
            v-if="search"
            type="button"
            aria-label="Zoekopdracht wissen"
            class="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground hover:text-foreground"
            @click="clear"
        >
            <X class="size-4" />
        </button>
    </form>
</template>
