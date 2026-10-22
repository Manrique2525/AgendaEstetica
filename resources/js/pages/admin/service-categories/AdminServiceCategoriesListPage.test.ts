import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import router from '../../../router';
import AdminServiceCategoriesListPage from './AdminServiceCategoriesListPage.vue';

const catalog = vi.hoisted(() => ({
    serviceCategories: {
        list: vi.fn(),
        create: vi.fn(),
        update: vi.fn(),
        setStatus: vi.fn(),
        remove: vi.fn(),
    },
}));

vi.mock('../../../services/api/adminCatalog', () => ({ adminCatalogApi: catalog }));

const global = { plugins: [router] };

const categoryList = [
    { id: 1, name: 'Cabello', slug: 'cabello', description: null, active: true, services_count: 3 },
    { id: 2, name: 'Uñas', slug: 'unas', description: null, active: false, services_count: 0 },
];

describe('AdminServiceCategoriesListPage', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        catalog.serviceCategories.list.mockResolvedValue(categoryList);
    });

    it('renders categories from a plain array response', async () => {
        const wrapper = mount(AdminServiceCategoriesListPage, { global });
        await flushPromises();

        expect(wrapper.text()).toContain('Cabello');
        expect(wrapper.text()).toContain('Uñas');
        expect(wrapper.text()).not.toContain('No pudimos cargar las categorías.');
    });

    it('shows an empty state when there are no categories', async () => {
        catalog.serviceCategories.list.mockResolvedValue([]);

        const wrapper = mount(AdminServiceCategoriesListPage, { global });
        await flushPromises();

        expect(wrapper.text()).toContain('Sin categorías registradas.');
    });

    it('shows an error message when the request fails', async () => {
        catalog.serviceCategories.list.mockRejectedValue(new Error('boom'));

        const wrapper = mount(AdminServiceCategoriesListPage, { global });
        await flushPromises();

        expect(wrapper.text()).toContain('No pudimos cargar las categorías.');
    });

    it('toggles the status of a category', async () => {
        catalog.serviceCategories.setStatus.mockResolvedValue(categoryList[0]);

        const wrapper = mount(AdminServiceCategoriesListPage, { global });
        await flushPromises();

        const toggle = wrapper.findAll('button').find((button) => button.text().includes('Desactivar'));
        await toggle!.trigger('click');
        await flushPromises();

        expect(catalog.serviceCategories.setStatus).toHaveBeenCalledWith(1, false);
    });
});
