<script setup lang="ts">
import UiCard from '../ui/UiCard.vue';
import { RouterLink } from 'vue-router';
import { publicSite } from '../../data/publicSite';
</script>

<template>
    <section id="confianza" class="scroll-mt-6 py-6 md:py-10" aria-labelledby="trust-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <header class="text-center max-w-3xl mx-auto mb-10 md:mb-14">
                <p class="font-ui text-xs font-bold uppercase tracking-[0.18em] text-brand-turquoise">Confianza y seguridad</p>
                <h2 id="trust-title" class="mt-2 font-display text-4xl font-bold text-text-primary sm:text-5xl md:text-6xl leading-tight">
                    Confianza y seguridad en cada reserva
                </h2>
                <p class="mt-4 max-w-3xl mx-auto font-body text-base leading-relaxed text-text-secondary">
                    Conoce cómo gestionamos tus citas y qué medidas ayudan a proteger tu información.
                </p>
            </header>

            <div class="grid gap-5 sm:gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Card 1: Protección de tu información -->
                <UiCard class="group relative overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 !border-border-default !bg-surface-elevated !text-text-primary !shadow-sm">
                    <div class="p-6 space-y-4">
                        <div class="inline-flex items-center justify-center size-12 rounded-xl bg-brand-turquoise/10 text-brand-turquoise group-hover:bg-brand-turquoise/20 transition-colors duration-300" aria-hidden="true">
                            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-display text-xl font-bold text-text-primary group-hover:text-brand-turquoise transition-colors">Protección de tu información</h3>
                        <p class="font-body text-sm leading-relaxed text-text-secondary">
                            Solo solicitamos nombre y teléfono. Los datos viajan por HTTPS, se normalizan en el servidor y se almacenan con acceso restringido al personal autorizado.
                        </p>
                    </div>
                </UiCard>

                <!-- Card 2: Gestión segura de citas -->
                <UiCard class="group relative overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 !border-border-default !bg-surface-elevated !text-text-primary !shadow-sm">
                    <div class="p-6 space-y-4">
                        <div class="inline-flex items-center justify-center size-12 rounded-xl bg-brand-pink/10 text-brand-pink group-hover:bg-brand-pink/20 transition-colors duration-300" aria-hidden="true">
                            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="font-display text-xl font-bold text-text-primary group-hover:text-brand-pink transition-colors">Gestión segura de citas</h3>
                        <p class="font-body text-sm leading-relaxed text-text-secondary">
                            El backend valida disponibilidad en tiempo real, previene dobles reservas con bloqueos concurrentes y confirma la cita solo tras validar disponibilidad real.
                        </p>
                    </div>
                </UiCard>

                <!-- Card 3: Acceso administrativo protegido -->
                <UiCard class="group relative overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 !border-border-default !bg-surface-elevated !text-text-primary !shadow-sm">
                    <div class="p-6 space-y-4">
                        <div class="inline-flex items-center justify-center size-12 rounded-xl bg-brand-turquoise/10 text-brand-turquoise group-hover:bg-brand-turquoise/20 transition-colors duration-300" aria-hidden="true">
                            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 15v-2.5m0 0a2.5 2.5 0 015 0v-2.5m0 0a2.5 2.5 0 00-5 0v2.5m0 0h10" />
                            </svg>
                        </div>
                        <h3 class="font-display text-xl font-bold text-text-primary group-hover:text-brand-turquoise transition-colors">Acceso administrativo protegido</h3>
                        <p class="font-body text-sm leading-relaxed text-text-secondary">
                            El panel administrativo requiere autenticación con credenciales válidas y rol de administrador. Las sesiones usan cookies HttpOnly, CSRF tokens y regeneración de sesión tras login.
                        </p>
                    </div>
                </UiCard>

                <!-- Card 4: Transparencia y contacto -->
                <UiCard class="group relative overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 !border-border-default !bg-surface-elevated !text-text-primary !shadow-sm">
                    <div class="p-6 space-y-4">
                        <div class="inline-flex items-center justify-center size-12 rounded-xl bg-brand-gold/10 text-brand-gold group-hover:bg-brand-gold/20 transition-colors duration-300" aria-hidden="true">
                            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-display text-xl font-bold text-text-primary group-hover:text-brand-gold transition-colors">Transparencia y contacto</h3>
                        <p class="font-body text-sm leading-relaxed text-text-secondary">
                            Nuestro aviso de privacidad y políticas de citas están accesibles antes de confirmar. Puedes consultar políticas de cancelación, reprogramación y contactarnos por WhatsApp.
                        </p>
                    </div>
                </UiCard>
            </div>

            <!-- CTA Section -->
            <div class="mt-10 md:mt-14 text-center">
                <p class="font-body text-sm text-text-secondary mb-4">
                    ¿Tienes dudas sobre cómo protegemos tu información?
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <RouterLink
                        to="/terminos-condiciones"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md border border-brand-turquoise/30 bg-brand-turquoise/5 px-5 py-2 font-ui text-sm font-semibold text-brand-turquoise hover:bg-brand-turquoise/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-turquoise focus-visible:ring-offset-2"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Leer aviso de privacidad
                    </RouterLink>
                    <a
                        :href="publicSite.business.whatsappUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md border border-brand-pink/30 bg-brand-pink/5 px-5 py-2 font-ui text-sm font-semibold text-brand-pink hover:bg-brand-pink/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-pink focus-visible:ring-offset-2 focus:ring-offset-brand-black"
                        :aria-label="`Abrir WhatsApp de ${publicSite.business.name}`"
                    >
                        <svg class="size-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M17.472 14.382c-.013-.165-.183-.278-.35-.238h-.688l-.007-.303c.048-.3.042-.63-.015-.921a1.992 1.992 0 00-.582-.79l-.041-.035A2.004 2.004 0 0015.92 11.5H13V6.5C13 5.672 12.328 5 11.5 5S10 5.672 10 6.5v2.781c-.336-.047-.679-.07-1.022-.07a3.002 3.002 0 00-3 3v7.381A2.997 2.997 0 005.5 21h13c1.654 0 3-1.346 3-3v-7.391a2.996 2.996 0 00-.582-1.591l-.04-.035c-.104-.193-.258-.378-.478-.515a1.99 1.99 0 00-.756-.361c-.127-.017-.256-.022-.385-.022H5c-2.757 0-5 2.243-5 5v7.391c0 2.757 2.243 5 5 5h14c1.097 0 2.077-.652 2.462-1.608l.04-.036z" />
                        </svg>
                        Contactar por WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>