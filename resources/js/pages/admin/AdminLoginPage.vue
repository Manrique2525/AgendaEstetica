<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import AdminLayout from '../../layouts/AdminLayout.vue';
import { useAuth } from '../../composables/useAuth';
import { ApiError } from '../../services/http';

const router = useRouter();
const auth = useAuth();
const email = ref('');
const password = ref('');
const error = ref('');
const isSubmitting = ref(false);

async function submit(): Promise<void> {
    error.value = '';
    isSubmitting.value = true;

    try {
        await auth.login(email.value, password.value);
        await router.push({ name: 'admin.home' });
    } catch (exception) {
        error.value = exception instanceof ApiError
            ? exception.message
            : 'No fue posible iniciar sesión.';
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <AdminLayout>
        <section class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm" aria-labelledby="admin-login-title">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Admin surface</p>
            <h1 id="admin-login-title" class="mt-3 text-3xl font-semibold tracking-tight">Acceso administrativo</h1>
            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="block text-sm font-medium" for="email">Email</label>
                    <input id="email" v-model="email" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" type="email" autocomplete="username" required>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="password">Password</label>
                    <input id="password" v-model="password" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" type="password" autocomplete="current-password" required>
                </div>
                <p v-if="error" class="text-sm text-red-700" role="alert">{{ error }}</p>
                <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50" type="submit" :disabled="isSubmitting">
                    {{ isSubmitting ? 'Validando...' : 'Iniciar sesión' }}
                </button>
            </form>
        </section>
    </AdminLayout>
</template>
