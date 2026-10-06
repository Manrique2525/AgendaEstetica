import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import DemoInvoiceDocument from './DemoInvoiceDocument.vue';
import { getDemoInvoiceDraft } from '../../data/fiscalDemo';

describe('DemoInvoiceDocument', () => {
    it('renders canonical fiscal demo data and derived totals', () => {
        const wrapper = mount(DemoInvoiceDocument, { props: { draft: getDemoInvoiceDraft() } });

        expect(wrapper.get('h3').text()).toBe('Factura digital demostrativa');
        expect(wrapper.text()).toContain('DEM010101AA0');
        expect(wrapper.text()).toContain('DEMO010101AAA');
        expect(wrapper.text()).toContain('91101701');
        expect(wrapper.text()).toContain('$750.00');
        expect(wrapper.text()).toContain('$120.00');
        expect(wrapper.text()).toContain('$870.00 MXN');
        expect(wrapper.text()).toContain('SIN VALIDEZ FISCAL');
        expect(wrapper.text()).toContain('NO ES UN CFDI TIMBRADO');
        expect(wrapper.text()).toContain('NO HA SIDO CERTIFICADO POR EL SAT NI POR UN PAC');
    });

    it('prints without inserting a download anchor or fiscal certification artifact', async () => {
        const print = vi.spyOn(window, 'print').mockImplementation(() => undefined);
        const wrapper = mount(DemoInvoiceDocument, { props: { draft: getDemoInvoiceDraft() } });

        await wrapper.get('button.invoice-document__print-action').trigger('click');

        expect(print).toHaveBeenCalledOnce();
        expect(wrapper.find('a[download]').exists()).toBe(false);
        expect(wrapper.text()).not.toMatch(/UUID|TimbreFiscalDigital|SelloSAT|SelloCFD|PAC certificado/i);
    });

    it('downloads a demonstrative XML without persisting any fiscal certification data', async () => {
        vi.useFakeTimers();
        try {
            const createObjectUrl = vi.fn((blob: Blob) => {
                void blob;

                return 'blob:spec024-demo';
            });
            const revokeObjectUrl = vi.fn();
            Object.defineProperty(URL, 'createObjectURL', { configurable: true, value: createObjectUrl });
            Object.defineProperty(URL, 'revokeObjectURL', { configurable: true, value: revokeObjectUrl });
            const clickSpy = vi.spyOn(HTMLAnchorElement.prototype, 'click').mockImplementation(() => undefined);

            const wrapper = mount(DemoInvoiceDocument, { props: { draft: getDemoInvoiceDraft() } });
            await wrapper.get('button.invoice-document__xml-action').trigger('click');
            vi.advanceTimersByTime(1100);

            expect(wrapper.get('button.invoice-document__xml-action').text()).toContain('Descargar XML demostrativo');
            expect(wrapper.text()).toContain('XML demostrativo');
            expect(wrapper.text()).toContain('SIN VALIDEZ FISCAL');
            expect(createObjectUrl).toHaveBeenCalledOnce();

            const blob = createObjectUrl.mock.calls[0][0] as Blob;

            expect(blob.type).toBe('application/xml;charset=utf-8');
            expect(blob.size).toBeGreaterThan(0);
            expect(clickSpy).toHaveBeenCalledOnce();
            expect(revokeObjectUrl).toHaveBeenCalledWith('blob:spec024-demo');
        } finally {
            vi.useRealTimers();
        }
    });
});
