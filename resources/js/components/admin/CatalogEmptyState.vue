<script setup lang="ts">
import { h } from 'vue'
import UiButton from '../ui/UiButton.vue'
import { RouterLink } from 'vue-router'

interface Props {
    message?: string
    actionLabel?: string
    actionRoute?: string
    icon?: 'search' | 'package' | 'users' | 'tag'
}

const props = withDefaults(defineProps<Props>(), {
    message: 'No hay registros.',
    actionLabel: 'Crear',
})

function renderIcon(icon: 'search' | 'package' | 'users' | 'tag'): ReturnType<typeof h> {
    switch (icon) {
        case 'package':
            return h('svg', { class: 'size-12', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', 'aria-hidden': 'true' }, [
                h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '1.5', d: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10c0 2.21.34 1.95 1 2.5C7 23 12 24 17 24h1c.5 0 1.01-.1 1.5-.28' }),
            ])
        case 'users':
            return h('svg', { class: 'size-12', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', 'aria-hidden': 'true' }, [
                h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '1.5', d: 'M12 4.354a4 4 0 110 8 4 4 0 010-8zM2 10c0 4.418 4 8 8 8s8-3.582 8-8H2z' }),
            ])
        case 'tag':
            return h('svg', { class: 'size-12', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', 'aria-hidden': 'true' }, [
                h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '1.5', d: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' }),
            ])
        case 'search':
        default:
            return h('svg', { class: 'size-12', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', 'aria-hidden': 'true' }, [
                h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '1.5', d: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' }),
            ])
    }
}
</script>

<template>
    <div class="text-center py-16 sm:py-20">
        <div class="mx-auto size-16 rounded-full bg-surface-subtle flex items-center justify-center text-text-secondary mb-6" aria-hidden="true">
            <component :is="renderIcon(props.icon ?? 'search')" />
        </div>
        <h3 class="font-display text-2xl font-bold text-text-primary mb-2">
            Sin registros
        </h3>
        <p class="font-body text-base text-text-secondary max-w-md mx-auto mb-8">
            {{ message }}
        </p>
        <div v-if="actionRoute && actionLabel" class="flex flex-col sm:flex-row gap-3 justify-center">
            <RouterLink
                v-if="actionRoute"
                :to="actionRoute"
                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-action-primary px-6 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring"
            >
                {{ actionLabel }}
            </RouterLink>
            <button
                v-else
                type="button"
                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-action-primary px-6 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring"
            >
                {{ actionLabel }}
            </button>
        </div>
    </div>
</template>