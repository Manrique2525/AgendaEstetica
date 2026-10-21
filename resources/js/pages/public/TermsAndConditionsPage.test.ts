import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import router from '../../router';
import TermsAndConditionsPage from './TermsAndConditionsPage.vue';

describe('TermsAndConditionsPage', () => {
    it('renders provisional academic terms without inventing legal identity', () => {
        const wrapper = mount(TermsAndConditionsPage, { global: { plugins: [router] } });

        expect(wrapper.get('h1').text()).toBe('Términos y Condiciones');
        expect(wrapper.findAll('h1')).toHaveLength(1);
        expect(wrapper.text()).toContain('BORRADOR PROVISIONAL — PENDIENTE DE VALIDACIÓN DEL RESPONSABLE DEL NEGOCIO');
        expect(wrapper.text()).toContain('Objeto de la plataforma');
        expect(wrapper.text()).toContain('Citas');
        expect(wrapper.text()).toContain('Mary Kay — sitio externo');
        expect(wrapper.text()).toContain('Demostraciones académicas');
        expect(wrapper.text()).toContain('No es un CFDI');
        expect(wrapper.text()).toContain('SHA-256');
        expect(wrapper.text()).toContain('Aviso de Privacidad');
        expect(wrapper.text()).toContain('Contacto');
        expect(wrapper.text()).not.toMatch(/RFC|régimen fiscal|razón social/i);
        // Mary Kay link is now a button that opens modal
        const maryKayButtons = wrapper.findAll('button');
        const maryKayButton = maryKayButtons.find(b => b.text().includes('Abrir tienda externa de Mary Kay'));
        expect(maryKayButton).toBeDefined();
        expect(wrapper.findAll('a[href="/"]').some((link) => link.text() === 'Volver al inicio')).toBe(true);
    });
});
