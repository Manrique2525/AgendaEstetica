<script setup lang="ts">
import { ref } from 'vue';
import { RouterLink } from 'vue-router';
import AdminLayout from '../../layouts/AdminLayout.vue';
import { authApi } from '../../services/api/auth';
import { ApiError } from '../../services/http';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';
import UiFormField from '../../components/ui/UiFormField.vue';
import UiInput from '../../components/ui/UiInput.vue';

const email = ref('');
const error = ref('');
const message = ref('');
const isSubmitting = ref(false);

async function submit(): Promise<void> {
    error.value = '';
    message.value = '';
    isSubmitting.value = true;

    try {
        const response = await authApi.forgotPassword(email.value);
        message.value = response.message;
    } catch (exception) {
        if (exception instanceof ApiError && exception.errors?.email?.[0]) {
            error.value = exception.errors.email[0];
        } else {
            error.value = exception instanceof ApiError
                ? exception.message
                : 'No fue posible procesar la solicitud.';
        }
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <AdminLayout>
        <UiCard>
            <p class="font-ui text-sm font-semibold uppercase tracking-[0.16em] text-text-secondary">Acceso administrativo</p>
            <h1 id="admin-forgot-title" class="mt-4 font-display text-4xl leading-none text-text-primary">Recuperar contraseña</h1>
            <p class="mt-4 font-body text-base text-text-secondary">
                Escribe el correo registrado y te enviaremos una nueva contraseña.
            </p>
            <form class="mt-8 space-y-5" aria-labelledby="admin-forgot-title" @submit.prevent="submit">
                <UiFormField id="email" label="Email" required :error="error">
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
                <p v-if="message" class="break-words font-body text-sm text-state-success" role="status">{{ message }}</p>
                <UiButton type="submit" :loading="isSubmitting">
                    {{ isSubmitting ? 'Enviando...' : 'Enviar contraseña' }}
                </UiButton>
            </form>
            <p class="mt-6 font-body text-sm text-text-secondary">
                <RouterLink class="font-ui font-semibold text-text-primary underline" to="/admin/login">
                    Volver a iniciar sesión
                </RouterLink>
            </p>
        </UiCard>
    </AdminLayout>
</template>
