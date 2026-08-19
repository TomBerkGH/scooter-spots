<script setup lang="ts">
import { LoaderCircle, LocateFixed, MapPin } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';

defineProps<{ error?: string }>();

const latitude = defineModel<number | null>('latitude', { required: true });
const longitude = defineModel<number | null>('longitude', { required: true });
const locating = defineModel<boolean>('locating', { required: true });
const attempted = defineModel<boolean>('attempted', { required: true });
const locationError = ref<string | null>(null);

function getLocation(): void {
    locationError.value = null;

    if (!navigator.geolocation) {
        locationError.value = 'Je browser ondersteunt geen locatiebepaling.';
        attempted.value = true;

        return;
    }

    locating.value = true;
    navigator.geolocation.getCurrentPosition(
        (position) => {
            latitude.value = position.coords.latitude;
            longitude.value = position.coords.longitude;
            locating.value = false;
            attempted.value = true;
        },
        () => {
            locationError.value =
                'Locatie ophalen lukte niet. Geef de browser toegang tot je locatie.';
            locating.value = false;
            attempted.value = true;
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 },
    );
}

onMounted(getLocation);
</script>

<template>
    <div class="space-y-2">
        <Label>Locatie</Label>
        <div
            v-if="latitude !== null && longitude !== null"
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
            {{ locationError }} Je kunt de plek alsnog zonder locatie opslaan.
        </p>
        <InputError :message="error" />
    </div>
</template>
