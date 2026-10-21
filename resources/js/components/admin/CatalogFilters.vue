<script setup lang="ts">
import UiInput from '../ui/UiInput.vue';
import { defineEmits, defineProps } from 'vue';

interface Props {
    search?: string;
    filters?: Record<string, string | number | boolean>;
}

interface Emits {
    search: [value: string];
    filter: [filters: Record<string, string | number | boolean>];
    clear: [];
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const search = ref(props.search ?? '');

function updateSearch(value: string): void {
    search.value = value;
}

function handleSearch(): void {
    emit('search', search.value);
}

function handleClear(): void {
    search.value = '';
    emit('clear');
}

watch(() => props.search, (value) => {
    search.value = value ?? '';
});
</script>

<template>
    <form class="flex flex-col sm:flex-row gap-3 mb-6" @submit.prevent="handleSearch">
        <div class="flex-1 min-w-0">
            <label class="sr-only" for="catalog-search">Buscar</label>
            <UiInput
                id="catalog-search"
                v-model="search"
                type="search"
                placeholder="Buscar..."
                @input="updateSearch"
                @keydown.enter="handleSearch"
            />
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <slot name="filters" />
            <button
                v-if="search"
                type="button"
                class="inline-flex min-h-11 items-center rounded-md border border-border-default px-4 font-ui text-sm font-semibold text-text-secondary hover:bg-surface-subtle focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring"
                @click="handleClear"
            >
                Limpiar
            </button>
            <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-action-primary px-4 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="sr-only">Buscar</span>
            </button>
        </div>
    </form>
</template>