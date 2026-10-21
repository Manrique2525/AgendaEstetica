<script setup lang="ts">
import UiButton from '../ui/UiButton.vue';
import { RouterLink } from 'vue-router';

interface Props {
    title: string;
    description?: string;
    createRoute?: string;
    createLabel?: string;
    count?: number;
}

const props = withDefaults(defineProps<Props>(), {
    createLabel: 'Nuevo',
});

const countText = computed(() => {
    if (props.count === undefined) return '';
    return props.count === 1 ? '1 registro' : `${props.count} registros`;
});
</script>

<template>
    <header class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div>
                <h1 class="font-display text-4xl leading-none text-text-primary">{{ title }}</h1>
                <p v-if="description" class="mt-2 font-body text-sm text-text-secondary">{{ description }}</p>
            </div>
            <div v-if="count !== undefined" class="flex items-center gap-3 shrink-0">
                <span class="font-body text-sm text-text-secondary">{{ countText }}</span>
                <RouterLink v-if="createRoute" :to="createRoute" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-action-primary px-5 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring focus-visible:ring-offset-2">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ createLabel }}
                </RouterLink>
            </div>
        </div>
    </header>
</template>