<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, LoaderCircle } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import SpotLocationPicker from '@/components/spots/SpotLocationPicker.vue';
import SpotPhotoInput from '@/components/spots/SpotPhotoInput.vue';
import SpotTagSelector from '@/components/spots/SpotTagSelector.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Tag } from '@/types/spot';

defineProps<{ tags: Tag[] }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Mijn plekken', href: '/spots' },
            { title: 'Plek vastleggen', href: '/spots/create' },
        ],
    },
});

const form = useForm({
    title: '',
    description: '',
    latitude: null as number | null,
    longitude: null as number | null,
    image: null as string | null,
    tags: [] as number[],
});
const locating = ref(false);
const locationAttempted = ref(false);

function submit(): void {
    form.post('/spots');
}
</script>

<template>
    <Head title="Plek vastleggen" />

    <main class="mx-auto w-full max-w-xl px-4 py-6 sm:px-6">
        <Link
            href="/spots"
            class="mb-5 inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground"
        >
            <ArrowLeft class="size-4" /> Terug naar mijn plekken
        </Link>

        <h1 class="text-2xl font-semibold tracking-tight">Plek vastleggen</h1>
        <p class="mt-1 text-sm text-muted-foreground">
            Maak een foto terwijl je veilig stilstaat.
        </p>

        <form class="mt-7 space-y-6" @submit.prevent="submit">
            <SpotPhotoInput v-model="form.image" :error="form.errors.image" />

            <SpotLocationPicker
                v-model:latitude="form.latitude"
                v-model:longitude="form.longitude"
                v-model:locating="locating"
                v-model:attempted="locationAttempted"
                :error="form.errors.latitude || form.errors.longitude"
            />

            <div class="space-y-2">
                <Label for="title">Naam van de plek</Label>
                <Input
                    id="title"
                    v-model="form.title"
                    placeholder="Bijvoorbeeld: bankje aan het water"
                    maxlength="255"
                    required
                    autofocus
                />
                <InputError :message="form.errors.title" />
            </div>

            <div class="space-y-2">
                <Label for="description">
                    Notitie
                    <span class="text-muted-foreground">(optioneel)</span>
                </Label>
                <textarea
                    id="description"
                    v-model="form.description"
                    rows="4"
                    maxlength="1000"
                    placeholder="Wat maakt deze plek bijzonder?"
                    class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                />
                <InputError :message="form.errors.description" />
            </div>

            <SpotTagSelector
                v-model="form.tags"
                :tags="tags"
                :error="form.errors.tags"
            />

            <Button
                type="submit"
                size="lg"
                class="w-full"
                :disabled="
                    form.processing ||
                    locating ||
                    !locationAttempted ||
                    !form.image
                "
            >
                <LoaderCircle v-if="form.processing" class="animate-spin" />
                {{
                    form.processing
                        ? 'Opslaan…'
                        : form.latitude === null
                          ? 'Opslaan zonder locatie'
                          : 'Plek opslaan'
                }}
            </Button>
            <p
                v-if="Object.keys(form.errors).length"
                class="text-center text-sm text-destructive"
            >
                Opslaan is niet gelukt. Controleer de meldingen hierboven en
                probeer opnieuw.
            </p>
        </form>
    </main>
</template>
