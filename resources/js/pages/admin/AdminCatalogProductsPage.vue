<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import AdminLayout from '../../layouts/AdminLayout.vue';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';
import UiConfirmDialog from '../../components/ui/UiConfirmDialog.vue';
import UiTextarea from '../../components/ui/UiTextarea.vue';
import { ApiError } from '../../services/http';
import { adminCatalogApi, type AdminProduct, type AdminProductCategory } from '../../services/api/adminCatalog';

const products = ref<AdminProduct[]>([]);
const categories = ref<AdminProductCategory[]>([]);
const loading = ref(true);
const error = ref('');
const message = ref('');
const saving = ref(false);
const editingId = ref<number | null>(null);
const pendingDelete = ref<AdminProduct | null>(null);
const deleting = ref(false);

const form = reactive({
    product_category_id: null as number | null,
    name: '',
    description: '',
    price: '',
    stock: '' as string,
    active: true,
});

function resetForm(): void {
    editingId.value = null;
    form.product_category_id = null;
    form.name = '';
    form.description = '';
    form.price = '';
    form.stock = '';
    form.active = true;
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const [productList, categoryList] = await Promise.all([
            adminCatalogApi.products.list(),
            adminCatalogApi.productCategories.list(),
        ]);
        products.value = productList.data;
        categories.value = categoryList.data;
    } catch {
        error.value = 'No pudimos cargar los productos.';
    } finally {
        loading.value = false;
    }
}

function startEdit(product: AdminProduct): void {
    editingId.value = product.id;
    form.product_category_id = product.category?.id ?? null;
    form.name = product.name;
    form.description = product.description ?? '';
    form.price = product.price;
    form.stock = '';
    form.active = product.active;
}

async function save(): Promise<void> {
    if (saving.value) return;
    saving.value = true;
    error.value = '';
    message.value = '';
    const payload = {
        product_category_id: form.product_category_id,
        name: form.name,
        description: form.description || null,
        price: form.price,
        stock: form.stock === '' ? null : Number(form.stock),
        active: form.active,
    };

    try {
        if (editingId.value) {
            await adminCatalogApi.products.update(editingId.value, payload);
            message.value = 'Producto actualizado.';
        } else {
            await adminCatalogApi.products.create(payload);
            message.value = 'Producto creado.';
        }
        resetForm();
        await load();
    } catch (exception) {
        error.value = exception instanceof ApiError ? exception.message : 'No pudimos guardar el producto.';
    } finally {
        saving.value = false;
    }
}

async function toggle(product: AdminProduct): Promise<void> {
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
    } catch (exception) {
        error.value = exception instanceof ApiError ? exception.message : 'No pudimos eliminar el producto.';
    } finally {
        deleting.value = false;
    }
}

onMounted(load);
</script>

<template>
    <AdminLayout>
        <UiCard>
            <h1 class="font-display text-4xl leading-none text-text-primary">Productos</h1>
            <p class="mt-3 font-body text-sm text-text-secondary">Registra únicamente productos reales confirmados por el negocio.</p>

            <p v-if="message" role="status" class="mt-4 rounded-md border border-brand-turquoise bg-surface-subtle p-3 font-body text-sm">{{ message }}</p>
            <p v-if="error" role="alert" class="mt-4 rounded-md border border-state-error bg-red-50 p-3 font-body text-sm text-state-error">{{ error }}</p>

            <form class="mt-6 grid gap-4 rounded-lg border border-border-default p-4 sm:grid-cols-2" @submit.prevent="save">
                <label class="block font-ui text-sm font-semibold">Categoría (opcional)
                    <select v-model="form.product_category_id" class="mt-1 block min-h-11 w-full rounded-md border border-border-default px-3 py-2 font-body text-base">
                        <option :value="null">Sin categoría</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                </label>
                <label class="block font-ui text-sm font-semibold">Nombre<input v-model="form.name" required class="mt-1 block min-h-11 w-full rounded-md border border-border-default px-3 py-2 font-body text-base"></label>
                <div class="sm:col-span-2">
                    <label class="block font-ui text-sm font-semibold" for="product-description">Descripción (opcional)</label>
                    <UiTextarea id="product-description" v-model="form.description" class="mt-1" />
                </div>
                <label class="block font-ui text-sm font-semibold">Precio (MXN)<input v-model="form.price" type="number" step="0.01" min="0" required class="mt-1 block min-h-11 w-full rounded-md border border-border-default px-3 py-2 font-body text-base"></label>
                <label class="block font-ui text-sm font-semibold">Existencia (opcional)<input v-model="form.stock" type="number" min="0" class="mt-1 block min-h-11 w-full rounded-md border border-border-default px-3 py-2 font-body text-base"></label>
                <label class="flex items-center gap-2 font-ui text-sm font-semibold"><input v-model="form.active" type="checkbox"> Activo</label>
                <div class="flex gap-3 sm:col-span-2">
                    <UiButton type="submit" :loading="saving">{{ editingId ? 'Guardar cambios' : 'Crear producto' }}</UiButton>
                    <UiButton v-if="editingId" type="button" variant="secondary" @click="resetForm">Cancelar</UiButton>
                </div>
            </form>

            <p v-if="loading" role="status" class="mt-6 font-body text-sm text-text-secondary">Cargando...</p>
            <p v-else-if="!products.length" class="mt-6 font-body text-sm text-text-secondary">Sin productos registrados. El catálogo público permanecerá vacío hasta que agregues productos reales.</p>
            <ul v-else class="mt-6 divide-y divide-border-default">
                <li v-for="product in products" :key="product.id" class="flex flex-wrap items-center justify-between gap-3 py-4">
                    <div>
                        <p class="font-ui font-bold">
{{ product.name }}
                            <span class="ml-2 font-body text-xs text-text-secondary">{{ product.active ? 'Activo' : 'Inactivo' }} · {{ product.category?.name ?? 'Sin categoría' }} · ${{ product.price }}</span>
                        </p>
                        <p v-if="product.description" class="mt-1 font-body text-sm text-text-secondary">{{ product.description }}</p>
                    </div>
                    <div class="flex gap-2">
                        <UiButton type="button" variant="secondary" @click="startEdit(product)">Editar</UiButton>
                        <UiButton type="button" variant="secondary" @click="toggle(product)">{{ product.active ? 'Desactivar' : 'Activar' }}</UiButton>
                        <UiButton type="button" variant="secondary" @click="pendingDelete = product">Eliminar</UiButton>
                    </div>
                </li>
            </ul>
        </UiCard>

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