import { http } from '../http';

export type AppointmentStatus = 'confirmed' | 'cancelled' | 'completed' | 'no_show';

export interface AdminAgendaContext {
    timezone: string;
}

export interface AgendaAppointment {
    id: number;
    status: AppointmentStatus;
    starts_at: string;
    ends_at: string;
    duration_minutes: number;
    customer: { id: number; name: string };
    service: { id: number; name: string };
    professional: { id: number; name: string };
}

export interface AgendaHistoryItem {
    id: number;
    event_type: 'created' | 'status_changed' | 'rescheduled';
    from_status: AppointmentStatus | null;
    to_status: AppointmentStatus | null;
    old_starts_at: string | null;
    old_ends_at: string | null;
    new_starts_at: string | null;
    new_ends_at: string | null;
    old_professional_id: number | null;
    new_professional_id: number | null;
    created_at: string | null;
}

export interface AgendaAppointmentDetail extends AgendaAppointment {
    customer: AgendaAppointment['customer'] & { phone: string | null };
    service: AgendaAppointment['service'] & {
        category: { id: number; name: string } | null;
    };
    history: AgendaHistoryItem[];
}

export interface AgendaLookupCustomer {
    id: number;
    name: string;
    phone: string | null;
}

export interface AgendaLookupService {
    id: number;
    name: string;
    duration_minutes: number;
    pricing_type: string;
    price: string | null;
    category: { id: number; name: string } | null;
}

export interface AgendaLookupProfessional {
    id: number;
    name: string;
}

export interface AgendaQuery {
    from: string;
    to: string;
    professional_id?: number;
    status?: AppointmentStatus;
    service_id?: number;
    customer_id?: number;
}

function queryString(query: object): string {
    const params = new URLSearchParams();

    Object.entries(query).forEach(([key, value]) => {
        if (value !== undefined && value !== '') {
            params.set(key, String(value));
        }
    });

    return params.toString();
}

export const adminAgendaApi = {
    getContext(): Promise<AdminAgendaContext> {
        return http.get<AdminAgendaContext>('/admin/agenda/context');
    },

    listAppointments(query: AgendaQuery): Promise<AgendaAppointment[]> {
        return http.get<AgendaAppointment[]>(`/admin/agenda/appointments?${queryString(query)}`);
    },

    getAppointment(id: number): Promise<AgendaAppointmentDetail> {
        return http.get<AgendaAppointmentDetail>(`/admin/agenda/appointments/${id}`);
    },

    searchCustomers(query: string): Promise<AgendaLookupCustomer[]> {
        return http.get<AgendaLookupCustomer[]>(`/admin/agenda/customers?${queryString({ q: query })}`);
    },

    listServices(): Promise<AgendaLookupService[]> {
        return http.get<AgendaLookupService[]>('/admin/agenda/services');
    },

    listProfessionals(serviceId?: number): Promise<AgendaLookupProfessional[]> {
        return http.get<AgendaLookupProfessional[]>(`/admin/agenda/professionals?${queryString({ service_id: serviceId })}`);
    },
};
