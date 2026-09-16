import { describe, expect, it } from 'vitest';
import router from './index';

describe('frontend router', () => {
    it('resolves the technical public route', () => {
        expect(router.resolve('/').name).toBeUndefined();
        expect(router.resolve('/').meta.surface).toBe('public');
        expect(router.resolve('/').meta.title).toBe('Salón y Barbería Yaris | Belleza y elegancia');
    });

    it('marks the admin route as protected', () => {
        expect(router.resolve('/admin').meta.requiresAuth).toBe(true);
        expect(router.resolve('/admin/login').meta.guestOnly).toBe(true);
    });

    it('resolves the public booking route without authentication', () => {
        expect(router.resolve('/reservar').name).toBe('public.booking');
        expect(router.resolve('/reservar').meta.surface).toBe('public');
        expect(router.resolve('/reservar').meta.requiresAuth).toBeUndefined();
        expect(router.resolve('/reservar').meta.title).toBe('Solicitar cita | Salón y Barbería Yaris');
    });

    it('updates document titles for public navigation', async () => {
        await router.push('/');
        expect(document.title).toBe('Salón y Barbería Yaris | Belleza y elegancia');
        await router.push('/reservar');
        expect(document.title).toBe('Solicitar cita | Salón y Barbería Yaris');
        await router.push('/');
    });

    it('resolves unknown frontend paths to NotFound', () => {
        expect(router.resolve('/unknown-foundation-route').matched.slice(-1)[0]?.components?.default)
            .toBeDefined();
    });
});
