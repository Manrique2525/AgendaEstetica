export const publicSite = {
    business: {
        name: 'Salón y Barbería Yaris',
        tagline: 'Belleza y elegancia',
        phone: '+52 993 229 4158',
        displayPhone: '993 229 4158',
        whatsappUrl: 'https://wa.me/529932294158',
        hours: 'Todos los días, 10:00 a. m. a 8:00 p. m.',
        location: ['Fraccionamiento Ciudad Bicentenario', 'C.P. 86290'],
    },
    navigation: [
        { label: 'Inicio', href: '/' },
        { label: 'Tienda', href: '/#tienda' },
        { label: 'Servicios', href: '/#servicios' },
        { label: 'Contacto', href: '/#contacto' },
    ],
    futureStoreCategories: ['Mary Kay', 'Cuidado capilar'],
} as const;
