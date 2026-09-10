<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AdminLayout from '../../layouts/AdminLayout.vue';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';
import { adminAgendaApi, type AgendaAppointmentDetail, type AgendaLookupProfessional, type AppointmentStatus } from '../../services/api/adminAgenda';
import { ApiError } from '../../services/http';
import { businessDateFromInstant, formatBusinessTime, resolveLocalDateTime } from '../../utils/adminAgendaTime';

const route = useRoute();
const router = useRouter();
const timezone = ref('');
const appointment = ref<AgendaAppointmentDetail | null>(null);
const loading = ref(true);
const error = ref('');
const rescheduleOpen = ref(false);
const rescheduleProfessionalId = ref('');
const rescheduleDate = ref('');
const rescheduleTime = ref('');
const rescheduleProfessionals = ref<AgendaLookupProfessional[]>([]);
const reschedulePending = ref(false);
const rescheduleError = ref('');
const rescheduleSuccess = ref('');
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

function mutationMessage(errorValue: unknown): string {
    if (errorValue instanceof ApiError && errorValue.status === 409) {
        return errorValue.code === 'appointment_state_conflict'
            ? 'La cita ya no puede reprogramarse en su estado actual.'
            : 'El horario seleccionado ya no está disponible.';
    }

    if (errorValue instanceof ApiError && errorValue.status === 422) return 'Revisa la fecha y hora seleccionadas.';
    return 'No fue posible reprogramar la cita.';
}

async function loadDetail(): Promise<void> {
    const detail = await adminAgendaApi.getAppointment(Number(route.params.id));
    appointment.value = detail;
    if (context.value) {
        rescheduleDate.value = businessDateFromInstant(detail.starts_at, context.value.timezone);
        rescheduleTime.value = formatBusinessTime(detail.starts_at, context.value.timezone);
    }
}

const context = ref<{ timezone: string } | null>(null);

async function submitReschedule(): Promise<void> {
    if (!appointment.value || !context.value || !rescheduleProfessionalId.value || !rescheduleDate.value || !rescheduleTime.value) {
        rescheduleError.value = 'Completa Professional, fecha y hora.';
        return;
    }

    reschedulePending.value = true;
    rescheduleError.value = '';
    rescheduleSuccess.value = '';

    try {
        const startsAt = resolveLocalDateTime(rescheduleDate.value, rescheduleTime.value, context.value.timezone);
        const endsAt = new Date(Date.parse(startsAt) + appointment.value.duration_minutes * 60_000).toISOString();
        await adminAgendaApi.rescheduleAppointment(appointment.value.id, {
            professional_id: Number(rescheduleProfessionalId.value),
            starts_at: startsAt,
            ends_at: endsAt,
        });
        rescheduleSuccess.value = 'Cita reprogramada correctamente.';
        rescheduleOpen.value = false;
        await loadDetail();
    } catch (reason) {
        rescheduleError.value = mutationMessage(reason);
    } finally {
        reschedulePending.value = false;
    }
}

onMounted(async () => {
    try {
        const [loadedContext, detail] = await Promise.all([
            adminAgendaApi.getContext(),
            adminAgendaApi.getAppointment(Number(route.params.id)),
        ]);
        context.value = loadedContext;
        timezone.value = loadedContext.timezone;
        appointment.value = detail;
        rescheduleDate.value = businessDateFromInstant(detail.starts_at, loadedContext.timezone);
        rescheduleTime.value = formatBusinessTime(detail.starts_at, loadedContext.timezone);
        rescheduleProfessionals.value = await adminAgendaApi.listProfessionals(detail.service.id);
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
                    <UiButton v-if="appointment.status === 'confirmed'" class="mt-4" @click="rescheduleOpen = !rescheduleOpen">Reprogramar</UiButton>
                </header>
                <UiCard v-if="rescheduleOpen">
                    <form class="space-y-5" aria-labelledby="reschedule-title" @submit.prevent="submitReschedule">
                        <h2 id="reschedule-title" class="font-display text-3xl text-text-primary">Reprogramar cita</h2>
                        <p class="font-body text-sm text-text-secondary">{{ appointment.customer.name }} · {{ appointment.service.name }} · {{ appointment.duration_minutes }} minutos</p>
                        <div class="grid gap-4 md:grid-cols-3">
                            <label class="font-ui text-sm font-semibold text-text-primary" for="reschedule-professional">Professional
                                <select id="reschedule-professional" v-model="rescheduleProfessionalId" required class="mt-2 min-h-11 w-full rounded-md border border-border-default bg-surface-elevated px-3 font-body text-base">
                                    <option value="">Selecciona un profesional</option>
                                    <option v-for="professional in rescheduleProfessionals" :key="professional.id" :value="professional.id">{{ professional.name }}</option>
                                </select>
                            </label>
                            <label class="font-ui text-sm font-semibold text-text-primary" for="reschedule-date">Fecha
                                <input id="reschedule-date" v-model="rescheduleDate" type="date" required class="mt-2 min-h-11 w-full rounded-md border border-border-default bg-surface-elevated px-3 font-body text-base">
                            </label>
                            <label class="font-ui text-sm font-semibold text-text-primary" for="reschedule-time">Hora de inicio
                                <input id="reschedule-time" v-model="rescheduleTime" type="time" required class="mt-2 min-h-11 w-full rounded-md border border-border-default bg-surface-elevated px-3 font-body text-base">
                            </label>
                        </div>
                        <p v-if="rescheduleError" role="alert" class="font-body text-sm text-state-error">{{ rescheduleError }}</p>
                        <p v-if="rescheduleSuccess" role="status" aria-live="polite" class="font-body text-sm text-state-success">{{ rescheduleSuccess }}</p>
                        <UiButton type="submit" :loading="reschedulePending">Guardar reprogramación</UiButton>
                    </form>
                </UiCard>
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
