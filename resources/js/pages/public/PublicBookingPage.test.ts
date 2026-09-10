import { flushPromises, mount, type VueWrapper } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { ApiError } from '../../services/http';
import PublicLayout from '../../layouts/PublicLayout.vue';
import PublicBookingPage from './PublicBookingPage.vue';

const api = vi.hoisted(() => ({
    getContext: vi.fn(),
    listServices: vi.fn(),
    listProfessionals: vi.fn(),
    getAvailability: vi.fn(),
    createAppointment: vi.fn(),
}));

vi.mock('../../services/api/publicBooking', () => ({ publicBookingApi: api }));

const services = [
    { id: 1, name: 'Corte', duration_minutes: 60, pricing_type: 'fixed' as const, price: '250.00', pricing_display: '$250.00', category: null },
    { id: 2, name: 'Color', duration_minutes: 90, pricing_type: 'starting_from' as const, price: '500.00', pricing_display: 'Desde $500.00', category: null },
    { id: 3, name: 'Consulta', duration_minutes: 30, pricing_type: 'variable' as const, price: null, pricing_display: 'Precio variable', category: null },
];
const professionals = [{ id: 2, name: 'Alex' }, { id: 3, name: 'Sam' }];
const slot = { starts_at: '2026-09-10T16:00:00Z', ends_at: '2026-09-10T17:00:00Z', local_start: '10:00', local_end: '11:00' };

function continueButton(wrapper: VueWrapper) {
    return wrapper.findAll('button').find((button) => button.text() === 'Continuar')!;
}

async function reachReview(wrapper: VueWrapper): Promise<void> {
    await wrapper.find('section button').trigger('click');
    await continueButton(wrapper).trigger('click');
    await flushPromises();
    await wrapper.find('section button').trigger('click');
    await continueButton(wrapper).trigger('click');
    await wrapper.get('#booking-date').setValue('2026-09-10');
    await flushPromises();
    await wrapper.find('section button').trigger('click');
    await continueButton(wrapper).trigger('click');
    await wrapper.get('#booking-name').setValue('Ana');
    await wrapper.get('#booking-phone').setValue('55501020');
    await continueButton(wrapper).trigger('click');
}

describe('PublicBookingPage', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        api.getContext.mockResolvedValue({ timezone: 'America/Mexico_City' });
        api.listServices.mockResolvedValue(services);
        api.listProfessionals.mockResolvedValue(professionals);
        api.getAvailability.mockResolvedValue({ date: '2026-09-10', timezone: 'America/Mexico_City', slots: [slot] });
        api.createAppointment.mockResolvedValue({ message: 'Tu cita fue confirmada.', status: 'confirmed', service: { name: 'Corte' }, professional: { name: 'Alex' }, starts_at: slot.starts_at, ends_at: slot.ends_at });
    });

    it('renders publicly with PublicLayout and completes the guest flow with the exact payload', async () => {
        const wrapper = mount(PublicBookingPage);
        await flushPromises();
        expect(wrapper.findComponent(PublicLayout).exists()).toBe(true);
        await reachReview(wrapper);
        await wrapper.findAll('button').find((button) => button.text() === 'Confirmar cita')!.trigger('click');
        await flushPromises();

        expect(api.createAppointment).toHaveBeenCalledWith({ service_id: 1, professional_id: 2, starts_at: slot.starts_at, name: 'Ana', phone: '55501020' }, expect.any(String));
        expect(wrapper.text()).toContain('Cita confirmada');
        expect(wrapper.text()).not.toMatch(/Customer|Appointment ID|cancelar|reprogramar|lookup/i);
    });

    it('renders every authoritative pricing label without adding a payment action', async () => {
        const wrapper = mount(PublicBookingPage);
        await flushPromises();
        expect(wrapper.text()).toContain('$250.00');
        await wrapper.findAll('section button')[1].trigger('click');
        await flushPromises();
        expect(wrapper.text()).toContain('Desde $500.00');
        await wrapper.findAll('section button')[2].trigger('click');
        expect(wrapper.text()).toContain('Precio variable');
        expect(wrapper.text()).not.toContain('Pagar');
    });

    it('resets dependent state when Service or Professional changes', async () => {
        const wrapper = mount(PublicBookingPage);
        await flushPromises();
        await wrapper.find('section button').trigger('click');
        await continueButton(wrapper).trigger('click');
        await flushPromises();
        await wrapper.find('section button').trigger('click');
        await continueButton(wrapper).trigger('click');
        await wrapper.get('#booking-date').setValue('2026-09-10');
        await flushPromises();
        await wrapper.find('section button').trigger('click');
        expect(wrapper.text()).toContain('10:00');
        await wrapper.findAll('button').find((button) => button.text() === 'Atrás')!.trigger('click');
        await wrapper.findAll('button').find((button) => button.text() === 'Atrás')!.trigger('click');
        await wrapper.findAll('section button')[1].trigger('click');
        await flushPromises();
        expect(wrapper.text()).not.toContain('10:00');
        await continueButton(wrapper).trigger('click');
        expect(wrapper.text()).toContain('Alex');
        await wrapper.findAll('button').find((button) => button.text() === 'Sam')!.trigger('click');
        await continueButton(wrapper).trigger('click');
        await wrapper.get('#booking-date').setValue('2026-09-10');
        await flushPromises();
        expect(api.getAvailability).toHaveBeenCalledTimes(2);
    });

    it('uses the same key and payload for an uncertain unchanged retry', async () => {
        api.createAppointment.mockRejectedValueOnce(new Error('Network unavailable')).mockResolvedValueOnce({ message: 'Tu cita fue confirmada.', status: 'confirmed', service: { name: 'Corte' }, professional: { name: 'Alex' }, starts_at: slot.starts_at, ends_at: slot.ends_at });
        const wrapper = mount(PublicBookingPage);
        await flushPromises();
        await reachReview(wrapper);
        await wrapper.findAll('button').find((button) => button.text() === 'Confirmar cita')!.trigger('click');
        await flushPromises();
        const retry = wrapper.findAll('button').find((button) => button.text() === 'Confirmar cita')!;
        await retry.trigger('click');
        await flushPromises();

        expect(api.createAppointment).toHaveBeenCalledTimes(2);
        expect(api.createAppointment.mock.calls[1]).toEqual(api.createAppointment.mock.calls[0]);
        expect(wrapper.text()).toContain('Cita confirmada');
    });

    it('creates a new key when a fingerprint field changes', async () => {
        api.createAppointment.mockRejectedValueOnce(new Error('Network unavailable')).mockResolvedValueOnce({ message: 'Tu cita fue confirmada.', status: 'confirmed', service: { name: 'Corte' }, professional: { name: 'Alex' }, starts_at: slot.starts_at, ends_at: slot.ends_at });
        const wrapper = mount(PublicBookingPage);
        await flushPromises();
        await reachReview(wrapper);
        await wrapper.findAll('button').find((button) => button.text() === 'Confirmar cita')!.trigger('click');
        await flushPromises();
        await wrapper.findAll('button').find((button) => button.text() === 'Atrás')!.trigger('click');
        await wrapper.get('#booking-name').setValue('Beatriz');
        await continueButton(wrapper).trigger('click');
        await wrapper.findAll('button').find((button) => button.text() === 'Confirmar cita')!.trigger('click');
        await flushPromises();

        expect(api.createAppointment.mock.calls[1][1]).not.toBe(api.createAppointment.mock.calls[0][1]);
    });

    it('allows only one POST when submit is triggered twice while pending', async () => {
        let release!: (value: unknown) => void;
        api.createAppointment.mockReturnValue(new Promise((resolve) => { release = resolve; }));
        const wrapper = mount(PublicBookingPage);
        await flushPromises();
        await reachReview(wrapper);
        const submit = wrapper.findAll('button').find((button) => button.text() === 'Confirmar cita')!;
        await submit.trigger('click');
        await submit.trigger('click');
        expect(api.createAppointment).toHaveBeenCalledTimes(1);
        release({ message: 'Tu cita fue confirmada.', status: 'confirmed', service: { name: 'Corte' }, professional: { name: 'Alex' }, starts_at: slot.starts_at, ends_at: slot.ends_at });
        await flushPromises();
    });

    it.each([
        ['idempotency_request_in_progress', 'La solicitud de reserva sigue en proceso'],
        ['idempotency_key_conflict', 'La clave de idempotencia ya fue utilizada'],
    ])('handles %s without success or internal details', async (code, message) => {
        api.createAppointment.mockRejectedValue(new ApiError(409, message, code));
        const wrapper = mount(PublicBookingPage);
        await flushPromises();
        await reachReview(wrapper);
        const submit = wrapper.findAll('button').find((button) => button.text() === 'Confirmar cita')!;
        await submit.trigger('click');
        await flushPromises();
        await submit.trigger('click');
        await flushPromises();

        expect(api.createAppointment).toHaveBeenCalledTimes(2);
        expect(api.createAppointment.mock.calls[1][1]).toBe(api.createAppointment.mock.calls[0][1]);
        expect(wrapper.text()).toContain(message);
        expect(wrapper.text()).not.toContain('Cita confirmada');
        expect(wrapper.text()).not.toMatch(/fingerprint|Customer ID|Appointment ID/i);
    });

    it('shows safe retry-later UX for 429 without an automatic retry loop', async () => {
        api.createAppointment.mockRejectedValue(new ApiError(429, 'Too many requests'));
        const wrapper = mount(PublicBookingPage);
        await flushPromises();
        await reachReview(wrapper);
        await wrapper.findAll('button').find((button) => button.text() === 'Confirmar cita')!.trigger('click');
        await flushPromises();

        expect(wrapper.text()).toContain('Espera un momento');
        expect(api.createAppointment).toHaveBeenCalledTimes(1);
    });

    it('preserves contact context and refreshes availability after appointment_unavailable', async () => {
        api.createAppointment
            .mockRejectedValueOnce(new ApiError(409, 'unavailable', 'appointment_unavailable'))
            .mockResolvedValueOnce({ message: 'Tu cita fue confirmada.', status: 'confirmed', service: { name: 'Corte' }, professional: { name: 'Alex' }, starts_at: slot.starts_at, ends_at: slot.ends_at });
        const wrapper = mount(PublicBookingPage);
        await flushPromises();
        await reachReview(wrapper);
        const submit = wrapper.findAll('button').find((button) => button.text() === 'Confirmar cita')!;
        await submit.trigger('click');
        await flushPromises();

        expect(wrapper.text()).toContain('Ese horario ya no está disponible');
        expect(wrapper.text()).toContain('10:00');
        expect(api.getAvailability).toHaveBeenCalledTimes(2);
        expect(wrapper.text()).not.toContain('Cita confirmada');
        expect((wrapper.get('#booking-date').element as HTMLInputElement).value).toBe('2026-09-10');
        await wrapper.find('section button').trigger('click');
        await continueButton(wrapper).trigger('click');
        expect(wrapper.text()).toContain('Tus datos');
        expect((wrapper.get('#booking-name').element as HTMLInputElement).value).toBe('Ana');
        expect((wrapper.get('#booking-phone').element as HTMLInputElement).value).toBe('55501020');
        await wrapper.findAll('button').find((button) => button.text() === 'Continuar')!.trigger('click');
        await wrapper.findAll('button').find((button) => button.text() === 'Confirmar cita')!.trigger('click');
        await flushPromises();
        expect(api.createAppointment.mock.calls[1][1]).not.toBe(api.createAppointment.mock.calls[0][1]);
    });

    it('ignores a late availability response from an older selection', async () => {
        let resolveFirst!: (value: unknown) => void;
        let resolveSecond!: (value: unknown) => void;
        api.getAvailability.mockReset();
        api.getAvailability.mockReturnValueOnce(new Promise((resolve) => { resolveFirst = resolve; })).mockReturnValueOnce(new Promise((resolve) => { resolveSecond = resolve; }));
        const wrapper = mount(PublicBookingPage);
        await flushPromises();
        await wrapper.find('section button').trigger('click');
        await continueButton(wrapper).trigger('click');
        await flushPromises();
        await wrapper.find('section button').trigger('click');
        await continueButton(wrapper).trigger('click');
        await wrapper.get('#booking-date').setValue('2026-09-10');
        await flushPromises();
        await wrapper.get('#booking-date').setValue('2026-09-11');
        resolveSecond({ date: '2026-09-11', timezone: 'America/Mexico_City', slots: [{ ...slot, local_start: '11:00' }] });
        await flushPromises();
        resolveFirst({ date: '2026-09-10', timezone: 'America/Mexico_City', slots: [slot] });
        await flushPromises();

        expect(wrapper.text()).toContain('11:00');
        expect(wrapper.text()).not.toContain('10:00');
    });
});
