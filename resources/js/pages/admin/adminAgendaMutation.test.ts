import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import router from '../../router';
import AdminAgendaDetailPage from './AdminAgendaDetailPage.vue';
import AdminAgendaPage from './AdminAgendaPage.vue';

const api = vi.hoisted(() => ({
    getContext: vi.fn(),
    listAppointments: vi.fn(),
    getAppointment: vi.fn(),
    searchCustomers: vi.fn(),
    listServices: vi.fn(),
    listProfessionals: vi.fn(),
    createAppointment: vi.fn(),
    rescheduleAppointment: vi.fn(),
}));

vi.mock('../../services/api/adminAgenda', () => ({ adminAgendaApi: api }));

const adminGlobal = { plugins: [router] };

describe('Admin Agenda mutation forms', () => {
    beforeEach(() => {
        api.getContext.mockResolvedValue({ timezone: 'America/New_York' });
        api.listAppointments.mockResolvedValue([]);
        api.searchCustomers.mockResolvedValue([{ id: 1, name: 'Ana', phone: '+521111111111' }]);
        api.listServices.mockResolvedValue([{ id: 1, name: 'Corte', duration_minutes: 60, pricing_type: 'fixed', price: '100.00', category: null }]);
        api.listProfessionals.mockResolvedValue([{ id: 1, name: 'Alex' }]);
        api.createAppointment.mockResolvedValue({});
        api.rescheduleAppointment.mockResolvedValue({});
        api.getAppointment.mockResolvedValue({
            id: 1,
            status: 'confirmed',
            starts_at: '2026-03-08T05:30:00Z',
            ends_at: '2026-03-08T06:30:00Z',
            duration_minutes: 60,
            customer: { id: 1, name: 'Ana', phone: '+521111111111' },
            service: { id: 1, name: 'Corte', category: null },
            professional: { id: 1, name: 'Alex' },
            history: [],
        });
    });

    it('renders labelled Create controls and submits only the approved payload', async () => {
        const wrapper = mount(AdminAgendaPage, { global: adminGlobal });
        await flushPromises();
        await wrapper.get('button').trigger('click');

        expect(wrapper.find('#create-customer').exists()).toBe(true);
        expect(wrapper.find('label[for="create-customer"]').exists()).toBe(true);
        expect(wrapper.find('label[for="create-service"]').exists()).toBe(true);
        expect(wrapper.find('label[for="create-professional"]').exists()).toBe(true);
        expect(wrapper.find('label[for="create-date"]').exists()).toBe(true);
        expect(wrapper.find('label[for="create-time"]').exists()).toBe(true);

        await wrapper.get('#create-customer').setValue('Ana');
        await wrapper.get('#create-customer').trigger('input');
        await flushPromises();
        await wrapper.find('[role="listbox"] button').trigger('click');
        await wrapper.get('#create-service').setValue('1');
        await wrapper.get('#create-professional').setValue('1');
        await wrapper.get('#create-date').setValue('2026-03-08');
        await wrapper.get('#create-time').setValue('00:30');
        await wrapper.get('form').trigger('submit');
        await flushPromises();

        expect(api.createAppointment).toHaveBeenCalledWith({
            customer_id: 1,
            service_id: 1,
            professional_id: 1,
            starts_at: '2026-03-08T05:30:00.000Z',
            ends_at: '2026-03-08T06:30:00.000Z',
        });
        expect(wrapper.text()).toContain('Cita creada correctamente.');
        const buttonText = wrapper.findAll('button').map((button) => button.text()).join(' ');
        expect(buttonText).not.toContain('Cancelar');
        expect(buttonText).not.toContain('Completar');
        expect(buttonText).not.toContain('No asistió');
    });

    it('renders accessible Reschedule controls with immutable appointment context', async () => {
        const wrapper = mount(AdminAgendaDetailPage, { global: adminGlobal });
        await flushPromises();
        await wrapper.findAll('button').find((button) => button.text() === 'Reprogramar')?.trigger('click');

        expect(wrapper.find('label[for="reschedule-professional"]').exists()).toBe(true);
        expect(wrapper.find('label[for="reschedule-date"]').exists()).toBe(true);
        expect(wrapper.find('label[for="reschedule-time"]').exists()).toBe(true);
        expect(wrapper.text()).toContain('Ana');
        expect(wrapper.text()).toContain('60 minutos');
        expect(wrapper.text()).not.toContain('Cancelar');
        expect(wrapper.text()).not.toContain('Completar');
        expect(wrapper.text()).not.toContain('No asistió');
    });
});
