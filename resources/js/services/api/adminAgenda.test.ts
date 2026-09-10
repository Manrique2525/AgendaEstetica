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
});
