<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { ApiError } from '@/services/http';
import AdminLayout from '@/layouts/AdminLayout.vue';
import CatalogPageHeader from '@/components/admin/CatalogPageHeader.vue';
import CatalogTable from '@/components/admin/CatalogTable.vue';
import CatalogFilters from '@/components/admin/CatalogFilters.vue';
import CatalogPagination from '@/components/admin/CatalogPagination.vue';
import UiConfirmDialog from '@/components/ui/UiConfirmDialog.vue';
import { adminCatalogApi, type AdminServiceCategory } from '@/services/api/adminCatalog';

type CategoryRow = AdminServiceCategory & { services_count: number };

const columns: { key: string; label: string; render?: (row: Record<string, unknown>) => string | number | null }[] = [
    { key: 'name', label: 'Nombre' },
    { key: 'slug', label: 'Slug', render: (row) => (row.slug as string | null) ?? '—' },
    { key: 'active', label: 'Estado', render: (row) => row.active ? 'Activa' : 'Inactiva' },
    { key: 'services_count', label: 'Servicios', render: (row) => `${(row.services_count as number) ?? 0}` },
];

const categories = ref<CategoryRow[]>([]);
const loading = ref(true);
const error = ref('');

const pagination = reactive({
    currentPage: 1,
    perPage: 15,
});

const filters = reactive({
    q: '',
    active: '' as '' | 'true' | 'false',
});

const pendingDelete = ref<AdminServiceCategory | null>(null);
const deleting = ref(false);

const lastPage = computed(() => Math.max(1, Math.ceil(categories.value.length / pagination.perPage)));
const total = computed(() => categories.value.length);
const pagedCategories = computed(() => {
    const start = (pagination.currentPage - 1) * pagination.perPage;
    return categories.value.slice(start, start + pagination.perPage);
});

function toRow(category: AdminServiceCategory): CategoryRow {
    return { ...category, services_count: category.services_count ?? 0 };
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const params: Record<string, string | boolean> = {};

        if (filters.q.trim()) params.q = filters.q.trim();
        if (filters.active !== '') params.active = filters.active === 'true';

        const response = await adminCatalogApi.serviceCategories.list(params);
        categories.value = response.map(toRow);
        pagination.currentPage = 1;
    } catch {
        error.value = 'No pudimos cargar las categorías.';
    } finally {
        loading.value = false;
    }
}

async function toggle(row: Record<string, unknown>): Promise<void> {
    const category = row as unknown as AdminServiceCategory;
    try {
        await adminCatalogApi.serviceCategories.setStatus(category.id, !category.active);
        await load();
    } catch {
        error.value = 'No pudimos cambiar el estado.';
    }
}

async function confirmDelete(): Promise<void> {
    if (!pendingDelete.value || deleting.value) return;
    deleting.value = true;
    try {
        await adminCatalogApi.serviceCategories.remove(pendingDelete.value.id);
        pendingDelete.value = null;
        await load();
    } catch (exception: unknown) {
        error.value = exception instanceof ApiError ? exception.message : 'No pudimos eliminar la categoría.';
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

function editCategory(row: Record<string, unknown>): void {
    const category = row as unknown as AdminServiceCategory;
    window.location.href = `/admin/catalogo/categorias/${category.id}/editar`;
}

onMounted(load);
</script>

<template>
    <AdminLayout>
        <CatalogPageHeader
            title="Categorías de servicios"
            description="Agrupan los servicios publicados en el catálogo."
            :count="total"
            create-route="/admin/catalogo/categorias/nueva"
            create-label="Nueva categoría"
        />

        <p v-if="error" role="alert" class="mb-6 rounded-md border border-state-error bg-red-50 p-3 font-body text-sm text-state-error">{{ error }}</p>

        <CatalogFilters :search="filters.q" @search="(value) => { filters.q = value; handleSearch(); }" @clear="handleSearch">
            <template #filters>
                <select v-model="filters.active" class="min-h-11 rounded-md border border-border-default px-3 py-2 font-body text-sm">
                    <option value="">Todos los estados</option>
                    <option value="true">Activas</option>
                    <option value="false">Inactivas</option>
                </select>
            </template>
        </CatalogFilters>

        <CatalogTable
            :columns="columns"
            :rows="pagedCategories"
            :loading="loading"
            empty-message="Sin categorías registradas."
            row-key="id"
            :on-edit="editCategory"
            :on-toggle="toggle"
            :on-delete="(row) => { pendingDelete = row as unknown as AdminServiceCategory; }"
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
            title="Eliminar categoría"
            :message="`¿Confirmas eliminar ${pendingDelete?.name ?? ''}? Esta acción no se puede deshacer.`"
            confirm-label="Eliminar"
            :pending="deleting"
            @cancel="pendingDelete = null"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
