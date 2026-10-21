<script setup lang="ts">
import { ref } from 'vue';
import { RouterLink } from 'vue-router';
import PublicLayout from '../../layouts/PublicLayout.vue';
import UiCard from '../../components/ui/UiCard.vue';
import MaryKayExitWarning from '../../components/public/MaryKayExitWarning.vue';
import { publicSite } from '../../data/publicSite';

const maryKayModalOpen = ref(false);
const MARY_KAY_URL = 'https://www.marykay.com.mx/yaris';
const MARY_KAY_LABEL = 'tienda externa de Mary Kay';

function openMaryKayModal(): void {
    maryKayModalOpen.value = true;
}

function handleMaryKayConfirm(): void {
    maryKayModalOpen.value = false;
    window.open(MARY_KAY_URL, '_blank', 'noopener,noreferrer');
}
</script>

<template>
    <PublicLayout>
        <div class="space-y-10">
            <section aria-labelledby="terms-page-title" class="rounded-xl border border-white/15 bg-gradient-to-br from-brand-black via-brand-black to-brand-fuchsia/20 px-6 py-10 sm:px-10 sm:py-14">
                <p class="font-ui text-xs font-bold uppercase tracking-[0.2em] text-brand-gold">Salón y Barbería Yaris</p>
                <h1 id="terms-page-title" class="mt-4 font-display text-5xl leading-[0.95] text-text-inverse sm:text-7xl">Términos y Condiciones</h1>
                <div class="mt-5 max-w-3xl p-4 rounded-lg bg-brand-gold/10 border border-brand-gold/30" role="alert" aria-live="polite">
                    <p class="font-ui text-xs font-bold uppercase tracking-[0.1em] text-brand-gold">BORRADOR PROVISIONAL — PENDIENTE DE VALIDACIÓN DEL RESPONSABLE DEL NEGOCIO</p>
                    <p class="mt-1 font-body text-sm leading-relaxed text-white/90">Este documento es un borrador académico provisional. No constituye una política legal final ni garantiza cumplimiento normativo. Debe ser revisado, completado y aprobado por el responsable del negocio antes de cualquier uso en producción.</p>
                </div>
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
                    <h2 class="font-display text-3xl text-text-primary">3. Citas — Política de reservas, confirmación, reprogramación y cancelación</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">El flujo público permite gestionar una cita mediante las opciones disponibles. La aplicación conserva la autoridad del backend para validar disponibilidad y confirmar la cita conforme a sus reglas operativas.</p>
                    <div class="mt-6 space-y-4">
                        <h3 class="font-ui font-semibold text-text-primary">3.1 Solicitud y confirmación</h3>
                        <p class="font-body text-sm text-text-secondary">La cita se considera confirmada únicamente cuando el backend responde con estado <code class="font-mono text-brand-turquoise bg-brand-turquoise/10 px-1.5 py-0.5 rounded">confirmed</code>. El cliente recibe los detalles (servicio, profesional, fecha, hora, precio) en la pantalla de confirmación.</p>

                        <h3 class="font-ui font-semibold text-text-primary">3.2 Reprogramación</h3>
                        <p class="font-body text-sm text-text-secondary">La funcionalidad de reprogramación desde la interfaz de cliente no está implementada en esta versión. Para reprogramar una cita, contacte al negocio directamente por WhatsApp o teléfono. El personal administrativo puede reprogramar citas desde el panel de administración.</p>

                        <h3 class="font-ui font-semibold text-text-primary">3.3 Cancelación</h3>
                        <p class="font-body text-sm text-text-secondary">La cancelación desde la interfaz de cliente no está implementada en esta versión. Para cancelar una cita, contacte al negocio directamente. El personal administrativo puede cancelar citas desde el panel de administración. No se aplican cargos por cancelación en esta versión.</p>

                        <h3 class="font-ui font-semibold text-text-primary">3.3 Disponibilidad y conflictos</h3>
                        <p class="font-body text-sm text-text-secondary">El backend valida la disponibilidad en tiempo real antes de confirmar. Si el horario solicitado ya no está disponible, se informa al usuario y se le invita a elegir otro. La idempotencia de la solicitud evita reservas duplicadas por reintentos de red.</p>
                    </div>
                </UiCard>

                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">4. Productos y catálogo</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">El catálogo se encuentra en preparación donde se indique. La opción de Mary Kay dirige a una tienda externa; esta aplicación no procesa sus compras, pagos ni pedidos.</p>
                </UiCard>

                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">5. Mary Kay — sitio externo</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">La tienda externa de Mary Kay opera fuera de esta aplicación. La disponibilidad de productos, checkout, pagos y políticas de ese sitio no son controlados por este proyecto.</p>
                    <button type="button" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-md border border-brand-pink/30 px-4 py-2 font-ui text-sm font-bold text-brand-pink hover:bg-brand-pink/10 focus:outline-none focus:ring-2 focus:ring-brand-pink focus:ring-offset-2 focus:ring-offset-brand-black" @click="openMaryKayModal">Abrir tienda externa de Mary Kay</button>
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
                    <div class="mt-4 space-y-4">
                        <div class="p-4 rounded-lg bg-brand-gold/10 border border-brand-gold/30" role="alert">
                            <p class="font-ui text-xs font-bold uppercase tracking-[0.1em] text-brand-gold">BORRADOR PROVISIONAL — PENDIENTE DE VALIDACIÓN DEL RESPONSABLE DEL NEGOCIO</p>
                            <p class="mt-1 font-body text-sm text-white/90">Este aviso de privacidad es un borrador académico provisional. No constituye una política legal final. Debe ser revisado, completado y aprobado por el responsable del negocio antes de cualquier uso en producción.</p>
                        </div>

                        <h3 class="font-ui font-semibold text-text-primary">9.1 Responsable del tratamiento</h3>
                        <p class="font-body text-sm text-text-secondary">{{ publicSite.business.name }}</p>
                        <p class="font-body text-sm text-text-secondary">Teléfono / WhatsApp: {{ publicSite.business.displayPhone }}</p>
                        <p v-for="line in publicSite.business.location" :key="line" class="font-body text-sm text-text-secondary">{{ line }}</p>

                        <h3 class="font-ui font-semibold text-text-primary">9.2 Datos personales que se recaban</h3>
                        <ul class="mt-2 list-disc list-inside space-y-1 font-body text-sm text-text-secondary">
                            <li>Nombre completo (requerido para identificar la cita)</li>
                            <li>Teléfono (requerido para contacto y normalización en formato E.164)</li>
                        </ul>

                        <h3 class="font-ui font-semibold text-text-primary">9.3 Finalidades del tratamiento</h3>
                        <ul class="mt-2 list-disc list-inside space-y-1 font-body text-sm text-text-secondary">
                            <li>Gestión y confirmación de citas solicitadas por el cliente</li>
                            <li>Comunicación de recordatorios, cambios o cancelaciones relacionadas con la cita</li>
                            <li>Identificación del cliente en futuras interacciones (reutilización por teléfono normalizado)</li>
                        </ul>

                        <h3 class="font-ui font-semibold text-text-primary">9.4 Base legal</h3>
                        <p class="font-body text-sm text-text-secondary">Ejecución de un contrato o medidas precontractuales a petición del interesado (solicitud de cita).</p>

                        <h3 class="font-ui font-semibold text-text-primary">9.5 Conservación y eliminación</h3>
                        <p class="font-body text-sm text-text-secondary">Los datos se conservan mientras sean necesarios para la finalidad para la que se recabaron y para el cumplimiento de obligaciones legales. No existe actualmente una política automatizada de eliminación; el responsable debe definir y aplicar la política de retención.</p>

                        <h3 class="font-ui font-semibold text-text-primary">9.6 Destinatarios y transferencias</h3>
                        <p class="font-body text-sm text-text-secondary">No se comparten datos con terceros, salvo obligación legal. La tienda externa de Mary Kay recibe datos únicamente si el usuario navega voluntariamente a su sitio.</p>

                        <h3 class="font-ui font-semibold text-text-primary">9.7 Derechos ARCO / Derechos del titular</h3>
                        <p class="font-body text-sm text-text-secondary">El titular puede ejercer sus derechos de Acceso, Rectificación, Cancelación y Oposición (ARCO), así como el derecho a la portabilidad y a limitar el uso o divulgación de sus datos, contactando al responsable a través de los medios proporcionados.</p>

                        <h3 class="font-ui font-semibold text-text-primary">9.8 Uso de cookies y tecnologías similares</h3>
                        <p class="font-body text-sm text-text-secondary">El sitio utiliza cookies técnicas estrictamente necesarias para la sesión de usuario y la protección CSRF. No se utilizan cookies de análisis, publicidad o seguimiento sin consentimiento.</p>
                    </div>
                </UiCard>

                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">10. Enlaces a terceros</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">Los enlaces externos pueden conducir a servicios operados por terceros, incluyendo la tienda externa de Mary Kay. El uso de esos sitios se rige por sus propios contenidos y condiciones. Antes de continuar, se muestra una advertencia de salida.</p>
                </UiCard>

                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">11. Demostraciones académicas</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">Fase 1 incluye acceso de clientes como demostración, acceso administrativo existente, una demostración funcional de integridad SHA-256, firma digital pendiente y una demostración de factura sin validez fiscal.</p>
                </UiCard>

                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">12. Facturación</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">La factura disponible en la demostración es únicamente educativa. No es un CFDI, no está timbrada, no tiene validez ante el SAT y no representa un proceso fiscal real.</p>
                </UiCard>

                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">12. Integridad de la información</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">La demostración académica utiliza una huella SHA-256 para mostrar cómo detectar cambios en datos protegidos del demo. SHA-256 demuestra integridad, pero no constituye una firma digital.</p>
                </UiCard>

                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">13. Enlaces a terceros</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">Los enlaces externos pueden conducir a servicios operados por terceros, incluyendo la tienda externa de Mary Kay. El uso de esos sitios se rige por sus propios contenidos y condiciones.</p>
                </UiCard>

                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">14. Disponibilidad del servicio</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">Este proyecto académico puede cambiar, evolucionar o no estar disponible temporalmente. Esta presentación no promete niveles de servicio o disponibilidad permanente.</p>
                </UiCard>

                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">15. Cambios al documento</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">Este documento provisional puede actualizarse conforme evolucione el proyecto y debe recibir revisión legal antes de cualquier lanzamiento de producción.</p>
                </UiCard>

                <UiCard>
                    <h2 class="font-display text-3xl text-text-primary">16. Contacto</h2>
                    <p class="mt-4 font-body leading-relaxed text-text-secondary">{{ publicSite.business.name }}</p>
                    <p class="mt-2 font-body text-text-secondary">WhatsApp / teléfono: {{ publicSite.business.displayPhone }}</p>
                    <p v-for="line in publicSite.business.location" :key="line" class="mt-1 font-body text-text-secondary">{{ line }}</p>
                </UiCard>
            </section>

            <RouterLink to="/" class="inline-flex min-h-11 items-center justify-center rounded-md bg-action-primary px-5 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black">Volver al inicio</RouterLink>
        </div>
        <MaryKayExitWarning :open="maryKayModalOpen" :destination-url="MARY_KAY_URL" :destination-label="MARY_KAY_LABEL" @close="maryKayModalOpen = false" @confirm="handleMaryKayConfirm" />
    </PublicLayout>
</template>