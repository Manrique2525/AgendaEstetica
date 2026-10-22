<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
}

interface Emits {
    'update:currentPage': [page: number];
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const pageNumbers = computed(() => {
    const pages: (number | string)[] = [];
    const maxPages = 5;
    let start = Math.max(1, props.currentPage - Math.floor(maxPages / 2));
    let end = Math.min(props.lastPage, start + maxPages - 1);

    if (end - start + 1 < maxPages) {
        start = Math.max(1, end - maxPages + 1);
    }

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    if (start > 1) {
        pages.unshift('...');
        pages.unshift(1);
    }
    if (end < props.lastPage) {
        pages.push('...');
        pages.push(props.lastPage);
    }

    return pages;
});

function changePage(page: number | string): void {
    if (typeof page === 'number' && page !== props.currentPage && page >= 1 && page <= props.lastPage) {
        emit('update:currentPage', page);
    }
}
</script>

<template>
    <nav v-if="lastPage > 1" class="flex items-center justify-center gap-1" aria-label="Paginación">
        <button
            type="button"
            class="inline-flex min-h-10 items-center justify-center rounded-md border border-border-default px-3 font-body text-sm font-semibold text-text-secondary hover:bg-surface-subtle focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="currentPage === 1"
            aria-label="Página anterior"
            @click="changePage(currentPage - 1)"
        >
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <template v-for="page in pageNumbers" :key="page">
            <template v-if="page === '...'">
                <span class="px-2 font-body text-sm text-text-secondary">…</span>
            </template>
            <template v-else>
                <button
                    type="button"
                    class="inline-flex min-h-10 items-center justify-center min-w-10 rounded-md px-3 font-body text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring"
                    :class="[
                        page === currentPage
                            ? 'bg-action-primary text-action-primary-foreground'
                            : 'border border-border-default text-text-secondary hover:bg-surface-subtle'
                    ]"
                    :disabled="page === currentPage"
                    :aria-current="page === currentPage ? 'page' : undefined"
                    :aria-label="`Página ${page}`"
                    @click="changePage(page)"
                >
                    {{ page }}
                </button>
            </template>
        </template>

        <button
            type="button"
            class="inline-flex min-h-10 items-center justify-center rounded-md border border-border-default px-3 font-body text-sm font-semibold text-text-secondary hover:bg-surface-subtle focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="currentPage === lastPage"
            aria-label="Página siguiente"
            @click="changePage(currentPage + 1)"
        >
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </nav>

    <p class="mt-4 text-center font-body text-sm text-text-secondary">
        Mostrando {{ (currentPage - 1) * perPage + 1 }} a {{ Math.min(currentPage * perPage, total) }} de {{ total }} resultados
    </p>
</template>