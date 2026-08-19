<script setup lang="ts">
import { ChevronDown, ExternalLink } from '@lucide/vue';
import { computed } from 'vue';
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
import { locationDetailRows, openStreetMapUrl } from '@/lib/spotLocation';
import type { Spot } from '@/types/spot';

const props = defineProps<{ spot: Spot | null }>();
const emit = defineEmits<{ close: [] }>();

const location = computed(() => props.spot?.location_data ?? null);
</script>

<template>
    <Dialog
        :open="spot !== null"
        @update:open="(open) => !open && emit('close')"
    >
        <DialogScrollContent v-if="spot && location" class="max-w-2xl">
            <DialogHeader>
                <DialogTitle>Locatiegegevens</DialogTitle>
                <DialogDescription>
                    OpenStreetMap-gegevens voor {{ spot.title }}
                </DialogDescription>
            </DialogHeader>

            <div class="rounded-lg border bg-muted/40 p-3 text-sm">
                {{ location.display_name }}
            </div>

            <dl class="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                <div
                    v-for="row in locationDetailRows(location)"
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
                v-if="openStreetMapUrl(location)"
                as-child
                variant="outline"
                class="w-full"
            >
                <a
                    :href="openStreetMapUrl(location) ?? undefined"
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
                            JSON.stringify(location.raw_response, null, 2)
                        }}</pre>
                </CollapsibleContent>
            </Collapsible>

            <p class="text-xs text-muted-foreground">{{ location.license }}</p>
        </DialogScrollContent>
    </Dialog>
</template>
