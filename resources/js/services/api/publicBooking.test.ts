import { afterEach, describe, expect, it, vi } from 'vitest';
import { http } from '../http';
import { publicBookingApi } from './publicBooking';

vi.mock('../http', () => ({ http: { get: vi.fn(), post: vi.fn() } }));

afterEach(() => vi.clearAllMocks());

describe('publicBookingApi', () => {
    it('sends only the approved mutation payload and Idempotency-Key header', async () => {
        vi.mocked(http.post).mockResolvedValue({});
        const payload = { service_id: 1, professional_id: 2, starts_at: '2026-09-10T16:00:00Z', name: 'Ana', phone: '55501020' };

        await publicBookingApi.createAppointment(payload, '550e8400-e29b-41d4-a716-446655440000');

        expect(http.post).toHaveBeenCalledWith('/public/booking/appointments', payload, { 'Idempotency-Key': '550e8400-e29b-41d4-a716-446655440000' });
    });
});
