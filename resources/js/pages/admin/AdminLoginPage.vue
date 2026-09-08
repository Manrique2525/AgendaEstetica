<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import AdminLayout from '../../layouts/AdminLayout.vue';
import { useAuth } from '../../composables/useAuth';
import { ApiError } from '../../services/http';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';
import UiFormField from '../../components/ui/UiFormField.vue';
import UiInput from '../../components/ui/UiInput.vue';

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
        <UiCard>
            <p class="font-ui text-sm font-semibold uppercase tracking-[0.16em] text-text-secondary">Acceso administrativo</p>
            <h1 id="admin-login-title" class="mt-4 font-display text-4xl leading-none text-text-primary">Iniciar sesión</h1>
            <form class="mt-8 space-y-5" aria-labelledby="admin-login-title" @submit.prevent="submit">
                <UiFormField id="email" label="Email" required>
                    <template #default="{ inputId, describedBy, invalid }">
                        <UiInput
                            :id="inputId"
                            v-model="email"
                            type="email"
                            autocomplete="username"
                            required
                            :aria-describedby="describedBy"
                            :invalid="invalid"
                        />
                    </template>
                </UiFormField>
                <UiFormField id="password" label="Password" required>
                    <template #default="{ inputId, describedBy, invalid }">
                        <UiInput
                            :id="inputId"
                            v-model="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            :aria-describedby="describedBy"
                            :invalid="invalid"
                        />
                    </template>
                </UiFormField>
                <p v-if="error" class="break-words font-body text-sm text-state-error" role="alert">{{ error }}</p>
                <UiButton type="submit" :loading="isSubmitting">
                    {{ isSubmitting ? 'Validando...' : 'Iniciar sesión' }}
                </UiButton>
            </form>
        </UiCard>
    </AdminLayout>
</template>
