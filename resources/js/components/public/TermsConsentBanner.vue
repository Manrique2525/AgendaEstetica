<script setup lang="ts">
import { ref } from 'vue';
import { RouterLink } from 'vue-router';

type TermsDecision = 'none' | 'accepted' | 'rejected';

const decision = ref<TermsDecision>('none');
</script>

<template>
    <section class="fixed inset-x-0 bottom-0 z-40 border-t border-brand-pink/40 bg-brand-black/95 text-text-inverse shadow-[0_-12px_30px_rgba(0,0,0,0.35)] backdrop-blur" aria-labelledby="terms-banner-title">
        <div class="mx-auto max-w-5xl px-5 py-4 sm:px-8 sm:py-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between lg:gap-8">
                <div class="min-w-0">
                    <h2 id="terms-banner-title" class="font-ui text-sm font-bold uppercase tracking-[0.12em] text-brand-gold">Términos y Condiciones y Aviso de Privacidad</h2>
                    <p class="mt-2 max-w-3xl font-body text-sm leading-relaxed text-white/80">Revisa la información provisional antes de elegir si aceptas o rechazas este alcance académico. Esta decisión no se guarda en backend.</p>
                    <RouterLink to="/terminos-condiciones" class="mt-2 inline-block rounded-md font-ui text-sm font-bold text-brand-pink underline underline-offset-4 focus:outline-none focus:ring-2 focus:ring-focus-ring">Leer Términos y Condiciones</RouterLink>
                    <p v-if="decision === 'accepted'" role="status" class="mt-2 font-body text-sm font-semibold text-brand-gold">Términos aceptados para esta demostración.</p>
                    <p v-else-if="decision === 'rejected'" role="status" class="mt-2 font-body text-sm font-semibold text-brand-pink">Has rechazado los términos de esta demostración.</p>
                    <p v-else class="mt-2 font-body text-sm text-white/60">Aún no has elegido una opción.</p>
                </div>
                <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
                    <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-md border border-white/40 px-4 py-2 font-ui text-sm font-bold text-text-inverse hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black" :aria-pressed="decision === 'rejected'" @click="decision = 'rejected'">Rechazar</button>
                    <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-md bg-action-primary px-4 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black" :aria-pressed="decision === 'accepted'" @click="decision = 'accepted'">Aceptar</button>
                </div>
            </div>
        </div>
    </section>
</template>
