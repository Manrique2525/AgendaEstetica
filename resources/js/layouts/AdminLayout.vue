<script setup lang="ts">
import UiContainer from '../components/ui/UiContainer.vue';
import { RouterLink } from 'vue-router';
</script>

<template>
    <main class="min-h-screen bg-surface-page py-8 text-text-primary sm:py-12">
        <UiContainer>
            <header class="mb-10 space-y-1">
                <p class="font-display text-3xl leading-none text-text-primary">Salón y Barbería Yaris</p>
                <p class="font-ui text-xs font-semibold uppercase tracking-[0.2em] text-text-secondary">Administración técnica</p>
            </header>
            <nav aria-label="Navegación administrativa" class="mb-8 flex flex-wrap gap-3 font-ui text-sm font-semibold">
                <RouterLink class="rounded-md px-3 py-2 text-text-secondary hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring" to="/admin">
                    Inicio
                </RouterLink>
                <RouterLink class="rounded-md px-3 py-2 text-text-secondary hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring" to="/admin/agenda">
                    Agenda
                </RouterLink>
            </nav>
            <slot />
        </UiContainer>
    </main>
</template>
