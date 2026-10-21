<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import CatalogPageHeader from '../../../../components/admin/CatalogPageHeader.vue'
import CatalogTable from '../CatalogTable.vue'
import CatalogFilters from '@/components/admin/CatalogFilters.vue'
import CatalogPagination from '@/components/admin/CatalogPagination.vue'
import CatalogEmptyState from '@/components/admin/CatalogEmptyState.vue'
import UiConfirmDialog from '../../../components/ui/UiConfirmDialog.vue'
import { adminCatalogApi, type AdminProductCategory } from '@/services/api/adminCatalog'

interface CategoryRow {
    id: number
    name: string
    description: string | null
    active: boolean
    products_count: number
}

const columns = [
    { key: 'name', label: 'Nombre', render: (row: any) => row.name },
    { key: 'active', label: 'Estado', render: (row: any) => row.active ? 'Activa' : 'Inactiva' },
    { key: 'products_count', label: 'Productos', render: (row: any) => `${row.products_count ?? 0}` },
]

const categories = ref<any[]>([])
const loading = ref(true)
const error = ref('')
const message = ref('')

const pagination = reactive({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
})

const filters = reactive({
    q: '',
    active: '' as '' | 'true' | 'false',
})

async function load(): Promise<void> {
    loading.value = true
    error.value = ''

    try {
        const params: Record<string, string | number | boolean> = {
            page: pagination.currentPage,
            per_page: pagination.perPage,
        }

        if (filters.q.trim()) params.q = filters.q.trim()
        if (filters.active !== '') params.active = filters.active === 'true'

        const response = await adminCatalogApi.productCategories.list(params)
        categories.value = response.data ?? response
        pagination.currentPage = response.current_page ?? 1
        pagination.lastPage = response.last_page ?? 1
        pagination.total = response.total ?? 0
    } catch {
        error.value = 'No pudimos cargar las categorías.'
    } finally {
        loading.value = false
    }
}

async function toggle(category: any): Promise<void> {
    try {
        await adminCatalogApi.productCategories.setStatus(category.id, !category.active)
        await load()
    } catch {
        error.value = 'No pudimos cambiar el estado.'
    }
}

async function confirmDelete(): Promise<void> {
    if (!pendingDelete.value || deleting.value) return
    deleting.value = true
    try {
        await adminCatalogApi.productCategories.remove(pendingDelete.value.id)
        pendingDelete.value = null
        await load()
    } catch (exception: any) {
        error.value = exception instanceof Error ? exception.message : 'No pudimos eliminar la categoría.'
    } finally {
        deleting.value = false
    }
}

async function handleSearch(): Promise<void> {
    pagination.currentPage = 1
    await load()
}

async function handlePageChange(page: number): Promise<void> {
    pagination.currentPage = page
    await load()
}

onMounted(load)
</script>

<template>
    <AdminLayout>
        <CatalogPageHeader
            title="Categorías de productos"
            description="Agrupan los productos del catálogo."
            :count="pagination.total"
            createRoute="/admin/catalogo/productos/categorias/nueva"
            createLabel="Nueva categoría"
        />

        <CatalogFilters v-model:search="filters.q" @search="handleSearch" @clear="load">
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
        :rows="categories"
        :loading="loading"
        :emptyMessage="'Sin categorías registradas.'"
        rowKey="id"
        @edit="editCategory"
        @toggle="toggle"
        @delete="confirmDelete"
    />

    <CatalogPagination
        v-model:currentPage="pagination.currentPage"
        :lastPage="pagination.lastPage"
        :perPage="pagination.perPage"
        :total="pagination.total"
        @update:currentPage="handlePageChange"
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
</template>