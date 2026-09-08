<script setup lang="ts">
import { useRouter } from 'vue-router';
import AdminLayout from '../../layouts/AdminLayout.vue';
import { useAuth } from '../../composables/useAuth';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';

const router = useRouter();
const auth = useAuth();

async function logout(): Promise<void> {
    await auth.logout();
    await router.push({ name: 'admin.login' });
}
</script>

<template>
    <AdminLayout>
        <UiCard>
            <p class="font-ui text-sm font-semibold uppercase tracking-[0.16em] text-text-secondary">Administración técnica</p>
            <h1 id="admin-title" class="mt-4 font-display text-4xl leading-none text-text-primary">Sesión activa</h1>
            <p class="mt-6 break-words font-body text-base text-text-secondary">{{ auth.user.value?.name }} · {{ auth.user.value?.email }}</p>
            <UiButton class="mt-8" variant="secondary" type="button" @click="logout">
                Cerrar sesión
            </UiButton>
        </UiCard>
    </AdminLayout>
</template>
