import { http } from '../http';

export interface AuthenticatedUser {
    id: number;
    name: string;
    email: string;
}

export const authApi = {
    login(email: string, password: string): Promise<AuthenticatedUser> {
        return http.post<AuthenticatedUser>('/admin/auth/login', { email, password });
    },

    me(): Promise<AuthenticatedUser> {
        return http.get<AuthenticatedUser>('/admin/auth/me');
    },

    logout(): Promise<void> {
        return http.post<void>('/admin/auth/logout');
    },
};
