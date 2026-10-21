import { afterEach, describe, expect, it, vi } from 'vitest';
import { http } from '../http';
import { adminOperationsApi } from './adminOperations';

vi.mock('../http', () => ({ http: { get: vi.fn(), post: vi.fn(), put: vi.fn(), patch: vi.fn(), delete: vi.fn() } }));

afterEach(() => vi.clearAllMocks());

describe('adminOperationsApi businessProfile', () => {
    it('gets and updates the singleton business profile', async () => {
        vi.mocked(http.get).mockResolvedValue({});
        vi.mocked(http.put).mockResolvedValue({});

        await adminOperationsApi.businessProfile.get();
        expect(http.get).toHaveBeenCalledWith('/admin/business/profile');

        const payload = {
            name: 'Salón Yaris',
            phone: '+529930000000',
            timezone: 'America/Mexico_City',
            max_simultaneous_clients: 2,
            hours: [{ weekday: 1, opens_at: '10:00', closes_at: '20:00' }],
        };

        await adminOperationsApi.businessProfile.update(payload);
        expect(http.put).toHaveBeenCalledWith('/admin/business/profile', payload);
    });
});

describe('adminOperationsApi professionals', () => {
    it('lists without a query string when no filters are provided', async () => {
        vi.mocked(http.get).mockResolvedValue([]);

        await adminOperationsApi.professionals.list();

        expect(http.get).toHaveBeenCalledWith('/admin/professionals');
    });

    it('encodes filters as a query string', async () => {
        vi.mocked(http.get).mockResolvedValue([]);

        await adminOperationsApi.professionals.list({ q: 'ana', active: true, service_id: 6 });

        expect(http.get).toHaveBeenCalledWith('/admin/professionals?q=ana&active=true&service_id=6');
    });

    it('creates, updates, toggles status and deletes', async () => {
        vi.mocked(http.post).mockResolvedValue({});
        vi.mocked(http.put).mockResolvedValue({});
        vi.mocked(http.patch).mockResolvedValue({});
        vi.mocked(http.delete).mockResolvedValue(undefined);

        const payload = { name: 'Ana', service_ids: [1, 6] };

        await adminOperationsApi.professionals.create(payload);
        await adminOperationsApi.professionals.update(3, payload);
        await adminOperationsApi.professionals.setStatus(3, false);
        await adminOperationsApi.professionals.remove(3);

        expect(http.post).toHaveBeenCalledWith('/admin/professionals', payload);
        expect(http.put).toHaveBeenCalledWith('/admin/professionals/3', payload);
        expect(http.patch).toHaveBeenCalledWith('/admin/professionals/3/status', { active: false });
        expect(http.delete).toHaveBeenCalledWith('/admin/professionals/3');
    });
});

describe('adminOperationsApi professionals.schedules', () => {
    it('lists and replaces weekly schedules', async () => {
        vi.mocked(http.get).mockResolvedValue([]);
        vi.mocked(http.put).mockResolvedValue([]);

        await adminOperationsApi.professionals.schedules.list(3);
        expect(http.get).toHaveBeenCalledWith('/admin/professionals/3/schedules');

        const intervals = [{ weekday: 1, starts_at: '09:00', ends_at: '14:00' }];
        await adminOperationsApi.professionals.schedules.replace(3, intervals);
        expect(http.put).toHaveBeenCalledWith('/admin/professionals/3/schedules', { intervals });
    });
});
