import { createRouter, createWebHistory } from 'vue-router';
import AdminLoginPage from '../pages/admin/AdminLoginPage.vue';
import AdminPage from '../pages/admin/AdminPage.vue';
import AdminAgendaPage from '../pages/admin/AdminAgendaPage.vue';
import AdminAgendaDetailPage from '../pages/admin/AdminAgendaDetailPage.vue';
import NotFoundPage from '../pages/NotFoundPage.vue';
import FoundationPage from '../pages/public/FoundationPage.vue';
import PublicBookingPage from '../pages/public/PublicBookingPage.vue';
import AcademicPhaseOnePage from '../pages/public/AcademicPhaseOnePage.vue';
import CustomerAccessDemoPage from '../pages/public/CustomerAccessDemoPage.vue';
import DemoOrderPage from '../pages/public/DemoOrderPage.vue';
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
