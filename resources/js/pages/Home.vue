<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { MapPin } from '@lucide/vue';
import { computed, ref } from 'vue';
import SpotCard from '@/components/spots/SpotCard.vue';
import SpotLocationDetailsModal from '@/components/spots/SpotLocationDetailsModal.vue';
import SpotSearchBar from '@/components/spots/SpotSearchBar.vue';
import SpotsEmptyState from '@/components/spots/SpotsEmptyState.vue';
import { Button } from '@/components/ui/button';
import type { Spot } from '@/types/spot';

defineProps<{ spots: Spot[]; filters: { search: string } }>();
const selectedSpot = ref<Spot | null>(null);
const page = usePage();
const appName = computed(() => page.props.name);
const isAuthenticated = computed(() => Boolean(page.props.auth?.user));
</script>

<template>
    <Head title="Alle spots" />
    <main class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <Link href="/" class="flex items-center gap-3 text-lg font-semibold">
                <span class="flex size-10 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                    <MapPin class="size-6" />
                </span>
                {{ appName }}
            </Link>
            <Button as-child variant="outline">
                <Link :href="isAuthenticated ? '/spots' : '/login'">
                    {{ isAuthenticated ? 'Mijn plekken' : 'Inloggen' }}
                </Link>
            </Button>
        </div>
        <SpotSearchBar v-if="spots.length || filters.search" :initial-search="filters.search" action="/" />
        <SpotsEmptyState v-if="!spots.length" :search="filters.search" />
        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 xl:grid-cols-7 2xl:grid-cols-8">
            <SpotCard v-for="spot in spots" :key="spot.id" :spot="spot" :can-delete="false" @show-location="selectedSpot = $event" />
        </div>
    </main>
    <SpotLocationDetailsModal :spot="selectedSpot" @close="selectedSpot = null" />
</template>
