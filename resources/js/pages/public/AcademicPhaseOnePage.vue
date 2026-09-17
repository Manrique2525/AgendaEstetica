<script setup lang="ts">
import PublicLayout from '../../layouts/PublicLayout.vue';
import UiCard from '../../components/ui/UiCard.vue';

const areas = [
    { id: 'privacidad-seguridad', title: 'Privacidad y seguridad', status: 'PENDIENTE DE REVISIÓN', summary: 'Presentación provisional de los controles y datos mínimos del flujo público.' },
    { id: 'autenticacion', title: 'Autenticación', status: 'DEMOSTRACIÓN', summary: 'El acceso administrativo es funcional; el acceso de clientes se presenta como demostración.' },
    { id: 'integridad-firma', title: 'Integridad y firma digital', status: 'DEMOSTRACIÓN', summary: 'La huella SHA-256 de una demostración funciona en el navegador; la firma digital queda pendiente.' },
    { id: 'factura-digital', title: 'Factura digital', status: 'DEMOSTRACIÓN', summary: 'El flujo puede solicitar un documento educativo; la facturación real queda pendiente.' },
] as const;
</script>

<template>
    <PublicLayout>
        <div class="space-y-12">
            <section aria-labelledby="phase-one-title" class="rounded-xl border border-white/15 bg-gradient-to-br from-brand-black via-brand-black to-brand-fuchsia/20 px-6 py-10 sm:px-10 sm:py-14">
                <p class="font-ui text-xs font-bold uppercase tracking-[0.2em] text-brand-gold">Presentación académica</p>
                <h1 id="phase-one-title" class="mt-4 max-w-3xl font-display text-5xl leading-[0.95] text-text-inverse sm:text-7xl">Fase 1</h1>
                <p class="mt-5 max-w-2xl font-body text-lg leading-relaxed text-white/80">Una vista académica de cuatro áreas que acompañan la evolución digital de Salón y Barbería Yaris. En este checkpoint solo se presenta privacidad y seguridad.</p>
            </section>

            <section aria-labelledby="areas-title" class="space-y-6">
                <div>
                    <p class="font-ui text-xs font-bold uppercase tracking-[0.18em] text-brand-turquoise">Mapa de trabajo</p>
                    <h2 id="areas-title" class="mt-2 font-display text-4xl text-text-inverse sm:text-5xl">Cuatro áreas de Phase 1</h2>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <UiCard v-for="area in areas" :id="`${area.id}-overview`" :key="area.id" class="!border-white/15 !bg-white/5 !text-text-inverse !shadow-none">
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col items-start gap-3 sm:flex-row sm:justify-between">
                                <h3 class="font-display text-3xl">{{ area.title }}</h3>
                                <span class="rounded-full border border-brand-gold/60 px-2 py-1 text-left font-ui text-[0.62rem] font-bold leading-tight tracking-[0.08em] text-brand-gold sm:text-right">{{ area.status }}</span>
                            </div>
                            <p class="font-body text-sm leading-relaxed text-white/75">{{ area.summary }}</p>
                            <a :href="`/fase-1#${area.id}`" class="self-start font-ui text-sm font-bold text-brand-pink underline underline-offset-4 focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black">Ver estado</a>
                        </div>
                    </UiCard>
                </div>
            </section>

            <section id="privacidad-seguridad" class="scroll-mt-6 rounded-xl border border-brand-pink/40 bg-white/5 px-6 py-8 sm:px-10" aria-labelledby="privacy-title">
                <p class="font-ui text-xs font-bold uppercase tracking-[0.18em] text-brand-pink">Área 1</p>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <h2 id="privacy-title" class="font-display text-4xl text-text-inverse sm:text-5xl">Privacidad y seguridad</h2>
                    <span class="self-start rounded-full border border-brand-gold/60 px-3 py-1 font-ui text-xs font-bold tracking-[0.08em] text-brand-gold">PENDIENTE DE REVISIÓN</span>
                </div>
                <p class="mt-5 max-w-3xl font-body text-base leading-relaxed text-white/80">Este contenido es provisional y debe revisarse antes de publicarse como una política definitiva. Su objetivo es explicar, de forma académica y honesta, qué protege actualmente el flujo público.</p>
                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <UiCard class="!border-white/15 !bg-brand-black/50 !text-text-inverse !shadow-none">
                        <h3 class="font-ui text-sm font-bold uppercase tracking-[0.12em] text-brand-gold">Controles existentes</h3>
                        <ul class="mt-4 list-disc space-y-2 pl-5 font-body text-sm leading-relaxed text-white/80">
                            <li>La reserva aplica validación del lado del servidor.</li>
                            <li>El backend conserva la autoridad para validar y confirmar una cita.</li>
                            <li>El flujo público cuenta con protección contra solicitudes excesivas e idempotencia.</li>
                            <li>El acceso administrativo existente está protegido mediante Sanctum.</li>
                        </ul>
                    </UiCard>
                    <UiCard class="!border-white/15 !bg-brand-black/50 !text-text-inverse !shadow-none">
                        <h3 class="font-ui text-sm font-bold uppercase tracking-[0.12em] text-brand-gold">Datos mínimos de reserva</h3>
                        <p class="mt-4 font-body text-sm leading-relaxed text-white/80">El formulario existente solicita nombre y teléfono para identificar la reserva, además del servicio, profesional, fecha y horario elegidos para gestionarla.</p>
                        <p class="mt-4 font-body text-sm leading-relaxed text-white/80">Esta fase no agrega RFC, CURP, domicilio, fecha de nacimiento ni datos fiscales.</p>
                    </UiCard>
                </div>
                <p class="mt-6 font-body text-sm leading-relaxed text-white/65">Esta presentación no afirma certificaciones, cumplimiento legal integral, cifrado específico, periodos de conservación ni políticas definitivas de eliminación.</p>
            </section>

            <section id="autenticacion" class="scroll-mt-6 border-t border-white/15 pt-8" aria-labelledby="auth-title">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <h2 id="auth-title" class="font-display text-4xl text-text-inverse">Autenticación</h2>
                    <span class="self-start rounded-full border border-brand-gold/60 px-3 py-1 font-ui text-xs font-bold tracking-[0.08em] text-brand-gold">DEMOSTRACIÓN</span>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <UiCard class="!border-white/15 !bg-white/5 !text-text-inverse !shadow-none">
                        <h3 class="font-ui text-sm font-bold uppercase tracking-[0.12em] text-brand-gold">Administración Yaris</h3>
                        <p class="mt-3 font-body text-sm leading-relaxed text-white/75"><strong class="text-white">FUNCIONAL.</strong> El acceso administrativo real está reservado para Yaris y utiliza el flujo existente.</p>
                        <a href="/admin/login" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-md bg-action-primary px-4 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black">Acceso administrativo</a>
                    </UiCard>
                    <UiCard class="!border-white/15 !bg-white/5 !text-text-inverse !shadow-none">
                        <h3 class="font-ui text-sm font-bold uppercase tracking-[0.12em] text-brand-pink">Acceso de clientes</h3>
                        <p class="mt-3 font-body text-sm leading-relaxed text-white/75"><strong class="text-white">DEMOSTRACIÓN / VERIFICACIÓN DE WHATSAPP PENDIENTE.</strong> No hay autenticación de clientes en este checkpoint. Abrir WhatsApp es comunicación, no autentica, no verifica identidad y no crea una sesión.</p>
                        <a href="/cliente/acceso" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-md border border-white/40 px-4 py-2 font-ui text-sm font-bold text-text-inverse hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black">Ver demostración</a>
                    </UiCard>
                </div>
            </section>

            <section id="integridad-firma" class="scroll-mt-6 border-t border-white/15 pt-8" aria-labelledby="integrity-title">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <h2 id="integrity-title" class="font-display text-4xl text-text-inverse">Integridad y firma digital</h2>
                    <span class="self-start rounded-full border border-brand-gold/60 px-3 py-1 font-ui text-xs font-bold tracking-[0.08em] text-brand-gold">DEMOSTRACIÓN</span>
                </div>
                <p class="mt-3 max-w-2xl font-body leading-relaxed text-white/75"><strong class="text-white">Huella SHA-256: DEMOSTRACIÓN FUNCIONAL.</strong> La huella representa la integridad de datos académicos. <strong class="text-white">Firma digital: PENDIENTE DE INTEGRACIÓN.</strong> Una huella no es una firma digital, y una casilla de aceptación tampoco lo es.</p>
                <a href="/demo/pedido" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-md bg-action-primary px-4 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black">Ver demostración de integridad</a>
            </section>

            <section id="factura-digital" class="scroll-mt-6 border-t border-white/15 pt-8" aria-labelledby="invoice-title">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <h2 id="invoice-title" class="font-display text-4xl text-text-inverse">Factura digital</h2>
                    <span class="self-start rounded-full border border-brand-gold/60 px-3 py-1 font-ui text-xs font-bold tracking-[0.08em] text-brand-gold">DEMOSTRACIÓN</span>
                </div>
                <p class="mt-3 max-w-2xl font-body leading-relaxed text-white/75">El flujo académico permite solicitar un documento educativo sin pedir datos fiscales. <strong class="text-white">La facturación real permanece PENDIENTE DE INTEGRACIÓN.</strong> No se genera CFDI, no hay timbrado ni validez fiscal.</p>
                <a href="/demo/pedido" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-md bg-action-primary px-4 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black">Ver demostración de factura</a>
            </section>
        </div>
    </PublicLayout>
</template>
