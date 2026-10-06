<script setup lang="ts">
import { computed } from 'vue';
import { calculateInvoiceTotals, type InvoiceDraft } from '../../data/fiscalDemo';

const props = defineProps<{
    draft: InvoiceDraft;
}>();

const totals = computed(() => calculateInvoiceTotals(props.draft));
const money = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

function formatCents(cents: number): string {
    return money.format(cents / 100);
}

function printInvoice(): void {
    window.print();
}
</script>

<template>
    <article class="invoice-document rounded-xl border border-brand-gold/50 bg-white p-5 text-text-primary shadow-lg sm:p-8" aria-labelledby="demo-invoice-title">
        <div class="flex flex-col gap-4 border-b-2 border-brand-gold pb-5 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="font-ui text-xs font-bold uppercase tracking-[0.18em] text-brand-pink">{{ draft.disclaimers.label }}</p>
                <h3 id="demo-invoice-title" class="mt-2 font-display text-3xl text-text-primary sm:text-4xl">Factura digital demostrativa</h3>
                <p class="mt-2 font-ui text-xs font-bold uppercase tracking-[0.12em] text-brand-pink">{{ draft.disclaimers.validity }}</p>
            </div>
            <button type="button" class="invoice-document__print-action inline-flex min-h-11 items-center justify-center rounded-md bg-action-primary px-4 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus:outline-none focus:ring-2 focus:ring-focus-ring" @click="printInvoice">Imprimir / guardar PDF</button>
        </div>

        <div class="mt-5 grid gap-5 text-sm sm:grid-cols-2">
            <section aria-labelledby="demo-invoice-issuer" class="rounded-lg border border-black/10 bg-surface-muted p-4">
                <h4 id="demo-invoice-issuer" class="font-ui text-xs font-bold uppercase tracking-[0.12em] text-text-secondary">Emisor</h4>
                <dl class="mt-3 space-y-2">
                    <div><dt class="text-text-secondary">Nombre</dt><dd class="font-semibold">{{ draft.issuer.name }}</dd></div>
                    <div><dt class="text-text-secondary">RFC sintético</dt><dd class="font-semibold">{{ draft.issuer.rfc }}</dd></div>
                    <div><dt class="text-text-secondary">Régimen fiscal</dt><dd class="font-semibold">{{ draft.issuer.regimenFiscal }}</dd></div>
                    <div><dt class="text-text-secondary">Lugar de expedición</dt><dd class="font-semibold">{{ draft.issuer.placeOfIssuePostalCode }}</dd></div>
                </dl>
            </section>
            <section aria-labelledby="demo-invoice-receiver" class="rounded-lg border border-black/10 bg-surface-muted p-4">
                <h4 id="demo-invoice-receiver" class="font-ui text-xs font-bold uppercase tracking-[0.12em] text-text-secondary">Receptor</h4>
                <dl class="mt-3 space-y-2">
                    <div><dt class="text-text-secondary">Nombre</dt><dd class="font-semibold">{{ draft.receiver.name }}</dd></div>
                    <div><dt class="text-text-secondary">RFC sintético</dt><dd class="font-semibold">{{ draft.receiver.rfc }}</dd></div>
                    <div><dt class="text-text-secondary">Domicilio fiscal</dt><dd class="font-semibold">{{ draft.receiver.fiscalPostalCode }}</dd></div>
                    <div><dt class="text-text-secondary">Régimen receptor</dt><dd class="font-semibold">{{ draft.receiver.regimenFiscalReceptor }}</dd></div>
                    <div><dt class="text-text-secondary">Uso CFDI</dt><dd class="font-semibold">{{ draft.receiver.usoCfdi }}</dd></div>
                </dl>
            </section>
        </div>

        <dl class="mt-5 grid gap-3 border-y border-black/10 py-4 text-sm sm:grid-cols-4">
            <div><dt class="text-text-secondary">Versión</dt><dd class="font-semibold">{{ draft.metadata.version }}</dd></div>
            <div><dt class="text-text-secondary">Serie / Folio</dt><dd class="font-semibold">{{ draft.metadata.serie }} / {{ draft.metadata.folio }}</dd></div>
            <div><dt class="text-text-secondary">Fecha</dt><dd class="font-semibold">{{ draft.metadata.issuedAt }}</dd></div>
            <div><dt class="text-text-secondary">Tipo</dt><dd class="font-semibold">{{ draft.metadata.type }} / {{ draft.metadata.exportation }}</dd></div>
        </dl>

        <section aria-labelledby="demo-invoice-payment" class="mt-5">
            <h4 id="demo-invoice-payment" class="font-ui text-xs font-bold uppercase tracking-[0.12em] text-text-secondary">Pago</h4>
            <p class="mt-2 text-sm">Forma de pago: <strong>{{ draft.payment.form }}</strong> · Método: <strong>{{ draft.payment.method }}</strong> · Moneda: <strong>{{ draft.payment.currency }}</strong></p>
            <p v-if="draft.payment.conditions" class="mt-1 text-sm text-text-secondary">{{ draft.payment.conditions }}</p>
        </section>

        <section aria-labelledby="demo-invoice-concepts" class="mt-5">
            <h4 id="demo-invoice-concepts" class="font-ui text-xs font-bold uppercase tracking-[0.12em] text-text-secondary">Conceptos</h4>
            <div class="mt-2 overflow-x-auto">
                <table class="min-w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-black/15 text-text-secondary">
                            <th class="px-2 py-2 font-semibold">Clave / unidad</th>
                            <th class="px-2 py-2 font-semibold">Descripción</th>
                            <th class="px-2 py-2 text-right font-semibold">Cantidad</th>
                            <th class="px-2 py-2 text-right font-semibold">Unitario</th>
                            <th class="px-2 py-2 text-right font-semibold">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="concept in draft.concepts" :key="concept.claveProdServ" class="border-b border-black/10">
                            <td class="px-2 py-3">{{ concept.claveProdServ }} / {{ concept.claveUnidad }}</td>
                            <td class="px-2 py-3">{{ concept.description }}</td>
                            <td class="px-2 py-3 text-right">{{ concept.quantity }}</td>
                            <td class="px-2 py-3 text-right">{{ formatCents(concept.unitPriceCents) }}</td>
                            <td class="px-2 py-3 text-right font-semibold">{{ formatCents(concept.quantity * concept.unitPriceCents) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <section aria-labelledby="demo-invoice-taxes" class="rounded-lg border border-black/10 bg-surface-muted p-4 text-sm">
                <h4 id="demo-invoice-taxes" class="font-ui text-xs font-bold uppercase tracking-[0.12em] text-text-secondary">Impuestos</h4>
                <dl class="mt-3 space-y-2">
                    <div><dt class="text-text-secondary">Base</dt><dd class="font-semibold">{{ formatCents(draft.concepts[0]?.tax?.baseCents ?? 0) }}</dd></div>
                    <div><dt class="text-text-secondary">Impuesto / factor</dt><dd class="font-semibold">{{ draft.concepts[0]?.tax?.impuesto }} / {{ draft.concepts[0]?.tax?.tipoFactor }}</dd></div>
                    <div><dt class="text-text-secondary">Tasa demostrativa</dt><dd class="font-semibold">{{ ((draft.concepts[0]?.tax?.rateBasisPoints ?? 0) / 100).toFixed(4) }}%</dd></div>
                    <div><dt class="text-text-secondary">Trasladado</dt><dd class="font-semibold">{{ formatCents(totals.transferredTaxesCents) }}</dd></div>
                </dl>
            </section>
            <section aria-labelledby="demo-invoice-totals" class="rounded-lg border-2 border-brand-gold/50 bg-brand-gold/10 p-4 text-sm">
                <h4 id="demo-invoice-totals" class="font-ui text-xs font-bold uppercase tracking-[0.12em] text-text-secondary">Totales</h4>
                <dl class="mt-3 space-y-2">
                    <div class="flex justify-between gap-4"><dt>Subtotal</dt><dd class="font-semibold">{{ formatCents(totals.subtotalCents) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt>Descuento</dt><dd class="font-semibold">{{ formatCents(totals.discountCents) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt>Impuestos trasladados</dt><dd class="font-semibold">{{ formatCents(totals.transferredTaxesCents) }}</dd></div>
                    <div class="flex justify-between gap-4 border-t border-black/15 pt-2 text-lg"><dt class="font-bold">Total</dt><dd class="font-bold">{{ formatCents(totals.totalCents) }} MXN</dd></div>
                </dl>
            </section>
        </div>

        <aside class="mt-5 border-t-2 border-brand-pink/50 pt-4 text-sm font-semibold leading-relaxed text-text-primary" aria-label="Estado fiscal de demostración">
            {{ draft.disclaimers.certification }}<br>
            {{ draft.disclaimers.pac }}<br>
            {{ draft.disclaimers.validity }}
        </aside>
    </article>
</template>

<style>
@media print {
    body > * {
        visibility: hidden !important;
    }

    .invoice-document,
    .invoice-document * {
        visibility: visible !important;
    }

    .invoice-document {
        position: absolute !important;
        inset: 0 !important;
    }

    .invoice-document__print-action {
        display: none !important;
    }

    .invoice-document {
        border: 0 !important;
        box-shadow: none !important;
        color: #111827 !important;
        width: 100% !important;
    }
}
</style>
