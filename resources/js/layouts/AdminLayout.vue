<script setup lang="ts">
import { RouterLink, useRouter } from 'vue-router';
import UiContainer from '../components/ui/UiContainer.vue';
import UiButton from '../components/ui/UiButton.vue';
import { useAuth } from '../composables/useAuth';

const router = useRouter();
const auth = useAuth();

const navigation = [
    { to: '/admin', label: 'Inicio' },
    { to: '/admin/agenda', label: 'Agenda' },
    { to: '/admin/negocio', label: 'Negocio' },
    { to: '/admin/profesionales', label: 'Profesionales' },
    { to: '/admin/catalogo/servicios', label: 'Servicios' },
    { to: '/admin/catalogo/categorias', label: 'Categorías' },
    { to: '/admin/catalogo/productos', label: 'Productos' },
    { to: '/admin/catalogo/categorias-producto', label: 'Categorías de productos' },
];

async function logout(): Promise<void> {
    await auth.logout();
    await router.push({ name: 'admin.login' });
}
</script>

<template>
    <main class="min-h-screen bg-surface-page py-8 text-text-primary sm:py-12">
        <UiContainer>
            <header class="mb-10 flex flex-wrap items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="font-display text-3xl leading-none text-text-primary">Salón y Barbería Yaris</p>
                    <p class="font-ui text-xs font-semibold uppercase tracking-[0.2em] text-text-secondary">Administración técnica</p>
                </div>
                <div v-if="auth.user.value" class="flex items-center gap-3">
                    <RouterLink
                        class="rounded-md px-3 py-2 font-ui text-sm font-semibold text-text-secondary hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring"
                        to="/admin/perfil"
                    >
                        Perfil
                    </RouterLink>
                    <UiButton variant="secondary" type="button" @click="logout">
                        Cerrar sesión
                    </UiButton>
                </div>
            </header>
            <nav
                v-if="auth.user.value"
                aria-label="Navegación administrativa"
                class="mb-8 flex flex-wrap gap-3 font-ui text-sm font-semibold"
            >
                <RouterLink
                    v-for="item in navigation"
                    :key="item.to"
                    class="rounded-md px-3 py-2 text-text-secondary hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring"
                    :to="item.to"
                >
                    {{ item.label }}
                </RouterLink>
            </nav>
            <slot />
        </UiContainer>
    </main>
</template>
