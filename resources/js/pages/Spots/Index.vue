<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import DeleteSpotDialog from '@/components/spots/DeleteSpotDialog.vue';
import EditSpotDialog from '@/components/spots/EditSpotDialog.vue';
import SpotCard from '@/components/spots/SpotCard.vue';
import SpotLocationDetailsModal from '@/components/spots/SpotLocationDetailsModal.vue';
import SpotSearchBar from '@/components/spots/SpotSearchBar.vue';
import SpotsEmptyState from '@/components/spots/SpotsEmptyState.vue';
import { Button } from '@/components/ui/button';
import type { Spot, Tag } from '@/types/spot';

defineProps<{
    spots: Spot[];
    tags: Tag[];
    filters: { search: string };
}>();

const selectedSpot = ref<Spot | null>(null);
const spotToDelete = ref<Spot | null>(null);
const spotToEdit = ref<Spot | null>(null);
const isDeleting = ref(false);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Mijn plekken', href: '/spots' }],
    },
});

function removeSpot(spot: Spot): void {
    spotToDelete.value = spot;
}

function editSpot(spot: Spot): void {
    spotToEdit.value = spot;
}

function closeDeleteDialog(): void {
    if (!isDeleting.value) {
        spotToDelete.value = null;
    }
}

function confirmRemoveSpot(): void {
    const spot = spotToDelete.value;

    if (!spot || isDeleting.value) {
        return;
    }

    isDeleting.value = true;
    router.delete(`/spots/${spot.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            spotToDelete.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
}
</script>

<template>
    <Head title="Mijn plekken" />

    <main class="mx-auto w-full px-4 py-6 pb-28 sm:px-6 lg:px-8">
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

        <div
            v-else
            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 xl:grid-cols-7 2xl:grid-cols-8"
        >
            <SpotCard
                v-for="spot in spots"
                :key="spot.id"
                :spot="spot"
                :can-edit="true"
                :can-delete="true"
                @edit="editSpot"
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

    <DeleteSpotDialog
        :spot="spotToDelete"
        :deleting="isDeleting"
        @close="closeDeleteDialog"
        @confirm="confirmRemoveSpot"
    />

    <EditSpotDialog
        :spot="spotToEdit"
        :tags="tags"
        @close="spotToEdit = null"
        @saved="spotToEdit = null"
    />

    <div
        class="fixed inset-x-0 bottom-5 z-20 flex justify-center px-4 sm:hidden"
    >
        <Button as-child size="lg" class="w-full max-w-sm shadow-lg">
            <Link href="/spots/create"><Plus /> Plek vastleggen</Link>
        </Button>
    </div>
</template>
