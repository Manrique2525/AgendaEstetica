import { describe, expect, it } from 'vitest';
import { buildDemoIntegrityPayload, normalizeDemoData, serializeDemoIntegrityPayload, sha256Hex, type DemoOrderData } from './demoIntegrity';

const data: DemoOrderData = {
    customerName: '  Cliente   de demostración ',
    itemName: 'DEMO ACADÉMICA — Producto de prueba',
    note: 'Sin datos personales reales',
};

describe('demo integrity helper', () => {
    it('normalizes and serializes an explicitly ordered canonical payload', () => {
        const payload = buildDemoIntegrityPayload(data, 'DEMO-ABC123');

        expect(normalizeDemoData(data)).toEqual({
            customerName: 'Cliente de demostración',
            itemName: 'DEMO ACADÉMICA — Producto de prueba',
            note: 'Sin datos personales reales',
        });
        expect(Object.keys(payload)).toEqual(['version', 'folio', 'demo', 'customer', 'items', 'summary']);
        expect(serializeDemoIntegrityPayload(payload)).toBe('{"version":1,"folio":"DEMO-ABC123","demo":true,"customer":{"name":"Cliente de demostración"},"items":[{"name":"DEMO ACADÉMICA — Producto de prueba","quantity":1,"price_label":"DATO DE PRUEBA"}],"summary":{"note":"Sin datos personales reales"}}');
    });

    it('matches a known SHA-256 vector', async () => {
        await expect(sha256Hex('hello')).resolves.toBe('2cf24dba5fb0a30e26e83b2ac5b9e29e1b161e5c1fa7425e73043362938b9824');
    });

    it('repeats the same digest and changes it when meaningful data changes', async () => {
        const first = serializeDemoIntegrityPayload(buildDemoIntegrityPayload(data, 'DEMO-ABC123'));
        const same = serializeDemoIntegrityPayload(buildDemoIntegrityPayload(data, 'DEMO-ABC123'));
        const changed = serializeDemoIntegrityPayload(buildDemoIntegrityPayload({ ...data, note: 'Dato corregido de prueba' }, 'DEMO-ABC123'));

        await expect(sha256Hex(first)).resolves.toBe(await sha256Hex(same));
        await expect(sha256Hex(first)).resolves.not.toBe(await sha256Hex(changed));
    });

    it('returns no digest when Web Crypto is unavailable', async () => {
        await expect(sha256Hex('hello', undefined)).resolves.toMatch(/^[a-f0-9]{64}$/);
        await expect(sha256Hex('hello', { subtle: undefined } as unknown as Crypto)).resolves.toBeNull();
    });
});
