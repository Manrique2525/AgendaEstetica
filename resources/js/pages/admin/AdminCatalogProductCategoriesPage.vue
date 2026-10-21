<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import AdminLayout from '../../layouts/AdminLayout.vue';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';
import UiConfirmDialog from '../../components/ui/UiConfirmDialog.vue';
import UiTextarea from '../../components/ui/UiTextarea.vue';
import { ApiError } from '../../services/http';
import { adminCatalogApi, type AdminProductCategory } from '../../services/api/adminCatalog';

const categories = ref<AdminProductCategory[]>([]);
const loading = ref(true);
const error = ref('');
const message = ref('');
const saving = ref(false);
const editingId = ref<number | null>(null);
const pendingDelete = ref<AdminProductCategory | null>(null);
const deleting = ref(false);
const form = reactive({ name: '', description: '', active: true });

function resetForm(): void {
    editingId.value = null;
    form.name = '';
    form.description = '';
    form.active = true;
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const response = await adminCatalogApi.productCategories.list();
        categories.value = response.data;
    } catch {
        error.value = 'No pudimos cargar las categorías de productos.';
    } finally {
        loading.value = false;
    }
}

function startEdit(category: AdminProductCategory): void {
    editingId.value = category.id;
    form.name = category.name;
    form.description = category.description ?? '';
    form.active = category.active;
}

async function save(): Promise<void> {
    if (saving.value) return;
    saving.value = true;
    error.value = '';
    message.value = '';
    const payload = { name: form.name, description: form.description || null, active: form.active };

    try {
        if (editingId.value) {
            await adminCatalogApi.productCategories.update(editingId.value, payload);
            message.value = 'Categoría actualizada.';
        } else {
            await adminCatalogApi.productCategories.create(payload);
            message.value = 'Categoría creada.';
        }
        resetForm();
        await load();
    } catch (exception) {
        error.value = exception instanceof ApiError ? exception.message : 'No pudimos guardar la categoría.';
    } finally {
        saving.value = false;
    }
}

async function toggle(category: AdminProductCategory): Promise<void> {
    try {
        await adminCatalogApi.productCategories.setStatus(category.id, !category.active);
        await load();
    } catch {
        error.value = 'No pudimos cambiar el estado.';
    }
}

async function confirmDelete(): Promise<void> {
    if (!pendingDelete.value || deleting.value) return;
    deleting.value = true;
    try {
        await adminCatalogApi.productCategories.remove(pendingDelete.value.id);
        pendingDelete.value = null;
        await load();
    } catch (exception) {
        error.value = exception instanceof ApiError ? exception.message : 'No pudimos eliminar la categoría.';
    } finally {
        deleting.value = false;
    }
}

onMounted(load);
</script>

<template>
    <AdminLayout>
        <UiCard>
            <h1 class="font-display text-4xl leading-none text-text-primary">Categorías de productos</h1>
            <p class="mt-3 font-body text-sm text-text-secondary">Opcionales. Solo se muestran si el negocio confirma productos reales.</p>

            <p v-if="message" role="status" class="mt-4 rounded-md border border-brand-turquoise bg-surface-subtle p-3 font-body text-sm">{{ message }}</p>
            <p v-if="error" role="alert" class="mt-4 rounded-md border border-state-error bg-red-50 p-3 font-body text-sm text-state-error">{{ error }}</p>

            <form class="mt-6 grid gap-4 rounded-lg border border-border-default p-4 sm:grid-cols-2" @submit.prevent="save">
                <label class="block font-ui text-sm font-semibold">Nombre<input v-model="form.name" required class="mt-1 block min-h-11 w-full rounded-md border border-border-default px-3 py-2 font-body text-base"></label>
                <label class="flex items-center gap-2 font-ui text-sm font-semibold"><input v-model="form.active" type="checkbox"> Activa</label>
                <div class="sm:col-span-2">
                    <label class="block font-ui text-sm font-semibold" for="product-category-description">Descripción (opcional)</label>
                    <UiTextarea id="product-category-description" v-model="form.description" class="mt-1" />
                </div>
                <div class="flex gap-3 sm:col-span-2">
                    <UiButton type="submit" :loading="saving">{{ editingId ? 'Guardar cambios' : 'Crear categoría' }}</UiButton>
                    <UiButton v-if="editingId" type="button" variant="secondary" @click="resetForm">Cancelar</UiButton>
                </div>
            </form>

            <p v-if="loading" role="status" class="mt-6 font-body text-sm text-text-secondary">Cargando...</p>
            <p v-else-if="!categories.length" class="mt-6 font-body text-sm text-text-secondary">Sin categorías registradas.</p>
            <ul v-else class="mt-6 divide-y divide-border-default">
                <li v-for="category in categories" :key="category.id" class="flex flex-wrap items-center justify-between gap-3 py-4">
                    <div>
                        <p class="font-ui font-bold">{{ category.name }} <span class="ml-2 font-body text-xs text-text-secondary">{{ category.active ? 'Activa' : 'Inactiva' }} · {{ category.products_count ?? 0 }} productos</span></p>
                        <p v-if="category.description" class="mt-1 font-body text-sm text-text-secondary">{{ category.description }}</p>
                    </div>
                    <div class="flex gap-2">
                        <UiButton type="button" variant="secondary" @click="startEdit(category)">Editar</UiButton>
                        <UiButton type="button" variant="secondary" @click="toggle(category)">{{ category.active ? 'Desactivar' : 'Activar' }}</UiButton>
                        <UiButton type="button" variant="secondary" @click="pendingDelete = category">Eliminar</UiButton>
                    </div>
                </li>
            </ul>
        </UiCard>

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