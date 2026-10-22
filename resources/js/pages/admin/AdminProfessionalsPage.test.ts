import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import router from '../../router';
import { ApiError } from '../../services/http';
import AdminProfessionalsPage from './AdminProfessionalsPage.vue';

const api = vi.hoisted(() => ({
    professionals: {
        list: vi.fn(),
        create: vi.fn(),
        update: vi.fn(),
        setStatus: vi.fn(),
        remove: vi.fn(),
    },
}));

const catalog = vi.hoisted(() => ({ services: { list: vi.fn() } }));

vi.mock('../../services/api/adminOperations', () => ({ adminOperationsApi: api }));
vi.mock('../../services/api/adminCatalog', () => ({ adminCatalogApi: catalog }));

const global = { plugins: [router] };

const serviceList = [
    { id: 6, name: 'Peinado', active: true },
    { id: 1, name: 'Corte', active: true },
];

const professionalList = [
    { id: 1, name: 'Ana', active: true, services: [{ id: 6, name: 'Peinado', active: true }], created_at: null, updated_at: null },
];

describe('AdminProfessionalsPage', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        api.professionals.list.mockResolvedValue(professionalList);
        api.professionals.create.mockResolvedValue(professionalList[0]);
        api.professionals.update.mockResolvedValue(professionalList[0]);
        api.professionals.setStatus.mockResolvedValue(professionalList[0]);
        api.professionals.remove.mockResolvedValue(undefined);
        catalog.services.list.mockResolvedValue({
            data: serviceList,
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: serviceList.length,
        });
    });

    it('lists professionals with their assigned services', async () => {
        const wrapper = mount(AdminProfessionalsPage, { global });
        await flushPromises();

        expect(wrapper.text()).toContain('Ana');
        expect(wrapper.text()).toContain('Peinado');
    });

    it('shows an empty state when there are no professionals', async () => {
        api.professionals.list.mockResolvedValue([]);

        const wrapper = mount(AdminProfessionalsPage, { global });
        await flushPromises();

        expect(wrapper.text()).toContain('Sin profesionales registrados.');
    });

    it('creates a professional with the selected services', async () => {
        const wrapper = mount(AdminProfessionalsPage, { global });
        await flushPromises();

        await wrapper.get('#professional-name').setValue('Beatriz');
        const peinado = wrapper.findAll('input[type="checkbox"]').find((input) => input.element.parentElement?.textContent?.includes('Peinado'));
        await peinado!.setValue(true);
        await wrapper.get('form').trigger('submit');
        await flushPromises();

        expect(api.professionals.create).toHaveBeenCalledWith({
            name: 'Beatriz',
            active: true,
            service_ids: [6],
        });
        expect(wrapper.text()).toContain('Profesional creado.');
    });

    it('validates the name before saving', async () => {
        const wrapper = mount(AdminProfessionalsPage, { global });
        await flushPromises();

        await wrapper.get('#professional-name').setValue('');
        await wrapper.get('form').trigger('submit');
        await flushPromises();

        expect(wrapper.text()).toContain('El nombre es obligatorio.');
        expect(api.professionals.create).not.toHaveBeenCalled();
    });

    it('toggles professional status', async () => {
        const wrapper = mount(AdminProfessionalsPage, { global });
        await flushPromises();

        const toggle = wrapper.findAll('button').find((button) => button.text() === 'Desactivar')!;
        await toggle.trigger('click');
        await flushPromises();

        expect(api.professionals.setStatus).toHaveBeenCalledWith(1, false);
    });

    it('requires confirmation before deleting', async () => {
        const wrapper = mount(AdminProfessionalsPage, { global });
        await flushPromises();

        const remove = wrapper.findAll('button').find((button) => button.text() === 'Eliminar')!;
        await remove.trigger('click');
        await flushPromises();

        expect(api.professionals.remove).not.toHaveBeenCalled();

        expect(wrapper.find('[role="dialog"]').exists()).toBe(true);

        const confirmButton = wrapper.find('[role="dialog"] button:last-child');
        await confirmButton.trigger('click');
        await flushPromises();

        expect(api.professionals.remove).toHaveBeenCalledWith(1);
    });

    it('explains the 409 conflict when the professional has appointments', async () => {
        api.professionals.remove.mockRejectedValue(new ApiError(409, 'Conflict', 'professional_has_appointments'));

        const wrapper = mount(AdminProfessionalsPage, { global });
        await flushPromises();

        const remove = wrapper.findAll('button').find((button) => button.text() === 'Eliminar')!;
        await remove.trigger('click');
        await flushPromises();

        const confirmButton = wrapper.find('[role="dialog"] button:last-child');
        await confirmButton.trigger('click');
        await flushPromises();

        expect(wrapper.text()).toContain('tiene citas asociadas');
    });

    it('applies filters when requesting the list', async () => {
        const wrapper = mount(AdminProfessionalsPage, { global });
        await flushPromises();

        await wrapper.get('#filter-q').setValue('ana');
        await wrapper.find('form:last-of-type').trigger('submit');
        await flushPromises();

        expect(api.professionals.list).toHaveBeenLastCalledWith({ q: 'ana' });
    });
});
