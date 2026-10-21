import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import router from '../../router';
import { ApiError } from '../../services/http';
import AdminBusinessProfilePage from './AdminBusinessProfilePage.vue';

const api = vi.hoisted(() => ({
    businessProfile: { get: vi.fn(), update: vi.fn() },
    professionals: { list: vi.fn() },
}));

vi.mock('../../services/api/adminOperations', () => ({ adminOperationsApi: api }));

const global = { plugins: [router] };

const profile = {
    id: 1,
    name: 'Salón Yaris',
    phone: '+529930000000',
    timezone: 'America/Mexico_City',
    max_simultaneous_clients: 2,
    hours: [
        { id: 1, weekday: 1, interval_order: 1, opens_at: '10:00', closes_at: '20:00' },
    ],
    created_at: null,
    updated_at: null,
};

describe('AdminBusinessProfilePage', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        api.businessProfile.get.mockResolvedValue(profile);
        api.businessProfile.update.mockResolvedValue(profile);
    });

    it('loads the current profile into the form', async () => {
        const wrapper = mount(AdminBusinessProfilePage, { global });
        await flushPromises();

        expect((wrapper.get('#business-name').element as HTMLInputElement).value).toBe('Salón Yaris');
        expect((wrapper.get('#business-timezone').element as HTMLInputElement).value).toBe('America/Mexico_City');
        expect(wrapper.text()).toContain('Horarios generales de atención');
    });

    it('validates client-side before calling the API', async () => {
        const wrapper = mount(AdminBusinessProfilePage, { global });
        await flushPromises();

        await wrapper.get('#business-name').setValue('');
        await wrapper.get('form').trigger('submit');
        await flushPromises();

        expect(wrapper.text()).toContain('El nombre comercial es obligatorio.');
        expect(api.businessProfile.update).not.toHaveBeenCalled();
    });

    it('saves only the accepted payload and shows success', async () => {
        const wrapper = mount(AdminBusinessProfilePage, { global });
        await flushPromises();

        await wrapper.get('#business-timezone').setValue('America/Tijuana');
        await wrapper.get('form').trigger('submit');
        await flushPromises();

        expect(api.businessProfile.update).toHaveBeenCalledWith({
            name: 'Salón Yaris',
            phone: '+529930000000',
            timezone: 'America/Tijuana',
            max_simultaneous_clients: 2,
            hours: [{ weekday: 1, opens_at: '10:00', closes_at: '20:00' }],
        });
        expect(wrapper.text()).toContain('Configuración del negocio guardada.');
    });

    it('surfaces server validation errors', async () => {
        api.businessProfile.update.mockRejectedValue(
            new ApiError(422, 'The given data was invalid.', undefined, { timezone: ['La zona horaria no es válida.'] }),
        );

        const wrapper = mount(AdminBusinessProfilePage, { global });
        await flushPromises();

        await wrapper.get('form').trigger('submit');
        await flushPromises();

        expect(wrapper.text()).toContain('La zona horaria no es válida.');
    });

    it('shows a useful message when no profile exists yet', async () => {
        api.businessProfile.get.mockRejectedValue(new ApiError(404, 'Not found'));

        const wrapper = mount(AdminBusinessProfilePage, { global });
        await flushPromises();

        expect(wrapper.text()).toContain('Aún no existe un perfil de negocio.');
    });
});
