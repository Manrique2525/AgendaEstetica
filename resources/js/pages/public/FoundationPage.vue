<script setup lang="ts">
import PublicLayout from '../../layouts/PublicLayout.vue';
import UiCard from '../../components/ui/UiCard.vue';
import { publicSite } from '../../data/publicSite';
</script>

<template>
    <PublicLayout>
        <div class="space-y-16">
            <section aria-labelledby="hero-title" class="rounded-xl border border-white/15 bg-gradient-to-br from-brand-black via-brand-black to-brand-fuchsia/20 px-6 py-12 sm:px-10 sm:py-16">
                <p class="font-ui text-xs font-bold uppercase tracking-[0.2em] text-brand-gold">Salón y Barbería</p>
                <h1 id="hero-title" class="mt-4 max-w-2xl font-display text-5xl leading-[0.95] text-text-inverse sm:text-7xl">{{ publicSite.business.name }}</h1>
                <p class="mt-5 font-ui text-xl font-semibold text-brand-pink sm:text-2xl">{{ publicSite.business.tagline }}</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="/#tienda" class="inline-flex min-h-11 items-center justify-center rounded-md bg-action-primary px-5 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black">Ver productos</a>
                    <a href="/reservar" class="inline-flex min-h-11 items-center justify-center rounded-md border border-white/40 px-5 py-2 font-ui text-sm font-bold text-text-inverse hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black">Solicitar cita</a>
                </div>
            </section>

            <section id="tienda" class="scroll-mt-6 space-y-6" aria-labelledby="store-title">
                <div>
                    <p class="font-ui text-xs font-bold uppercase tracking-[0.18em] text-brand-gold">Próximamente</p>
                    <h2 id="store-title" class="mt-2 font-display text-4xl text-text-inverse sm:text-5xl">Tienda</h2>
                    <p class="mt-3 max-w-2xl font-body text-base leading-relaxed text-white/75">Nuestro catálogo se está preparando. Estas son las categorías que podrás conocer más adelante.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <UiCard v-for="category in publicSite.futureStoreCategories" :key="category" class="!border-white/15 !bg-white/5 !text-text-inverse !shadow-none">
                        <h3 class="font-display text-3xl">{{ category }}</h3>
                        <p class="mt-3 font-ui text-sm font-semibold uppercase tracking-[0.12em] text-brand-pink">Catálogo en preparación</p>
                    </UiCard>
                </div>
            </section>

            <section id="servicios" class="scroll-mt-6 rounded-xl border border-white/15 bg-white/5 px-6 py-8 sm:px-10" aria-labelledby="services-title">
                <p class="font-ui text-xs font-bold uppercase tracking-[0.18em] text-brand-turquoise">Agenda</p>
                <h2 id="services-title" class="mt-2 font-display text-4xl text-text-inverse sm:text-5xl">Servicios</h2>
                <p class="mt-4 max-w-2xl font-body text-base leading-relaxed text-white/75">Consulta los servicios disponibles y encuentra un horario en nuestro flujo de reserva.</p>
                <a href="/reservar" class="mt-6 inline-flex min-h-11 items-center justify-center rounded-md bg-action-primary px-5 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black">Solicitar cita</a>
            </section>

            <section id="contacto" class="scroll-mt-6 space-y-6" aria-labelledby="contact-title">
                <div>
                    <p class="font-ui text-xs font-bold uppercase tracking-[0.18em] text-brand-gold">Estamos para atenderte</p>
                    <h2 id="contact-title" class="mt-2 font-display text-4xl text-text-inverse sm:text-5xl">Contacto</h2>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <UiCard class="!border-white/15 !bg-white/5 !text-text-inverse !shadow-none">
                        <h3 class="font-ui text-sm font-bold uppercase tracking-[0.12em] text-brand-gold">Horario</h3>
                        <p class="mt-3 font-body text-sm text-white/80">{{ publicSite.business.hours }}</p>
                    </UiCard>
                    <UiCard class="!border-white/15 !bg-white/5 !text-text-inverse !shadow-none">
                        <h3 class="font-ui text-sm font-bold uppercase tracking-[0.12em] text-brand-gold">WhatsApp</h3>
                        <a :href="publicSite.business.whatsappUrl" target="_blank" rel="noopener noreferrer" class="mt-3 inline-block font-body text-sm text-text-inverse underline decoration-brand-pink underline-offset-4 focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black" :aria-label="`Abrir WhatsApp de ${publicSite.business.name}`">{{ publicSite.business.displayPhone }}</a>
                    </UiCard>
                    <UiCard class="!border-white/15 !bg-white/5 !text-text-inverse !shadow-none">
                        <h3 class="font-ui text-sm font-bold uppercase tracking-[0.12em] text-brand-gold">Ubicación</h3>
                        <p v-for="line in publicSite.business.location" :key="line" class="mt-3 font-body text-sm text-white/80 first:mt-3">{{ line }}</p>
                    </UiCard>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
