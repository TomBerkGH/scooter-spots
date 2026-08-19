<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
import type { Tag } from '@/types/spot';

defineProps<{
    tags: Tag[];
    error?: string;
}>();

const selectedTags = defineModel<number[]>({ required: true });

function setTag(tagId: number, selected: boolean): void {
    selectedTags.value = selected
        ? [...selectedTags.value, tagId]
        : selectedTags.value.filter((id) => id !== tagId);
}
</script>

<template>
    <fieldset v-if="tags.length" class="space-y-3">
        <legend class="text-sm font-medium">Tags</legend>
        <p class="text-sm text-muted-foreground">
            Kies één of meer tags die bij deze plek passen (optioneel).
        </p>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
            <label
                v-for="tag in tags"
                :key="tag.id"
                :for="`tag-${tag.id}`"
                class="flex cursor-pointer items-center gap-2 rounded-lg border p-3 text-sm transition-colors has-data-[state=checked]:border-primary has-data-[state=checked]:bg-primary/5"
            >
                <Checkbox
                    :id="`tag-${tag.id}`"
                    :model-value="selectedTags.includes(tag.id)"
                    @update:model-value="setTag(tag.id, $event === true)"
                />
                <span>{{ tag.name }}</span>
            </label>
        </div>
        <InputError :message="error" />
    </fieldset>
</template>
