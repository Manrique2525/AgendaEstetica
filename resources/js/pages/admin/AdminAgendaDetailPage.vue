<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AdminLayout from '../../layouts/AdminLayout.vue';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';
import { adminAgendaApi, type AgendaAppointmentDetail, type AppointmentStatus } from '../../services/api/adminAgenda';
import { ApiError } from '../../services/http';
import { formatBusinessTime } from '../../utils/adminAgendaTime';

const route = useRoute();
const router = useRouter();
const timezone = ref('');
const appointment = ref<AgendaAppointmentDetail | null>(null);
const loading = ref(true);
const error = ref('');
const statusLabels: Record<AppointmentStatus, string> = {
    confirmed: 'Confirmada',
    cancelled: 'Cancelada',
    completed: 'Completada',
    no_show: 'No asistió',
};

function formatTime(value: string | null): string {
    return value && timezone.value ? formatBusinessTime(value, timezone.value) : '';
}

function messageFor(errorValue: unknown): string {
    if (errorValue instanceof ApiError && errorValue.status === 404) return 'La cita no existe.';
    if (errorValue instanceof ApiError && errorValue.status === 401) return 'La sesión administrativa ya no es válida.';
    return 'No fue posible cargar el detalle de la cita.';
}

onMounted(async () => {
    try {
        const [context, detail] = await Promise.all([
            adminAgendaApi.getContext(),
            adminAgendaApi.getAppointment(Number(route.params.id)),
        ]);
        timezone.value = context.timezone;
        appointment.value = detail;
    } catch (reason) {
        error.value = messageFor(reason);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
            <UiButton variant="secondary" @click="router.push({ name: 'admin.agenda', query: route.query })">Volver a agenda</UiButton>
            <p v-if="loading" role="status" aria-live="polite" class="font-body text-text-secondary">Cargando detalle...</p>
            <div v-else-if="error" role="alert" class="rounded-md border border-state-error p-4 font-body text-state-error">{{ error }}</div>
            <template v-else-if="appointment">
                <header class="space-y-2">
                    <p class="font-ui text-sm font-semibold uppercase tracking-[0.16em] text-text-secondary">Detalle de cita</p>
                    <h1 class="font-display text-5xl leading-none text-text-primary">{{ appointment.service.name }}</h1>
                    <p class="font-body text-text-secondary">{{ statusLabels[appointment.status] }} · {{ formatTime(appointment.starts_at) }} - {{ formatTime(appointment.ends_at) }}</p>
                </header>
                <UiCard>
                    <div class="grid gap-4 font-body text-sm text-text-secondary sm:grid-cols-2">
                        <p><strong class="text-text-primary">Cliente:</strong> {{ appointment.customer.name }}</p>
                        <p><strong class="text-text-primary">Teléfono:</strong> {{ appointment.customer.phone }}</p>
                        <p><strong class="text-text-primary">Profesional:</strong> {{ appointment.professional.name }}</p>
                        <p><strong class="text-text-primary">Duración:</strong> {{ appointment.duration_minutes }} minutos</p>
                    </div>
                </UiCard>
                <section aria-labelledby="history-title" class="space-y-3">
                    <h2 id="history-title" class="font-display text-3xl text-text-primary">Historial</h2>
                    <ol class="space-y-3">
                        <li v-for="event in appointment.history" :key="event.id" class="rounded-xl border border-border-default bg-surface-elevated p-4 font-body text-sm text-text-secondary">
                            <strong class="text-text-primary">{{ event.event_type === 'created' ? 'Cita creada' : event.event_type === 'rescheduled' ? 'Cita reprogramada' : 'Estado actualizado' }}</strong>
                            <span v-if="event.from_status && event.to_status"> · {{ statusLabels[event.from_status] }} a {{ statusLabels[event.to_status] }}</span>
                        </li>
                    </ol>
                </section>
            </template>
        </div>
    </AdminLayout>
</template>
