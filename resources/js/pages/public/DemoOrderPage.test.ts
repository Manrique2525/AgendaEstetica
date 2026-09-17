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

    it('confirms only from review and creates a local demo folio and digest', async () => {
        const wrapper = mount(DemoOrderPage);

        expect(wrapper.find('h2').text()).toBe('Prepara datos de demostración');
        await wrapper.get('form').trigger('submit');
        await flushPromises();
        await wrapper.findAll('section[aria-labelledby="review-title"] button')[1].trigger('click');
        await flushPromises();
        const fetchSpy = vi.spyOn(globalThis, 'fetch');

        expect(wrapper.find('#confirmed-title').text()).toBe('Demostración confirmada');
        expect(wrapper.get('h3').text()).toBe('FOLIO DE DEMOSTRACIÓN');
        expect(wrapper.text()).toMatch(/DEMO-[A-Z0-9]{6}/);
        expect(wrapper.text()).toContain('NO constituye una firma digital');
        expect(wrapper.text()).toContain('PENDIENTE DE INTEGRACIÓN');
        expect(wrapper.text()).toContain('Factura de demostración');
        expect(wrapper.text()).toContain('No solicitada');
        expect(wrapper.find('a[download][href="/demo/factura-demostracion-sin-validez-fiscal.pdf"]').exists()).toBe(false);
        expect(wrapper.find('p.font-mono').text()).toMatch(/^[a-f0-9]{64}$/);
        expect(fetchSpy).not.toHaveBeenCalled();
        expect(Object.keys(localStorage)).toHaveLength(0);
        expect(Object.keys(sessionStorage)).toHaveLength(0);
        fetchSpy.mockRestore();
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
