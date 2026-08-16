<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { MapPin, Navigation, Plus, Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';

type Spot = {
    id: number;
    title: string;
    description: string | null;
    latitude: number | null;
    longitude: number | null;
    image_url: string;
    navigation_url: string | null;
    created_at: string;
};

defineProps<{ spots: Spot[] }>();

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

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('nl-NL', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(value));
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

        <div
            v-if="spots.length === 0"
            class="flex min-h-80 flex-col items-center justify-center rounded-2xl border border-dashed p-8 text-center"
        >
            <div class="mb-4 rounded-full bg-primary/10 p-4">
                <MapPin class="size-8 text-primary" />
            </div>
            <h2 class="text-lg font-medium">Nog geen plekken opgeslagen</h2>
            <p class="mt-1 max-w-sm text-sm text-muted-foreground">
                Leg je eerste leuke plek vast met een foto en je huidige
                locatie.
            </p>
            <Button as-child class="mt-6">
                <Link href="/spots/create"
                    ><Plus /> Eerste plek vastleggen</Link
                >
            </Button>
        </div>

        <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="spot in spots"
                :key="spot.id"
                class="overflow-hidden rounded-2xl border bg-card shadow-sm"
            >
                <img
                    :src="spot.image_url"
                    :alt="spot.title"
                    class="aspect-[4/3] w-full object-cover"
                />
                <div class="p-4">
                    <h2 class="truncate text-lg font-semibold">
                        {{ spot.title }}
                    </h2>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ formatDate(spot.created_at) }}
                    </p>
                    <p
                        v-if="spot.description"
                        class="mt-3 line-clamp-2 text-sm text-muted-foreground"
                    >
                        {{ spot.description }}
                    </p>
                    <div class="mt-4 flex gap-2">
                        <Button
                            v-if="spot.navigation_url"
                            as-child
                            class="flex-1"
                        >
                            <a
                                :href="spot.navigation_url"
                                target="_blank"
                                rel="noopener"
                            >
                                <Navigation /> Navigeer
                            </a>
                        </Button>
                        <div
                            v-else
                            class="flex flex-1 items-center justify-center rounded-md bg-muted px-3 text-sm text-muted-foreground"
                        >
                            Geen locatie
                        </div>
                        <Button
                            variant="outline"
                            size="icon"
                            aria-label="Plek verwijderen"
                            @click="removeSpot(spot)"
                        >
                            <Trash2 />
                        </Button>
                    </div>
                </div>
            </article>
        </div>
    </main>

    <div
        class="fixed inset-x-0 bottom-5 z-20 flex justify-center px-4 sm:hidden"
    >
        <Button as-child size="lg" class="w-full max-w-sm shadow-lg">
            <Link href="/spots/create"><Plus /> Plek vastleggen</Link>
        </Button>
    </div>
</template>
