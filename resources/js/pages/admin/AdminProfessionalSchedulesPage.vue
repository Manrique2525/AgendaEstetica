<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import AdminLayout from '../../layouts/AdminLayout.vue';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';
import UiConfirmDialog from '../../components/ui/UiConfirmDialog.vue';
import UiInput from '../../components/ui/UiInput.vue';
import { ApiError } from '../../services/http';
import { adminOperationsApi, type AdminProfessional } from '../../services/api/adminOperations';

interface DayConfig {
    weekday: number;
    label: string;
    enabled: boolean;
    starts_at: string;
    ends_at: string;
}

const WEEKDAYS = [
    { weekday: 1, label: 'Lunes' },
    { weekday: 2, label: 'Martes' },
    { weekday: 3, label: 'Miércoles' },
    { weekday: 4, label: 'Jueves' },
    { weekday: 5, label: 'Viernes' },
    { weekday: 6, label: 'Sábado' },
    { weekday: 7, label: 'Domingo' },
];

const route = useRoute();
const professionalId = computed(() => Number(route.params.id));

const professionals = ref<AdminProfessional[]>([]);
const professionalName = ref('');
const loading = ref(true);
const error = ref('');
const message = ref('');
const saving = ref(false);
const confirming = ref(false);
const serverErrors = reactive<Record<string, string>>({});

const days = reactive<DayConfig[]>(
    WEEKDAYS.map((day) => ({ ...day, enabled: false, starts_at: '10:00', ends_at: '20:00' })),
);

const enabledDays = computed(() => days.filter((day) => day.enabled));

function clearServerErrors(): void {
    Object.keys(serverErrors).forEach((key) => delete serverErrors[key]);
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const [schedule, list] = await Promise.all([
            adminOperationsApi.professionals.schedules.list(professionalId.value),
            adminOperationsApi.professionals.list(),
        ]);

        professionals.value = list;
        professionalName.value = list.find((item) => item.id === professionalId.value)?.name ?? '';

        days.forEach((day) => {
            const interval = schedule.find((item) => item.weekday === day.weekday);
            day.enabled = Boolean(interval);
            day.starts_at = interval ? interval.starts_at.slice(0, 5) : '10:00';
            day.ends_at = interval ? interval.ends_at.slice(0, 5) : '20:00';
        });
    } catch {
        error.value = 'No pudimos cargar los horarios de este profesional.';
    } finally {
        loading.value = false;
    }
}

function clientValidate(): boolean {
    clearServerErrors();

    for (const day of enabledDays.value) {
        if (!day.starts_at || !day.ends_at) {
            serverErrors.intervals = `Completa el horario de ${day.label}.`;
            return false;
        }
        if (day.starts_at >= day.ends_at) {
            serverErrors.intervals = `En ${day.label}, la hora de inicio debe ser anterior a la de finalización.`;
            return false;
        }
    }

    return true;
}

function requestSave(): void {
    message.value = '';
    error.value = '';

    if (!clientValidate()) {
        error.value = 'Revisa los horarios marcados para guardar.';
        return;
    }

    const hasEnabledDays = enabledDays.value.length > 0;
    confirming.value = hasEnabledDays;
    if (!hasEnabledDays) void save();
}

async function save(): Promise<void> {
    confirming.value = false;

    if (saving.value) return;
    saving.value = true;
    error.value = '';

    const payload = enabledDays.value.map((day) => ({
        weekday: day.weekday,
        starts_at: day.starts_at,
        ends_at: day.ends_at,
    }));

    try {
        await adminOperationsApi.professionals.schedules.replace(professionalId.value, payload);
        message.value = 'Horarios actualizados. Las citas existentes que queden fuera de estos horarios deberán revisarse en la agenda.';
    } catch (exception) {
        if (exception instanceof ApiError) {
            if (exception.errors) {
                Object.entries(exception.errors).forEach(([key, messages]) => {
                    serverErrors[key] = messages[0] ?? 'Revisa este campo.';
                });
            }
            error.value = exception.status === 422 ? 'Revisa los horarios marcados para guardar.' : exception.message;
        } else {
            error.value = 'No pudimos guardar los horarios.';
        }
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <AdminLayout>
        <UiCard>
            <h1 class="font-display text-4xl leading-none text-text-primary">Horarios del profesional</h1>
            <p class="mt-3 font-body text-sm text-text-secondary">
                Define los días y horas en que cada profesional puede recibir citas. El flujo público solo ofrecerá horarios dentro de esta configuración.
            </p>

            <p v-if="message" role="status" class="mt-4 rounded-md border border-brand-turquoise bg-surface-subtle p-3 font-body text-sm">{{ message }}</p>
            <p v-if="error" role="alert" class="mt-4 rounded-md border border-state-error bg-red-50 p-3 font-body text-sm text-state-error">{{ error }}</p>
            <p v-if="serverErrors.intervals" role="alert" class="mt-2 font-body text-sm text-state-error">{{ serverErrors.intervals }}</p>

            <p v-if="loading" role="status" class="mt-6 font-body text-sm text-text-secondary">Cargando horarios...</p>

            <template v-else>
                <p class="mt-6 font-ui text-sm font-semibold">
                    Profesional: <span class="font-body font-normal">{{ professionalName || `#${professionalId}` }}</span>
                </p>

                <label class="mt-4 block max-w-sm font-ui text-sm font-semibold">Cambiar de profesional
                    <select class="mt-1 block min-h-11 w-full rounded-md border border-border-default px-3 py-2 font-body text-base" :value="professionalId" @change="($event) => $router.push(`/admin/profesionales/${Number(($event.target as HTMLSelectElement).value)}/horarios`)">
                        <option v-for="item in professionals" :key="item.id" :value="item.id">{{ item.name }}</option>
                    </select>
                </label>

                <fieldset class="mt-6">
                    <legend class="font-ui text-sm font-semibold text-text-primary">Días de atención</legend>
                    <p class="mt-1 font-body text-sm text-text-secondary">Marca un día como laborable y define su intervalo. Los días sin marcar no se ofrecen en la reserva pública.</p>
                    <ul class="mt-3 divide-y divide-border-default rounded-lg border border-border-default">
                        <li v-for="day in days" :key="day.weekday" class="flex flex-wrap items-center gap-3 p-3">
                            <label class="flex min-w-40 items-center gap-2 font-ui text-sm font-semibold">
                                <input v-model="day.enabled" type="checkbox">
                                {{ day.label }}
                            </label>
                            <label class="font-ui text-xs font-semibold text-text-secondary">Inicio
                                <UiInput v-model="day.starts_at" type="time" :disabled="!day.enabled" class="mt-1" />
                            </label>
                            <label class="font-ui text-xs font-semibold text-text-secondary">Fin
                                <UiInput v-model="day.ends_at" type="time" :disabled="!day.enabled" class="mt-1" />
                            </label>
                        </li>
                    </ul>
                </fieldset>

                <div class="mt-6 flex flex-wrap gap-3">
                    <UiButton type="button" :loading="saving" @click="requestSave">Guardar horarios</UiButton>
                    <RouterLink to="/admin/profesionales" class="inline-flex min-h-11 items-center rounded-md border border-border-strong bg-action-secondary px-4 font-ui text-sm font-bold text-action-secondary-foreground hover:bg-action-secondary-hover">Volver a profesionales</RouterLink>
                </div>
            </template>
        </UiCard>

        <UiConfirmDialog
            :open="confirming"
            title="Confirmar cambio de horarios"
            message="Cambiar los horarios puede afectar la disponibilidad de reservas futuras de este profesional. ¿Deseas guardar los cambios?"
            confirm-label="Guardar horarios"
            :pending="saving"
            @cancel="confirming = false"
            @confirm="save"
        />
    </AdminLayout>
</template>
