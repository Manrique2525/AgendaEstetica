<script setup lang="ts">
import PublicLayout from '../../layouts/PublicLayout.vue';
import UiCard from '../../components/ui/UiCard.vue';
import { publicSite } from '../../data/publicSite';
</script>

<template>
    <PublicLayout>
        <div class="space-y-10">
            <section aria-labelledby="terms-page-title" class="rounded-xl border border-white/15 bg-gradient-to-br from-brand-black via-brand-black to-brand-fuchsia/20 px-6 py-10 sm:px-10 sm:py-14">
                <p class="font-ui text-xs font-bold uppercase tracking-[0.2em] text-brand-gold">Salón y Barbería Yaris</p>
                <h1 id="terms-page-title" class="mt-4 font-display text-5xl leading-[0.95] text-text-inverse sm:text-7xl">Términos y Condiciones</h1>
                <p class="mt-5 max-w-3xl font-body text-lg leading-relaxed text-white/80">Documento provisional para fines académicos. Su contenido deberá ser revisado y adaptado legalmente antes de utilizarse en un entorno de producción.</p>
            </section>

            <section aria-labelledby="terms-object-title" class="space-y-8">
                <UiCard>
                    <h2 id="terms-object-title" class="font-display text-3xl text-text-primary">1. Objeto de la plataforma</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">Salón y Barbería Yaris presenta información sobre servicios de belleza y barbería, reserva de citas, datos del negocio, presentación de catálogo y demostraciones académicas de Fase 1.</p>
                </UiCard>
                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">2. Uso del sitio</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">El sitio debe utilizarse de forma lícita y respetuosa. Cuando una persona solicite una cita, debe proporcionar información correcta. No se permite interferir con el servicio ni intentar acceder sin autorización.</p>
                </UiCard>
                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">3. Citas</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">El flujo público permite gestionar una cita mediante las opciones disponibles. La aplicación conserva la autoridad del backend para validar disponibilidad y confirmar la cita conforme a sus reglas operativas.</p>
                </UiCard>
                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">4. Productos y catálogo</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">El catálogo se encuentra en preparación donde se indique. La opción de Mary Kay dirige a una tienda externa; esta aplicación no procesa sus compras, pagos ni pedidos.</p>
                </UiCard>
                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">5. Mary Kay — sitio externo</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">La tienda externa de Mary Kay opera fuera de esta aplicación. La disponibilidad de productos, checkout, pagos y políticas de ese sitio no son controlados por este proyecto.</p>
                    <a href="https://www.marykay.com.mx/yaris" target="_blank" rel="noopener noreferrer" class="mt-5 inline-block font-ui text-sm font-bold text-action-primary underline underline-offset-4 focus:outline-none focus:ring-2 focus:ring-focus-ring">Abrir tienda externa de Mary Kay</a>
                </UiCard>
                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">6. Demostraciones académicas</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">Fase 1 incluye acceso de clientes como demostración, acceso administrativo existente, una demostración funcional de integridad SHA-256, firma digital pendiente y una demostración de factura sin validez fiscal.</p>
                </UiCard>
                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">7. Facturación</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">La factura disponible en la demostración es únicamente educativa. No es un CFDI, no está timbrada, no tiene validez ante el SAT y no representa un proceso fiscal real.</p>
                </UiCard>
                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">8. Integridad de la información</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">La demostración académica utiliza una huella SHA-256 para mostrar cómo detectar cambios en datos protegidos del demo. SHA-256 demuestra integridad, pero no constituye una firma digital.</p>
                </UiCard>
                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">9. Aviso de Privacidad — versión provisional</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">La información ingresada para citas se utiliza para proporcionar la funcionalidad correspondiente. Este texto es académico y provisional; no constituye una política legal final ni inventa compromisos de producción no confirmados.</p>
                </UiCard>
                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">10. Enlaces a terceros</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">Los enlaces externos pueden conducir a servicios operados por terceros, incluyendo la tienda externa de Mary Kay. El uso de esos sitios se rige por sus propios contenidos y condiciones.</p>
                </UiCard>
                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">11. Disponibilidad del servicio</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">Este proyecto académico puede cambiar, evolucionar o no estar disponible temporalmente. Esta presentación no promete niveles de servicio o disponibilidad permanente.</p>
                </UiCard>
                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">12. Cambios al documento</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">Este documento provisional puede actualizarse conforme evolucione el proyecto y debe recibir revisión legal antes de cualquier lanzamiento de producción.</p>
                </UiCard>
                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">13. Contacto</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">{{ publicSite.business.name }}</p>
                    <p class="mt-2 font-body text-text-secondary">WhatsApp / teléfono: {{ publicSite.business.displayPhone }}</p>
                    <p v-for="line in publicSite.business.location" :key="line" class="mt-1 font-body text-text-secondary">{{ line }}</p>
                </UiCard>
            </section>

            <RouterLink to="/" class="inline-flex min-h-11 items-center justify-center rounded-md bg-action-primary px-5 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black">Volver al inicio</RouterLink>
        </div>
    </PublicLayout>
</template>
