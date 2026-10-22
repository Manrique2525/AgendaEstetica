<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import AdminLayout from '../../layouts/AdminLayout.vue';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';
import UiFormField from '../../components/ui/UiFormField.vue';
import UiInput from '../../components/ui/UiInput.vue';
import { ApiError } from '../../services/http';
import { adminOperationsApi } from '../../services/api/adminOperations';

interface DayConfig {
    weekday: number;
    label: string;
    enabled: boolean;
    opens_at: string;
    closes_at: string;
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

const loading = ref(true);
const error = ref('');
const message = ref('');
const saving = ref(false);
const notFound = ref(false);

const form = reactive({
    name: '',
    phone: '',
    timezone: '',
    max_simultaneous_clients: 1,
});

const days = reactive<DayConfig[]>(
    WEEKDAYS.map((day) => ({ ...day, enabled: false, opens_at: '10:00', closes_at: '20:00' })),
);

const serverErrors = reactive<Record<string, string>>({});

const enabledDays = computed(() => days.filter((day) => day.enabled));

function clearServerErrors(): void {
    Object.keys(serverErrors).forEach((key) => delete serverErrors[key]);
}

function applyServerErrors(exception: ApiError): void {
    clearServerErrors();

    if (exception.errors) {
        Object.entries(exception.errors).forEach(([key, messages]) => {
            serverErrors[key] = messages[0] ?? 'Revisa este campo.';
        });
    }
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    notFound.value = false;

    try {
        const profile = await adminOperationsApi.businessProfile.get();
        form.name = profile.name;
        form.phone = profile.phone;
        form.timezone = profile.timezone;
        form.max_simultaneous_clients = profile.max_simultaneous_clients;

        days.forEach((day) => {
            const hour = profile.hours.find((item) => item.weekday === day.weekday);
            day.enabled = Boolean(hour);
            day.opens_at = hour ? hour.opens_at.slice(0, 5) : '10:00';
            day.closes_at = hour ? hour.closes_at.slice(0, 5) : '20:00';
        });
    } catch (exception) {
        if (exception instanceof ApiError && exception.status === 404) {
            notFound.value = true;
            error.value = 'Aún no existe un perfil de negocio. Completa los datos y guarda para crearlo.';
        } else {
            error.value = 'No pudimos cargar la configuración del negocio.';
        }
    } finally {
        loading.value = false;
    }
}

function clientValidate(): string {
    clearServerErrors();

    if (!form.name.trim()) {
        serverErrors.name = 'El nombre comercial es obligatorio.';
    }
    if (!form.phone.trim()) {
        serverErrors.phone = 'El teléfono es obligatorio.';
    }
    if (!form.timezone.trim()) {
        serverErrors.timezone = 'La zona horaria es obligatoria.';
    }
    if (form.max_simultaneous_clients < 1) {
        serverErrors.max_simultaneous_clients = 'Debe ser al menos 1.';
    }
    for (const day of enabledDays.value) {
        if (!day.opens_at || !day.closes_at) {
            serverErrors.hours = `Completa el horario de ${day.label}.`;
            break;
        }
        if (day.opens_at >= day.closes_at) {
            serverErrors.hours = `En ${day.label}, la hora de apertura debe ser anterior al cierre.`;
            break;
        }
    }

    return serverErrors.name || serverErrors.phone || serverErrors.timezone
        || serverErrors.max_simultaneous_clients || serverErrors.hours || '';
}

async function save(): Promise<void> {
    if (saving.value) return;

    message.value = '';
    if (clientValidate()) {
        error.value = 'Revisa los datos marcados para guardar.';
        return;
    }

    saving.value = true;
    error.value = '';

    const payload = {
        name: form.name.trim(),
        phone: form.phone.trim(),
        timezone: form.timezone.trim(),
        max_simultaneous_clients: Number(form.max_simultaneous_clients),
        hours: enabledDays.value.map((day) => ({
            weekday: day.weekday,
            opens_at: day.opens_at,
            closes_at: day.closes_at,
        })),
    };

    try {
        const profile = await adminOperationsApi.businessProfile.update(payload);
        notFound.value = false;
        message.value = 'Configuración del negocio guardada.';
        form.name = profile.name;
        form.phone = profile.phone;
        form.timezone = profile.timezone;
        form.max_simultaneous_clients = profile.max_simultaneous_clients;
    } catch (exception) {
        if (exception instanceof ApiError) {
            applyServerErrors(exception);
            error.value = exception.status === 422
                ? 'Revisa los datos marcados para guardar.'
                : exception.message;
        } else {
            error.value = 'No pudimos guardar la configuración del negocio.';
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
            <h1 class="font-display text-4xl leading-none text-text-primary">Configuración del negocio</h1>
            <p class="mt-3 font-body text-sm text-text-secondary">
                Estos datos alimentan la zona horaria y los horarios de atención del flujo público de reservas.
                No se cargan datos reales automáticamente: solo se guarda lo que registres aquí.
            </p>

            <p v-if="message" role="status" class="mt-4 rounded-md border border-brand-turquoise bg-surface-subtle p-3 font-body text-sm">{{ message }}</p>
            <p v-if="error" role="alert" class="mt-4 rounded-md border border-state-error bg-red-50 p-3 font-body text-sm text-state-error">{{ error }}</p>

            <p v-if="loading" role="status" class="mt-6 font-body text-sm text-text-secondary">Cargando configuración...</p>

            <template v-else>
                <form class="mt-6 grid gap-5 sm:grid-cols-2" @submit.prevent="save">
                    <UiFormField id="business-name" label="Nombre comercial" required :error="serverErrors.name">
                        <template #default="{ inputId, describedBy, invalid }">
                            <UiInput :id="inputId" v-model="form.name" :aria-describedby="describedBy" :invalid="invalid" />
                        </template>
                    </UiFormField>
                    <UiFormField id="business-phone" label="Teléfono" required :error="serverErrors.phone">
                        <template #default="{ inputId, describedBy, invalid }">
                            <UiInput :id="inputId" v-model="form.phone" type="tel" :aria-describedby="describedBy" :invalid="invalid" />
                        </template>
                    </UiFormField>
                    <UiFormField id="business-timezone" label="Zona horaria (IANA)" required help="Ejemplo: America/Mexico_City" :error="serverErrors.timezone">
                        <template #default="{ inputId, describedBy, invalid }">
                            <UiInput :id="inputId" v-model="form.timezone" :aria-describedby="describedBy" :invalid="invalid" />
                        </template>
                    </UiFormField>
                    <UiFormField id="business-capacity" label="Clientes simultáneos máximos" required :error="serverErrors.max_simultaneous_clients">
                        <template #default="{ inputId, describedBy, invalid }">
                            <input :id="inputId" v-model.number="form.max_simultaneous_clients" type="number" min="1" :aria-describedby="describedBy" :aria-invalid="invalid || undefined" class="mt-1 block min-h-11 w-full rounded-md border px-3 py-2 font-body text-base" :class="invalid ? 'border-state-error' : 'border-border-default'">
                        </template>
                    </UiFormField>

                    <fieldset class="sm:col-span-2">
                        <legend class="font-ui text-sm font-semibold text-text-primary">Horarios generales de atención</legend>
                        <p class="mt-1 font-body text-sm text-text-secondary">Activa los días que el negocio atiende y define su horario.</p>
                        <p v-if="serverErrors.hours" role="alert" class="mt-2 font-body text-sm text-state-error">{{ serverErrors.hours }}</p>
                        <ul class="mt-3 divide-y divide-border-default rounded-lg border border-border-default">
                            <li v-for="day in days" :key="day.weekday" class="flex flex-wrap items-center gap-3 p-3">
                                <label class="flex min-w-40 items-center gap-2 font-ui text-sm font-semibold">
                                    <input v-model="day.enabled" type="checkbox">
                                    {{ day.label }}
                                </label>
                                <label class="font-ui text-xs font-semibold text-text-secondary">Apertura
                                    <UiInput v-model="day.opens_at" type="time" :disabled="!day.enabled" class="mt-1" />
                                </label>
                                <label class="font-ui text-xs font-semibold text-text-secondary">Cierre
                                    <UiInput v-model="day.closes_at" type="time" :disabled="!day.enabled" class="mt-1" />
                                </label>
                            </li>
                        </ul>
                    </fieldset>

                    <div class="sm:col-span-2">
                        <UiButton type="submit" :loading="saving">Guardar configuración</UiButton>
                    </div>
                </form>
            </template>
        </UiCard>
    </AdminLayout>
</template>
