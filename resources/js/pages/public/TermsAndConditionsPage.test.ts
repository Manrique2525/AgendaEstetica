import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import router from '../../router';
import TermsAndConditionsPage from './TermsAndConditionsPage.vue';

describe('TermsAndConditionsPage', () => {
    it('renders provisional academic terms without inventing legal identity', () => {
        const wrapper = mount(TermsAndConditionsPage, { global: { plugins: [router] } });

        expect(wrapper.get('h1').text()).toBe('Términos y Condiciones');
        expect(wrapper.findAll('h1')).toHaveLength(1);
        expect(wrapper.text()).toContain('Documento provisional para fines académicos');
        expect(wrapper.text()).toContain('Objeto de la plataforma');
        expect(wrapper.text()).toContain('Citas');
        expect(wrapper.text()).toContain('Mary Kay — sitio externo');
        expect(wrapper.text()).toContain('Demostraciones académicas');
        expect(wrapper.text()).toContain('No es un CFDI');
        expect(wrapper.text()).toContain('SHA-256');
        expect(wrapper.text()).toContain('Aviso de Privacidad');
        expect(wrapper.text()).toContain('Contacto');
        expect(wrapper.text()).not.toMatch(/RFC|régimen fiscal|ARCO|razón social/i);
        expect(wrapper.find('a[href="https://www.marykay.com.mx/yaris"]').exists()).toBe(true);
        expect(wrapper.findAll('a[href="/"]').some((link) => link.text() === 'Volver al inicio')).toBe(true);
    });
});
