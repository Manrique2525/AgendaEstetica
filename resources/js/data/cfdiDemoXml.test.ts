import { describe, expect, it } from 'vitest';
import { getDemoInvoiceDraft, type InvoiceDraft } from './fiscalDemo';
import { demoCfdiNamespace, demoCfdiXmlFilename, demoCfdiXsdLocation, serializeDemoCfdiXml } from './cfdiDemoXml';

function withMutatedDraft(): InvoiceDraft {
    const draft = getDemoInvoiceDraft();

    return {
        ...draft,
        issuer: { ...draft.issuer, name: 'YARIS & CÍA "<Taller>" d\'Em' },
        receiver: { ...draft.receiver, name: 'CLIENTE & DEMO <ACADEMICA> "MX" \'01\'' },
        concepts: draft.concepts.map((concept) => ({
            ...concept,
            description: 'Corte & tinte <DEMOSTRATIVO> "sesión" \'única\'',
        })),
    };
}

function parseXml(xml: string): Document {
    return new DOMParser().parseFromString(xml, 'application/xml');
}

describe('serializeDemoCfdiXml', () => {
    it('produces a deterministic demo XML from the canonical fixture', () => {
        const first = serializeDemoCfdiXml(getDemoInvoiceDraft());
        const second = serializeDemoCfdiXml(getDemoInvoiceDraft());

        expect(first).toBe(second);
    });

    it('writes the XML declaration, demo comment and expected root structure', () => {
        const xml = serializeDemoCfdiXml(getDemoInvoiceDraft());

        expect(xml.startsWith('<?xml version="1.0" encoding="UTF-8"?>')).toBe(true);
        expect(xml).toContain('DOCUMENTO DEMOSTRATIVO.');
        expect(xml).toContain('SIN VALIDEZ FISCAL.');
        expect(xml).toContain('NO ES UN CFDI TIMBRADO.');
        expect(xml).toContain('<cfdi:Comprobante');
        expect(xml).toContain(`xmlns:cfdi="${demoCfdiNamespace}"`);
        expect(xml).toContain(`xsi:schemaLocation="${demoCfdiNamespace} ${demoCfdiXsdLocation}"`);
        expect(xml).toContain('Version="4.0"');
        expect(xml.endsWith('</cfdi:Comprobante>\n')).toBe(true);
    });

    it('parses as well-formed XML with the expected root and namespace', () => {
        const doc = parseXml(serializeDemoCfdiXml(getDemoInvoiceDraft()));

        expect(doc.getElementsByTagName('parsererror').length).toBe(0);
        expect(doc.documentElement.localName).toBe('Comprobante');
        expect(doc.documentElement.namespaceURI).toBe(demoCfdiNamespace);
        expect(doc.documentElement.getAttribute('Version')).toBe('4.0');
        expect(doc.documentElement.getAttribute('Serie')).toBe('DEMO');
        expect(doc.documentElement.getAttribute('Folio')).toBe('DEMO-FISCAL-0001');
        expect(doc.documentElement.getAttribute('Fecha')).toBe('2026-01-15T10:00:00');
    });

    it('never emits a DOCTYPE or external entity reference', () => {
        const xml = serializeDemoCfdiXml(getDemoInvoiceDraft());

        expect(xml).not.toContain('<!DOCTYPE');
        expect(xml).not.toContain('<!ENTITY');
    });

    it('maps canonical issuer, receiver, concept, tax and totals values (parity)', () => {
        const doc = parseXml(serializeDemoCfdiXml(getDemoInvoiceDraft()));
        const comprobante = doc.documentElement;
        const issuer = doc.getElementsByTagName('cfdi:Emisor')[0];
        const receiver = doc.getElementsByTagName('cfdi:Receptor')[0];
        const concept = doc.getElementsByTagName('cfdi:Concepto')[0];
        const conceptTax = doc.getElementsByTagName('cfdi:Traslado')[0];
        const rootTax = doc.getElementsByTagName('cfdi:Traslado')[1];
        const rootImpuestos = Array.from(doc.getElementsByTagName('cfdi:Impuestos'))
            .find((node) => node.hasAttribute('TotalImpuestosTrasladados'));

        expect(comprobante.getAttribute('SubTotal')).toBe('750.00');
        expect(comprobante.getAttribute('Total')).toBe('870.00');
        expect(comprobante.getAttribute('Moneda')).toBe('MXN');
        expect(comprobante.getAttribute('TipoDeComprobante')).toBe('I');
        expect(comprobante.getAttribute('Exportacion')).toBe('01');
        expect(comprobante.getAttribute('MetodoPago')).toBe('PUE');
        expect(comprobante.getAttribute('LugarExpedicion')).toBe('01000');
        expect(rootImpuestos?.getAttribute('TotalImpuestosTrasladados')).toBe('120.00');
        expect(issuer.getAttribute('Rfc')).toBe('DEM010101AA0');
        expect(issuer.getAttribute('Nombre')).toBe('YARIS DEMOSTRACION ACADEMICA');
        expect(issuer.getAttribute('RegimenFiscal')).toBe('601');
        expect(receiver.getAttribute('Rfc')).toBe('DEMO010101AAA');
        expect(receiver.getAttribute('Nombre')).toBe('CLIENTE DEMOSTRACION ACADEMICA');
        expect(receiver.getAttribute('DomicilioFiscalReceptor')).toBe('01000');
        expect(receiver.getAttribute('RegimenFiscalReceptor')).toBe('612');
        expect(receiver.getAttribute('UsoCFDI')).toBe('S01');
        expect(concept.getAttribute('ClaveProdServ')).toBe('91101701');
        expect(concept.getAttribute('Cantidad')).toBe('1');
        expect(concept.getAttribute('ClaveUnidad')).toBe('E48');
        expect(concept.getAttribute('ValorUnitario')).toBe('750.00');
        expect(concept.getAttribute('Importe')).toBe('750.00');
        expect(concept.getAttribute('ObjetoImp')).toBe('02');
        expect(conceptTax.getAttribute('Base')).toBe('750.00');
        expect(conceptTax.getAttribute('Impuesto')).toBe('002');
        expect(conceptTax.getAttribute('TipoFactor')).toBe('Tasa');
        expect(conceptTax.getAttribute('TasaOCuota')).toBe('0.160000');
        expect(conceptTax.getAttribute('Importe')).toBe('120.00');
        expect(rootTax.getAttribute('Importe')).toBe('120.00');
    });

    it('escapes XML special characters and remains well-formed when parsed', () => {
        const xml = serializeDemoCfdiXml(withMutatedDraft());

        expect(xml).toContain('&amp;');
        expect(xml).toContain('&lt;');
        expect(xml).toContain('&gt;');
        expect(xml).toContain('&quot;');
        expect(xml).toContain('&apos;');

        const doc = parseXml(xml);
        const issuer = doc.getElementsByTagName('cfdi:Emisor')[0];
        const receiver = doc.getElementsByTagName('cfdi:Receptor')[0];
        const concept = doc.getElementsByTagName('cfdi:Concepto')[0];

        expect(doc.getElementsByTagName('parsererror').length).toBe(0);
        expect(issuer.getAttribute('Nombre')).toBe('YARIS & CÍA "<Taller>" d\'Em');
        expect(receiver.getAttribute('Nombre')).toBe('CLIENTE & DEMO <ACADEMICA> "MX" \'01\'');
        expect(concept.getAttribute('Descripcion')).toBe('Corte & tinte <DEMOSTRATIVO> "sesión" \'única\'');
    });

    it('contains no invented signing, timbrado or certification artifacts', () => {
        const xml = serializeDemoCfdiXml(getDemoInvoiceDraft());
        const structuralForbidden = [
            'cfdi:Complemento',
            'TimbreFiscalDigital',
            'UUID',
            'SelloSAT',
            'SelloCFD',
            'NoCertificadoSAT',
            'Certificado=',
            'Sello=',
        ];

        for (const token of structuralForbidden) {
            expect(xml).not.toContain(token);
        }
    });

    it('derives totals and taxes from the draft contract instead of hardcoded literals', () => {
        const draft = getDemoInvoiceDraft();
        draft.concepts.push({
            ...draft.concepts[0],
            claveProdServ: '91101600',
            description: 'Corte y peinado DEMOSTRATIVO',
            quantity: 2,
            unitPriceCents: 12500,
            tax: {
                baseCents: 25000,
                impuesto: '002',
                tipoFactor: 'Tasa',
                rateBasisPoints: 1600,
            },
        });

        const doc = parseXml(serializeDemoCfdiXml(draft));
        const comprobante = doc.documentElement;
        const concepts = Array.from(doc.getElementsByTagName('cfdi:Concepto'));
        const conceptTaxes = doc.getElementsByTagName('cfdi:Traslado');
        const conceptImportes = concepts.map((node) => Number(node.getAttribute('Importe')));
        const rootImpuestos = Array.from(doc.getElementsByTagName('cfdi:Impuestos'))
            .find((node) => node.hasAttribute('TotalImpuestosTrasladados'));

        // subtotal = 75000 + 25000 = 100000 -> "1000.00"; tax = 12000 + 4000 = 16000 -> "160.00"; total = 116000 -> "1160.00"
        expect(comprobante.getAttribute('SubTotal')).toBe('1000.00');
        expect(rootImpuestos?.getAttribute('TotalImpuestosTrasladados')).toBe('160.00');
        expect(comprobante.getAttribute('Total')).toBe('1160.00');
        expect(conceptImportes).toEqual([750, 250]);
        expect(conceptTaxes.length).toBe(3);
    });

    it('exposes the expected demo filename', () => {
        expect(demoCfdiXmlFilename).toBe('cfdi-demo-sin-validez-fiscal.xml');
    });
});