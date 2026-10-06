import { afterEach, describe, expect, it, vi } from 'vitest';

afterEach(() => {
    vi.restoreAllMocks();
});

describe('useAuth', () => {
    it('stores the authenticated user and clears it on logout', async () => {
        vi.stubGlobal('fetch', vi.fn().mockImplementation((input: RequestInfo | URL) => {
            const url = input.toString();

            if (url.includes('/sanctum/csrf-cookie')) {
                return Promise.resolve(new Response(null, { status: 204 }));
            }

            if (url.includes('/admin/auth/login')) {
                return Promise.resolve(new Response(JSON.stringify({
                    data: { id: 1, name: 'Admin', email: 'admin@example.invalid' },
                }), {
                    status: 200,
                    headers: { 'content-type': 'application/json' },
                }));
            }

            return Promise.resolve(new Response(null, { status: 204 }));
        }));

        const { useAuth } = await import('./useAuth');
        const auth = useAuth();

        await auth.login('admin@example.invalid', 'a-strong-password');
        expect(auth.user.value?.email).toBe('admin@example.invalid');

        await auth.logout();
        expect(auth.user.value).toBeNull();
    });
});
