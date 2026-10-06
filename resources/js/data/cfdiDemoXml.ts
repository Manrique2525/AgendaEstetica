import { calculateInvoiceTotals, calculateTaxCents, type InvoiceDraft, type InvoiceConceptTax } from './fiscalDemo';

export const demoCfdiNamespace = 'http://www.sat.gob.mx/cfd/4';
export const demoCfdiXsdLocation = 'http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd';
export const demoCfdiXmlFilename = 'cfdi-demo-sin-validez-fiscal.xml';

const xsiNamespace = 'http://www.w3.org/2001/XMLSchema-instance';
const xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>\n';
const demoComment = [
    'DOCUMENTO DEMOSTRATIVO.',
    'SIN VALIDEZ FISCAL.',
    'NO ES UN CFDI TIMBRADO.',
    'NO HA SIDO CERTIFICADO POR EL SAT NI POR UN PAC.',
].join('\n');

interface Taxable {
    impuesto: string;
    tipoFactor: InvoiceConceptTax['tipoFactor'];
    rateBasisPoints: number;
}

interface TaxGroup extends Taxable {
    baseCents: number;
}

function escapeXml(value: string): string {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&apos;');
}

function formatAmountCents(cents: number): string {
    const sign = cents < 0 ? '-' : '';
    const absolute = Math.abs(Math.round(cents));
    const whole = Math.floor(absolute / 100);
    const fraction = absolute % 100;

    return `${sign}${whole}.${String(fraction).padStart(2, '0')}`;
}

function formatRateBasisPoints(basisPoints: number): string {
    const sign = basisPoints < 0 ? '-' : '';
    const absolute = Math.abs(basisPoints);
    const ratio = absolute / 10000;
    const whole = Math.floor(ratio);
    const fraction = Math.round((ratio - whole) * 1000000);

    return `${sign}${whole}.${String(fraction).padStart(6, '0')}`;
}

function attribute(pairs: Array<[string, string | number]>): string {
    return pairs.map(([key, value]) => ` ${key}="${escapeXml(String(value))}"`).join('');
}

function buildTaxGroups(draft: InvoiceDraft): TaxGroup[] {
    const groups: TaxGroup[] = [];
    const byKey = new Map<string, TaxGroup>();

    for (const concept of draft.concepts) {
        if (!concept.tax) continue;

        const key = `${concept.tax.impuesto}|${concept.tax.tipoFactor}|${concept.tax.rateBasisPoints}`;
        const baseCents = concept.quantity * concept.unitPriceCents;
        const group = byKey.get(key) ?? {
            impuesto: concept.tax.impuesto,
            tipoFactor: concept.tax.tipoFactor,
            rateBasisPoints: concept.tax.rateBasisPoints,
            baseCents: 0,
        };

        group.baseCents += baseCents;
        byKey.set(key, group);
    }

    for (const group of byKey.values()) {
        groups.push(group);
    }

    return groups;
}

function trasladoXml(baseCents: number, tax: Taxable): string {
    const importeCents = calculateTaxCents(baseCents, tax.rateBasisPoints);

    return `<cfdi:Traslado${attribute([
        ['Base', formatAmountCents(baseCents)],
        ['Impuesto', tax.impuesto],
        ['TipoFactor', tax.tipoFactor],
        ['TasaOCuota', formatRateBasisPoints(tax.rateBasisPoints)],
        ['Importe', formatAmountCents(importeCents)],
    ])} />`;
}

export function serializeDemoCfdiXml(draft: InvoiceDraft): string {
    const totals = calculateInvoiceTotals(draft);
    const taxGroups = buildTaxGroups(draft);
    const aggregateTraslados = taxGroups.map((group) => trasladoXml(group.baseCents, {
        impuesto: group.impuesto,
        tipoFactor: group.tipoFactor,
        rateBasisPoints: group.rateBasisPoints,
    })).join('');

    const rootImpuestosXml = taxGroups.length > 0
        ? `<cfdi:Impuestos${attribute([
            ['TotalImpuestosTrasladados', formatAmountCents(totals.transferredTaxesCents)],
        ])}><cfdi:Traslados>${aggregateTraslados}</cfdi:Traslados></cfdi:Impuestos>`
        : '';

    const conceptosXml = draft.concepts.map((concept) => {
        const importeCents = concept.quantity * concept.unitPriceCents;
        const conceptTaxXml = concept.tax
            ? `<cfdi:Impuestos><cfdi:Traslados>${trasladoXml(importeCents, concept.tax)}</cfdi:Traslados></cfdi:Impuestos>`
            : '';

        return `<cfdi:Concepto${attribute([
            ['ClaveProdServ', concept.claveProdServ],
            ['Cantidad', concept.quantity],
            ['ClaveUnidad', concept.claveUnidad],
            ['Descripcion', concept.description],
            ['ValorUnitario', formatAmountCents(concept.unitPriceCents)],
            ['Importe', formatAmountCents(importeCents)],
            ['ObjetoImp', concept.objetoImp],
        ])}>${conceptTaxXml}</cfdi:Concepto>`;
    }).join('');

    return `${xmlDeclaration}<!--\n${demoComment}\n-->\n`
        + `<cfdi:Comprobante${attribute([
            ['xmlns:cfdi', demoCfdiNamespace],
            ['xmlns:xsi', xsiNamespace],
            ['xsi:schemaLocation', `${demoCfdiNamespace} ${demoCfdiXsdLocation}`],
            ['Version', draft.metadata.version],
            ['Serie', draft.metadata.serie],
            ['Folio', draft.metadata.folio],
            ['Fecha', draft.metadata.issuedAt],
            ['FormaPago', draft.metadata.paymentForm],
            ['SubTotal', formatAmountCents(totals.subtotalCents)],
            ['Moneda', draft.metadata.currency],
            ['Total', formatAmountCents(totals.totalCents)],
            ['TipoDeComprobante', draft.metadata.type],
            ['Exportacion', draft.metadata.exportation],
            ['MetodoPago', draft.metadata.paymentMethod],
            ['LugarExpedicion', draft.metadata.placeOfIssuePostalCode],
        ])}>`
        + `<cfdi:Emisor${attribute([
            ['Rfc', draft.issuer.rfc],
            ['Nombre', draft.issuer.name],
            ['RegimenFiscal', draft.issuer.regimenFiscal],
        ])} />`
        + `<cfdi:Receptor${attribute([
            ['Rfc', draft.receiver.rfc],
            ['Nombre', draft.receiver.name],
            ['DomicilioFiscalReceptor', draft.receiver.fiscalPostalCode],
            ['RegimenFiscalReceptor', draft.receiver.regimenFiscalReceptor],
            ['UsoCFDI', draft.receiver.usoCfdi],
        ])} />`
        + `<cfdi:Conceptos>${conceptosXml}</cfdi:Conceptos>`
        + rootImpuestosXml
        + '</cfdi:Comprobante>\n';
}