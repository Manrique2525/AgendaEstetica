export const fiscalDemoCatalog = {
    claveProdServ: '91101701',
    claveUnidad: 'E48',
    issuerRegimenFiscal: '601',
    receiverRegimenFiscal: '612',
    usoCfdi: 'S01',
    formaPago: '03',
    metodoPago: 'PUE',
    moneda: 'MXN',
    tipoDeComprobante: 'I',
    exportacion: '01',
    objetoImp: '02',
    impuesto: '002',
    tipoFactor: 'Tasa',
    tasaIvaBasisPoints: 1600,
    demoPostalCode: '01000',
} as const;

export interface IssuerFiscalProfile {
    personType: 'moral';
    rfc: string;
    name: string;
    regimenFiscal: string;
    placeOfIssuePostalCode: string;
}

export interface ReceiverFiscalProfile {
    personType: 'fisica';
    rfc: string;
    name: string;
    fiscalPostalCode: string;
    regimenFiscalReceptor: string;
    usoCfdi: string;
}

export interface InvoiceMetadata {
    version: '4.0';
    serie: string;
    folio: string;
    issuedAt: string;
    paymentForm: string;
    paymentMethod: string;
    currency: 'MXN';
    type: 'I';
    exportation: '01';
    placeOfIssuePostalCode: string;
}

export interface PaymentInformation {
    form: string;
    method: string;
    currency: 'MXN';
    conditions?: string;
}

export interface InvoiceConceptTax {
    baseCents: number;
    impuesto: string;
    tipoFactor: 'Tasa';
    rateBasisPoints: number;
}

export interface InvoiceConcept {
    claveProdServ: string;
    quantity: number;
    claveUnidad: string;
    description: string;
    unitPriceCents: number;
    objetoImp: string;
    tax?: InvoiceConceptTax;
}

export interface DemoFiscalDisclaimer {
    demo: true;
    label: 'DOCUMENTO DEMOSTRATIVO';
    validity: 'SIN VALIDEZ FISCAL';
    certification: 'NO ES UN CFDI TIMBRADO';
    pac: 'NO HA SIDO CERTIFICADO POR EL SAT NI POR UN PAC';
}

export interface InvoiceDraft {
    demo: true;
    issuer: IssuerFiscalProfile;
    receiver: ReceiverFiscalProfile;
    metadata: InvoiceMetadata;
    payment: PaymentInformation;
    concepts: InvoiceConcept[];
    discountCents: number;
    disclaimers: DemoFiscalDisclaimer;
}

export interface InvoiceTotals {
    subtotalCents: number;
    discountCents: number;
    transferredTaxesCents: number;
    withheldTaxesCents: number;
    totalCents: number;
}

export function calculateTaxCents(baseCents: number, rateBasisPoints: number): number {
    return Math.round((baseCents * rateBasisPoints) / 10000);
}

export function calculateInvoiceTotals(draft: InvoiceDraft): InvoiceTotals {
    const subtotalCents = draft.concepts.reduce((total, concept) => total + concept.quantity * concept.unitPriceCents, 0);
    const transferredTaxesCents = draft.concepts.reduce((total, concept) => {
        if (!concept.tax) return total;

        const baseCents = concept.quantity * concept.unitPriceCents;

        return total + calculateTaxCents(baseCents, concept.tax.rateBasisPoints);
    }, 0);
    const withheldTaxesCents = 0;
    const totalCents = subtotalCents - draft.discountCents + transferredTaxesCents - withheldTaxesCents;

    return {
        subtotalCents,
        discountCents: draft.discountCents,
        transferredTaxesCents,
        withheldTaxesCents,
        totalCents,
    };
}

export function getDemoInvoiceDraft(): InvoiceDraft {
    return {
        demo: true,
        issuer: {
            personType: 'moral',
            rfc: 'DEM010101AA0',
            name: 'YARIS DEMOSTRACION ACADEMICA',
            regimenFiscal: fiscalDemoCatalog.issuerRegimenFiscal,
            placeOfIssuePostalCode: fiscalDemoCatalog.demoPostalCode,
        },
        receiver: {
            personType: 'fisica',
            rfc: 'DEMO010101AAA',
            name: 'CLIENTE DEMOSTRACION ACADEMICA',
            fiscalPostalCode: fiscalDemoCatalog.demoPostalCode,
            regimenFiscalReceptor: fiscalDemoCatalog.receiverRegimenFiscal,
            usoCfdi: fiscalDemoCatalog.usoCfdi,
        },
        metadata: {
            version: '4.0',
            serie: 'DEMO',
            folio: 'DEMO-FISCAL-0001',
            issuedAt: '2026-01-15T10:00:00',
            paymentForm: fiscalDemoCatalog.formaPago,
            paymentMethod: fiscalDemoCatalog.metodoPago,
            currency: fiscalDemoCatalog.moneda,
            type: fiscalDemoCatalog.tipoDeComprobante,
            exportation: fiscalDemoCatalog.exportacion,
            placeOfIssuePostalCode: fiscalDemoCatalog.demoPostalCode,
        },
        payment: {
            form: fiscalDemoCatalog.formaPago,
            method: fiscalDemoCatalog.metodoPago,
            currency: fiscalDemoCatalog.moneda,
            conditions: 'ESCENARIO FISCAL DEMOSTRATIVO',
        },
        concepts: [{
            claveProdServ: fiscalDemoCatalog.claveProdServ,
            quantity: 1,
            claveUnidad: fiscalDemoCatalog.claveUnidad,
            description: 'Servicio de corte y tinte DEMOSTRATIVO',
            unitPriceCents: 75000,
            objetoImp: fiscalDemoCatalog.objetoImp,
            tax: {
                baseCents: 75000,
                impuesto: fiscalDemoCatalog.impuesto,
                tipoFactor: fiscalDemoCatalog.tipoFactor,
                rateBasisPoints: fiscalDemoCatalog.tasaIvaBasisPoints,
            },
        }],
        discountCents: 0,
        disclaimers: {
            demo: true,
            label: 'DOCUMENTO DEMOSTRATIVO',
            validity: 'SIN VALIDEZ FISCAL',
            certification: 'NO ES UN CFDI TIMBRADO',
            pac: 'NO HA SIDO CERTIFICADO POR EL SAT NI POR UN PAC',
        },
    };
}
