import { createRouter, createWebHistory } from 'vue-router';
import AdminLoginPage from '../pages/admin/AdminLoginPage.vue';
import AdminPage from '../pages/admin/AdminPage.vue';
import NotFoundPage from '../pages/NotFoundPage.vue';
import FoundationPage from '../pages/public/FoundationPage.vue';
import { useAuth } from '../composables/useAuth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: FoundationPage,
            meta: { surface: 'public' },
        },
        {
            path: '/admin',
            component: AdminPage,
            name: 'admin.home',
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

export default router;
