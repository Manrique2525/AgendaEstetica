<script setup lang="ts">
import { ref } from 'vue';
import { RouterLink } from 'vue-router';

type TermsDecision = 'none' | 'accepted' | 'rejected';

const ACADEMIC_CONSENT_STORAGE_KEY = 'yaris.academic-consent.v1';

function readStoredDecision(): TermsDecision {
    try {
        const stored = sessionStorage.getItem(ACADEMIC_CONSENT_STORAGE_KEY);

        return stored === 'accepted' || stored === 'rejected' ? stored : 'none';
    } catch {
        return 'none';
    }
}

const decision = ref<TermsDecision>(readStoredDecision());

function chooseDecision(nextDecision: Exclude<TermsDecision, 'none'>): void {
    decision.value = nextDecision;

    try {
        sessionStorage.setItem(ACADEMIC_CONSENT_STORAGE_KEY, nextDecision);
    } catch {
        // Keep the interaction usable; a failed write shows the banner again later.
    }
}
</script>

<template>
    <section v-if="decision === 'none'" class="fixed inset-x-0 bottom-0 z-40 border-t border-brand-pink/40 bg-brand-black/95 text-text-inverse shadow-[0_-12px_30px_rgba(0,0,0,0.35)] backdrop-blur" aria-labelledby="terms-banner-title">
        <div class="mx-auto max-w-5xl px-4 py-3 sm:px-8 sm:py-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-6">
                <div class="min-w-0">
                    <h2 id="terms-banner-title" class="font-ui text-sm font-bold uppercase tracking-[0.1em] text-brand-gold">Términos y privacidad</h2>
                    <p class="mt-1 font-body text-sm leading-snug text-white/80">Consulta nuestros términos antes de continuar.</p>
                    <RouterLink to="/terminos-condiciones" class="mt-1 inline-block rounded-md font-ui text-sm font-bold text-brand-pink underline underline-offset-4 focus:outline-none focus:ring-2 focus:ring-focus-ring">Leer términos</RouterLink>
                </div>
                <div class="grid shrink-0 grid-cols-2 gap-2 sm:flex sm:flex-row">
                    <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-md border border-white/40 px-3 py-2 font-ui text-sm font-bold text-text-inverse hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black" @click="chooseDecision('rejected')">Rechazar</button>
                    <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-md bg-action-primary px-3 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black" @click="chooseDecision('accepted')">Aceptar</button>
                </div>
            </div>
        </div>
    </section>
</template>
