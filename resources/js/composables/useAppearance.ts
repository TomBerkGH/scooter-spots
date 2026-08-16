import type { ComputedRef, Ref } from 'vue';
import { computed, ref } from 'vue';
import type { Appearance, ResolvedAppearance } from '@/types';

export type { Appearance, ResolvedAppearance };

export type UseAppearanceReturn = {
    appearance: Ref<Appearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: Appearance) => void;
};

const appearance = ref<Appearance>('light');

function persistLightAppearance(): void {
    if (typeof window === 'undefined') {
        return;
    }

    document.documentElement.classList.remove('dark');
    localStorage.setItem('appearance', 'light');
    document.cookie = 'appearance=light;path=/;max-age=31536000;SameSite=Lax';
}

export function updateTheme(): void {
    persistLightAppearance();
}

export function initializeTheme(): void {
    persistLightAppearance();
}

export function useAppearance(): UseAppearanceReturn {
    const resolvedAppearance = computed<ResolvedAppearance>(() => 'light');

    function updateAppearance(): void {
        appearance.value = 'light';
        persistLightAppearance();
    }

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
}
