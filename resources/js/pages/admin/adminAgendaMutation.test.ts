import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { nextTick } from 'vue';
import router from '../../router';
import AdminAgendaDetailPage from './AdminAgendaDetailPage.vue';
import AdminAgendaPage from './AdminAgendaPage.vue';
import { ApiError } from '../../services/http';

const api = vi.hoisted(() => ({
    getContext: vi.fn(),
    listAppointments: vi.fn(),
    getAppointment: vi.fn(),
    searchCustomers: vi.fn(),
    listServices: vi.fn(),
    listProfessionals: vi.fn(),
    createAppointment: vi.fn(),
    rescheduleAppointment: vi.fn(),
    cancelAppointment: vi.fn(),
    completeAppointment: vi.fn(),
    markAppointmentNoShow: vi.fn(),
}));

vi.mock('../../services/api/adminAgenda', () => ({ adminAgendaApi: api }));

const adminGlobal = { plugins: [router] };

describe('Admin Agenda mutation forms', () => {
    beforeEach(() => {
        vi.clearAllMocks();
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
        expect(wrapper.findAll('button').some((button) => button.text() === 'Cancelar')).toBe(true);
        expect(wrapper.findAll('button').some((button) => button.text() === 'Completar')).toBe(true);
        expect(wrapper.findAll('button').some((button) => button.text() === 'Marcar como no asistió')).toBe(true);
    });

    it.each([
        ['Cancelar', 'cancelAppointment', 'Cancelada', 'Cancelar cita'],
        ['Completar', 'completeAppointment', 'Completada', 'Completar cita'],
        ['Marcar como no asistió', 'markAppointmentNoShow', 'No asistió', 'Marcar no asistió'],
    ])('%s requires explicit confirmation and renders authoritative success', async (label, method, statusLabel, confirmLabel) => {
        api[method as 'cancelAppointment' | 'completeAppointment' | 'markAppointmentNoShow'].mockResolvedValue({
            ...await api.getAppointment(),
            status: statusLabel === 'Cancelada' ? 'cancelled' : statusLabel === 'Completada' ? 'completed' : 'no_show',
            history: [{ id: 2, event_type: 'status_changed', from_status: 'confirmed', to_status: statusLabel === 'Cancelada' ? 'cancelled' : statusLabel === 'Completada' ? 'completed' : 'no_show' }],
        });
        const wrapper = mount(AdminAgendaDetailPage, { global: adminGlobal });
        await flushPromises();

        const actionButton = wrapper.findAll('button').find((button) => button.text() === label);
        expect(actionButton).toBeDefined();
        await actionButton?.trigger('click');
        await flushPromises();

        expect(wrapper.find('[role="dialog"]').exists()).toBe(true);
        expect(api[method as 'cancelAppointment' | 'completeAppointment' | 'markAppointmentNoShow']).not.toHaveBeenCalled();
        await wrapper.find('[role="dialog"]').findAll('button').find((button) => button.text() === confirmLabel)?.trigger('click');
        await flushPromises();

        expect(api[method as 'cancelAppointment' | 'completeAppointment' | 'markAppointmentNoShow']).toHaveBeenCalledWith(1);
        expect(wrapper.text()).toContain('Estado de la cita actualizado correctamente.');
        expect(wrapper.text()).toContain(statusLabel);
    });

    it('refreshes authoritative detail after a terminal 409 and hides stale actions', async () => {
        api.cancelAppointment.mockRejectedValue(new ApiError(409, 'conflict', 'appointment_state_conflict'));
        const confirmedAppointment = {
            id: 1,
            status: 'confirmed' as const,
            starts_at: '2026-03-08T05:30:00Z',
            ends_at: '2026-03-08T06:30:00Z',
            duration_minutes: 60,
            customer: { id: 1, name: 'Ana', phone: '+521111111111' },
            service: { id: 1, name: 'Corte', category: null },
            professional: { id: 1, name: 'Alex' },
            history: [],
        };
        api.getAppointment
            .mockResolvedValueOnce(confirmedAppointment)
            .mockResolvedValueOnce({ ...confirmedAppointment, status: 'completed', history: [{ id: 2, event_type: 'status_changed', from_status: 'confirmed', to_status: 'completed' }] });
        const wrapper = mount(AdminAgendaDetailPage, { global: adminGlobal });
        await flushPromises();
        await wrapper.findAll('button').find((button) => button.text() === 'Cancelar')?.trigger('click');
        const confirmButton = wrapper.find('[role="dialog"]').findAll('button').find((button) => button.text() === 'Cancelar cita');
        expect(confirmButton).toBeDefined();
        await confirmButton!.trigger('click');
        await flushPromises();

        expect(wrapper.text()).toContain('Se actualizó el detalle.');
        expect(wrapper.text()).toContain('Completada');
        expect(wrapper.findAll('button').some((button) => button.text() === 'Completar')).toBe(false);
        expect(wrapper.findAll('button').some((button) => button.text() === 'Cancelar')).toBe(false);
    });

    it.each([
        ['cancelled', 'Cancelada'],
        ['completed', 'Completada'],
        ['no_show', 'No asistió'],
    ])('renders %s detail as read-only', async (status, label) => {
        api.getAppointment.mockResolvedValue({
            id: 1,
            status,
            starts_at: '2026-03-08T05:30:00Z',
            ends_at: '2026-03-08T06:30:00Z',
            duration_minutes: 60,
            customer: { id: 1, name: 'Ana', phone: '+521111111111' },
            service: { id: 1, name: 'Corte', category: null },
            professional: { id: 1, name: 'Alex' },
            history: [],
        });
        const wrapper = mount(AdminAgendaDetailPage, { global: adminGlobal });
        await flushPromises();

        expect(wrapper.text()).toContain(label);
        expect(wrapper.findAll('button').some((button) => button.text() === 'Reprogramar')).toBe(false);
        expect(wrapper.findAll('button').some((button) => button.text() === 'Cancelar')).toBe(false);
        expect(wrapper.findAll('button').some((button) => button.text() === 'Completar')).toBe(false);
        expect(wrapper.findAll('button').some((button) => button.text() === 'Marcar como no asistió')).toBe(false);
    });

    it('keeps terminal confirmation keyboard accessible and prevents duplicate submission', async () => {
        let release: ((value: unknown) => void) | undefined;
        api.cancelAppointment.mockReturnValue(new Promise((resolve) => { release = resolve; }));
        const wrapper = mount(AdminAgendaDetailPage, { global: adminGlobal });
        await flushPromises();
        await wrapper.findAll('button').find((button) => button.text() === 'Cancelar')?.trigger('click');
        await flushPromises();

        const dialog = wrapper.get('[role="dialog"]');
        expect(dialog.attributes('aria-modal')).toBe('true');
        expect(dialog.attributes('aria-labelledby')).toBeTruthy();
        expect(dialog.attributes('tabindex')).toBe('-1');

        window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
        await nextTick();
        expect(wrapper.find('[role="dialog"]').exists()).toBe(false);
        await wrapper.findAll('button').find((button) => button.text() === 'Cancelar')?.trigger('click');
        await wrapper.get('[role="dialog"]').findAll('button').find((button) => button.text() === 'Cancelar cita')?.trigger('click');
        await wrapper.get('[role="dialog"]').findAll('button').find((button) => button.text() === 'Cancelar cita')?.trigger('click');

        expect(api.cancelAppointment).toHaveBeenCalledTimes(1);
        release?.({ ...await api.getAppointment(), status: 'cancelled', history: [] });
        await flushPromises();
    });
});
