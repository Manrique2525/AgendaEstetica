import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { describe, expect, it } from 'vitest';
import router from '../router';
import PublicLayout from './PublicLayout.vue';

function mountLayout() {
    return mount(PublicLayout, { global: { plugins: [router] } });
}

describe('PublicLayout', () => {
    it('renders the shared shell, approved navigation and contact link', () => {
        const wrapper = mountLayout();

        expect(wrapper.find('header').exists()).toBe(true);
        expect(wrapper.find('footer').exists()).toBe(true);
        expect(wrapper.find('a[href="/"]').exists()).toBe(true);
        expect(wrapper.find('a[href="/#tienda"]').exists()).toBe(true);
        expect(wrapper.find('a[href="/#servicios"]').exists()).toBe(true);
        expect(wrapper.find('a[href="/#contacto"]').exists()).toBe(true);
        expect(wrapper.find('a[href="/reservar"]').text()).toBe('Solicitar cita');
        expect(wrapper.find('a[href="https://wa.me/529932294158"]').attributes('target')).toBe('_blank');
        expect(wrapper.find('#main-content').attributes('tabindex')).toBe('-1');
    });

    it('supports an accessible local mobile menu lifecycle', async () => {
        const wrapper = mountLayout();
        const toggle = wrapper.find('button[aria-controls="public-navigation"]');

        expect(toggle.attributes('aria-expanded')).toBe('false');
        await toggle.trigger('click');
        expect(toggle.attributes('aria-expanded')).toBe('true');

        const appointmentLink = wrapper.find('a[href="/reservar"]');
        appointmentLink.element.addEventListener('click', (event) => event.preventDefault(), { once: true });
        await appointmentLink.trigger('click');
        expect(toggle.attributes('aria-expanded')).toBe('false');

        await toggle.trigger('click');
        await wrapper.find('a[href="/#tienda"]').trigger('click');
        expect(toggle.attributes('aria-expanded')).toBe('false');

        await toggle.trigger('click');
        window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
        await nextTick();
        expect(toggle.attributes('aria-expanded')).toBe('false');
    });
});
