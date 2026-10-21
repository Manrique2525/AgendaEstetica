import { http } from '../http';

export interface PublicCatalogProduct {
    id: number;
    name: string;
    slug: string | null;
    description: string | null;
    price: string;
    price_display: string;
    category: { id: number; name: string } | null;
}

export const publicCatalogApi = {
    listProducts: () => http.get<PublicCatalogProduct[]>('/public/products'),
};
