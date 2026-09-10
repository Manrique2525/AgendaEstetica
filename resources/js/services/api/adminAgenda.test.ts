import { beforeEach, describe, expect, it, vi } from 'vitest';
import { adminAgendaApi } from './adminAgenda';

describe('Admin Agenda API', () => {
    beforeEach(() => {
        vi.restoreAllMocks();
    });

    it('loads the authoritative business context', async () => {
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue(new Response(
            JSON.stringify({ data: { timezone: 'America/New_York' } }),
            { status: 200, headers: { 'Content-Type': 'application/json' } },
        )));

        await expect(adminAgendaApi.getContext()).resolves.toEqual({ timezone: 'America/New_York' });
        expect(fetch).toHaveBeenCalledWith('/api/v1/admin/agenda/context', expect.objectContaining({ credentials: 'include' }));
    });

    it('serializes only active agenda filters into the read query', async () => {
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue(new Response(
            JSON.stringify({ data: [] }),
            { status: 200, headers: { 'Content-Type': 'application/json' } },
        )));

        await adminAgendaApi.listAppointments({
            from: '2026-03-08',
            to: '2026-03-09',
            status: 'confirmed',
            professional_id: 7,
        });

        expect(fetch).toHaveBeenCalledWith(
            '/api/v1/admin/agenda/appointments?from=2026-03-08&to=2026-03-09&status=confirmed&professional_id=7',
            expect.objectContaining({ credentials: 'include' }),
        );
    });

    it.each([
        ['cancelAppointment', 1, '/api/v1/admin/agenda/appointments/1/cancel'],
        ['completeAppointment', 2, '/api/v1/admin/agenda/appointments/2/complete'],
        ['markAppointmentNoShow', 3, '/api/v1/admin/agenda/appointments/3/no-show'],
    ])('%s uses an explicit terminal intent endpoint', async (method, id, path) => {
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue(new Response(
            JSON.stringify({ data: { id } }),
            { status: 200, headers: { 'Content-Type': 'application/json' } },
        )));

        await adminAgendaApi[method as 'cancelAppointment' | 'completeAppointment' | 'markAppointmentNoShow'](id as number);

        expect(fetch).toHaveBeenCalledWith(path, expect.objectContaining({
            method: 'POST',
            body: undefined,
            credentials: 'include',
        }));
    });
});
