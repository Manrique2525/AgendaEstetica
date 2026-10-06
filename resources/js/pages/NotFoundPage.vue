<script setup lang="ts">
import PublicLayout from '../layouts/PublicLayout.vue';
import UiCard from '../components/ui/UiCard.vue';
</script>

<template>
    <PublicLayout>
        <UiCard>
            <p class="font-ui text-sm font-semibold uppercase tracking-[0.16em] text-text-secondary">Error técnico</p>
            <p class="mt-4 font-display text-6xl leading-none text-text-primary" aria-hidden="true">404</p>
            <h1 class="mt-4 font-ui text-xl font-bold text-text-primary">Página no encontrada</h1>
            <p class="mt-3 font-body text-base text-text-secondary">Esta ruta técnica no existe.</p>
        </UiCard>
    </PublicLayout>
</template>
