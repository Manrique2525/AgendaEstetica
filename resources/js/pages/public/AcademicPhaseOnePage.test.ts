import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import AcademicPhaseOnePage from './AcademicPhaseOnePage.vue';

describe('AcademicPhaseOnePage', () => {
    it('presents the four academic areas with truthful pending statuses', () => {
        const wrapper = mount(AcademicPhaseOnePage);

        expect(wrapper.get('h1').text()).toBe('Fase 1');
        expect(wrapper.findAll('h1')).toHaveLength(1);
        expect(wrapper.findAll('article')).toHaveLength(0);
        expect(wrapper.text()).toContain('Privacidad y seguridad');
        expect(wrapper.text()).toContain('Autenticación');
        expect(wrapper.text()).toContain('Integridad y firma digital');
        expect(wrapper.text()).toContain('Factura digital');
        expect(wrapper.findAll('section[id]').map((section) => section.attributes('id'))).toEqual([
            'privacidad-seguridad',
            'autenticacion',
            'integridad-firma',
            'factura-digital',
        ]);
    });

    it('keeps privacy provisional and does not claim future capabilities', () => {
        const wrapper = mount(AcademicPhaseOnePage);

        expect(wrapper.get('#privacidad-seguridad').text()).toContain('PENDIENTE DE REVISIÓN');
        expect(wrapper.get('#autenticacion').text()).toContain('no está implementada');
        expect(wrapper.get('#autenticacion').text()).toContain('WhatsApp no autentica');
        expect(wrapper.get('#integridad-firma').text()).toContain('SHA-256');
        expect(wrapper.get('#integridad-firma').text()).toContain('no es una firma digital');
        expect(wrapper.get('#factura-digital').text()).toContain('ni genera CFDI');
        expect(wrapper.text()).not.toMatch(/crear sesión|iniciar sesión|OTP|timbrado/i);
    });

    it('links the privacy anchor from the overview', () => {
        const wrapper = mount(AcademicPhaseOnePage);

        expect(wrapper.find('a[href="/fase-1#privacidad-seguridad"]').exists()).toBe(true);
        expect(wrapper.findAll('[id="privacidad-seguridad"]')).toHaveLength(1);
    });
});
