<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import AdminLayout from '../../layouts/AdminLayout.vue';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';
import UiConfirmDialog from '../../components/ui/UiConfirmDialog.vue';
import UiTextarea from '../../components/ui/UiTextarea.vue';
import { ApiError } from '../../services/http';
import { adminCatalogApi, type AdminService, type AdminServiceCategory, type PricingType } from '../../services/api/adminCatalog';

const services = ref<AdminService[]>([]);
const categories = ref<AdminServiceCategory[]>([]);
const loading = ref(true);
const error = ref('');
const message = ref('');
const saving = ref(false);
const editingId = ref<number | null>(null);
const pendingDelete = ref<AdminService | null>(null);
const deleting = ref(false);

const form = reactive({
    service_category_id: 0,
    name: '',
    description: '',
    duration_minutes: 60,
    pricing_type: 'fixed' as PricingType,
    price: '',
    active: true,
});

const isVariable = computed(() => form.pricing_type === 'variable');

function resetForm(): void {
    editingId.value = null;
    form.service_category_id = categories.value[0]?.id ?? 0;
    form.name = '';
    form.description = '';
    form.duration_minutes = 60;
    form.pricing_type = 'fixed';
    form.price = '';
    form.active = true;
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const [serviceList, categoryList] = await Promise.all([
            adminCatalogApi.services.list(),
            adminCatalogApi.serviceCategories.list(),
        ]);
        services.value = serviceList.data;
        categories.value = categoryList.data;
        if (!form.service_category_id) form.service_category_id = categoryList.data[0]?.id ?? 0;
    } catch {
        error.value = 'No pudimos cargar los servicios.';
    } finally {
        loading.value = false;
    }
}

function startEdit(service: AdminService): void {
    editingId.value = service.id;
    form.service_category_id = service.category?.id ?? 0;
    form.name = service.name;
    form.description = service.description ?? '';
    form.duration_minutes = service.duration_minutes;
    form.pricing_type = service.pricing_type;
    form.price = service.price ?? '';
    form.active = service.active;
}

async function save(): Promise<void> {
    if (saving.value) return;
    saving.value = true;
    error.value = '';
    message.value = '';
    const payload = {
        service_category_id: form.service_category_id,
        name: form.name,
        description: form.description || null,
        duration_minutes: Number(form.duration_minutes),
        pricing_type: form.pricing_type,
        price: isVariable.value ? null : form.price,
        active: form.active,
    };

    try {
        if (editingId.value) {
            await adminCatalogApi.services.update(editingId.value, payload);
            message.value = 'Servicio actualizado.';
        } else {
            await adminCatalogApi.services.create(payload);
            message.value = 'Servicio creado.';
        }
        resetForm();
        await load();
    } catch (exception) {
        error.value = exception instanceof ApiError ? exception.message : 'No pudimos guardar el servicio.';
    } finally {
        saving.value = false;
    }
}

async function toggle(service: AdminService): Promise<void> {
    try {
        await adminCatalogApi.services.setStatus(service.id, !service.active);
        await load();
    } catch {
        error.value = 'No pudimos cambiar el estado.';
    }
}

async function confirmDelete(): Promise<void> {
    if (!pendingDelete.value || deleting.value) return;
    deleting.value = true;
    try {
        await adminCatalogApi.services.remove(pendingDelete.value.id);
        pendingDelete.value = null;
        await load();
    } catch (exception) {
        error.value = exception instanceof ApiError ? exception.message : 'No pudimos eliminar el servicio.';
    } finally {
        deleting.value = false;
    }
}

onMounted(load);
</script>

<template>
    <AdminLayout>
        <UiCard>
            <h1 class="font-display text-4xl leading-none text-text-primary">Servicios</h1>
            <p class="mt-3 font-body text-sm text-text-secondary">Catálogo publicado en el sitio y disponible para agendar.</p>

            <p v-if="message" role="status" class="mt-4 rounded-md border border-brand-turquoise bg-surface-subtle p-3 font-body text-sm">{{ message }}</p>
            <p v-if="error" role="alert" class="mt-4 rounded-md border border-state-error bg-red-50 p-3 font-body text-sm text-state-error">{{ error }}</p>

            <form class="mt-6 grid gap-4 rounded-lg border border-border-default p-4 sm:grid-cols-2" @submit.prevent="save">
                <label class="block font-ui text-sm font-semibold">Categoría
                    <select v-model.number="form.service_category_id" required class="mt-1 block min-h-11 w-full rounded-md border border-border-default px-3 py-2 font-body text-base">
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                </label>
                <label class="block font-ui text-sm font-semibold">Nombre<input v-model="form.name" required class="mt-1 block min-h-11 w-full rounded-md border border-border-default px-3 py-2 font-body text-base"></label>
                <div class="sm:col-span-2">
                    <label class="block font-ui text-sm font-semibold" for="service-description">Descripción (opcional)</label>
                    <UiTextarea id="service-description" v-model="form.description" class="mt-1" />
                </div>
                <label class="block font-ui text-sm font-semibold">Duración (minutos)<input v-model.number="form.duration_minutes" type="number" min="1" max="600" required class="mt-1 block min-h-11 w-full rounded-md border border-border-default px-3 py-2 font-body text-base"></label>
                <label class="block font-ui text-sm font-semibold">Tipo de precio
                    <select v-model="form.pricing_type" class="mt-1 block min-h-11 w-full rounded-md border border-border-default px-3 py-2 font-body text-base">
                        <option value="fixed">Precio fijo</option>
                        <option value="starting_from">Desde</option>
                        <option value="variable">Variable</option>
                    </select>
                </label>
                <label v-if="!isVariable" class="block font-ui text-sm font-semibold">Precio (MXN)<input v-model="form.price" type="number" step="0.01" min="0" required class="mt-1 block min-h-11 w-full rounded-md border border-border-default px-3 py-2 font-body text-base"></label>
                <label class="flex items-center gap-2 font-ui text-sm font-semibold"><input v-model="form.active" type="checkbox"> Activo</label>
                <div class="flex gap-3 sm:col-span-2">
                    <UiButton type="submit" :loading="saving">{{ editingId ? 'Guardar cambios' : 'Crear servicio' }}</UiButton>
                    <UiButton v-if="editingId" type="button" variant="secondary" @click="resetForm">Cancelar</UiButton>
                </div>
            </form>

            <p v-if="loading" role="status" class="mt-6 font-body text-sm text-text-secondary">Cargando...</p>
            <p v-else-if="!services.length" class="mt-6 font-body text-sm text-text-secondary">Sin servicios registrados.</p>
            <ul v-else class="mt-6 divide-y divide-border-default">
                <li v-for="service in services" :key="service.id" class="flex flex-wrap items-center justify-between gap-3 py-4">
                    <div>
                        <p class="font-ui font-bold">
{{ service.name }}
                            <span class="ml-2 font-body text-xs text-text-secondary">{{ service.active ? 'Activo' : 'Inactivo' }} · {{ service.category?.name }} · {{ service.duration_minutes }} min · {{ service.pricing_type === 'variable' ? 'Precio variable' : `$${service.price}` }} · {{ service.appointments_count ?? 0 }} citas</span>
                        </p>
                        <p v-if="service.description" class="mt-1 font-body text-sm text-text-secondary">{{ service.description }}</p>
                    </div>
                    <div class="flex gap-2">
                        <UiButton type="button" variant="secondary" @click="startEdit(service)">Editar</UiButton>
                        <UiButton type="button" variant="secondary" @click="toggle(service)">{{ service.active ? 'Desactivar' : 'Activar' }}</UiButton>
                        <UiButton type="button" variant="secondary" @click="pendingDelete = service">Eliminar</UiButton>
                    </div>
                </li>
            </ul>
        </UiCard>

        <UiConfirmDialog
            :open="pendingDelete !== null"
            title="Eliminar servicio"
            :message="`¿Confirmas eliminar ${pendingDelete?.name ?? ''}? Esta acción no se puede deshacer.`"
            confirm-label="Eliminar"
            :pending="deleting"
            @cancel="pendingDelete = null"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>