<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ChevronDown,
    Database,
    ExternalLink,
    MapPin,
    Navigation,
    Plus,
    Search,
    Trash2,
    X,
} from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Dialog,
    DialogDescription,
    DialogHeader,
    DialogScrollContent,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';

type Tag = {
    id: number;
    name: string;
};

type LocationData = {
    osm_place_id: number | null;
    osm_id: number | null;
    osm_type: string | null;
    osm_class: string | null;
    place_type: string | null;
    place_rank: number | null;
    importance: number | null;
    latitude: number | null;
    longitude: number | null;
    display_name: string | null;
    name: string | null;
    house_number: string | null;
    road: string | null;
    neighbourhood: string | null;
    suburb: string | null;
    city_district: string | null;
    city: string | null;
    town: string | null;
    village: string | null;
    municipality: string | null;
    county: string | null;
    state_district: string | null;
    state: string | null;
    region: string | null;
    postcode: string | null;
    country: string | null;
    country_code: string | null;
    bounding_box: unknown[] | null;
    address: Record<string, unknown> | null;
    extra_tags: Record<string, unknown> | null;
    name_details: Record<string, unknown> | null;
    geometry: Record<string, unknown> | null;
    raw_response: Record<string, unknown>;
    license: string | null;
    fetched_at: string;
};

type Spot = {
    id: number;
    title: string;
    description: string | null;
    latitude: number | null;
    longitude: number | null;
    image_url: string;
    navigation_url: string | null;
    created_at: string;
    tags: Tag[];
    location_data: LocationData | null;
};

const props = defineProps<{
    spots: Spot[];
    filters: { search: string };
}>();

const search = ref(props.filters.search);
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

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('nl-NL', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(value));
}

function locationLabel(location: LocationData): string {
    const street = [location.road, location.house_number]
        .filter(Boolean)
        .join(' ');
    const place = location.city ?? location.town ?? location.village;
    const locality = [location.postcode, place].filter(Boolean).join(' ');

    return (
        [street, locality].filter(Boolean).join(', ') ||
        location.display_name ||
        ''
    );
}

function submitSearch(): void {
    router.get('/spots', search.value ? { search: search.value } : {}, {
        preserveState: true,
        replace: true,
    });
}

function clearSearch(): void {
    search.value = '';
    submitSearch();
}

function detailRows(
    location: LocationData,
): { label: string; value: string }[] {
    const rows = [
        ['Naam', location.name],
        ['Straat', location.road],
        ['Huisnummer', location.house_number],
        ['Buurt', location.neighbourhood],
        ['Wijk', location.suburb],
        ['Stadsdeel', location.city_district],
        ['Stad', location.city],
        ['Plaats', location.town ?? location.village],
        ['Gemeente', location.municipality],
        ['Provincie', location.state],
        ['Regio', location.region ?? location.state_district],
        ['Postcode', location.postcode],
        ['Land', location.country],
        ['Landcode', location.country_code?.toUpperCase()],
        ['OSM-type', location.osm_type],
        ['Categorie', location.osm_class],
        ['Plektype', location.place_type],
        ['OSM-ID', location.osm_id?.toString()],
        ['Place-ID', location.osm_place_id?.toString()],
        ['Rang', location.place_rank?.toString()],
        ['Belangscore', location.importance?.toString()],
        [
            'Coördinaten',
            location.latitude !== null && location.longitude !== null
                ? `${location.latitude}, ${location.longitude}`
                : null,
        ],
        ['Opgehaald', new Date(location.fetched_at).toLocaleString('nl-NL')],
    ];

    return rows
        .filter((row): row is [string, string] => Boolean(row[1]))
        .map(([label, value]) => ({ label, value }));
}

function osmUrl(location: LocationData): string | null {
    if (!location.osm_type || !location.osm_id) {
        return null;
    }

    const type =
        { N: 'node', W: 'way', R: 'relation' }[
            location.osm_type.toUpperCase()
        ] ?? location.osm_type.toLowerCase();

    return `https://www.openstreetmap.org/${type}/${location.osm_id}`;
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

        <form
            v-if="spots.length > 0"
            class="relative mb-6"
            @submit.prevent="submitSearch"
        >
            <Search
                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                v-model="search"
                type="search"
                aria-label="Plekken zoeken"
                placeholder="Zoek op naam, notitie of tag…"
                class="pr-10 pl-9"
            />
            <button
                v-if="search"
                type="button"
                aria-label="Zoekopdracht wissen"
                class="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                @click="clearSearch"
            >
                <X class="size-4" />
            </button>
        </form>

        <div
            v-if="spots.length === 0"
            class="flex min-h-80 flex-col items-center justify-center rounded-2xl border border-dashed p-8 text-center"
        >
            <div class="mb-4 rounded-full bg-primary/10 p-4">
                <MapPin class="size-8 text-primary" />
            </div>
            <h2 class="text-lg font-medium">
                {{
                    filters.search
                        ? 'Geen plekken gevonden'
                        : 'Nog geen plekken opgeslagen'
                }}
            </h2>
            <p
                v-if="filters.search"
                class="mt-1 max-w-sm text-sm text-muted-foreground"
            >
                Probeer een andere naam, notitie of tag.
            </p>
            <p v-else class="mt-1 max-w-sm text-sm text-muted-foreground">
                Leg je eerste leuke plek vast met een foto en je huidige
                locatie.
            </p>
            <Button
                v-if="filters.search"
                variant="outline"
                class="mt-6"
                @click="clearSearch"
            >
                Zoekopdracht wissen
            </Button>
            <Button v-else as-child class="mt-6">
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
                    <div
                        v-if="spot.tags.length"
                        class="mt-3 flex flex-wrap gap-1.5"
                    >
                        <span
                            v-for="tag in spot.tags"
                            :key="tag.id"
                            class="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground"
                        >
                            {{ tag.name }}
                        </span>
                    </div>
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
                            v-if="spot.location_data"
                            variant="outline"
                            size="icon"
                            aria-label="OpenStreetMap-gegevens bekijken"
                            @click="selectedSpot = spot"
                        >
                            <Database />
                        </Button>
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

    <Dialog
        :open="selectedSpot !== null"
        @update:open="(open) => !open && (selectedSpot = null)"
    >
        <DialogScrollContent
            v-if="selectedSpot?.location_data"
            class="max-w-2xl"
        >
            <DialogHeader>
                <DialogTitle>Locatiegegevens</DialogTitle>
                <DialogDescription>
                    OpenStreetMap-gegevens voor {{ selectedSpot.title }}
                </DialogDescription>
            </DialogHeader>

            <div class="rounded-lg border bg-muted/40 p-3 text-sm">
                {{ selectedSpot.location_data.display_name }}
            </div>

            <dl class="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                <div
                    v-for="row in detailRows(selectedSpot.location_data)"
                    :key="row.label"
                >
                    <dt class="text-xs text-muted-foreground">
                        {{ row.label }}
                    </dt>
                    <dd class="mt-0.5 font-medium break-words">
                        {{ row.value }}
                    </dd>
                </div>
            </dl>

            <Button
                v-if="osmUrl(selectedSpot.location_data)"
                as-child
                variant="outline"
                class="w-full"
            >
                <a
                    :href="osmUrl(selectedSpot.location_data) ?? undefined"
                    target="_blank"
                    rel="noopener"
                >
                    <ExternalLink /> Bekijk object op OpenStreetMap
                </a>
            </Button>

            <Collapsible class="rounded-lg border">
                <CollapsibleTrigger
                    class="group flex w-full items-center justify-between p-3 text-left text-sm font-medium"
                >
                    Volledige technische gegevens
                    <ChevronDown
                        class="size-4 transition-transform group-data-[state=open]:rotate-180"
                    />
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <pre
                        class="max-h-80 overflow-auto border-t bg-muted/40 p-3 text-xs whitespace-pre-wrap"
                        >{{
                            JSON.stringify(
                                selectedSpot.location_data.raw_response,
                                null,
                                2,
                            )
                        }}</pre>
                </CollapsibleContent>
            </Collapsible>

            <p class="text-xs text-muted-foreground">
                {{ selectedSpot.location_data.license }}
            </p>
        </DialogScrollContent>
    </Dialog>

    <div
        class="fixed inset-x-0 bottom-5 z-20 flex justify-center px-4 sm:hidden"
    >
        <Button as-child size="lg" class="w-full max-w-sm shadow-lg">
            <Link href="/spots/create"><Plus /> Plek vastleggen</Link>
        </Button>
    </div>
</template>
