import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import ServiceCatalog from './ServiceCatalog.vue';
import type { PublicBookingService } from '../../services/api/publicBooking';

const api = vi.hoisted(() => ({ listServices: vi.fn() }));

vi.mock('../../services/api/publicBooking', () => ({ publicBookingApi: api }));

const service = (overrides: Partial<PublicBookingService> = {}): PublicBookingService => ({
    id: 1,
    name: 'Corte',
    slug: 'corte',
    description: 'Corte a tu estilo.',
    image_url: null,
    duration_minutes: 45,
    pricing_type: 'fixed',
    price: '250.00',
    pricing_display: '$250.00',
    category: { id: 1, name: 'Cabello', slug: 'cabello' },
    ...overrides,
});

beforeEach(() => {
    api.listServices.mockReset();
});

afterEach(() => vi.clearAllMocks());

describe('ServiceCatalog', () => {
    it('shows a loading status before the catalog resolves', () => {
        api.listServices.mockReturnValue(new Promise(() => {}));

        const wrapper = mount(ServiceCatalog);

        expect(wrapper.get('[role="status"]').text()).toContain('Cargando servicios...');
    });

    it('renders services grouped by category', async () => {
        api.listServices.mockResolvedValue([
            service({ id: 1, name: 'Corte', category: { id: 1, name: 'Cabello', slug: 'cabello' } }),
            service({ id: 2, name: 'Balayage', pricing_display: '$1,800.00', category: { id: 2, name: 'Coloración', slug: 'coloracion' } }),
        ]);

        const wrapper = mount(ServiceCatalog);
        await flushPromises();

        const headings = wrapper.findAll('h3').map((heading) => heading.text());
        expect(headings).toEqual(['Cabello', 'Coloración']);
        expect(wrapper.text()).toContain('Corte');
        expect(wrapper.text()).toContain('Balayage');
        expect(wrapper.findAll('section')).toHaveLength(2);
    });

    it('groups services without category under Otros servicios', async () => {
        api.listServices.mockResolvedValue([service({ id: 9, name: 'Diagnóstico', category: null })]);

        const wrapper = mount(ServiceCatalog);
        await flushPromises();

        expect(wrapper.get('h3').text()).toBe('Otros servicios');
        expect(wrapper.get('h3').attributes('id')).toBe('catalog-otros-servicios');
        expect(wrapper.text()).toContain('Diagnóstico');
    });

    it('renders an honest empty state when there are no services', async () => {
        api.listServices.mockResolvedValue([]);

        const wrapper = mount(ServiceCatalog);
        await flushPromises();

        expect(wrapper.text()).toContain('Aún no hay servicios publicados');
        expect(wrapper.findAll('section')).toHaveLength(0);
    });

    it('renders the error state when loading fails', async () => {
        api.listServices.mockRejectedValue(new Error('network'));

        const wrapper = mount(ServiceCatalog);
        await flushPromises();

        expect(wrapper.get('[role="alert"]').text()).toContain('No pudimos cargar los servicios. Intenta nuevamente.');
        expect(wrapper.findAll('section')).toHaveLength(0);
    });

    it('retries the request and renders the loaded catalog', async () => {
        api.listServices
            .mockRejectedValueOnce(new Error('network'))
            .mockResolvedValueOnce([service({ id: 3, name: 'Peinado' })]);

        const wrapper = mount(ServiceCatalog);
        await flushPromises();

        expect(api.listServices).toHaveBeenCalledTimes(1);

        await wrapper.get('[role="alert"] button').trigger('click');
        await flushPromises();

        expect(api.listServices).toHaveBeenCalledTimes(2);
        expect(wrapper.text()).toContain('Peinado');
        expect(wrapper.find('[role="alert"]').exists()).toBe(false);
    });

    it('links each service to the booking flow with its identifier', async () => {
        api.listServices.mockResolvedValue([
            service({ id: 4, name: 'Uñas', category: { id: 3, name: 'Uñas y pedicure', slug: 'unas-y-pedicure' } }),
        ]);

        const wrapper = mount(ServiceCatalog);
        await flushPromises();

        const link = wrapper.get('a[href="/reservar?service=4"]');
        expect(link.text()).toBe('Reservar');
        expect(wrapper.text()).toContain('45 minutos');
        expect(wrapper.text()).toContain('$250.00');
        expect(wrapper.text()).toContain('precios mostrados son orientativos');
    });
});
