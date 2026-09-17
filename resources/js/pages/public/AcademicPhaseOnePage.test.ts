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
        expect(wrapper.text()).toContain('Mary Kay a domicilio');
        expect(wrapper.get('#autenticacion').text()).toContain('DEMOSTRACIÓN');
        expect(wrapper.findAll('section[id]').map((section) => section.attributes('id'))).toEqual([
            'privacidad-seguridad',
            'autenticacion',
            'integridad-firma',
            'factura-digital',
            'mary-kay-domicilio',
            'terminos-privacidad',
        ]);
    });

    it('keeps privacy provisional and does not claim future capabilities', () => {
        const wrapper = mount(AcademicPhaseOnePage);

        expect(wrapper.get('#privacidad-seguridad').text()).toContain('PENDIENTE DE REVISIÓN');
        expect(wrapper.get('#privacidad-seguridad').text()).toContain('Términos y condiciones');
        expect(wrapper.get('#privacidad-seguridad').text()).not.toContain('Autenticación');
        expect(wrapper.get('#privacidad-seguridad').text()).not.toContain('SHA-256');
        expect(wrapper.get('#privacidad-seguridad').text()).not.toContain('Factura digital');
        expect(wrapper.get('#privacidad-seguridad').text()).not.toContain('Mary Kay');
        expect(wrapper.get('#autenticacion').text()).toContain('No hay autenticación de clientes');
        expect(wrapper.get('#autenticacion').text()).toContain('no autentica');
        expect(wrapper.get('#autenticacion').text()).toContain('FUNCIONAL');
        expect(wrapper.find('#autenticacion a[href="/cliente/acceso"]').exists()).toBe(true);
        expect(wrapper.find('#autenticacion a[href="/admin/login"]').exists()).toBe(true);
        expect(wrapper.get('#integridad-firma').text()).toContain('SHA-256');
        expect(wrapper.get('#integridad-firma').text()).toContain('no es una firma digital');
        expect(wrapper.get('#integridad-firma').text()).toContain('PENDIENTE DE INTEGRACIÓN');
        expect(wrapper.find('#integridad-firma a[href="/demo/pedido"]').exists()).toBe(true);
        expect(wrapper.get('#factura-digital').text()).toContain('No se genera CFDI');
        expect(wrapper.get('#autenticacion').text()).not.toMatch(/crear sesión|iniciar sesión|OTP|timbrado/i);
        expect(wrapper.get('#factura-digital').text()).toContain('DEMOSTRACIÓN');
        expect(wrapper.find('#factura-digital a[href="/demo/pedido"]').exists()).toBe(true);
        expect(wrapper.find('#mary-kay-domicilio').exists()).toBe(true);
        expect(wrapper.find('#mary-kay-domicilio').text()).toContain('tienda externa de Mary Kay');
        expect(wrapper.find('#mary-kay-domicilio a[href="https://www.marykay.com.mx/yaris"]').attributes()).toMatchObject({ target: '_blank', rel: 'noopener noreferrer' });
        expect(wrapper.find('#terminos-privacidad').exists()).toBe(true);
        const sectionIds = wrapper.findAll('section[id]').map((section) => section.attributes('id'));
        expect(sectionIds[sectionIds.length - 1]).toBe('terminos-privacidad');
        expect(wrapper.find('#terminos-privacidad').find('#autenticacion').exists()).toBe(false);
        expect(wrapper.find('#terminos-privacidad').find('#mary-kay-domicilio').exists()).toBe(false);
    });

    it('links the privacy anchor from the overview', () => {
        const wrapper = mount(AcademicPhaseOnePage);

        expect(wrapper.find('a[href="/fase-1#privacidad-seguridad"]').exists()).toBe(true);
        expect(wrapper.findAll('[id="privacidad-seguridad"]')).toHaveLength(1);
    });

    it('supports explicit undecided, accepted and rejected terms states', async () => {
        const wrapper = mount(AcademicPhaseOnePage);
        const terms = wrapper.find('#terminos-privacidad');

        expect(terms.text()).toContain('Aún no has elegido una opción');
        await terms.find('button[aria-pressed="false"]').trigger('click');
        expect(terms.text()).toContain('Has rechazado los términos');
        await terms.findAll('button')[1].trigger('click');
        expect(terms.text()).toContain('Términos y condiciones aceptados');
    });
});
