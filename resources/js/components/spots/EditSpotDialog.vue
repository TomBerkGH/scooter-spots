<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle, Pencil } from '@lucide/vue';
import { computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import SpotPhotoInput from '@/components/spots/SpotPhotoInput.vue';
import SpotTagSelector from '@/components/spots/SpotTagSelector.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Spot, Tag } from '@/types/spot';

const props = defineProps<{
    spot: Spot | null;
    tags: Tag[];
}>();

const emit = defineEmits<{
    close: [];
    saved: [];
}>();

const form = useForm({
    title: '',
    description: '',
    tags: [] as number[],
    images: { view: null as string | null },
});

const existingViewImageUrl = computed(
    () =>
        props.spot?.images.find((image) => image.type === 'view')?.image_url ??
        null,
);

watch(
    () => props.spot,
    (spot) => {
        if (spot) {
            form.title = spot.title ?? '';
            form.description = spot.description ?? '';
            form.tags = spot.tags.map((tag) => tag.id);
            form.images.view = null;
            form.clearErrors();
        }
    },
    { immediate: true },
);

function handleOpenChange(open: boolean): void {
    if (!open && !form.processing) {
        emit('close');
    }
}

function submit(): void {
    if (!props.spot || form.processing) {
        return;
    }

    form.patch(`/spots/${props.spot.id}`, {
        preserveScroll: true,
        onSuccess: () => emit('saved'),
    });
}
</script>

<template>
    <Dialog :open="spot !== null" @update:open="handleOpenChange">
        <DialogContent
            v-if="spot"
            class="max-h-[calc(100dvh-2rem)] overflow-y-auto overscroll-contain sm:max-w-lg"
        >
            <form class="space-y-6" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Pencil class="size-5 text-muted-foreground" />
                        Plek bewerken
                    </DialogTitle>
                    <DialogDescription>
                        Pas de naam, omschrijving en afbeeldingen van deze plek
                        aan.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-2">
                    <Label for="edit-title">
                        Naam van de plek
                        <span class="text-muted-foreground">(optioneel)</span>
                    </Label>
                    <Input
                        id="edit-title"
                        v-model="form.title"
                        maxlength="255"
                        required
                        autofocus
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="space-y-2">
                    <Label for="edit-description">
                        Omschrijving
                        <span class="text-muted-foreground">(optioneel)</span>
                    </Label>
                    <textarea
                        id="edit-description"
                        v-model="form.description"
                        rows="4"
                        maxlength="1000"
                        placeholder="Wat maakt deze plek bijzonder?"
                        class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <SpotPhotoInput
                    id="edit-view"
                    v-model="form.images.view"
                    label="Uitzicht"
                    :required="false"
                    :existing-image-url="existingViewImageUrl"
                    :error="form.errors['images.view']"
                />

                <SpotTagSelector
                    v-model="form.tags"
                    :tags="tags"
                    :error="form.errors.tags"
                />

                <DialogFooter class="gap-2 sm:justify-end">
                    <DialogClose as-child>
                        <Button variant="outline" :disabled="form.processing">
                            Annuleren
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        :disabled="form.processing || !form.tags.length"
                    >
                        <LoaderCircle
                            v-if="form.processing"
                            class="animate-spin"
                        />
                        <Pencil v-else />
                        {{ form.processing ? 'Opslaan…' : 'Opslaan' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
