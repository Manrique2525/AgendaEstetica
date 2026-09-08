<script setup lang="ts">
import PublicLayout from '../../layouts/PublicLayout.vue';
import UiCard from '../../components/ui/UiCard.vue';
</script>

<template>
    <PublicLayout>
        <UiCard>
            <p class="font-ui text-sm font-semibold uppercase tracking-[0.16em] text-text-secondary">Base visual del sistema</p>
            <h1 class="mt-4 font-display text-5xl leading-none text-text-primary">Salón y Barbería Yaris</h1>
            <p class="mt-4 font-ui text-lg font-semibold text-action-primary">Belleza y elegancia</p>
            <p class="mt-6 max-w-xl font-body text-base leading-relaxed text-text-secondary">
                Vue SPA shell técnico. El contenido de negocio se incorporará en SPECs posteriores.
            </p>
        </UiCard>
    </PublicLayout>
</template>
