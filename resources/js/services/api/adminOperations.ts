import { http } from '../http';

export interface AdminBusinessHour {
    id: number;
    weekday: number;
    interval_order: number;
    opens_at: string;
    closes_at: string;
}

export interface AdminBusinessProfile {
    id: number;
    name: string;
    phone: string;
    timezone: string;
    max_simultaneous_clients: number;
    hours: AdminBusinessHour[];
    created_at: string | null;
    updated_at: string | null;
}

export interface AdminProfessionalServiceRef {
    id: number;
    name: string;
    active: boolean;
}

export interface AdminProfessional {
    id: number;
    name: string;
    active: boolean;
    services: AdminProfessionalServiceRef[];
    schedules_count?: number;
    appointments_count?: number;
    created_at: string | null;
    updated_at: string | null;
}

export interface AdminProfessionalSchedule {
    id: number;
    weekday: number;
    starts_at: string;
    ends_at: string;
}

export interface BusinessProfilePayload {
    name: string;
    phone: string;
    timezone: string;
    max_simultaneous_clients: number;
    hours?: Array<{ weekday: number; opens_at: string; closes_at: string }>;
}

export interface ProfessionalPayload {
    name: string;
    active?: boolean;
    service_ids?: number[];
}

export interface ScheduleIntervalPayload {
    weekday: number;
    starts_at: string;
    ends_at: string;
}

function query(params: Record<string, string | number | boolean | undefined>): string {
    const entries = Object.entries(params)
        .filter(([, value]) => value !== undefined && value !== '')
        .map(([key, value]) => [key, String(value)]);

    return entries.length ? `?${new URLSearchParams(entries).toString()}` : '';
}

export const adminOperationsApi = {
    businessProfile: {
        get: () => http.get<AdminBusinessProfile>('/admin/business/profile'),
        update: (payload: BusinessProfilePayload) => http.put<AdminBusinessProfile>('/admin/business/profile', payload),
    },
    professionals: {
        list: (params: { q?: string; active?: boolean; service_id?: number } = {}) =>
            http.get<AdminProfessional[]>(`/admin/professionals${query(params)}`),
        create: (payload: ProfessionalPayload) => http.post<AdminProfessional>('/admin/professionals', payload),
        update: (id: number, payload: ProfessionalPayload) => http.put<AdminProfessional>(`/admin/professionals/${id}`, payload),
        setStatus: (id: number, active: boolean) =>
            http.patch<AdminProfessional>(`/admin/professionals/${id}/status`, { active }),
        remove: (id: number) => http.delete(`/admin/professionals/${id}`),
        schedules: {
            list: (id: number) => http.get<AdminProfessionalSchedule[]>(`/admin/professionals/${id}/schedules`),
            replace: (id: number, intervals: ScheduleIntervalPayload[]) =>
                http.put<AdminProfessionalSchedule[]>(`/admin/professionals/${id}/schedules`, { intervals }),
        },
    },
};
