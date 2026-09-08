<script setup lang="ts">
import UiContainer from '../components/ui/UiContainer.vue';
</script>

<template>
    <main class="min-h-screen bg-surface-page py-8 text-text-primary sm:py-12">
        <UiContainer>
            <header class="mb-10 space-y-1">
                <p class="font-display text-3xl leading-none text-text-primary">Salón y Barbería Yaris</p>
                <p class="font-ui text-xs font-semibold uppercase tracking-[0.2em] text-text-secondary">Administración técnica</p>
            </header>
            <slot />
        </UiContainer>
    </main>
</template>
