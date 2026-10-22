import { afterEach, describe, expect, it, vi } from 'vitest';
import { http } from './http';

afterEach(() => {
    vi.restoreAllMocks();
});

describe('http client', () => {
    it('returns data from a JSON response', async () => {
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue(
            new Response(JSON.stringify({ data: { status: 'ok' } }), {
                status: 200,
                headers: { 'content-type': 'application/json' },
            }),
        ));

        await expect(http.get<{ status: string }>('/health')).resolves.toEqual({ status: 'ok' });
    });

    it('handles a no-content response', async () => {
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue(new Response(null, { status: 204 })));

        await expect(http.post<void>('/admin/auth/logout')).resolves.toBeUndefined();
    });

    it('normalizes API errors', async () => {
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue(
            new Response(JSON.stringify({ message: 'Invalid input', errors: { email: ['Required'] } }), {
                status: 422,
                headers: { 'content-type': 'application/json' },
            }),
        ));

        await expect(http.get('/health')).rejects.toEqual(
            expect.objectContaining({ status: 422, message: 'Invalid input' }),
        );
    });

    it('propagates network errors', async () => {
        vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new Error('Network unavailable')));

        await expect(http.get('/health')).rejects.toThrow('Network unavailable');
    });
});
