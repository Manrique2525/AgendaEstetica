export interface DemoOrderData {
    customerName: string;
    itemName: string;
    note: string;
}

export interface DemoIntegrityPayload {
    version: 2;
    folio: string;
    demo: true;
    requestInvoice: boolean;
    customer: {
        name: string;
    };
    items: Array<{
        name: string;
        quantity: 1;
        price_label: 'DATO DE PRUEBA';
    }>;
    summary: {
        note: string;
    };
}

export function normalizeDemoData(data: DemoOrderData): DemoOrderData {
    return {
        customerName: data.customerName.trim().replace(/\s+/g, ' '),
        itemName: data.itemName.trim().replace(/\s+/g, ' '),
        note: data.note.trim().replace(/\s+/g, ' '),
    };
}

export function buildDemoIntegrityPayload(data: DemoOrderData, folio: string, requestInvoice = false): DemoIntegrityPayload {
    const normalized = normalizeDemoData(data);

    return {
        version: 2,
        folio,
        demo: true,
        requestInvoice,
        customer: {
            name: normalized.customerName,
        },
        items: [{
            name: normalized.itemName,
            quantity: 1,
            price_label: 'DATO DE PRUEBA',
        }],
        summary: {
            note: normalized.note,
        },
    };
}

export function serializeDemoIntegrityPayload(payload: DemoIntegrityPayload): string {
    return JSON.stringify(payload);
}

export async function sha256Hex(value: string, cryptoApi: Crypto | undefined = globalThis.crypto): Promise<string | null> {
    if (!cryptoApi?.subtle) return null;

    const digest = await cryptoApi.subtle.digest('SHA-256', new TextEncoder().encode(value));

    return Array.from(new Uint8Array(digest), (byte) => byte.toString(16).padStart(2, '0')).join('');
}
