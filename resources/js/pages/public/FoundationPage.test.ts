import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import router from '../../router';
import FoundationPage from './FoundationPage.vue';

const api = vi.hoisted(() => ({ listServices: vi.fn() }));
const productsApi = vi.hoisted(() => ({ listProducts: vi.fn() }));

vi.mock('../../services/api/publicBooking', () => ({ publicBookingApi: api }));
vi.mock('../../services/api/publicCatalog', () => ({ publicCatalogApi: productsApi }));

const services = [
    { id: 1, name: 'Corte', slug: 'corte', description: 'Corte a tu estilo.', duration_minutes: 45, pricing_type: 'fixed' as const, price: '250.00', pricing_display: '$250.00', category: { id: 1, name: 'Cabello', slug: 'cabello' } },
    { id: 2, name: 'Balayage', slug: 'balayage', description: 'Degradado natural.', duration_minutes: 180, pricing_type: 'fixed' as const, price: '1800.00', pricing_display: '$1,800.00', category: { id: 2, name: 'Coloración', slug: 'coloracion' } },
];

const global = { plugins: [router] };

describe('FoundationPage', () => {
    beforeEach(() => {
        sessionStorage.removeItem('yaris.academic-consent.v1');
        api.listServices.mockReset();
        api.listServices.mockResolvedValue(services);
        productsApi.listProducts.mockReset();
        productsApi.listProducts.mockResolvedValue([]);
    });

    it('renders the approved Yaris homepage content and destinations', () => {
        const wrapper = mount(FoundationPage, { global });

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
        // Mary Kay link now opens a modal instead of direct navigation
        const maryKayButton = store.findAll('button').find(b => b.text().includes('Visitar tienda externa de Mary Kay'));
        expect(maryKayButton).toBeDefined();
        expect(store.get('#tienda').text()).toContain('tienda externa de Mary Kay');
    });

    it('renders the services bridge and approved contact information', () => {
        const wrapper = mount(FoundationPage, { global });

        expect(wrapper.get('#servicios').text()).toContain('Servicios');
        expect(wrapper.get('#servicios').find('a[href="/reservar"]').exists()).toBe(true);
        expect(wrapper.get('#contacto').text()).toContain('Todos los días, 10:00 a. m. a 8:00 p. m.');
        expect(wrapper.get('#contacto').text()).toContain('993 229 4158');
        expect(wrapper.get('#contacto').text()).toContain('Fraccionamiento Ciudad Bicentenario');
        expect(wrapper.get('#contacto').text()).toContain('C.P. 86290');
        expect(wrapper.get('#contacto a').attributes('href')).toBe('https://wa.me/529932294158');
    });

    it('renders the seeded service catalog with prices and booking preselection links', async () => {
        const wrapper = mount(FoundationPage, { global });
        await flushPromises();

        const catalog = wrapper.get('#servicios');

        expect(api.listServices).toHaveBeenCalled();
        expect(catalog.text()).toContain('Corte');
        expect(catalog.text()).toContain('Balayage');
        expect(catalog.text()).toContain('$250.00');
        expect(catalog.text()).toContain('45 minutos');
        expect(catalog.text()).toContain('Cabello');
        expect(catalog.text()).toContain('Coloración');
        expect(catalog.text()).toContain('precios mostrados son orientativos');
        expect(catalog.find('a[href="/reservar?service=1"]').exists()).toBe(true);
        expect(catalog.find('a[href="/reservar?service=2"]').exists()).toBe(true);
        expect(catalog.text()).not.toMatch(/Comprar|Agregar al carrito|Pagar/i);
    });

    it('renders the dynamic product catalog in the store when products are published', async () => {
        productsApi.listProducts.mockResolvedValue([
            { id: 1, name: 'Shampoo', slug: 'shampoo', description: 'Cuidado capilar diario.', price: '180.00', price_display: '$180.00', category: { id: 1, name: 'Cuidado capilar' } },
        ]);

        const wrapper = mount(FoundationPage, { global });
        await flushPromises();

        const store = wrapper.get('#tienda');
        expect(productsApi.listProducts).toHaveBeenCalled();
        expect(store.text()).toContain('Shampoo');
        expect(store.text()).toContain('Cuidado capilar');
        expect(store.text()).toContain('$180.00');
        // Mary Kay link is now a button that opens modal
        const maryKayButton = store.findAll('button').find(b => b.text().includes('Visitar tienda externa de Mary Kay'));
        expect(maryKayButton).toBeDefined();
    });

    it('renders the dynamic product catalog empty state honestly', async () => {
        productsApi.listProducts.mockResolvedValue([]);

        const wrapper = mount(FoundationPage, { global });
        await flushPromises();

        expect(wrapper.get('#tienda').text()).toContain('Nuestro catálogo se está preparando');
    });

    it('renders independent academic capabilities and the fixed terms banner', () => {
        const wrapper = mount(FoundationPage, { global });

        expect(wrapper.get('#academic-phase-1').text()).toContain('Autenticación');
        expect(wrapper.get('#academic-phase-1').text()).toContain('Integridad');
        expect(wrapper.get('#academic-phase-1').text()).toContain('Firma digital');
        expect(wrapper.get('#academic-phase-1').text()).toContain('Factura digital');
        expect(wrapper.get('#academic-phase-1').findAll('section')).toHaveLength(0);
        expect(wrapper.find('section[aria-labelledby="terms-banner-title"]').exists()).toBe(true);
        expect(wrapper.get('section[aria-labelledby="terms-banner-title"]').text()).toContain('Términos y privacidad');
    });
});

function wrapperStore() {
    return mount(FoundationPage, { global });
}
