<script setup lang="ts">
import { Database, MapPin, Navigation, Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { formatSpotDate, locationLabel } from '@/lib/spotLocation';
import type { Spot } from '@/types/spot';

defineProps<{ spot: Spot }>();

defineEmits<{
    delete: [spot: Spot];
    showLocation: [spot: Spot];
}>();
</script>

<template>
    <article class="overflow-hidden rounded-2xl border bg-card shadow-sm">
        <img
            :src="spot.image_url"
            :alt="spot.title"
            class="aspect-[4/3] w-full object-cover"
        />
        <div class="p-4">
            <h2 class="truncate text-lg font-semibold">{{ spot.title }}</h2>
            <p class="mt-1 text-xs text-muted-foreground">
                {{ formatSpotDate(spot.created_at) }}
            </p>
            <p
                v-if="spot.location_data"
                class="mt-2 flex items-start gap-1.5 text-xs text-muted-foreground"
            >
                <MapPin class="mt-0.5 size-3.5 shrink-0" />
                <span>{{ locationLabel(spot.location_data) }}</span>
            </p>
            <p
                v-if="spot.description"
                class="mt-3 line-clamp-2 text-sm text-muted-foreground"
            >
                {{ spot.description }}
            </p>
            <div v-if="spot.tags.length" class="mt-3 flex flex-wrap gap-1.5">
                <span
                    v-for="tag in spot.tags"
                    :key="tag.id"
                    class="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground"
                >
                    {{ tag.name }}
                </span>
            </div>
            <div class="mt-4 flex gap-2">
                <Button v-if="spot.navigation_url" as-child class="flex-1">
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
                    v-if="spot.location_data"
                    variant="outline"
                    size="icon"
                    aria-label="OpenStreetMap-gegevens bekijken"
                    @click="$emit('showLocation', spot)"
                >
                    <Database />
                </Button>
                <Button
                    variant="outline"
                    size="icon"
                    aria-label="Plek verwijderen"
                    @click="$emit('delete', spot)"
                >
                    <Trash2 />
                </Button>
            </div>
        </div>
    </article>
</template>
