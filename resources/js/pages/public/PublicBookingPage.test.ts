import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import PublicBookingPage from './PublicBookingPage.vue';
import { ApiError } from '../../services/http';

const api = vi.hoisted(() => ({
    getContext: vi.fn(),
    listServices: vi.fn(),
    listProfessionals: vi.fn(),
    getAvailability: vi.fn(),
    createAppointment: vi.fn(),
}));

vi.mock('../../services/api/publicBooking', () => ({ publicBookingApi: api }));

const service = { id: 1, name: 'Corte', duration_minutes: 60, pricing_type: 'fixed' as const, price: '250.00', pricing_display: '$250.00', category: null };
const professional = { id: 2, name: 'Alex' };
const slot = { starts_at: '2026-09-10T16:00:00Z', ends_at: '2026-09-10T17:00:00Z', local_start: '10:00', local_end: '11:00' };

describe('PublicBookingPage', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        api.getContext.mockResolvedValue({ timezone: 'America/Mexico_City' });
        api.listServices.mockResolvedValue([service]);
        api.listProfessionals.mockResolvedValue([professional]);
        api.getAvailability.mockResolvedValue({ date: '2026-09-10', timezone: 'America/Mexico_City', slots: [slot] });
        api.createAppointment.mockResolvedValue({ message: 'Tu cita fue confirmada.', status: 'confirmed', service: { name: 'Corte' }, professional: { name: 'Alex' }, starts_at: slot.starts_at, ends_at: slot.ends_at });
    });

    it('completes the guest flow with the public payload and idempotency key', async () => {
        const wrapper = mount(PublicBookingPage);
        await flushPromises();
        await wrapper.find('section button').trigger('click');
        await wrapper.findAll('button').find((button) => button.text() === 'Continuar')!.trigger('click');
        await flushPromises();
        await wrapper.find('section button').trigger('click');
        await wrapper.findAll('button').find((button) => button.text() === 'Continuar')!.trigger('click');
        await wrapper.get('#booking-date').setValue('2026-09-10');
        await flushPromises();
        await wrapper.find('section button').trigger('click');
        await wrapper.findAll('button').find((button) => button.text() === 'Continuar')!.trigger('click');
        await wrapper.get('#booking-name').setValue(' Ana  López ');
        await wrapper.get('#booking-phone').setValue('555 010 20');
        await wrapper.findAll('button').find((button) => button.text() === 'Continuar')!.trigger('click');
        await wrapper.findAll('button').find((button) => button.text() === 'Confirmar cita')!.trigger('click');
        await flushPromises();

        expect(api.createAppointment).toHaveBeenCalledWith({ service_id: 1, professional_id: 2, starts_at: slot.starts_at, name: 'Ana  López', phone: '555 010 20' }, expect.any(String));
        expect(wrapper.text()).toContain('Cita confirmada');
    });

    it('refreshes availability after a stale slot conflict without claiming success', async () => {
        api.createAppointment.mockRejectedValue(new ApiError(409, 'unavailable', 'appointment_unavailable'));
        const wrapper = mount(PublicBookingPage);
        await flushPromises();
        await wrapper.find('section button').trigger('click');
        await wrapper.findAll('button').find((button) => button.text() === 'Continuar')!.trigger('click');
        await flushPromises();
        await wrapper.find('section button').trigger('click');
        await wrapper.findAll('button').find((button) => button.text() === 'Continuar')!.trigger('click');
        await wrapper.get('#booking-date').setValue('2026-09-10');
        await flushPromises();
        await wrapper.find('section button').trigger('click');
        await wrapper.findAll('button').find((button) => button.text() === 'Continuar')!.trigger('click');
        await wrapper.get('#booking-name').setValue('Ana');
        await wrapper.get('#booking-phone').setValue('55501020');
        await wrapper.findAll('button').find((button) => button.text() === 'Continuar')!.trigger('click');
        await wrapper.findAll('button').find((button) => button.text() === 'Confirmar cita')!.trigger('click');
        await flushPromises();

        expect(wrapper.text()).toContain('Ese horario ya no está disponible');
        expect(wrapper.text()).not.toContain('Cita confirmada');
        expect(api.getAvailability).toHaveBeenCalledTimes(2);
    });
});
