<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AdminLayout from '../../layouts/AdminLayout.vue';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';
import UiFormField from '../../components/ui/UiFormField.vue';
import UiInput from '../../components/ui/UiInput.vue';
import { ApiError } from '../../services/http';
import {
    adminAgendaApi,
    type AdminAgendaContext,
    type AgendaAppointment,
    type AgendaLookupCustomer,
    type AgendaLookupProfessional,
    type AgendaLookupService,
    type AppointmentStatus,
} from '../../services/api/adminAgenda';
import { addCalendarDays, businessDateToday, formatBusinessTime, resolveLocalDateTime } from '../../utils/adminAgendaTime';

const route = useRoute();
const router = useRouter();
const context = ref<AdminAgendaContext | null>(null);
const appointments = ref<AgendaAppointment[]>([]);
const professionals = ref<AgendaLookupProfessional[]>([]);
const services = ref<AgendaLookupService[]>([]);
const customers = ref<AgendaLookupCustomer[]>([]);
const view = ref(route.query.view === 'list' ? 'list' : 'day');
const selectedDate = ref(typeof route.query.from === 'string' ? route.query.from : '');
const rangeFrom = ref(selectedDate.value);
const rangeTo = ref(typeof route.query.to === 'string' ? route.query.to : '');
const professionalId = ref('');
const serviceId = ref('');
const status = ref<AppointmentStatus | ''>('');
const customerQuery = ref('');
const customerId = ref('');
const loading = ref(true);
const error = ref('');
const requestVersion = ref(0);
const customerSearchVersion = ref(0);
const createOpen = ref(false);
const createCustomerQuery = ref('');
const createCustomers = ref<AgendaLookupCustomer[]>([]);
const createCustomerId = ref('');
const createServiceId = ref('');
const createProfessionalId = ref('');
const createDate = ref('');
const createTime = ref('');
const createProfessionals = ref<AgendaLookupProfessional[]>([]);
const createPending = ref(false);
const createError = ref('');
const createSuccess = ref('');
const createSearchVersion = ref(0);

const statusLabels: Record<AppointmentStatus, string> = {
    confirmed: 'Confirmada',
    cancelled: 'Cancelada',
    completed: 'Completada',
    no_show: 'No asistió',
};

const rangeDates = computed(() => view.value === 'day'
    ? { from: selectedDate.value, to: addCalendarDays(selectedDate.value, 1) }
    : { from: rangeFrom.value, to: rangeTo.value });

function apiErrorMessage(value: unknown): string {
    if (value instanceof ApiError && value.status === 401) {
        return 'La sesión administrativa ya no es válida.';
    }

    return 'No fue posible cargar la agenda. Intenta nuevamente.';
}

async function loadLookups(): Promise<void> {
    [professionals.value, services.value] = await Promise.all([
        adminAgendaApi.listProfessionals(),
        adminAgendaApi.listServices(),
    ]);
}

async function searchCustomers(): Promise<void> {
    if (customerQuery.value.trim().length < 2) {
        customers.value = [];
        return;
    }

    const version = ++customerSearchVersion.value;
    const results = await adminAgendaApi.searchCustomers(customerQuery.value.trim());

    if (version === customerSearchVersion.value) {
        customers.value = results;
    }
}

async function searchCreateCustomers(): Promise<void> {
    if (createCustomerQuery.value.trim().length < 2) {
        createCustomers.value = [];
        return;
    }

    const version = ++createSearchVersion.value;
    const results = await adminAgendaApi.searchCustomers(createCustomerQuery.value.trim());

    if (version === createSearchVersion.value) {
        createCustomers.value = results;
    }
}

async function loadCreateProfessionals(): Promise<void> {
    createProfessionals.value = await adminAgendaApi.listProfessionals(
        createServiceId.value ? Number(createServiceId.value) : undefined,
    );
}

function selectCreateCustomer(customer: AgendaLookupCustomer): void {
    createCustomerId.value = String(customer.id);
    createCustomerQuery.value = `${customer.name} · ${customer.phone}`;
    createCustomers.value = [];
}

function apiMutationMessage(value: unknown): string {
    if (value instanceof ApiError && value.status === 409) {
        return value.code === 'appointment_state_conflict'
            ? 'La cita ya no puede procesarse en su estado actual.'
            : 'El horario seleccionado ya no está disponible.';
    }

    if (value instanceof ApiError && value.status === 422) {
        return 'Revisa los datos y el horario seleccionados.';
    }

    return 'No fue posible completar la operación.';
}

function createEndTime(): string | null {
    const service = services.value.find((item) => item.id === Number(createServiceId.value));

    if (!context.value || !service || !createDate.value || !createTime.value) {
        return null;
    }

    try {
        return new Date(Date.parse(resolveLocalDateTime(createDate.value, createTime.value, context.value.timezone)) + service.duration_minutes * 60_000).toISOString();
    } catch {
        return null;
    }
}

async function submitCreate(): Promise<void> {
    const endsAt = createEndTime();

    if (!createCustomerId.value || !createServiceId.value || !createProfessionalId.value || !createDate.value || !createTime.value || !endsAt || !context.value) {
        createError.value = 'Completa Customer, Service, Professional, fecha y hora válida.';
        return;
    }

    createPending.value = true;
    createError.value = '';
    createSuccess.value = '';

    try {
        const startsAt = resolveLocalDateTime(createDate.value, createTime.value, context.value.timezone);
        await adminAgendaApi.createAppointment({
            customer_id: Number(createCustomerId.value),
            service_id: Number(createServiceId.value),
            professional_id: Number(createProfessionalId.value),
            starts_at: startsAt,
            ends_at: endsAt,
        });
        createSuccess.value = 'Cita creada correctamente.';
        createOpen.value = false;
        createCustomerQuery.value = '';
        createCustomerId.value = '';
        createServiceId.value = '';
        createProfessionalId.value = '';
        await loadAppointments();
    } catch (reason) {
        createError.value = apiMutationMessage(reason);
    } finally {
        createPending.value = false;
    }
}

async function loadAppointments(): Promise<void> {
    if (!rangeDates.value.from || !rangeDates.value.to) {
        return;
    }

    const version = ++requestVersion.value;
    loading.value = true;
    error.value = '';

    try {
        const result = await adminAgendaApi.listAppointments({
            ...rangeDates.value,
            professional_id: professionalId.value ? Number(professionalId.value) : undefined,
            service_id: serviceId.value ? Number(serviceId.value) : undefined,
            status: status.value || undefined,
            customer_id: customerId.value ? Number(customerId.value) : undefined,
        });

        if (version === requestVersion.value) {
            appointments.value = result;
        }
    } catch (reason) {
        if (version === requestVersion.value) {
            error.value = apiErrorMessage(reason);
        }
    } finally {
        if (version === requestVersion.value) {
            loading.value = false;
        }
    }
}

function syncQuery(): void {
    void router.replace({
        query: {
            view: view.value,
            from: rangeDates.value.from,
            to: rangeDates.value.to,
            ...(professionalId.value ? { professional_id: professionalId.value } : {}),
            ...(serviceId.value ? { service_id: serviceId.value } : {}),
            ...(status.value ? { status: status.value } : {}),
            ...(customerId.value ? { customer_id: customerId.value } : {}),
        },
    });
}

function moveDay(days: number): void {
    selectedDate.value = addCalendarDays(selectedDate.value, days);
    syncQuery();
    void loadAppointments();
}

function selectCustomer(customer: AgendaLookupCustomer): void {
    customerId.value = String(customer.id);
    customerQuery.value = customer.name;
    customers.value = [];
    syncQuery();
    void loadAppointments();
}

function appointmentTime(appointment: AgendaAppointment): string {
    return context.value ? `${formatBusinessTime(appointment.starts_at, context.value.timezone)} - ${formatBusinessTime(appointment.ends_at, context.value.timezone)}` : '';
}

function appointmentDate(appointment: AgendaAppointment): string {
    if (! context.value) return '';
    return new Intl.DateTimeFormat('es-MX', { timeZone: context.value.timezone, dateStyle: 'full' }).format(new Date(appointment.starts_at));
}

function applyRange(): void {
    if (view.value === 'list') {
        syncQuery();
        void loadAppointments();
    }
}

onMounted(async () => {
    try {
        context.value = await adminAgendaApi.getContext();
        if (!selectedDate.value) {
            selectedDate.value = businessDateToday(context.value.timezone);
        }
        if (!rangeFrom.value) rangeFrom.value = selectedDate.value;
        if (!rangeTo.value) rangeTo.value = addCalendarDays(rangeFrom.value, 7);
        await loadLookups();
        await loadCreateProfessionals();
        await loadAppointments();
    } catch (reason) {
        error.value = apiErrorMessage(reason);
        loading.value = false;
    }
});

watch(createServiceId, () => {
    createProfessionalId.value = '';
    void loadCreateProfessionals();
});

watch([professionalId, serviceId, status, customerId], () => {
    syncQuery();
    void loadAppointments();
});
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
            <header class="space-y-2">
                <p class="font-ui text-sm font-semibold uppercase tracking-[0.16em] text-text-secondary">Administración</p>
                <h1 class="font-display text-5xl leading-none text-text-primary">Agenda</h1>
                <p class="max-w-2xl font-body text-base text-text-secondary">Consulta las citas confirmadas y su historial operativo.</p>
                <UiButton class="mt-4" @click="createOpen = !createOpen; createDate = selectedDate">Nueva cita</UiButton>
            </header>

            <UiCard v-if="createOpen">
                <form class="space-y-5" aria-labelledby="create-appointment-title" @submit.prevent="submitCreate">
                    <h2 id="create-appointment-title" class="font-display text-3xl text-text-primary">Nueva cita</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                    <UiFormField id="create-customer" label="Cliente existente">
                            <UiInput id="create-customer" v-model="createCustomerQuery" type="text" autocomplete="off" required @input="void searchCreateCustomers()" />
                            <div v-if="createCustomers.length" class="mt-2 space-y-1 rounded-md border border-border-default bg-surface-elevated p-2" role="listbox" aria-label="Clientes para la cita">
                                <button v-for="customer in createCustomers" :key="customer.id" type="button" class="block w-full rounded px-3 py-2 text-left font-body text-sm hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring" @click="selectCreateCustomer(customer)">
                                    {{ customer.name }}<span class="ml-2 text-text-secondary">{{ customer.phone }}</span>
                                </button>
                            </div>
                        </UiFormField>
                        <UiFormField id="create-service" label="Servicio">
                            <select id="create-service" v-model="createServiceId" required class="min-h-11 w-full rounded-md border border-border-default bg-surface-elevated px-3 font-body text-base">
                                <option value="">Selecciona un servicio</option>
                                <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }} · {{ service.duration_minutes }} min</option>
                            </select>
                        </UiFormField>
                        <UiFormField id="create-professional" label="Profesional">
                            <select id="create-professional" v-model="createProfessionalId" required class="min-h-11 w-full rounded-md border border-border-default bg-surface-elevated px-3 font-body text-base">
                                <option value="">Selecciona un profesional</option>
                                <option v-for="professional in createProfessionals" :key="professional.id" :value="professional.id">{{ professional.name }}</option>
                            </select>
                        </UiFormField>
                        <UiFormField id="create-date" label="Fecha">
                            <UiInput id="create-date" v-model="createDate" type="date" required />
                        </UiFormField>
                        <UiFormField id="create-time" label="Hora de inicio">
                            <UiInput id="create-time" v-model="createTime" type="time" required />
                        </UiFormField>
                    </div>
                    <p v-if="createError" role="alert" class="font-body text-sm text-state-error">{{ createError }}</p>
                    <UiButton type="submit" :loading="createPending">Crear cita</UiButton>
                </form>
            </UiCard>
            <p v-if="createSuccess" role="status" aria-live="polite" class="font-body text-sm text-state-success">{{ createSuccess }}</p>

            <UiCard>
                <div class="flex flex-wrap gap-2" aria-label="Vista de agenda">
                    <UiButton :variant="view === 'day' ? 'primary' : 'secondary'" @click="view = 'day'; selectedDate = selectedDate || (context ? businessDateToday(context.timezone) : ''); syncQuery(); void loadAppointments()">Día</UiButton>
                    <UiButton :variant="view === 'list' ? 'primary' : 'secondary'" @click="view = 'list'; syncQuery(); void loadAppointments()">Lista</UiButton>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <UiFormField id="agenda-date" label="Fecha">
                        <UiInput id="agenda-date" v-model="selectedDate" type="date" @change="syncQuery(); void loadAppointments()" />
                    </UiFormField>
                    <div v-if="view === 'list'" class="grid gap-4 sm:grid-cols-2">
                        <UiFormField id="agenda-from" label="Desde">
                            <UiInput id="agenda-from" v-model="rangeFrom" type="date" />
                        </UiFormField>
                        <UiFormField id="agenda-to" label="Hasta">
                            <UiInput id="agenda-to" v-model="rangeTo" type="date" />
                        </UiFormField>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <UiButton variant="secondary" aria-label="Día anterior" @click="moveDay(-1)">Anterior</UiButton>
                    <UiButton variant="secondary" @click="selectedDate = context ? businessDateToday(context.timezone) : selectedDate; syncQuery(); void loadAppointments()">Hoy</UiButton>
                    <UiButton variant="secondary" aria-label="Día siguiente" @click="moveDay(1)">Siguiente</UiButton>
                    <UiButton v-if="view === 'list'" variant="secondary" @click="applyRange">Aplicar rango</UiButton>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <UiFormField id="agenda-professional" label="Profesional">
                        <select id="agenda-professional" v-model="professionalId" class="min-h-11 w-full rounded-md border border-border-default bg-surface-elevated px-3 font-body text-base" aria-label="Filtrar por profesional">
                            <option value="">Todos</option>
                            <option v-for="professional in professionals" :key="professional.id" :value="professional.id">{{ professional.name }}</option>
                        </select>
                    </UiFormField>
                    <UiFormField id="agenda-service" label="Servicio">
                        <select id="agenda-service" v-model="serviceId" class="min-h-11 w-full rounded-md border border-border-default bg-surface-elevated px-3 font-body text-base" aria-label="Filtrar por servicio">
                            <option value="">Todos</option>
                            <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }}</option>
                        </select>
                    </UiFormField>
                    <UiFormField id="agenda-status" label="Estado">
                        <select id="agenda-status" v-model="status" class="min-h-11 w-full rounded-md border border-border-default bg-surface-elevated px-3 font-body text-base" aria-label="Filtrar por estado">
                            <option value="">Todos</option>
                            <option value="confirmed">Confirmada</option>
                            <option value="cancelled">Cancelada</option>
                            <option value="completed">Completada</option>
                            <option value="no_show">No asistió</option>
                        </select>
                    </UiFormField>
                </div>

                <div class="mt-5 max-w-xl">
                    <UiFormField id="agenda-customer" label="Buscar cliente">
                        <UiInput id="agenda-customer" v-model="customerQuery" type="text" autocomplete="off" @input="void searchCustomers()" />
                    </UiFormField>
                    <div v-if="customers.length" class="mt-2 space-y-1 rounded-md border border-border-default bg-surface-elevated p-2" role="listbox" aria-label="Clientes encontrados">
                        <button v-for="customer in customers" :key="customer.id" type="button" class="block w-full rounded px-3 py-2 text-left font-body text-sm hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring" @click="selectCustomer(customer)">
                            {{ customer.name }}<span class="ml-2 text-text-secondary">{{ customer.phone }}</span>
                        </button>
                    </div>
                </div>
            </UiCard>

            <p v-if="loading" role="status" aria-live="polite" class="font-body text-text-secondary">Cargando agenda...</p>
            <div v-else-if="error" role="alert" class="rounded-md border border-state-error p-4 font-body text-state-error">{{ error }}</div>
            <div v-else-if="!appointments.length" class="rounded-md border border-border-default p-8 text-center font-body text-text-secondary">No hay citas para este periodo.</div>
            <div v-else class="space-y-4">
                <section v-for="appointment in appointments" :key="appointment.id" class="rounded-xl border border-border-default bg-surface-elevated p-5 shadow-sm">
                    <RouterLink :to="{ name: 'admin.agenda.detail', params: { id: appointment.id }, query: route.query }" class="block rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-ui text-lg font-bold text-text-primary">{{ appointmentTime(appointment) }}</p>
                                <p class="font-body text-sm text-text-secondary">{{ appointmentDate(appointment) }}</p>
                            </div>
                            <span class="rounded-full bg-surface-muted px-3 py-1 font-ui text-xs font-semibold">{{ statusLabels[appointment.status] }}</span>
                        </div>
                        <div class="mt-4 grid gap-1 font-body text-sm text-text-secondary sm:grid-cols-3">
                            <span><strong class="text-text-primary">Cliente:</strong> {{ appointment.customer.name }}</span>
                            <span><strong class="text-text-primary">Servicio:</strong> {{ appointment.service.name }}</span>
                            <span><strong class="text-text-primary">Profesional:</strong> {{ appointment.professional.name }}</span>
                        </div>
                    </RouterLink>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>
