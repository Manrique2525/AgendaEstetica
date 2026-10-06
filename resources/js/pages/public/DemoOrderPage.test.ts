import { flushPromises, mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { describe, expect, it, vi } from 'vitest';
import DemoOrderPage from './DemoOrderPage.vue';

describe('DemoOrderPage', () => {
    it('starts as an academic, non-purchase edit flow', () => {
        const wrapper = mount(DemoOrderPage);

        expect(wrapper.get('h1').text()).toBe('Demostración de pedido');
        expect(wrapper.findAll('h1')).toHaveLength(1);
        expect(wrapper.text()).toContain('DEMOSTRACIÓN ACADÉMICA');
        expect(wrapper.text()).toContain('no una compra real');
        expect(wrapper.find('#demo-order-customer').exists()).toBe(true);
        expect(wrapper.find('#demo-request-invoice').exists()).toBe(true);
        expect(wrapper.text()).toContain('no se generará una factura real');
        expect(wrapper.findAll('button').find((button) => button.text().includes('Revisar demostración'))).toBeDefined();
        expect(wrapper.text()).not.toMatch(/Comprar|Pagar|Pedido realizado|Pago aprobado/i);
    });

    it('supports review, correction and re-review with updated local data', async () => {
        const wrapper = mount(DemoOrderPage);

        await wrapper.get('#demo-order-customer').setValue('Cliente inicial de prueba');
        await wrapper.get('form').trigger('submit');
        expect(wrapper.find('#review-title').exists()).toBe(true);
        expect(wrapper.text()).toContain('Cliente inicial de prueba');

        await wrapper.findAll('button').find((button) => button.text().includes('Corregir'))!.trigger('click');
        expect(wrapper.find('#demo-order-customer').exists()).toBe(true);
        await wrapper.get('#demo-order-customer').setValue('Cliente corregido de prueba');
        await wrapper.get('#demo-request-invoice').setValue(true);
        await wrapper.get('form').trigger('submit');

        expect(wrapper.find('#review-title').exists()).toBe(true);
        expect(wrapper.text()).toContain('Cliente corregido de prueba');
        expect(wrapper.text()).toContain('Factura de demostraciónSolicitada');
    });

    it('keeps the no-invoice demo review state without a download link', async () => {
        const wrapper = mount(DemoOrderPage);

        await wrapper.get('form').trigger('submit');
        await flushPromises();
        await nextTick();

        expect(wrapper.find('#review-title').exists()).toBe(true);
        expect(wrapper.text()).toContain('No solicitada');
        expect(wrapper.find('a[download][href="/demo/factura-demostracion-sin-validez-fiscal.pdf"]').exists()).toBe(false);
    });

    it('shows the invoice-demo request in review without collecting fiscal data', async () => {
        const wrapper = mount(DemoOrderPage);

        await wrapper.get('#demo-request-invoice').setValue(true);
        await wrapper.get('form').trigger('submit');
        await flushPromises();
        await nextTick();
        expect(wrapper.text()).toContain('Factura de demostraciónSolicitada');
        expect(wrapper.text()).not.toMatch(/RFC|régimen fiscal|uso CFDI|domicilio fiscal/i);
        expect(wrapper.find('[data-testid="confirm-demo"]').exists()).toBe(true);
    });

    it('renders the complete invoice demonstration from the canonical fiscal fixture', async () => {
        const wrapper = mount(DemoOrderPage);

        await wrapper.get('#demo-request-invoice').setValue(true);
        await wrapper.get('form').trigger('submit');
        await wrapper.get('[data-testid="confirm-demo"]').trigger('click');
        await flushPromises();
        await vi.waitFor(() => {
            expect(wrapper.find('article[aria-labelledby="demo-invoice-title"]').exists()).toBe(true);
        });

        const invoice = wrapper.get('article[aria-labelledby="demo-invoice-title"]');

        expect(invoice.text()).toContain('DOCUMENTO DEMOSTRATIVO');
        expect(invoice.text()).toContain('YARIS DEMOSTRACION ACADEMICA');
        expect(invoice.text()).toContain('CLIENTE DEMOSTRACION ACADEMICA');
        expect(invoice.text()).toContain('91101701');
        expect(invoice.text()).toContain('$750.00');
        expect(invoice.text()).toContain('$120.00');
        expect(invoice.text()).toContain('$870.00 MXN');
        expect(invoice.text()).toContain('NO ES UN CFDI TIMBRADO');
        expect(invoice.text()).toContain('NO HA SIDO CERTIFICADO POR EL SAT NI POR UN PAC');
        expect(invoice.find('button[aria-label="Imprimir factura demostrativa"]').exists()).toBe(false);
        expect(invoice.find('a[href="/demo/factura-demostracion-sin-validez-fiscal.pdf"]').exists()).toBe(false);
        expect(invoice.find('button.invoice-document__print-action').exists()).toBe(true);
    });

    it('keeps the privacy notice and acceptance state visible in review', async () => {
        const wrapper = mount(DemoOrderPage);

        await wrapper.get('form').trigger('submit');
        await flushPromises();
        await flushPromises();

        expect(wrapper.get('aside[aria-label="Aviso provisional de privacidad"]').text()).toContain('no se persisten ni se transmiten');
        expect(wrapper.find('aside a[href="/fase-1#privacidad-seguridad"]').exists()).toBe(true);
        expect(wrapper.text()).toContain('Factura de demostraciónNo solicitada');
    });
});
