import { createRouter, createWebHistory } from 'vue-router';
import AdminLoginPage from '../pages/admin/AdminLoginPage.vue';
import NotFoundPage from '../pages/NotFoundPage.vue';
import FoundationPage from '../pages/public/FoundationPage.vue';

export default createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: FoundationPage,
            meta: { surface: 'public' },
        },
        {
            path: '/admin/login',
            component: AdminLoginPage,
            meta: { surface: 'admin' },
        },
        {
            path: '/:pathMatch(.*)*',
            component: NotFoundPage,
        },
    ],
});
