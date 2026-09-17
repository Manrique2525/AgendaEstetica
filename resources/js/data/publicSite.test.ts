import { describe, expect, it } from 'vitest';
import { publicSite } from './publicSite';

describe('public site data', () => {
    it('contains only the approved public business data', () => {
        expect(publicSite.business).toEqual({
            name: 'Salón y Barbería Yaris',
            tagline: 'Belleza y elegancia',
            phone: '+52 993 229 4158',
            displayPhone: '993 229 4158',
            whatsappUrl: 'https://wa.me/529932294158',
            hours: 'Todos los días, 10:00 a. m. a 8:00 p. m.',
            location: ['Fraccionamiento Ciudad Bicentenario', 'C.P. 86290'],
        });
    });

    it('defines root navigation and future store categories without products', () => {
        expect(publicSite.navigation).toEqual([
            { label: 'Inicio', href: '/' },
            { label: 'Tienda', href: '/#tienda' },
            { label: 'Servicios', href: '/#servicios' },
            { label: 'Contacto', href: '/#contacto' },
        ]);
        expect(publicSite.futureStoreCategories).toEqual(['Mary Kay', 'Cuidado capilar']);
    });
});
