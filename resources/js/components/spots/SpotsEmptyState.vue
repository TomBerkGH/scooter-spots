<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { MapPin, Plus } from '@lucide/vue';
import { Button } from '@/components/ui/button';

defineProps<{ search: string }>();
</script>

<template>
    <div
        class="flex min-h-80 flex-col items-center justify-center rounded-2xl border border-dashed p-8 text-center"
    >
        <div class="mb-4 rounded-full bg-primary/10 p-4">
            <MapPin class="size-8 text-primary" />
        </div>
        <h2 class="text-lg font-medium">
            {{
                search ? 'Geen plekken gevonden' : 'Nog geen plekken opgeslagen'
            }}
        </h2>
        <p v-if="search" class="mt-1 max-w-sm text-sm text-muted-foreground">
            Probeer een andere naam, notitie, tag of locatie.
        </p>
        <p v-else class="mt-1 max-w-sm text-sm text-muted-foreground">
            Leg je eerste leuke plek vast met een foto en je huidige locatie.
        </p>
        <Button
            v-if="search"
            variant="outline"
            class="mt-6"
            @click="router.get('/spots')"
        >
            Zoekopdracht wissen
        </Button>
        <Button v-else as-child class="mt-6">
            <Link href="/spots/create"><Plus /> Eerste plek vastleggen</Link>
        </Button>
    </div>
</template>
