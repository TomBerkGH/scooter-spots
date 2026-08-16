<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Camera,
    LoaderCircle,
    LocateFixed,
    MapPin,
} from '@lucide/vue';
import { onBeforeUnmount, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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
});

const previewUrl = ref<string | null>(null);
const locating = ref(false);
const locationAttempted = ref(false);
const locationError = ref<string | null>(null);

async function resizeImage(file: File): Promise<File> {
    const sourceUrl = URL.createObjectURL(file);

    try {
        const image = new Image();
        image.src = sourceUrl;
        await image.decode();

        const maxSize = 1600;
        const scale = Math.min(
            1,
            maxSize / Math.max(image.width, image.height),
        );
        const canvas = document.createElement('canvas');
        let width = Math.round(image.width * scale);
        let height = Math.round(image.height * scale);
        let quality = 0.82;
        let blob: Blob | null = null;

        for (let attempt = 0; attempt < 6; attempt += 1) {
            canvas.width = width;
            canvas.height = height;
            canvas
                .getContext('2d')
                ?.drawImage(image, 0, 0, canvas.width, canvas.height);

            blob = await new Promise<Blob | null>((resolve) => {
                canvas.toBlob(resolve, 'image/jpeg', quality);
            });

            if (blob && blob.size <= 1_500_000) {
                break;
            }

            width = Math.round(width * 0.85);
            height = Math.round(height * 0.85);
            quality = Math.max(0.55, quality - 0.07);
        }

        if (!blob || blob.size > 1_500_000) {
            throw new Error('Foto kon niet klein genoeg worden gemaakt.');
        }

        return new File([blob], `${file.name.replace(/\.[^.]+$/, '')}.jpg`, {
            type: 'image/jpeg',
        });
    } finally {
        URL.revokeObjectURL(sourceUrl);
    }
}

async function selectImage(event: Event): Promise<void> {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    if (!file) {
        previewUrl.value = null;
        form.image = null;

        return;
    }

    try {
        form.clearErrors('image');
        const resizedImage = await resizeImage(file);
        form.image = await new Promise<string>((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result as string);
            reader.onerror = () => reject(reader.error);
            reader.readAsDataURL(resizedImage);
        });
        previewUrl.value = URL.createObjectURL(resizedImage);
    } catch {
        form.image = null;
        form.setError(
            'image',
            'Deze foto kon niet worden verwerkt. Kies een andere foto.',
        );
    }
}

function getLocation(): void {
    locationError.value = null;

    if (!navigator.geolocation) {
        locationError.value = 'Je browser ondersteunt geen locatiebepaling.';
        locationAttempted.value = true;

        return;
    }

    locating.value = true;
    navigator.geolocation.getCurrentPosition(
        (position) => {
            form.latitude = position.coords.latitude;
            form.longitude = position.coords.longitude;

            locating.value = false;
            locationAttempted.value = true;
        },
        () => {
            locationError.value =
                'Locatie ophalen lukte niet. Geef de browser toegang tot je locatie.';
            locating.value = false;
            locationAttempted.value = true;
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 },
    );
}

function submit(): void {
    form.post('/spots');
}

onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
});

getLocation();
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
            <div class="space-y-2">
                <Label for="image">Foto</Label>
                <label
                    for="image"
                    class="relative flex aspect-[4/3] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed bg-muted/40 text-center hover:bg-muted/70"
                >
                    <img
                        v-if="previewUrl"
                        :src="previewUrl"
                        alt="Voorbeeld van gekozen foto"
                        class="absolute inset-0 size-full object-cover"
                    />
                    <div v-else class="p-6">
                        <Camera class="mx-auto mb-3 size-9" />
                        <span class="font-medium">Maak of kies een foto</span>
                        <span class="mt-1 block text-xs text-muted-foreground"
                            >Maximaal 10 MB</span
                        >
                    </div>
                </label>
                <input
                    id="image"
                    type="file"
                    accept="image/*"
                    capture="environment"
                    class="sr-only"
                    required
                    @change="selectImage"
                />
                <InputError :message="form.errors.image" />
            </div>

            <div class="space-y-2">
                <Label>Locatie</Label>
                <div
                    v-if="form.latitude !== null && form.longitude !== null"
                    class="flex items-center gap-3 rounded-xl border bg-green-50 p-3 text-sm text-green-800 dark:bg-green-950/30 dark:text-green-300"
                >
                    <MapPin class="size-5 shrink-0" />
                    <span>Locatie gevonden</span>
                </div>
                <Button
                    v-else
                    type="button"
                    variant="outline"
                    class="w-full"
                    :disabled="locating"
                    @click="getLocation"
                >
                    <LoaderCircle v-if="locating" class="animate-spin" />
                    <LocateFixed v-else />
                    {{ locating ? 'Locatie ophalen…' : 'Mijn locatie ophalen' }}
                </Button>
                <p v-if="locationError" class="text-sm text-destructive">
                    {{ locationError }} Je kunt de plek alsnog zonder locatie
                    opslaan.
                </p>
                <InputError
                    :message="form.errors.latitude || form.errors.longitude"
                />
            </div>

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
                <Label for="description"
                    >Notitie
                    <span class="text-muted-foreground"
                        >(optioneel)</span
                    ></Label
                >
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
                v-if="Object.keys(form.errors).length > 0"
                class="text-center text-sm text-destructive"
            >
                Opslaan is niet gelukt. Controleer de meldingen hierboven en
                probeer opnieuw.
            </p>
        </form>
    </main>
</template>
