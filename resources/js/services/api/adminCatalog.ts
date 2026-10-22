import { http } from '../http';

export type PricingType = 'fixed' | 'starting_from' | 'variable';

export interface AdminServiceCategory {
    id: number;
    name: string;
    slug: string | null;
    description: string | null;
    active: boolean;
    services_count?: number;
}

export interface AdminService {
    id: number;
    name: string;
    slug: string | null;
    description: string | null;
    duration_minutes: number;
    pricing_type: PricingType;
    price: string | null;
    active: boolean;
    category: { id: number; name: string; active: boolean } | null;
    appointments_count?: number;
}

export interface AdminProductCategory {
    id: number;
    name: string;
    slug: string | null;
    description: string | null;
    active: boolean;
    products_count?: number;
}

export interface AdminProduct {
    id: number;
    name: string;
    slug: string | null;
    description: string | null;
    price: string;
    active: boolean;
    category: { id: number; name: string; active: boolean } | null;
}

export interface ServiceCategoryPayload {
    name: string;
    slug?: string | null;
    description?: string | null;
    active?: boolean;
}

export interface ServicePayload {
    service_category_id: number;
    name: string;
    slug?: string | null;
    description?: string | null;
    duration_minutes: number;
    pricing_type: PricingType;
    price: string | null;
    active?: boolean;
}

export interface ProductCategoryPayload {
    name: string;
    slug?: string | null;
    description?: string | null;
    active?: boolean;
}

export interface ProductPayload {
    product_category_id: number | null;
    name: string;
    slug?: string | null;
    description?: string | null;
    price: string;
    stock?: number | null;
    active?: boolean;
}

function query(params: Record<string, string | number | boolean | undefined>): string {
    const entries = Object.entries(params)
        .filter(([, value]) => value !== undefined && value !== '')
        .map(([key, value]) => [key, String(value)]);

    return entries.length ? `?${new URLSearchParams(entries).toString()}` : '';
}

export const adminCatalogApi = {
    serviceCategories: {
        list: (params: { q?: string; active?: boolean } = {}) =>
            http.get<AdminServiceCategory[]>(`/admin/catalog/service-categories${query(params)}`),
        create: (payload: ServiceCategoryPayload) =>
            http.post<AdminServiceCategory>('/admin/catalog/service-categories', payload),
        update: (id: number, payload: ServiceCategoryPayload) =>
            http.put<AdminServiceCategory>(`/admin/catalog/service-categories/${id}`, payload),
        setStatus: (id: number, active: boolean) =>
            http.patch<AdminServiceCategory>(`/admin/catalog/service-categories/${id}/status`, { active }),
        remove: (id: number) => http.delete(`/admin/catalog/service-categories/${id}`),
    },
    services: {
        list: (params: { q?: string; service_category_id?: number; active?: boolean } = {}) =>
            http.get<AdminService[]>(`/admin/catalog/services${query(params)}`),
        show: (id: number) => http.get<AdminService>(`/admin/catalog/services/${id}`),
        create: (payload: ServicePayload) => http.post<AdminService>('/admin/catalog/services', payload),
        update: (id: number, payload: ServicePayload) => http.put<AdminService>(`/admin/catalog/services/${id}`, payload),
        setStatus: (id: number, active: boolean) => http.patch<AdminService>(`/admin/catalog/services/${id}/status`, { active }),
        remove: (id: number) => http.delete(`/admin/catalog/services/${id}`),
    },
    productCategories: {
        list: (params: { q?: string; active?: boolean } = {}) =>
            http.get<AdminProductCategory[]>(`/admin/catalog/product-categories${query(params)}`),
        create: (payload: ProductCategoryPayload) =>
            http.post<AdminProductCategory>('/admin/catalog/product-categories', payload),
        update: (id: number, payload: ProductCategoryPayload) =>
            http.put<AdminProductCategory>(`/admin/catalog/product-categories/${id}`, payload),
        setStatus: (id: number, active: boolean) =>
            http.patch<AdminProductCategory>(`/admin/catalog/product-categories/${id}/status`, { active }),
        remove: (id: number) => http.delete(`/admin/catalog/product-categories/${id}`),
    },
    products: {
        list: (params: { q?: string; product_category_id?: number; active?: boolean } = {}) =>
            http.get<AdminProduct[]>(`/admin/catalog/products${query(params)}`),
        create: (payload: ProductPayload) => http.post<AdminProduct>('/admin/catalog/products', payload),
        update: (id: number, payload: ProductPayload) => http.put<AdminProduct>(`/admin/catalog/products/${id}`, payload),
        setStatus: (id: number, active: boolean) => http.patch<AdminProduct>(`/admin/catalog/products/${id}/status`, { active }),
        remove: (id: number) => http.delete(`/admin/catalog/products/${id}`),
    },
};