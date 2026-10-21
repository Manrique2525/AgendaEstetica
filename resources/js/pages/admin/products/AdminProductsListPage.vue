<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import CatalogPageHeader from '@/components/admin/CatalogPageHeader.vue'
import CatalogTable from '@/components/admin/CatalogTable.vue'
import CatalogFilters from '@/components/admin/CatalogFilters.vue'
import CatalogPagination from '@/components/admin/CatalogPagination.vue'
import CatalogEmptyState from '@/components/admin/CatalogEmptyState.vue'
import UiConfirmDialog from '@/components/ui/UiConfirmDialog.vue'
import { adminCatalogApi, type AdminProduct, type AdminProductCategory } from '@/services/api/adminCatalog'
import { useRouter } from 'vue-router'

interface ProductRow {
    id: number
    name: string
    category: string
    price: string
    active: boolean
    stock: number | null
}

const columns = [
    { key: 'name', label: 'Nombre', render: (row: any) => row.name },
    { key: 'category', label: 'Categoría', render: (row: any) => row.category },
    { key: 'price', label: 'Precio', render: (row: any) => `$${row.price}` },
    { key: 'stock', label: 'Stock', render: (row: any) => row.stock !== null ? row.stock : '—' },
    { key: 'active', label: 'Estado', render: (row: any) => row.active ? 'Activo' : 'Inactivo' },
]

const products = ref<any[]>([])
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
    product_category_id: '' as '' | number,
})

const pendingDelete = ref<any | null>(null)
const deleting = ref(false)
const router = useRouter()

const categories = ref<any[]>([])

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
        if (filters.product_category_id !== '') params.product_category_id = Number(filters.product_category_id)

        const [productsResponse, categoriesResponse] = await Promise.all([
            adminCatalogApi.products.list(params),
            adminCatalogApi.productCategories.list({ active: true }),
        ])

        // Handle both paginated response and direct array response
        const productsData = Array.isArray(productsResponse) ? productsResponse : (productsResponse.data ?? productsResponse)
        const categoriesData = Array.isArray(categoriesResponse) ? categoriesResponse : (categoriesResponse.data ?? categoriesResponse)

        products.value = productsData
        categories.value = categoriesData

        // Handle pagination from response
        if (productsResponse.current_page !== undefined) {
            pagination.currentPage = productsResponse.current_page ?? 1
            pagination.lastPage = productsResponse.last_page ?? 1
            pagination.total = productsResponse.total ?? 0
        } else {
            pagination.currentPage = 1
            pagination.lastPage = 1
            pagination.total = productsData.length
        }
    } catch {
        error.value = 'No pudimos cargar los productos.'
    } finally {
        loading.value = false
    }
}

async function toggle(product: any): Promise<void> {
    try {
        await adminCatalogApi.products.setStatus(product.id, !product.active)
        await load()
    } catch {
        error.value = 'No pudimos cambiar el estado.'
    }
}

async function confirmDelete(): Promise<void> {
    if (!pendingDelete.value || deleting.value) return
    deleting.value = true
    try {
        await adminCatalogApi.products.remove(pendingDelete.value.id)
        pendingDelete.value = null
        await load()
    } catch (exception: any) {
        error.value = exception instanceof Error ? exception.message : 'No pudimos eliminar el producto.'
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

function editProduct(product: any): void {
    window.location.href = `/admin/catalogo/productos/${product.id}/editar`
}

onMounted(load)
</script>

<template>
    <AdminLayout>
        <CatalogPageHeader
            title="Productos"
            description="Catálogo de productos disponibles para la venta."
            :count="pagination.total"
            createRoute="/admin/catalogo/productos/nuevo"
            createLabel="Nuevo producto"
        />

        <CatalogFilters v-model:search="filters.q" @search="handleSearch" @clear="load">
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
        :emptyMessage="'Sin productos registrados.'"
        rowKey="id"
        @edit="editProduct"
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
        title="Eliminar producto"
        :message="`¿Confirmas eliminar ${pendingDelete?.name ?? ''}? Esta acción no se puede deshacer.`"
        confirm-label="Eliminar"
        :pending="deleting"
        @cancel="pendingDelete = null"
        @confirm="confirmDelete"
    />
</template>