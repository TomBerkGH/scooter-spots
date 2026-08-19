<script setup lang="ts">
import { Camera } from '@lucide/vue';
import { onBeforeUnmount, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

defineProps<{ error?: string }>();

const imageData = defineModel<string | null>({ required: true });
const previewUrl = ref<string | null>(null);
const processingError = ref<string | null>(null);

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
            canvas.getContext('2d')?.drawImage(image, 0, 0, width, height);
            blob = await new Promise<Blob | null>((resolve) =>
                canvas.toBlob(resolve, 'image/jpeg', quality),
            );

            if (blob && blob.size <= 1_500_000) {
                break;
            }

            width = Math.round(width * 0.85);
            height = Math.round(height * 0.85);
            quality = Math.max(0.55, quality - 0.07);
        }

        if (!blob || blob.size > 1_500_000) {
            throw new Error('De foto kon niet klein genoeg worden gemaakt.');
        }

        return new File([blob], `${file.name.replace(/\.[^.]+$/, '')}.jpg`, {
            type: 'image/jpeg',
        });
    } finally {
        URL.revokeObjectURL(sourceUrl);
    }
}

async function selectImage(event: Event): Promise<void> {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    previewUrl.value = null;
    imageData.value = null;
    processingError.value = null;

    if (!file) {
        return;
    }

    try {
        const resizedImage = await resizeImage(file);
        imageData.value = await new Promise<string>((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result as string);
            reader.onerror = () => reject(reader.error);
            reader.readAsDataURL(resizedImage);
        });
        previewUrl.value = URL.createObjectURL(resizedImage);
    } catch {
        processingError.value =
            'Deze foto kon niet worden verwerkt. Kies een andere foto.';
    }
}

onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
});
</script>

<template>
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
                <span class="mt-1 block text-xs text-muted-foreground">
                    Maximaal 10 MB
                </span>
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
        <InputError :message="processingError ?? error" />
    </div>
</template>
