<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import CatalogPageHeader from '@/components/admin/CatalogPageHeader.vue';
import CatalogTable from '@/components/admin/CatalogTable.vue';
import CatalogFilters from '@/components/admin/CatalogFilters.vue';
import CatalogPagination from '@/components/admin/CatalogPagination.vue';
import CatalogEmptyState from '@/components/admin/CatalogEmptyState.vue';
import UiConfirmDialog from '@/components/ui/UiConfirmDialog.vue';
import { adminCatalogApi, type AdminService, type AdminServiceCategory } from '@/services/api/adminCatalog';

interface ServiceRow {
    id: number;
    name: string;
    category: string;
    duration_minutes: number;
    pricing_type: string;
    price: string | null;
    active: boolean;
    appointments_count: number;
}

const columns = [
    { key: 'name', label: 'Nombre', render: (row: any) => row.name },
    { key: 'category', label: 'Categoría', render: (row: any) => row.category },
    { key: 'duration_minutes', label: 'Duración', render: (row: any) => `${row.duration_minutes} min` },
    { key: 'pricing_type', label: 'Tipo', render: (row: any) => row.pricing_type === 'variable' ? 'Variable' : row.pricing_type === 'starting_from' ? 'Desde' : 'Fijo' },
    { key: 'price', label: 'Precio', render: (row: any) => row.pricing_type === 'variable' ? 'Variable' : row.price ? `$${row.price}` : '—' },
    { key: 'active', label: 'Estado', render: (row: any) => row.active ? 'Activo' : 'Inactivo' },
];

const services = ref<any[]>([]);
const categories = ref<AdminServiceCategory[]>([]);
const loading = ref(true);
const error = ref('');
const message = ref('');

const pagination = reactive({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
});

const filters = reactive({
    q: '',
    active: '' as '' | 'true' | 'false',
    service_category_id: '' as '' | number,
});

const pendingDelete = ref<any | null>(null);
const deleting = ref(false);

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
        if (filters.service_category_id !== '') params.service_category_id = Number(filters.service_category_id);

        const [servicesResponse, categoriesResponse] = await Promise.all([
            adminCatalogApi.services.list(params),
            adminCatalogApi.serviceCategories.list({ active: true }),
        ]);

        services.value = servicesResponse.data ?? servicesResponse;
        categories.value = categoriesResponse.data ?? categoriesResponse;
        pagination.currentPage = servicesResponse.current_page ?? 1;
        pagination.lastPage = servicesResponse.last_page ?? 1;
        pagination.total = servicesResponse.total ?? 0;
    } catch {
        error.value = 'No pudimos cargar los servicios.';
    } finally {
        loading.value = false;
    }
}

async function toggle(service: any): Promise<void> {
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
    } catch (exception: any) {
        error.value = exception instanceof Error ? exception.message : 'No pudimos eliminar el servicio.';
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

function editService(service: any): void {
    // Navigate to edit page
    window.location.href = `/admin/catalogo/servicios/${service.id}/editar`;
}

onMounted(load);
</script>