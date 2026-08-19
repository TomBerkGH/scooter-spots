<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import SpotCard from '@/components/spots/SpotCard.vue';
import SpotLocationDetailsModal from '@/components/spots/SpotLocationDetailsModal.vue';
import SpotSearchBar from '@/components/spots/SpotSearchBar.vue';
import SpotsEmptyState from '@/components/spots/SpotsEmptyState.vue';
import { Button } from '@/components/ui/button';
import type { Spot } from '@/types/spot';

defineProps<{
    spots: Spot[];
    filters: { search: string };
}>();

const selectedSpot = ref<Spot | null>(null);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Mijn plekken', href: '/spots' }],
    },
});

function removeSpot(spot: Spot): void {
    if (window.confirm(`Wil je “${spot.title}” verwijderen?`)) {
        router.delete(`/spots/${spot.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Mijn plekken" />

    <main class="mx-auto w-full max-w-5xl px-4 py-6 pb-28 sm:px-6">
        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Mijn plekken
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Mooie plekken om later terug te vinden.
                </p>
            </div>
            <Button as-child class="hidden sm:flex">
                <Link href="/spots/create"><Plus /> Plek vastleggen</Link>
            </Button>
        </div>

        <SpotSearchBar
            v-if="spots.length || filters.search"
            :initial-search="filters.search"
        />
        <SpotsEmptyState v-if="!spots.length" :search="filters.search" />

        <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <SpotCard
                v-for="spot in spots"
                :key="spot.id"
                :spot="spot"
                @delete="removeSpot"
                @show-location="selectedSpot = $event"
            />
        </div>

        <p
            v-if="spots.some((spot) => spot.location_data)"
            class="mt-6 text-center text-xs text-muted-foreground"
        >
            Locatiegegevens ©
            <a
                href="https://www.openstreetmap.org/copyright"
                target="_blank"
                rel="noopener"
                class="underline underline-offset-2 hover:text-foreground"
                >OpenStreetMap-bijdragers</a
            >
        </p>
    </main>

    <SpotLocationDetailsModal
        :spot="selectedSpot"
        @close="selectedSpot = null"
    />

    <div
        class="fixed inset-x-0 bottom-5 z-20 flex justify-center px-4 sm:hidden"
    >
        <Button as-child size="lg" class="w-full max-w-sm shadow-lg">
            <Link href="/spots/create"><Plus /> Plek vastleggen</Link>
        </Button>
    </div>
</template>
