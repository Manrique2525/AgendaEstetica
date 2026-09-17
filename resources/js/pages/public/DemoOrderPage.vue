<script setup lang="ts">
import { reactive, ref } from 'vue';
import PublicLayout from '../../layouts/PublicLayout.vue';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';
import UiFormField from '../../components/ui/UiFormField.vue';
import UiInput from '../../components/ui/UiInput.vue';
import { buildDemoIntegrityPayload, normalizeDemoData, serializeDemoIntegrityPayload, sha256Hex, type DemoOrderData } from '../../utils/demoIntegrity';

type DemoStep = 'edit' | 'review' | 'confirmed';

const step = ref<DemoStep>('edit');
const demoData = reactive<DemoOrderData>({
    customerName: 'Cliente de demostración',
    itemName: 'DEMO ACADÉMICA — Producto de prueba',
    note: 'Sin datos personales reales',
});
const confirmedData = ref<DemoOrderData | null>(null);
const confirmedInvoiceRequest = ref(false);
const requestInvoice = ref(false);
const termsAccepted = ref(false);
const folio = ref('');
const canonicalPayload = ref('');
const digest = ref<string | null>(null);
const digestError = ref(false);

function reviewDemo(): void {
    step.value = 'review';
}

function correctDemo(): void {
    step.value = 'edit';
}

function makeDemoFolio(): string {
    const values = new Uint32Array(1);
    try {
        if (typeof globalThis.crypto?.getRandomValues === 'function') {
            globalThis.crypto.getRandomValues(values);
        }
    } catch {
        // The fixed fallback remains a clearly non-production demo folio.
    }

    return `DEMO-${(values[0] ?? 0).toString(36).toUpperCase().slice(0, 6).padStart(6, '0')}`;
}

async function confirmDemo(): Promise<void> {
    if (step.value !== 'review' || !termsAccepted.value) return;

    const snapshot = normalizeDemoData(demoData);
    const generatedFolio = makeDemoFolio();
    let serializedPayload = '';
    try {
        const payload = buildDemoIntegrityPayload(snapshot, generatedFolio, requestInvoice.value, termsAccepted.value);
        serializedPayload = serializeDemoIntegrityPayload(payload);
    } catch {
        digest.value = null;
    }

    confirmedData.value = snapshot;
    confirmedInvoiceRequest.value = requestInvoice.value;
    folio.value = generatedFolio;
    canonicalPayload.value = serializedPayload;
    if (serializedPayload) {
        try {
            digest.value = await sha256Hex(serializedPayload);
        } catch {
            digest.value = null;
        }
    }
    digestError.value = digest.value === null;
    step.value = 'confirmed';
}
</script>

<template>
    <PublicLayout>
        <div class="space-y-10">
            <section aria-labelledby="demo-order-title" class="rounded-xl border border-brand-gold/50 bg-gradient-to-br from-brand-black via-brand-black to-brand-fuchsia/20 px-6 py-10 sm:px-10 sm:py-14">
                <p class="font-ui text-xs font-bold uppercase tracking-[0.2em] text-brand-gold">Presentación académica</p>
                <h1 id="demo-order-title" class="mt-4 max-w-3xl font-display text-5xl leading-[0.95] text-text-inverse sm:text-7xl">Demostración de pedido</h1>
                <p class="mt-5 max-w-2xl font-body text-lg leading-relaxed text-white/80">Esta es una <strong class="text-white">DEMOSTRACIÓN ACADÉMICA</strong>, no una compra real. No crea un pedido, no procesa pagos y no envía la información a Yaris. Aquí se muestran revisión, corrección, integridad y firma digital como conceptos.</p>
            </section>

            <section v-if="step === 'edit'" aria-labelledby="edit-title">
                <UiCard class="!border-white/15 !bg-white/5 !text-text-inverse !shadow-none sm:!p-10">
                    <p class="font-ui text-xs font-bold uppercase tracking-[0.18em] text-brand-pink">Paso 1 de 2</p>
                    <h2 id="edit-title" class="mt-2 font-display text-4xl text-text-inverse">Prepara datos de demostración</h2>
                    <p class="mt-4 max-w-2xl font-body leading-relaxed text-white/75">Estos valores son sintéticos y viven solo en esta pantalla. No introduzcas datos personales reales.</p>
                    <form class="mt-8 max-w-xl space-y-5" @submit.prevent="reviewDemo">
                        <UiFormField id="demo-order-customer" label="Cliente de demostración" required>
                            <template #default="{ inputId, describedBy, invalid }">
                                <UiInput :id="inputId" v-model="demoData.customerName" :aria-describedby="describedBy" :invalid="invalid" />
                            </template>
                        </UiFormField>
                        <UiFormField id="demo-order-item" label="Producto de prueba" required>
                            <template #default="{ inputId, describedBy, invalid }">
                                <UiInput :id="inputId" v-model="demoData.itemName" :aria-describedby="describedBy" :invalid="invalid" />
                            </template>
                        </UiFormField>
                        <UiFormField id="demo-order-note" label="Nota de demostración" required help="Usa solo contenido académico; no se enviará ni se guardará.">
                            <template #default="{ inputId, describedBy, invalid }">
                                <UiInput :id="inputId" v-model="demoData.note" :aria-describedby="describedBy" :invalid="invalid" />
                            </template>
                        </UiFormField>
                        <label for="demo-request-invoice" class="flex cursor-pointer items-start gap-3 rounded-lg border border-brand-gold/40 bg-brand-gold/10 p-4 font-body text-sm leading-relaxed text-white">
                            <input id="demo-request-invoice" v-model="requestInvoice" type="checkbox" class="mt-1 size-4 shrink-0 accent-brand-gold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring">
                            <span><span class="font-ui font-bold">Solicitar factura</span> <span class="font-ui text-xs font-bold uppercase tracking-[0.08em] text-brand-gold">DEMOSTRACIÓN</span><br>Solo mostrará un documento educativo; no se generará una factura real ni se solicitarán datos fiscales.</span>
                        </label>
                        <label for="demo-terms-acceptance" class="flex cursor-pointer items-start gap-3 rounded-lg border border-brand-pink/40 bg-brand-pink/10 p-4 font-body text-sm leading-relaxed text-white">
                            <input id="demo-terms-acceptance" v-model="termsAccepted" type="checkbox" class="mt-1 size-4 shrink-0 accent-brand-pink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring">
                            <span><span class="font-ui font-bold">He leído y acepto los Términos y Condiciones y el Aviso de Privacidad.</span><br><a href="/fase-1#privacidad-seguridad" class="font-ui font-bold text-brand-pink underline underline-offset-4" @click.stop>Revisar información provisional</a></span>
                        </label>
                        <UiButton type="submit">Revisar demostración</UiButton>
                    </form>
                </UiCard>
            </section>

            <section v-else-if="step === 'review'" aria-labelledby="review-title">
                <UiCard class="!border-brand-gold/50 !bg-white/5 !text-text-inverse !shadow-none sm:!p-10">
                    <p class="font-ui text-xs font-bold uppercase tracking-[0.18em] text-brand-gold">Paso 2 de 2</p>
                    <h2 id="review-title" class="mt-2 font-display text-4xl text-text-inverse">Revisa la demostración</h2>
                    <p class="mt-4 max-w-2xl font-body leading-relaxed text-white/75">Confirma que estos datos de prueba son los que se representarán en la demostración de integridad.</p>
                    <dl class="mt-8 max-w-2xl divide-y divide-white/15 rounded-lg border border-white/15 bg-brand-black/40 font-body">
                        <div class="grid gap-1 px-4 py-4 sm:grid-cols-[12rem_1fr] sm:gap-4"><dt class="text-white/60">Cliente</dt><dd class="break-words font-semibold text-white">{{ normalizeDemoData(demoData).customerName }}</dd></div>
                        <div class="grid gap-1 px-4 py-4 sm:grid-cols-[12rem_1fr] sm:gap-4"><dt class="text-white/60">Producto</dt><dd class="break-words font-semibold text-white">{{ normalizeDemoData(demoData).itemName }}</dd></div>
                        <div class="grid gap-1 px-4 py-4 sm:grid-cols-[12rem_1fr] sm:gap-4"><dt class="text-white/60">Valor</dt><dd class="font-semibold text-brand-gold">DATO DE PRUEBA</dd></div>
                        <div class="grid gap-1 px-4 py-4 sm:grid-cols-[12rem_1fr] sm:gap-4"><dt class="text-white/60">Nota</dt><dd class="break-words font-semibold text-white">{{ normalizeDemoData(demoData).note }}</dd></div>
                        <div class="grid gap-1 px-4 py-4 sm:grid-cols-[12rem_1fr] sm:gap-4"><dt class="text-white/60">Factura de demostración</dt><dd class="font-semibold text-brand-gold">{{ requestInvoice ? 'Solicitada' : 'No solicitada' }}</dd></div>
                        <div class="grid gap-1 px-4 py-4 sm:grid-cols-[12rem_1fr] sm:gap-4"><dt class="text-white/60">Términos y privacidad</dt><dd class="font-semibold text-brand-gold">{{ termsAccepted ? 'Aceptados para esta demostración' : 'Pendientes de aceptación' }}</dd></div>
                    </dl>
                    <p v-if="!termsAccepted" role="alert" class="mt-5 rounded-md border border-brand-pink/50 bg-brand-pink/10 p-4 font-body text-sm leading-relaxed text-white">Acepta los Términos y Condiciones y el Aviso de Privacidad para confirmar esta demostración.</p>
                    <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                        <UiButton variant="secondary" type="button" @click="correctDemo">Corregir</UiButton>
                        <UiButton data-testid="confirm-demo" type="button" :disabled="!termsAccepted" @click="confirmDemo">Confirmar demostración</UiButton>
                    </div>
                </UiCard>
            </section>

            <section v-else aria-labelledby="confirmed-title">
                <UiCard class="!border-brand-turquoise/50 !bg-white/5 !text-text-inverse !shadow-none sm:!p-10">
                    <p class="font-ui text-xs font-bold uppercase tracking-[0.18em] text-brand-turquoise">DEMOSTRACIÓN ACADÉMICA</p>
                    <h2 id="confirmed-title" class="mt-2 font-display text-4xl text-text-inverse">Demostración confirmada</h2>
                    <p class="mt-4 font-body leading-relaxed text-white/75">La demostración fue confirmada localmente. No se creó un pedido real, no hubo pago y no se enviaron estos datos.</p>
                    <div class="mt-8 grid gap-6 lg:grid-cols-2">
                        <div class="rounded-lg border border-white/15 bg-brand-black/40 p-5">
                            <h3 class="font-ui text-sm font-bold uppercase tracking-[0.12em] text-brand-gold">FOLIO DE DEMOSTRACIÓN</h3>
                            <p class="mt-3 font-ui text-2xl font-bold tracking-[0.12em] text-white">{{ folio }}</p>
                            <dl class="mt-6 space-y-3 font-body text-sm">
                                <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-white/60">Cliente</dt><dd class="break-words font-semibold text-white sm:text-right">{{ confirmedData?.customerName }}</dd></div>
                                <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-white/60">Producto</dt><dd class="break-words font-semibold text-white sm:text-right">{{ confirmedData?.itemName }}</dd></div>
                                <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-white/60">Valor</dt><dd class="font-semibold text-brand-gold sm:text-right">DATO DE PRUEBA</dd></div>
                                <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-white/60">Nota</dt><dd class="break-words font-semibold text-white sm:text-right">{{ confirmedData?.note }}</dd></div>
                                <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-white/60">Factura de demostración</dt><dd class="font-semibold text-brand-gold sm:text-right">{{ confirmedInvoiceRequest ? 'Solicitada' : 'No solicitada' }}</dd></div>
                            </dl>
                        </div>
                        <div class="rounded-lg border border-brand-turquoise/40 bg-brand-black/40 p-5">
                            <h3 class="font-ui text-sm font-bold uppercase tracking-[0.12em] text-brand-turquoise">Huella de integridad SHA-256</h3>
                            <p v-if="digest" class="mt-4 break-all font-mono text-sm leading-relaxed text-white selection:bg-brand-turquoise/30">{{ digest }}</p>
                            <p v-else-if="digestError" role="alert" class="mt-4 font-body text-sm leading-relaxed text-white">No fue posible generar la huella SHA-256 en este navegador.</p>
                            <p class="mt-5 font-body text-sm leading-relaxed text-white/70">La huella representa los datos confirmados. Si cambia un dato antes de confirmar nuevamente, el resultado será diferente.</p>
                            <p class="mt-4 font-body text-sm font-semibold leading-relaxed text-brand-gold">Esta huella SHA-256 demuestra integridad de los datos de la demostración. NO constituye una firma digital.</p>
                        </div>
                    </div>
                    <div v-if="confirmedInvoiceRequest" class="mt-6 rounded-lg border border-brand-gold/50 bg-brand-gold/10 p-5">
                        <h3 class="font-ui text-sm font-bold uppercase tracking-[0.12em] text-brand-gold">Factura digital DEMOSTRACIÓN</h3>
                        <p class="mt-3 font-body text-sm leading-relaxed text-white/80">Documento de demostración disponible. No es un CFDI, no está timbrado y no tiene validez fiscal.</p>
                        <p class="mt-3 font-ui text-sm font-bold text-brand-gold">DOCUMENTO DE PRUEBA — SIN VALIDEZ FISCAL</p>
                        <a href="/demo/factura-demostracion-sin-validez-fiscal.pdf" download class="mt-4 inline-flex min-h-11 items-center justify-center rounded-md bg-action-primary px-4 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black">Descargar documento de prueba</a>
                    </div>
                </UiCard>
            </section>

            <section aria-labelledby="signature-title" class="border-t border-white/15 pt-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <h2 id="signature-title" class="font-display text-4xl text-text-inverse">Firma digital</h2>
                    <span class="self-start rounded-full border border-brand-gold/60 px-3 py-1 font-ui text-xs font-bold tracking-[0.08em] text-brand-gold">PENDIENTE DE INTEGRACIÓN</span>
                </div>
                <p class="mt-3 max-w-2xl font-body leading-relaxed text-white/75">Una solución de firma digital requiere infraestructura criptográfica adicional y una propuesta final del equipo. Este flujo no crea claves, certificados ni firmas.</p>
            </section>

            <aside aria-label="Aviso provisional de privacidad" class="rounded-lg border border-brand-pink/40 bg-white/5 p-5 font-body text-sm leading-relaxed text-white/75">
                Este flujo usa únicamente datos de demostración en memoria. No introduzcas datos personales reales: los valores no se persisten ni se transmiten.
                <a href="/fase-1#privacidad-seguridad" class="font-ui font-bold text-brand-pink underline underline-offset-4 focus:outline-none focus:ring-2 focus:ring-focus-ring">Consulta el aviso provisional</a>
            </aside>
        </div>
    </PublicLayout>
</template>
