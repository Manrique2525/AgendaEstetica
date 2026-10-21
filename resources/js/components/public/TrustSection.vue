<script setup lang="ts">
import UiCard from '../ui/UiCard.vue';
</script>

<template>
    <section id="confianza" class="scroll-mt-6 space-y-6" aria-labelledby="trust-title">
        <div>
            <p class="font-ui text-xs font-bold uppercase tracking-[0.18em] text-brand-turquoise">Confianza y seguridad</p>
            <h2 id="trust-title" class="mt-2 font-display text-4xl text-text-primary sm:text-5xl">Confianza y seguridad en cada reserva</h2>
            <p class="mt-3 max-w-3xl font-body text-base leading-relaxed text-text-secondary">Conoce cómo gestionamos tus citas y qué medidas ayudan a proteger tu información.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <UiCard class="!border-border-default !bg-surface-elevated !text-text-primary !shadow-sm">
                <div class="p-6 space-y-4">
                    <div class="inline-flex items-center justify-center size-12 rounded-full bg-brand-turquoise/15 text-brand-turquoise" aria-hidden="true">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="font-display text-2xl font-bold text-text-primary">Transparencia en las reservas</h3>
                    <p class="font-body text-sm leading-relaxed text-text-secondary">El flujo público te guía paso a paso: eliges servicio, profesional, fecha, horario y confirmas tus datos. El backend valida disponibilidad en tiempo real y previene dobles reservas mediante bloqueos concurrentes.</p>
                </div>
            </UiCard>

            <UiCard class="!border-border-default !bg-surface-elevated !text-text-primary !shadow-sm">
                <div class="p-6 space-y-4">
                    <div class="inline-flex items-center justify-center size-12 rounded-full bg-brand-pink/15 text-brand-pink" aria-hidden="true">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 15v-2.5m0 0a2.5 2.5 0 015 0v-2.5m0 0a2.5 2.5 0 00-5 0v2.5m0 0h10" />
                        </svg>
                    </div>
                    <h3 class="font-display text-2xl font-bold text-text-primary">Acceso protegido</h3>
                    <p class="font-body text-sm leading-relaxed text-text-secondary">El panel administrativo requiere autenticación con credenciales válidas y rol de administrador. Las sesiones usan cookies seguras HttpOnly, CSRF tokens y regeneración de sesión tras login.</p>
                </div>
            </UiCard>

            <UiCard class="!border-border-default !bg-surface-elevated !text-text-primary !shadow-sm">
                <div class="p-6 space-y-4">
                    <div class="inline-flex items-center justify-center size-12 rounded-full bg-brand-gold/15 text-brand-gold" aria-hidden="true">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="font-display text-2xl font-bold text-text-primary">Tus datos protegidos</h3>
                    <p class="font-body text-sm leading-relaxed text-text-secondary">Solo solicitamos nombre y teléfono para confirmar tu cita. Los datos viajan por HTTPS, se normalizan en el servidor y se almacenan en la base de datos con acceso restringido al personal autorizado.</p>
                </div>
            </UiCard>

            <UiCard class="!border-border-default !bg-surface-elevated !text-text-primary !shadow-sm">
                <div class="p-6 space-y-4">
                    <div class="inline-flex items-center justify-center size-12 rounded-full bg-brand-pink/15 text-brand-pink" aria-hidden="true">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="font-display text-2xl font-bold text-text-primary">Transparencia y contacto</h3>
                    <p class="font-body text-sm leading-relaxed text-text-secondary">Nuestro aviso de privacidad y términos están accesibles antes de confirmar la cita. Puedes consultar políticas de cancelación, reprogramación y contactarnos por WhatsApp para dudas.</p>
                </div>
            </UiCard>
        </div>
    </section>
</template>