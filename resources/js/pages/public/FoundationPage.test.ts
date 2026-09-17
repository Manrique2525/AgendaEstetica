import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import FoundationPage from './FoundationPage.vue';

describe('FoundationPage', () => {
    it('renders the approved Yaris homepage content and destinations', () => {
        const wrapper = mount(FoundationPage);

        expect(wrapper.get('h1').text()).toBe('Salón y Barbería Yaris');
        expect(wrapper.text()).toContain('Belleza y elegancia');
        expect(wrapper.findAll('a[href="/#tienda"]').some((link) => link.text() === 'Ver productos')).toBe(true);
        expect(wrapper.findAll('a[href="/reservar"]').length).toBe(3);
        expect(wrapper.text()).not.toContain('Base visual del sistema');
        expect(wrapper.text()).not.toContain('Vue SPA shell técnico');
    });

    it('presents only the catalog preparation categories without purchase claims', () => {
        const store = wrapperStore();

        expect(store.get('#tienda').text()).toContain('Mary Kay');
        expect(store.get('#tienda').text()).toContain('Cuidado capilar');
        expect(store.get('#tienda').text()).toContain('Catálogo en preparación');
        expect(store.get('#tienda').text()).not.toMatch(/Comprar|Agregar al carrito|Pagar|Stock|Precio/i);
        expect(store.get('#tienda').findAll('a[href="https://www.marykay.com.mx/yaris"]')).toHaveLength(1);
        expect(store.get('#tienda').find('a[href="https://www.marykay.com.mx/yaris"]').attributes()).toMatchObject({ target: '_blank', rel: 'noopener noreferrer' });
        expect(store.get('#tienda').text()).toContain('tienda externa de Mary Kay');
    });

    it('renders the services bridge and approved contact information', () => {
        const wrapper = mount(FoundationPage);

        expect(wrapper.get('#servicios').text()).toContain('Servicios');
        expect(wrapper.get('#servicios').find('a[href="/reservar"]').exists()).toBe(true);
        expect(wrapper.get('#contacto').text()).toContain('Todos los días, 10:00 a. m. a 8:00 p. m.');
        expect(wrapper.get('#contacto').text()).toContain('993 229 4158');
        expect(wrapper.get('#contacto').text()).toContain('Fraccionamiento Ciudad Bicentenario');
        expect(wrapper.get('#contacto').text()).toContain('C.P. 86290');
        expect(wrapper.get('#contacto a').attributes('href')).toBe('https://wa.me/529932294158');
    });

    it('renders independent academic capabilities and bottom terms decision', async () => {
        const wrapper = mount(FoundationPage);

        expect(wrapper.get('#academic-phase-1').text()).toContain('Autenticación');
        expect(wrapper.get('#academic-phase-1').text()).toContain('Integridad');
        expect(wrapper.get('#academic-phase-1').text()).toContain('Firma digital');
        expect(wrapper.get('#academic-phase-1').text()).toContain('Factura digital');
        expect(wrapper.get('#academic-phase-1').findAll('section')).toHaveLength(0);
        const sectionIds = wrapper.findAll('section[id]').map((section) => section.attributes('id'));
        expect(sectionIds[sectionIds.length - 1]).toBe('terminos-privacidad');
        expect(wrapper.get('#terminos-privacidad').text()).toContain('Aún no has elegido una opción');

        await wrapper.findAll('#terminos-privacidad button')[0].trigger('click');
        expect(wrapper.get('#terminos-privacidad').text()).toContain('Has rechazado');
        await wrapper.findAll('#terminos-privacidad button')[1].trigger('click');
        expect(wrapper.get('#terminos-privacidad').text()).toContain('Términos y condiciones aceptados');
    });
});

function wrapperStore() {
    return mount(FoundationPage);
}
