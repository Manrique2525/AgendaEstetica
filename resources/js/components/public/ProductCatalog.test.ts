import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import ProductCatalog from './ProductCatalog.vue';
import type { PublicCatalogProduct } from '../../services/api/publicCatalog';

const api = vi.hoisted(() => ({ listProducts: vi.fn() }));

vi.mock('../../services/api/publicCatalog', () => ({ publicCatalogApi: api }));

const product = (overrides: Partial<PublicCatalogProduct> = {}): PublicCatalogProduct => ({
    id: 1,
    name: 'Shampoo',
    slug: 'shampoo',
    description: 'Cuidado capilar diario.',
    price: '180.00',
    price_display: '$180.00',
    category: { id: 1, name: 'Cuidado capilar' },
    ...overrides,
});

beforeEach(() => {
    api.listProducts.mockReset();
});

afterEach(() => vi.clearAllMocks());

describe('ProductCatalog', () => {
    it('shows a loading status before the catalog resolves', () => {
        api.listProducts.mockReturnValue(new Promise(() => {}));

        const wrapper = mount(ProductCatalog);

        expect(wrapper.get('[role="status"]').text()).toContain('Cargando productos...');
    });

    it('renders products grouped by category with their prices', async () => {
        api.listProducts.mockResolvedValue([
            product({ id: 1, name: 'Shampoo', category: { id: 1, name: 'Cuidado capilar' } }),
            product({ id: 2, name: 'Labial', price_display: '$250.00', category: { id: 2, name: 'Maquillaje' } }),
        ]);

        const wrapper = mount(ProductCatalog);
        await flushPromises();

        const headings = wrapper.findAll('h3').map((heading) => heading.text());
        expect(headings).toEqual(['Cuidado capilar', 'Maquillaje']);
        expect(wrapper.text()).toContain('Shampoo');
        expect(wrapper.text()).toContain('$180.00');
        expect(wrapper.text()).toContain('$250.00');
        expect(wrapper.findAll('section')).toHaveLength(2);
    });

    it('groups products without category under Otros productos', async () => {
        api.listProducts.mockResolvedValue([product({ id: 9, name: 'Genérico', category: null })]);

        const wrapper = mount(ProductCatalog);
        await flushPromises();

        expect(wrapper.get('h3').text()).toBe('Otros productos');
        expect(wrapper.get('h3').attributes('id')).toBe('product-otros-productos');
    });

    it('renders an honest empty state when there are no products', async () => {
        api.listProducts.mockResolvedValue([]);

        const wrapper = mount(ProductCatalog);
        await flushPromises();

        expect(wrapper.text()).toContain('Nuestro catálogo se está preparando');
        expect(wrapper.findAll('section')).toHaveLength(0);
    });

    it('renders the error state when loading fails', async () => {
        api.listProducts.mockRejectedValue(new Error('network'));

        const wrapper = mount(ProductCatalog);
        await flushPromises();

        expect(wrapper.get('[role="alert"]').text()).toContain('No pudimos cargar los productos. Intenta nuevamente.');
        expect(wrapper.findAll('section')).toHaveLength(0);
    });

    it('retries the request and renders the loaded catalog', async () => {
        api.listProducts
            .mockRejectedValueOnce(new Error('network'))
            .mockResolvedValueOnce([product({ id: 3, name: 'Acondicionador' })]);

        const wrapper = mount(ProductCatalog);
        await flushPromises();

        expect(api.listProducts).toHaveBeenCalledTimes(1);

        await wrapper.get('[role="alert"] button').trigger('click');
        await flushPromises();

        expect(api.listProducts).toHaveBeenCalledTimes(2);
        expect(wrapper.text()).toContain('Acondicionador');
        expect(wrapper.find('[role="alert"]').exists()).toBe(false);
    });
});
