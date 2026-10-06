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

    it('uses print without exposing a download or fiscal certification artifact', async () => {
        const print = vi.spyOn(window, 'print').mockImplementation(() => undefined);
        const wrapper = mount(DemoInvoiceDocument, { props: { draft: getDemoInvoiceDraft() } });

        await wrapper.get('button.invoice-document__print-action').trigger('click');

        expect(print).toHaveBeenCalledOnce();
        expect(wrapper.find('a[download]').exists()).toBe(false);
        expect(wrapper.text()).not.toMatch(/UUID|TimbreFiscalDigital|SelloSAT|SelloCFD|PAC certificado/i);
    });
});
