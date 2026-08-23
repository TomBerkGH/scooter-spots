<script setup lang="ts">
import { Trash2 } from '@lucide/vue';
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
import type { Spot } from '@/types/spot';

defineProps<{
    spot: Spot | null;
    deleting?: boolean;
}>();

const emit = defineEmits<{
    close: [];
    confirm: [];
}>();

function handleOpenChange(open: boolean): void {
    if (!open) {
        emit('close');
    }
}
</script>

<template>
    <Dialog :open="spot !== null" @update:open="handleOpenChange">
        <DialogContent v-if="spot" class="sm:max-w-md">
            <DialogHeader>
                <div
                    class="mb-2 flex size-11 items-center justify-center rounded-full bg-destructive/10 text-destructive"
                >
                    <Trash2 class="size-5" />
                </div>
                <DialogTitle>Deze plek verwijderen?</DialogTitle>
                <DialogDescription>
                    Weet je zeker dat je
                    <span class="font-medium text-foreground">
                        “{{ spot.title }}”
                    </span>
                    wilt verwijderen? Deze actie kan niet ongedaan worden.
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2 sm:justify-end">
                <DialogClose as-child>
                    <Button variant="outline" :disabled="deleting">
                        Annuleren
                    </Button>
                </DialogClose>
                <Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="emit('confirm')"
                >
                    <Trash2 v-if="!deleting" />
                    {{ deleting ? 'Bezig met verwijderen…' : 'Verwijderen' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
