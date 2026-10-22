import { flushPromises, mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import CustomerAccessDemoPage from './CustomerAccessDemoPage.vue';

describe('CustomerAccessDemoPage', () => {
    it('presents customer access as a demonstration and reuses real admin access', () => {
        const wrapper = mount(CustomerAccessDemoPage);

        expect(wrapper.get('h1').text()).toBe('Acceso de clientes');
        expect(wrapper.findAll('h1')).toHaveLength(1);
        expect(wrapper.text()).toContain('DEMOSTRACIÓN');
        expect(wrapper.text()).toContain('FUNCIONAL');
        expect(wrapper.text()).toContain('verificación todavía está pendiente');
        expect(wrapper.findAll('a[href="/admin/login"]')).toHaveLength(1);
        expect(wrapper.find('a[href="https://wa.me/529932294158"]').attributes()).toMatchObject({ target: '_blank', rel: 'noopener noreferrer' });
        expect(wrapper.text()).not.toContain('Sesión iniciada');
    });

    it('keeps the phone demo local and shows a non-authenticated pending state', async () => {
        const fetchSpy = vi.spyOn(globalThis, 'fetch');
        const wrapper = mount(CustomerAccessDemoPage);
        const localStorageBefore = Object.keys(localStorage);
        const sessionStorageBefore = Object.keys(sessionStorage);

        await wrapper.get('#demo-phone').setValue('993 229 4158');
        await wrapper.get('form').trigger('submit');
        await flushPromises();

        expect(wrapper.get('[role="status"]').text()).toContain('No se ha iniciado sesión');
        expect(fetchSpy).not.toHaveBeenCalled();
        expect(Object.keys(localStorage)).toEqual(localStorageBefore);
        expect(Object.keys(sessionStorage)).toEqual(sessionStorageBefore);
        expect(wrapper.find('h1').text()).toBe('Acceso de clientes');
        expect(wrapper.find('a[href="/cliente/dashboard"]').exists()).toBe(false);
        fetchSpy.mockRestore();
    });

    it('includes the provisional privacy notice and never transmits the demo phone', () => {
        const wrapper = mount(CustomerAccessDemoPage);

        expect(wrapper.get('aside[aria-label="Aviso provisional de privacidad"]').text()).toContain('no se persiste ni se transmite');
        expect(wrapper.find('aside a[href="/fase-1#privacidad-seguridad"]').exists()).toBe(true);
        expect(wrapper.text()).toContain('Abrir este canal no autentica');
        expect(wrapper.find('a[href="https://wa.me/529932294158"]').attributes('href')).toBe('https://wa.me/529932294158');
    });
});
