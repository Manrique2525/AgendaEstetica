import { http } from '../http';

export interface PublicBookingContext { timezone: string }
export interface PublicBookingService {
    id: number;
    name: string;
    duration_minutes: number;
    pricing_type: 'fixed' | 'starting_from' | 'variable';
    price: string | null;
    pricing_display: string;
    category: { id: number; name: string } | null;
}
export interface PublicBookingProfessional { id: number; name: string }
export interface PublicBookingSlot {
    starts_at: string;
    ends_at: string;
    local_start: string;
    local_end: string;
}
export interface PublicBookingAvailability {
    date: string;
    timezone: string;
    slots: PublicBookingSlot[];
}
export interface PublicBookingConfirmation {
    message: string;
    status: 'confirmed';
    service: { name: string };
    professional: { name: string };
    starts_at: string;
    ends_at: string;
}

function query(params: Record<string, string | number>): string {
    return new URLSearchParams(Object.entries(params).map(([key, value]) => [key, String(value)])).toString();
}

export const publicBookingApi = {
    getContext: () => http.get<PublicBookingContext>('/public/booking/context'),
    listServices: () => http.get<PublicBookingService[]>('/public/booking/services'),
    listProfessionals: (serviceId: number) => http.get<PublicBookingProfessional[]>(`/public/booking/professionals?${query({ service_id: serviceId })}`),
    getAvailability: (serviceId: number, professionalId: number, date: string) =>
        http.get<PublicBookingAvailability>(`/public/booking/availability?${query({ service_id: serviceId, professional_id: professionalId, date })}`),
    createAppointment: (payload: { service_id: number; professional_id: number; starts_at: string; name: string; phone: string }, idempotencyKey: string) =>
        http.post<PublicBookingConfirmation>('/public/booking/appointments', payload, { 'Idempotency-Key': idempotencyKey }),
};
