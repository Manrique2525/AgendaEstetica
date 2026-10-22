<script setup lang="ts">
import AdminLayout from '../../layouts/AdminLayout.vue';
import { useAuth } from '../../composables/useAuth';
import UiCard from '../../components/ui/UiCard.vue';

const auth = useAuth();
</script>

<template>
    <AdminLayout>
        <UiCard>
            <p class="font-ui text-sm font-semibold uppercase tracking-[0.16em] text-text-secondary">Cuenta</p>
            <h1 id="admin-profile-title" class="mt-4 font-display text-4xl leading-none text-text-primary">Perfil</h1>
            <dl class="mt-8 space-y-4 font-body text-base text-text-secondary">
                <div>
                    <dt class="text-sm font-semibold uppercase tracking-[0.12em] text-text-secondary">Nombre</dt>
                    <dd class="mt-1 break-words text-text-primary">{{ auth.user.value?.name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold uppercase tracking-[0.12em] text-text-secondary">Email</dt>
                    <dd class="mt-1 break-words text-text-primary">{{ auth.user.value?.email ?? '—' }}</dd>
                </div>
            </dl>
        </UiCard>
    </AdminLayout>
</template>
