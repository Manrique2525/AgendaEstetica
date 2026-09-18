import { describe, expect, it } from 'vitest';
import { calculateInvoiceTotals, calculateTaxCents, fiscalDemoCatalog, getDemoInvoiceDraft } from './fiscalDemo';

describe('fiscal demo contract', () => {
    it('provides a synthetic, catalog-backed academic fixture', () => {
        const draft = getDemoInvoiceDraft();

        expect(draft.demo).toBe(true);
        expect(draft.issuer).toMatchObject({ personType: 'moral', rfc: 'DEM010101AA0', regimenFiscal: '601' });
        expect(draft.receiver).toMatchObject({ personType: 'fisica', rfc: 'DEMO010101AAA', regimenFiscalReceptor: '612', usoCfdi: 'S01' });
        expect(draft.issuer.name).toContain('DEMOSTRACION');
        expect(draft.receiver.name).toContain('DEMOSTRACION');
        expect(draft.issuer.rfc).toHaveLength(12);
        expect(draft.receiver.rfc).toHaveLength(13);
        expect(draft.issuer.placeOfIssuePostalCode).toBe('01000');
        expect(draft.receiver.fiscalPostalCode).toBe('01000');
        expect(draft.issuer.placeOfIssuePostalCode).not.toBe('00000');
        expect(draft.concepts[0]).toMatchObject({
            claveProdServ: '91101701',
            claveUnidad: 'E48',
            objetoImp: '02',
        });
        expect(fiscalDemoCatalog).toMatchObject({
            issuerRegimenFiscal: '601',
            receiverRegimenFiscal: '612',
            usoCfdi: 'S01',
            formaPago: '03',
            metodoPago: 'PUE',
            moneda: 'MXN',
            tipoDeComprobante: 'I',
            exportacion: '01',
            impuesto: '002',
            tipoFactor: 'Tasa',
        });
    });

    it('derives deterministic totals from concept data', () => {
        const draft = getDemoInvoiceDraft();

        expect(calculateInvoiceTotals(draft)).toEqual({
            subtotalCents: 75000,
            discountCents: 0,
            transferredTaxesCents: 12000,
            withheldTaxesCents: 0,
            totalCents: 87000,
        });
        expect(calculateInvoiceTotals(draft)).toEqual(calculateInvoiceTotals(draft));
    });

    it('changes totals when concept quantity changes', () => {
        const draft = getDemoInvoiceDraft();
        draft.concepts[0].quantity = 2;

        expect(calculateInvoiceTotals(draft).totalCents).toBe(174000);
    });

    it('rounds tax deterministically with fixed-point arithmetic', () => {
        expect(calculateTaxCents(10001, 1600)).toBe(1600);
        expect(calculateTaxCents(10002, 1600)).toBe(1600);
        expect(calculateTaxCents(10003, 1600)).toBe(1600);
    });

    it('does not include fiscal certification artifacts', () => {
        const draft = getDemoInvoiceDraft();
        const keys = Object.keys(draft).join('|') + Object.keys(draft.metadata).join('|') + Object.keys(draft.disclaimers).join('|');

        expect(keys).not.toMatch(/TimbreFiscalDigital|SelloSAT|SelloCFD|NoCertificadoSAT|UUID|PAC|CSD/);
    });

    it('exposes all required demo disclaimers', () => {
        expect(getDemoInvoiceDraft().disclaimers).toEqual({
            demo: true,
            label: 'DOCUMENTO DEMOSTRATIVO',
            validity: 'SIN VALIDEZ FISCAL',
            certification: 'NO ES UN CFDI TIMBRADO',
            pac: 'NO HA SIDO CERTIFICADO POR EL SAT NI POR UN PAC',
        });
    });
});
