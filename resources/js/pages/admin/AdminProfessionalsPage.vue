<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { RouterLink } from 'vue-router';
import AdminLayout from '../../layouts/AdminLayout.vue';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';
import UiConfirmDialog from '../../components/ui/UiConfirmDialog.vue';
import UiFormField from '../../components/ui/UiFormField.vue';
import UiInput from '../../components/ui/UiInput.vue';
import { ApiError } from '../../services/http';
import { adminCatalogApi, type AdminService } from '../../services/api/adminCatalog';
import { adminOperationsApi, type AdminProfessional } from '../../services/api/adminOperations';

const professionals = ref<AdminProfessional[]>([]);
const services = ref<AdminService[]>([]);
const loading = ref(true);
const error = ref('');
const message = ref('');
const saving = ref(false);
const editingId = ref<number | null>(null);
const pendingDelete = ref<AdminProfessional | null>(null);
const deleting = ref(false);
const serverErrors = reactive<Record<string, string>>({});

const filters = reactive({
    q: '',
    active: '' as '' | 'true' | 'false',
    service_id: '' as '' | number,
});

const form = reactive({
    name: '',
    active: true,
    serviceIds: [] as number[],
});

function clearServerErrors(): void {
    Object.keys(serverErrors).forEach((key) => delete serverErrors[key]);
}

function resetForm(): void {
    editingId.value = null;
    form.name = '';
    form.active = true;
    form.serviceIds = [];
    clearServerErrors();
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const params: { q?: string; active?: boolean; service_id?: number } = {};
        if (filters.q.trim()) params.q = filters.q.trim();
        if (filters.active !== '') params.active = filters.active === 'true';
        if (filters.service_id !== '') params.service_id = Number(filters.service_id);

        const response = await adminOperationsApi.professionals.list(params);
        professionals.value = response;
    } catch {
        error.value = 'No pudimos cargar los profesionales.';
    } finally {
        loading.value = false;
    }
}

async function loadServices(): Promise<void> {
    try {
        const response = await adminCatalogApi.services.list();
        services.value = response.filter((service) => service.active);
    } catch {
        // The catalog is optional for managing professionals; keep the page usable.
    }
}

function startEdit(professional: AdminProfessional): void {
    editingId.value = professional.id;
    form.name = professional.name;
    form.active = professional.active;
    form.serviceIds = professional.services.map((service) => service.id);
    clearServerErrors();
}

function toggleService(id: number): void {
    const index = form.serviceIds.indexOf(id);

    if (index === -1) {
        form.serviceIds.push(id);
    } else {
        form.serviceIds.splice(index, 1);
    }
}

async function save(): Promise<void> {
    if (saving.value) return;

    clearServerErrors();
    message.value = '';
    error.value = '';

    if (!form.name.trim()) {
        serverErrors.name = 'El nombre es obligatorio.';
        error.value = 'Revisa los datos marcados para guardar.';
        return;
    }

    saving.value = true;

    const payload = {
        name: form.name.trim(),
        active: form.active,
        service_ids: form.serviceIds,
    };

    try {
        if (editingId.value) {
            await adminOperationsApi.professionals.update(editingId.value, payload);
            message.value = 'Profesional actualizado.';
        } else {
            await adminOperationsApi.professionals.create(payload);
            message.value = 'Profesional creado.';
        }
        resetForm();
        await load();
    } catch (exception) {
        if (exception instanceof ApiError) {
            if (exception.errors) {
                Object.entries(exception.errors).forEach(([key, messages]) => {
                    serverErrors[key] = messages[0] ?? 'Revisa este campo.';
                });
            }
            error.value = exception.status === 422 ? 'Revisa los datos marcados para guardar.' : exception.message;
        } else {
            error.value = 'No pudimos guardar el profesional.';
        }
    } finally {
        saving.value = false;
    }
}

async function toggle(professional: AdminProfessional): Promise<void> {
    try {
        await adminOperationsApi.professionals.setStatus(professional.id, !professional.active);
        await load();
    } catch {
        error.value = 'No pudimos cambiar el estado.';
    }
}

async function confirmDelete(): Promise<void> {
    if (!pendingDelete.value || deleting.value) return;

    deleting.value = true;
    error.value = '';

    try {
        await adminOperationsApi.professionals.remove(pendingDelete.value.id);
        pendingDelete.value = null;
        await load();
    } catch (exception) {
        if (exception instanceof ApiError && exception.status === 409) {
            error.value = 'No se puede eliminar: el profesional tiene citas asociadas. Desactívalo en su lugar.';
        } else {
            error.value = exception instanceof ApiError ? exception.message : 'No pudimos eliminar el profesional.';
        }
        pendingDelete.value = null;
    } finally {
        deleting.value = false;
    }
}

onMounted(() => {
    void loadServices();
    void load();
});
</script>

<template>
    <AdminLayout>
        <UiCard>
            <h1 class="font-display text-4xl leading-none text-text-primary">Profesionales</h1>
            <p class="mt-3 font-body text-sm text-text-secondary">
                Registra únicamente profesionales reales. Los servicios asignados determinan quién aparece en el flujo público de reservas.
            </p>

            <p v-if="message" role="status" class="mt-4 rounded-md border border-brand-turquoise bg-surface-subtle p-3 font-body text-sm">{{ message }}</p>
            <p v-if="error" role="alert" class="mt-4 rounded-md border border-state-error bg-red-50 p-3 font-body text-sm text-state-error">{{ error }}</p>

            <form class="mt-6 grid gap-4 rounded-lg border border-border-default p-4 sm:grid-cols-2" @submit.prevent="save">
                <UiFormField id="professional-name" label="Nombre" required :error="serverErrors.name">
                    <template #default="{ inputId, describedBy, invalid }">
                        <UiInput :id="inputId" v-model="form.name" :aria-describedby="describedBy" :invalid="invalid" />
                    </template>
                </UiFormField>
                <label class="flex items-center gap-2 self-end font-ui text-sm font-semibold">
                    <input v-model="form.active" type="checkbox"> Activo
                </label>
                <fieldset class="sm:col-span-2">
                    <legend class="font-ui text-sm font-semibold">Servicios asignados</legend>
                    <p v-if="!services.length" class="mt-1 font-body text-sm text-text-secondary">No hay servicios activos en el catálogo.</p>
                    <div class="mt-2 flex flex-wrap gap-3">
                        <label v-for="service in services" :key="service.id" class="flex items-center gap-2 font-body text-sm">
                            <input type="checkbox" :checked="form.serviceIds.includes(service.id)" @change="toggleService(service.id)">
                            {{ service.name }}
                        </label>
                    </div>
                </fieldset>
                <div class="flex gap-3 sm:col-span-2">
                    <UiButton type="submit" :loading="saving">{{ editingId ? 'Guardar cambios' : 'Crear profesional' }}</UiButton>
                    <UiButton v-if="editingId" type="button" variant="secondary" @click="resetForm">Cancelar</UiButton>
                </div>
            </form>

            <form class="mt-6 flex flex-wrap items-end gap-3" @submit.prevent="load">
                <UiFormField id="filter-q" label="Buscar por nombre">
                    <template #default="{ inputId }">
                        <UiInput :id="inputId" v-model="filters.q" placeholder="Nombre" />
                    </template>
                </UiFormField>
                <label class="block font-ui text-sm font-semibold">Estado
                    <select v-model="filters.active" class="mt-1 block min-h-11 rounded-md border border-border-default px-3 py-2 font-body text-base">
                        <option value="">Todos</option>
                        <option value="true">Activos</option>
                        <option value="false">Inactivos</option>
                    </select>
                </label>
                <label class="block font-ui text-sm font-semibold">Servicio
                    <select v-model="filters.service_id" class="mt-1 block min-h-11 rounded-md border border-border-default px-3 py-2 font-body text-base">
                        <option value="">Todos</option>
                        <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }}</option>
                    </select>
                </label>
                <UiButton type="submit" variant="secondary">Filtrar</UiButton>
            </form>

            <p v-if="loading" role="status" class="mt-6 font-body text-sm text-text-secondary">Cargando profesionales...</p>
            <p v-else-if="!professionals.length" class="mt-6 font-body text-sm text-text-secondary">Sin profesionales registrados. Crea el primero con el formulario de arriba.</p>
            <ul v-else class="mt-6 divide-y divide-border-default">
                <li v-for="professional in professionals" :key="professional.id" class="flex flex-wrap items-center justify-between gap-3 py-4">
                    <div>
                        <p class="font-ui font-bold">
                            {{ professional.name }}
                            <span class="ml-2 font-body text-xs text-text-secondary">{{ professional.active ? 'Activo' : 'Inactivo' }}</span>
                        </p>
                        <p class="mt-1 font-body text-sm text-text-secondary">
                            Servicios: {{ professional.services.length ? professional.services.map((service) => service.name).join(', ') : 'Ninguno' }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <UiButton type="button" variant="secondary" @click="startEdit(professional)">Editar</UiButton>
                        <UiButton type="button" variant="secondary" @click="toggle(professional)">{{ professional.active ? 'Desactivar' : 'Activar' }}</UiButton>
                        <RouterLink :to="`/admin/profesionales/${professional.id}/horarios`" class="inline-flex min-h-11 items-center rounded-md border border-border-strong bg-action-secondary px-4 font-ui text-sm font-bold text-action-secondary-foreground hover:bg-action-secondary-hover">Horarios</RouterLink>
                        <UiButton type="button" variant="secondary" @click="pendingDelete = professional">Eliminar</UiButton>
                    </div>
                </li>
            </ul>
        </UiCard>

        <UiConfirmDialog
            :open="pendingDelete !== null"
            title="Eliminar profesional"
            :message="`¿Confirmas eliminar ${pendingDelete?.name ?? ''}? Esta acción no se puede deshacer. Si tiene citas, la operación será rechazada.`"
            confirm-label="Eliminar"
            :pending="deleting"
            @cancel="pendingDelete = null"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>