<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { ApiError } from '@/services/http';
import AdminLayout from '@/layouts/AdminLayout.vue';
import CatalogPageHeader from '@/components/admin/CatalogPageHeader.vue';
import CatalogTable from '@/components/admin/CatalogTable.vue';
import CatalogFilters from '@/components/admin/CatalogFilters.vue';
import CatalogPagination from '@/components/admin/CatalogPagination.vue';
import UiConfirmDialog from '@/components/ui/UiConfirmDialog.vue';
import { adminCatalogApi, type AdminService, type AdminServiceCategory } from '@/services/api/adminCatalog';

type ServiceRow = AdminService & { categoryName: string };

const columns: { key: string; label: string; render?: (row: Record<string, unknown>) => string | number | null }[] = [
    { key: 'name', label: 'Nombre' },
    { key: 'categoryName', label: 'Categoría' },
    { key: 'duration_minutes', label: 'Duración', render: (row) => `${row.duration_minutes as number} min` },
    {
        key: 'pricing_type',
        label: 'Tipo',
        render: (row) => row.pricing_type === 'variable' ? 'Variable' : row.pricing_type === 'starting_from' ? 'Desde' : 'Fijo',
    },
    {
        key: 'price',
        label: 'Precio',
        render: (row) => row.pricing_type === 'variable' ? 'Variable' : row.price ? `$${row.price as string}` : '—',
    },
    { key: 'active', label: 'Estado', render: (row) => row.active ? 'Activo' : 'Inactivo' },
];

const services = ref<ServiceRow[]>([]);
const categories = ref<AdminServiceCategory[]>([]);
const loading = ref(true);
const error = ref('');

const pagination = reactive({
    currentPage: 1,
    perPage: 15,
});

const filters = reactive({
    q: '',
    active: '' as '' | 'true' | 'false',
    service_category_id: '' as '' | number,
});

const pendingDelete = ref<AdminService | null>(null);
const deleting = ref(false);

const lastPage = computed(() => Math.max(1, Math.ceil(services.value.length / pagination.perPage)));
const total = computed(() => services.value.length);
const pagedServices = computed(() => {
    const start = (pagination.currentPage - 1) * pagination.perPage;
    return services.value.slice(start, start + pagination.perPage);
});

function toRow(service: AdminService): ServiceRow {
    return { ...service, categoryName: service.category?.name ?? '—' };
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const params: Record<string, string | number | boolean> = {};

        if (filters.q.trim()) params.q = filters.q.trim();
        if (filters.active !== '') params.active = filters.active === 'true';
        if (filters.service_category_id !== '') params.service_category_id = Number(filters.service_category_id);

        const [servicesResponse, categoriesResponse] = await Promise.all([
            adminCatalogApi.services.list(params),
            adminCatalogApi.serviceCategories.list({ active: true }),
        ]);

        services.value = servicesResponse.map(toRow);
        categories.value = categoriesResponse;
        pagination.currentPage = 1;
    } catch {
        error.value = 'No pudimos cargar los servicios.';
    } finally {
        loading.value = false;
    }
}

async function toggle(row: Record<string, unknown>): Promise<void> {
    const service = row as unknown as AdminService;
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
    } catch (exception: unknown) {
        error.value = exception instanceof ApiError ? exception.message : 'No pudimos eliminar el servicio.';
    } finally {
        deleting.value = false;
    }
}

async function handleSearch(): Promise<void> {
    await load();
}

function handlePageChange(page: number): void {
    pagination.currentPage = page;
}

function editService(row: Record<string, unknown>): void {
    const service = row as unknown as AdminService;
    window.location.href = `/admin/catalogo/servicios/${service.id}/editar`;
}

onMounted(load);
</script>

<template>
    <AdminLayout>
        <CatalogPageHeader
            title="Servicios"
            description="Catálogo de servicios que se ofrecen a los clientes."
            :count="total"
            create-route="/admin/catalogo/servicios/nuevo"
            create-label="Nuevo servicio"
        />

        <p v-if="error" role="alert" class="mb-6 rounded-md border border-state-error bg-red-50 p-3 font-body text-sm text-state-error">{{ error }}</p>

        <CatalogFilters :search="filters.q" @search="(value) => { filters.q = value; handleSearch(); }" @clear="handleSearch">
            <template #filters>
                <select v-model="filters.active" class="min-h-11 rounded-md border border-border-default px-3 py-2 font-body text-sm">
                    <option value="">Todos los estados</option>
                    <option value="true">Activos</option>
                    <option value="false">Inactivos</option>
                </select>
                <select v-model="filters.service_category_id" class="min-h-11 rounded-md border border-border-default px-3 py-2 font-body text-sm">
                    <option value="">Todas las categorías</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </template>
        </CatalogFilters>

        <CatalogTable
            :columns="columns"
            :rows="pagedServices"
            :loading="loading"
            empty-message="Sin servicios registrados."
            row-key="id"
            :on-edit="editService"
            :on-toggle="toggle"
            :on-delete="(row) => { pendingDelete = row as unknown as AdminService; }"
        />

        <CatalogPagination
            :current-page="pagination.currentPage"
            :last-page="lastPage"
            :per-page="pagination.perPage"
            :total="total"
            class="mt-6"
            @update:current-page="handlePageChange"
        />

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
