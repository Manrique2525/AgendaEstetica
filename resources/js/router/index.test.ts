import { describe, expect, it } from 'vitest';
import router from './index';

describe('frontend router', () => {
    it('resolves the technical public route', () => {
        expect(router.resolve('/').name).toBeUndefined();
        expect(router.resolve('/').meta.surface).toBe('public');
    });

    it('marks the admin route as protected', () => {
        expect(router.resolve('/admin').meta.requiresAuth).toBe(true);
        expect(router.resolve('/admin/login').meta.guestOnly).toBe(true);
    });

    it('resolves the public booking route without authentication', () => {
        expect(router.resolve('/reservar').name).toBe('public.booking');
        expect(router.resolve('/reservar').meta.surface).toBe('public');
        expect(router.resolve('/reservar').meta.requiresAuth).toBeUndefined();
    });

    it('resolves unknown frontend paths to NotFound', () => {
        expect(router.resolve('/unknown-foundation-route').matched.slice(-1)[0]?.components?.default)
            .toBeDefined();
    });
});
