<script setup lang="ts">
import { useRouter } from 'vue-router';
import AdminLayout from '../../layouts/AdminLayout.vue';
import { useAuth } from '../../composables/useAuth';

const router = useRouter();
const auth = useAuth();

async function logout(): Promise<void> {
    await auth.logout();
    await router.push({ name: 'admin.login' });
}
</script>

<template>
    <AdminLayout>
        <section class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm" aria-labelledby="admin-title">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Admin surface</p>
            <h1 id="admin-title" class="mt-3 text-3xl font-semibold tracking-tight">Sesión administrativa activa</h1>
            <p class="mt-4 text-slate-600">{{ auth.user.value?.name }} · {{ auth.user.value?.email }}</p>
            <button class="mt-6 rounded-md border border-slate-300 px-4 py-2 text-sm font-medium" type="button" @click="logout">
                Cerrar sesión
            </button>
        </section>
    </AdminLayout>
</template>
