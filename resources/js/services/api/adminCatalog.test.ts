import { afterEach, describe, expect, it, vi } from 'vitest';
import { http } from '../http';
import { adminCatalogApi } from './adminCatalog';

vi.mock('../http', () => ({ http: { get: vi.fn(), post: vi.fn(), put: vi.fn(), patch: vi.fn(), delete: vi.fn() } }));

afterEach(() => vi.clearAllMocks());

describe('adminCatalogApi serviceCategories', () => {
    it('lists without a query string when no filters are provided', async () => {
        vi.mocked(http.get).mockResolvedValue([]);

        await adminCatalogApi.serviceCategories.list();

        expect(http.get).toHaveBeenCalledWith('/admin/catalog/service-categories');
    });

    it('lists with filters encoded as a query string', async () => {
        vi.mocked(http.get).mockResolvedValue([]);

        await adminCatalogApi.serviceCategories.list({ q: 'corte', active: false });

        expect(http.get).toHaveBeenCalledWith('/admin/catalog/service-categories?q=corte&active=false');
    });

    it('creates, updates, changes status and deletes', async () => {
        vi.mocked(http.post).mockResolvedValue({});
        vi.mocked(http.put).mockResolvedValue({});
        vi.mocked(http.patch).mockResolvedValue({});
        vi.mocked(http.delete).mockResolvedValue(undefined);

        const payload = { name: 'Cabello', description: 'Corte y peinado', active: true };

        await adminCatalogApi.serviceCategories.create(payload);
        await adminCatalogApi.serviceCategories.update(5, payload);
        await adminCatalogApi.serviceCategories.setStatus(5, false);
        await adminCatalogApi.serviceCategories.remove(5);

        expect(http.post).toHaveBeenCalledWith('/admin/catalog/service-categories', payload);
        expect(http.put).toHaveBeenCalledWith('/admin/catalog/service-categories/5', payload);
        expect(http.patch).toHaveBeenCalledWith('/admin/catalog/service-categories/5/status', { active: false });
        expect(http.delete).toHaveBeenCalledWith('/admin/catalog/service-categories/5');
    });
});

describe('adminCatalogApi services', () => {
    it('lists with the category filter', async () => {
        vi.mocked(http.get).mockResolvedValue([]);

        await adminCatalogApi.services.list({ service_category_id: 7, active: true });

        expect(http.get).toHaveBeenCalledWith('/admin/catalog/services?service_category_id=7&active=true');
    });

    it('creates, updates, changes status and deletes with the real payload', async () => {
        vi.mocked(http.post).mockResolvedValue({});
        vi.mocked(http.put).mockResolvedValue({});
        vi.mocked(http.patch).mockResolvedValue({});
        vi.mocked(http.delete).mockResolvedValue(undefined);

        const payload = {
            service_category_id: 1,
            name: 'Balayage',
            slug: 'balayage',
            description: null,
            duration_minutes: 180,
            pricing_type: 'fixed' as const,
            price: '1800.00',
            active: true,
        };

        await adminCatalogApi.services.create(payload);
        await adminCatalogApi.services.update(2, payload);
        await adminCatalogApi.services.setStatus(2, false);
        await adminCatalogApi.services.remove(2);

        expect(http.post).toHaveBeenCalledWith('/admin/catalog/services', payload);
        expect(http.put).toHaveBeenCalledWith('/admin/catalog/services/2', payload);
        expect(http.patch).toHaveBeenCalledWith('/admin/catalog/services/2/status', { active: false });
        expect(http.delete).toHaveBeenCalledWith('/admin/catalog/services/2');
    });
});

describe('adminCatalogApi productCategories', () => {
    it('lists and mutates with the real routes', async () => {
        vi.mocked(http.get).mockResolvedValue([]);
        vi.mocked(http.post).mockResolvedValue({});
        vi.mocked(http.put).mockResolvedValue({});
        vi.mocked(http.patch).mockResolvedValue({});
        vi.mocked(http.delete).mockResolvedValue(undefined);

        await adminCatalogApi.productCategories.list({ q: 'capilar' });
        await adminCatalogApi.productCategories.create({ name: 'Cuidado capilar' });
        await adminCatalogApi.productCategories.update(3, { name: 'Cuidado capilar', active: false });
        await adminCatalogApi.productCategories.setStatus(3, true);
        await adminCatalogApi.productCategories.remove(3);

        expect(http.get).toHaveBeenCalledWith('/admin/catalog/product-categories?q=capilar');
        expect(http.post).toHaveBeenCalledWith('/admin/catalog/product-categories', { name: 'Cuidado capilar' });
        expect(http.put).toHaveBeenCalledWith('/admin/catalog/product-categories/3', { name: 'Cuidado capilar', active: false });
        expect(http.patch).toHaveBeenCalledWith('/admin/catalog/product-categories/3/status', { active: true });
        expect(http.delete).toHaveBeenCalledWith('/admin/catalog/product-categories/3');
    });
});

describe('adminCatalogApi products', () => {
    it('lists with the product category filter', async () => {
        vi.mocked(http.get).mockResolvedValue([]);

        await adminCatalogApi.products.list({ product_category_id: 4 });

        expect(http.get).toHaveBeenCalledWith('/admin/catalog/products?product_category_id=4');
    });

    it('creates, updates, changes status and deletes', async () => {
        vi.mocked(http.post).mockResolvedValue({});
        vi.mocked(http.put).mockResolvedValue({});
        vi.mocked(http.patch).mockResolvedValue({});
        vi.mocked(http.delete).mockResolvedValue(undefined);

        const payload = { product_category_id: 1, name: 'Shampoo', price: '199.00', stock: 5, active: true };

        await adminCatalogApi.products.create(payload);
        await adminCatalogApi.products.update(8, payload);
        await adminCatalogApi.products.setStatus(8, false);
        await adminCatalogApi.products.remove(8);

        expect(http.post).toHaveBeenCalledWith('/admin/catalog/products', payload);
        expect(http.put).toHaveBeenCalledWith('/admin/catalog/products/8', payload);
        expect(http.patch).toHaveBeenCalledWith('/admin/catalog/products/8/status', { active: false });
        expect(http.delete).toHaveBeenCalledWith('/admin/catalog/products/8');
    });
});
