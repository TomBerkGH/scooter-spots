<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { MapPin } from '@lucide/vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
    scenic?: boolean;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden bg-background p-6 md:p-10"
        :class="scenic && 'bg-cover bg-center'"
        :style="
            scenic
                ? { backgroundImage: `url('/images/maas-heusden.jpg')` }
                : undefined
        "
    >
        <div
            v-if="scenic"
            class="absolute inset-0 bg-slate-950/45 backdrop-saturate-125"
        />

        <div
            class="relative z-10 w-full max-w-sm"
            :class="
                scenic &&
                'rounded-3xl border border-white/40 bg-white/90 p-7 shadow-2xl shadow-slate-950/30 backdrop-blur-md sm:p-9 dark:bg-slate-950/85'
            "
        >
            <div class="flex flex-col gap-7">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <div
                            class="mb-1 flex h-11 w-11 items-center justify-center rounded-xl"
                            :class="
                                scenic && 'bg-emerald-700 text-white shadow-lg'
                            "
                        >
                            <MapPin :class="scenic ? 'size-6' : 'size-9'" />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>

        <a
            v-if="scenic"
            href="https://commons.wikimedia.org/wiki/File:Maas_bij_Heusden_01.jpg"
            target="_blank"
            rel="noopener noreferrer"
            class="absolute right-4 bottom-3 z-10 text-[11px] text-white/80 transition hover:text-white"
        >
            Foto: Klankbeeld · CC BY-SA 4.0
        </a>
    </div>
</template>
