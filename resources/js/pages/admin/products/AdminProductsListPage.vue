<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { ApiError } from '@/services/http';
import AdminLayout from '@/layouts/AdminLayout.vue';
import CatalogPageHeader from '@/components/admin/CatalogPageHeader.vue';
import CatalogTable from '@/components/admin/CatalogTable.vue';
import CatalogFilters from '@/components/admin/CatalogFilters.vue';
import CatalogPagination from '@/components/admin/CatalogPagination.vue';
import UiConfirmDialog from '@/components/ui/UiConfirmDialog.vue';
import { adminCatalogApi, type AdminProduct, type AdminProductCategory } from '@/services/api/adminCatalog';

type ProductRow = AdminProduct & { categoryName: string };

const columns: { key: string; label: string; render?: (row: Record<string, unknown>) => string | number | null }[] = [
    { key: 'name', label: 'Nombre' },
    { key: 'categoryName', label: 'Categoría' },
    { key: 'price', label: 'Precio', render: (row) => `$${row.price as string}` },
    { key: 'active', label: 'Estado', render: (row) => row.active ? 'Activo' : 'Inactivo' },
];

const products = ref<ProductRow[]>([]);
const categories = ref<AdminProductCategory[]>([]);
const loading = ref(true);
const error = ref('');

const pagination = reactive({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
});

const filters = reactive({
    q: '',
    active: '' as '' | 'true' | 'false',
    product_category_id: '' as '' | number,
});

const pendingDelete = ref<AdminProduct | null>(null);
const deleting = ref(false);

function toRow(product: AdminProduct): ProductRow {
    return { ...product, categoryName: product.category?.name ?? '—' };
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const params: Record<string, string | number | boolean> = {
            page: pagination.currentPage,
            per_page: pagination.perPage,
        };

        if (filters.q.trim()) params.q = filters.q.trim();
        if (filters.active !== '') params.active = filters.active === 'true';
        if (filters.product_category_id !== '') params.product_category_id = Number(filters.product_category_id);

        const [productsResponse, categoriesResponse] = await Promise.all([
            adminCatalogApi.products.list(params),
            adminCatalogApi.productCategories.list({ active: true }),
        ]);

        products.value = productsResponse.data.map(toRow);
        categories.value = categoriesResponse.data;
        pagination.currentPage = productsResponse.current_page;
        pagination.lastPage = productsResponse.last_page;
        pagination.total = productsResponse.total;
    } catch {
        error.value = 'No pudimos cargar los productos.';
    } finally {
        loading.value = false;
    }
}

async function toggle(row: Record<string, unknown>): Promise<void> {
    const product = row as unknown as AdminProduct;
    try {
        await adminCatalogApi.products.setStatus(product.id, !product.active);
        await load();
    } catch {
        error.value = 'No pudimos cambiar el estado.';
    }
}

async function confirmDelete(): Promise<void> {
    if (!pendingDelete.value || deleting.value) return;
    deleting.value = true;
    try {
        await adminCatalogApi.products.remove(pendingDelete.value.id);
        pendingDelete.value = null;
        await load();
    } catch (exception: unknown) {
        error.value = exception instanceof ApiError ? exception.message : 'No pudimos eliminar el producto.';
    } finally {
        deleting.value = false;
    }
}

async function handleSearch(): Promise<void> {
    pagination.currentPage = 1;
    await load();
}

async function handlePageChange(page: number): Promise<void> {
    pagination.currentPage = page;
    await load();
}

function editProduct(row: Record<string, unknown>): void {
    const product = row as unknown as AdminProduct;
    window.location.href = `/admin/catalogo/productos/${product.id}/editar`;
}

onMounted(load);
</script>

<template>
    <AdminLayout>
        <CatalogPageHeader
            title="Productos"
            description="Catálogo de productos disponibles para la venta."
            :count="pagination.total"
            create-route="/admin/catalogo/productos/nuevo"
            create-label="Nuevo producto"
        />

        <p v-if="error" role="alert" class="mb-6 rounded-md border border-state-error bg-red-50 p-3 font-body text-sm text-state-error">{{ error }}</p>

        <CatalogFilters :search="filters.q" @search="(value) => { filters.q = value; handleSearch(); }" @clear="handleSearch">
            <template #filters>
                <select v-model="filters.active" class="min-h-11 rounded-md border border-border-default px-3 py-2 font-body text-sm">
                    <option value="">Todos los estados</option>
                    <option value="true">Activos</option>
                    <option value="false">Inactivos</option>
                </select>
                <select v-model="filters.product_category_id" class="min-h-11 rounded-md border border-border-default px-3 py-2 font-body text-sm">
                    <option value="">Todas las categorías</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </template>
        </CatalogFilters>

        <CatalogTable
            :columns="columns"
            :rows="products"
            :loading="loading"
            empty-message="Sin productos registrados."
            row-key="id"
            :on-edit="editProduct"
            :on-toggle="toggle"
            :on-delete="(row) => { pendingDelete = row as unknown as AdminProduct; }"
        />

        <CatalogPagination
            :current-page="pagination.currentPage"
            :last-page="pagination.lastPage"
            :per-page="pagination.perPage"
            :total="pagination.total"
            class="mt-6"
            @update:current-page="handlePageChange"
        />

        <UiConfirmDialog
            :open="pendingDelete !== null"
            title="Eliminar producto"
            :message="`¿Confirmas eliminar ${pendingDelete?.name ?? ''}? Esta acción no se puede deshacer.`"
            confirm-label="Eliminar"
            :pending="deleting"
            @cancel="pendingDelete = null"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
