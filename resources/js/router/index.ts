import { createRouter, createWebHistory } from 'vue-router';
import AdminLoginPage from '../pages/admin/AdminLoginPage.vue';
import AdminPage from '../pages/admin/AdminPage.vue';
import AdminAgendaPage from '../pages/admin/AdminAgendaPage.vue';
import AdminAgendaDetailPage from '../pages/admin/AdminAgendaDetailPage.vue';
import AdminCatalogServicesPage from '../pages/admin/AdminCatalogServicesPage.vue';
import AdminCatalogServiceCategoriesPage from '../pages/admin/AdminCatalogServiceCategoriesPage.vue';
import AdminCatalogProductsPage from '../pages/admin/AdminCatalogProductsPage.vue';
import AdminCatalogProductCategoriesPage from '../pages/admin/AdminCatalogProductCategoriesPage.vue';
import AdminServicesCreatePage from '../pages/admin/services/AdminServicesCreatePage.vue';
import AdminServicesEditPage from '../pages/admin/services/AdminServicesEditPage.vue';
import AdminServiceCategoriesCreatePage from '../pages/admin/service-categories/AdminServiceCategoriesCreatePage.vue';
import AdminServiceCategoriesEditPage from '../pages/admin/service-categories/AdminServiceCategoriesEditPage.vue';
import AdminProductsCreatePage from '../pages/admin/products/AdminProductsCreatePage.vue';
import AdminProductsEditPage from '../pages/admin/products/AdminProductsEditPage.vue';
import AdminProductCategoriesCreatePage from '../pages/admin/product-categories/AdminProductCategoriesCreatePage.vue';
import AdminProductCategoriesEditPage from '../pages/admin/product-categories/AdminProductCategoriesEditPage.vue';
import AdminBusinessProfilePage from '../pages/admin/AdminBusinessProfilePage.vue';
import AdminProfessionalsPage from '../pages/admin/AdminProfessionalsPage.vue';
import AdminProfessionalSchedulesPage from '../pages/admin/AdminProfessionalSchedulesPage.vue';
import NotFoundPage from '../pages/NotFoundPage.vue';
import FoundationPage from '../pages/public/FoundationPage.vue';
import PublicBookingPage from '../pages/public/PublicBookingPage.vue';
import AcademicPhaseOnePage from '../pages/public/AcademicPhaseOnePage.vue';
import CustomerAccessDemoPage from '../pages/public/CustomerAccessDemoPage.vue';
import DemoOrderPage from '../pages/public/DemoOrderPage.vue';
import TermsAndConditionsPage from '../pages/public/TermsAndConditionsPage.vue';
import { useAuth } from '../composables/useAuth';
import { publicSite } from '../data/publicSite';

const publicTitle = `${publicSite.business.name} | ${publicSite.business.tagline}`;

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: FoundationPage,
            meta: { surface: 'public', title: publicTitle },
        },
        {
            path: '/reservar',
            component: PublicBookingPage,
            name: 'public.booking',
            meta: { surface: 'public', title: `Solicitar cita | ${publicSite.business.name}` },
        },
        {
            path: '/fase-1',
            component: AcademicPhaseOnePage,
            name: 'public.academic-phase-one',
            meta: { surface: 'public', title: `Fase 1 | ${publicSite.business.name}` },
        },
        {
            path: '/cliente/acceso',
            component: CustomerAccessDemoPage,
            name: 'public.customer-access-demo',
            meta: { surface: 'public', title: `Acceso de clientes | ${publicSite.business.name}` },
        },
        {
            path: '/demo/pedido',
            component: DemoOrderPage,
            name: 'public.demo-order',
            meta: { surface: 'public', title: `Pedido de demostración | ${publicSite.business.name}` },
        },
        {
            path: '/terminos-condiciones',
            component: TermsAndConditionsPage,
            name: 'public.terms',
            meta: { surface: 'public', title: `Términos y Condiciones | ${publicSite.business.name}` },
        },
        {
            path: '/admin',
            component: AdminPage,
            name: 'admin.home',
            meta: { requiresAuth: true },
        },
        {
            path: '/admin/agenda',
            component: AdminAgendaPage,
            name: 'admin.agenda',
            meta: { requiresAuth: true },
        },
        {
            path: '/admin/agenda/:id',
            component: AdminAgendaDetailPage,
            name: 'admin.agenda.detail',
            meta: { requiresAuth: true },
        },
        {
            path: '/admin/catalogo/servicios',
            component: AdminCatalogServicesPage,
            name: 'admin.catalog.services',
            meta: { requiresAuth: true, title: 'Servicios | Administración' },
        },
        {
            path: '/admin/catalogo/servicios/nuevo',
            component: AdminServicesCreatePage,
            name: 'admin.catalog.services.create',
            meta: { requiresAuth: true, title: 'Nuevo servicio | Administración' },
        },
        {
            path: '/admin/catalogo/servicios/:id/editar',
            component: AdminServicesEditPage,
            name: 'admin.catalog.services.edit',
            meta: { requiresAuth: true, title: 'Editar servicio | Administración' },
        },
        {
            path: '/admin/catalogo/categorias',
            component: AdminCatalogServiceCategoriesPage,
            name: 'admin.catalog.service-categories',
            meta: { requiresAuth: true, title: 'Categorías de servicios | Administración' },
        },
        {
            path: '/admin/catalogo/categorias/nueva',
            component: AdminServiceCategoriesCreatePage,
            name: 'admin.catalog.service-categories.create',
            meta: { requiresAuth: true, title: 'Nueva categoría | Administración' },
        },
        {
            path: '/admin/catalogo/categorias/:id/editar',
            component: AdminServiceCategoriesEditPage,
            name: 'admin.catalog.service-categories.edit',
            meta: { requiresAuth: true, title: 'Editar categoría | Administración' },
        },
        {
            path: '/admin/catalogo/productos',
            component: AdminCatalogProductsPage,
            name: 'admin.catalog.products',
            meta: { requiresAuth: true, title: 'Productos | Administración' },
        },
        {
            path: '/admin/catalogo/productos/nuevo',
            component: AdminProductsCreatePage,
            name: 'admin.catalog.products.create',
            meta: { requiresAuth: true, title: 'Nuevo producto | Administración' },
        },
        {
            path: '/admin/catalogo/productos/:id/editar',
            component: AdminProductsEditPage,
            name: 'admin.catalog.products.edit',
            meta: { requiresAuth: true, title: 'Editar producto | Administración' },
        },
        {
            path: '/admin/catalogo/categorias-producto',
            component: AdminCatalogProductCategoriesPage,
            name: 'admin.catalog.product-categories',
            meta: { requiresAuth: true, title: 'Categorías de productos | Administración' },
        },
        {
            path: '/admin/catalogo/categorias-producto/nueva',
            component: AdminProductCategoriesCreatePage,
            name: 'admin.catalog.product-categories.create',
            meta: { requiresAuth: true, title: 'Nueva categoría de producto | Administración' },
        },
        {
            path: '/admin/catalogo/categorias-producto/:id/editar',
            component: AdminProductCategoriesEditPage,
            name: 'admin.catalog.product-categories.edit',
            meta: { requiresAuth: true, title: 'Editar categoría de producto | Administración' },
        },
        {
            path: '/admin/negocio',
            component: AdminBusinessProfilePage,
            name: 'admin.business-profile',
            meta: { requiresAuth: true, title: 'Configuración del negocio | Administración' },
        },
        {
            path: '/admin/profesionales',
            component: AdminProfessionalsPage,
            name: 'admin.professionals',
            meta: { requiresAuth: true, title: 'Profesionales | Administración' },
        },
        {
            path: '/admin/profesionales/:id/horarios',
            component: AdminProfessionalSchedulesPage,
            name: 'admin.professionals.schedules',
            meta: { requiresAuth: true, title: 'Horarios del profesional | Administración' },
        },
        {
            path: '/admin/login',
            component: AdminLoginPage,
            name: 'admin.login',
            meta: { surface: 'admin', guestOnly: true },
        },
        {
            path: '/:pathMatch(.*)*',
            component: NotFoundPage,
        },
    ],
});

router.beforeEach(async (to) => {
    const auth = useAuth();

    if (to.meta.requiresAuth || to.meta.guestOnly) {
        await auth.initialize();
    }

    if (to.meta.requiresAuth && !auth.user.value) {
        return { name: 'admin.login' };
    }

    if (to.meta.guestOnly && auth.user.value) {
        return { name: 'admin.home' };
    }
});

router.afterEach((to) => {
    if (typeof document !== 'undefined' && typeof to.meta.title === 'string') {
        document.title = to.meta.title;
    }
});

export default router;
