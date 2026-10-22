<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AdminLayout from '@/layouts/AdminLayout.vue';
import UiCard from '@/components/ui/UiCard.vue';
import UiFormField from '@/components/ui/UiFormField.vue';
import UiInput from '@/components/ui/UiInput.vue';
import UiTextarea from '@/components/ui/UiTextarea.vue';
import { ApiError } from '../../../services/http';
import { adminCatalogApi, type AdminServiceCategory } from '@/services/api/adminCatalog';

const router = useRouter();
const route = useRoute();

const serviceId = computed(() => Number(route.params.id));

const categories = ref<AdminServiceCategory[]>([]);
const loading = ref(false);
const saving = ref(false);
const error = ref('');
const message = ref('');
const imageFile = ref<File | null>(null);
const currentImageUrl = ref<string | null>(null);
const imagePreview = ref('');

const isVariable = computed(() => form.pricing_type === 'variable');

const form = reactive({
    service_category_id: 0,
    name: '',
    description: '',
    duration_minutes: '60',
    pricing_type: 'fixed' as 'fixed' | 'starting_from' | 'variable',
    price: '',
    active: true,
});

function onImageChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    imageFile.value = file;
    imagePreview.value = file ? URL.createObjectURL(file) : '';
}

async function loadService(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const [serviceResponse, categoriesResponse] = await Promise.all([
            adminCatalogApi.services.show(serviceId.value),
            adminCatalogApi.serviceCategories.list({ active: true }),
        ]);

        const service = serviceResponse;
        form.service_category_id = service.category?.id ?? 0;
        form.name = service.name;
        form.description = service.description ?? '';
        form.duration_minutes = String(service.duration_minutes);
        form.pricing_type = service.pricing_type;
        form.price = service.price ?? '';
        form.active = service.active;
        currentImageUrl.value = service.image_url;
        categories.value = categoriesResponse;

        if (!form.service_category_id && categories.value.length > 0) {
            form.service_category_id = categories.value[0].id;
        }
    } catch {
        error.value = 'No pudimos cargar el servicio.';
    } finally {
        loading.value = false;
    }
}

async function save(): Promise<void> {
    if (saving.value) return;

    if (!form.name.trim()) {
        error.value = 'El nombre es obligatorio.';
        return;
    }

    saving.value = true;
    error.value = '';
    message.value = '';

    const payload = {
        service_category_id: String(form.service_category_id),
        name: form.name.trim(),
        description: form.description,
        duration_minutes: String(Number(form.duration_minutes)),
        pricing_type: form.pricing_type,
        price: form.pricing_type === 'variable' ? '' : form.price,
        active: form.active ? '1' : '0',
    };

    try {
        if (imageFile.value) {
            const formData = new FormData();
            Object.entries(payload).forEach(([key, value]) => formData.append(key, value));
            formData.append('image', imageFile.value);
            await adminCatalogApi.services.updateForm(serviceId.value, formData);
        } else {
            await adminCatalogApi.services.update(serviceId.value, {
                service_category_id: form.service_category_id,
                name: form.name.trim(),
                description: form.description || null,
                duration_minutes: Number(form.duration_minutes),
                pricing_type: form.pricing_type,
                price: form.pricing_type === 'variable' ? null : form.price,
                active: form.active,
            });
        }
        message.value = 'Servicio actualizado.';
        await router.push({ name: 'admin.catalog.services' });
    } catch (exception: unknown) {
        error.value = exception instanceof ApiError ? exception.message : 'No pudimos actualizar el servicio.';
    } finally {
        saving.value = false;
    }
}

async function cancel(): Promise<void> {
    router.push({ name: 'admin.catalog.services' });
}

onMounted(loadService);
</script>

<template>
    <AdminLayout>
        <UiCard>
            <header class="mb-8">
                <h1 class="font-display text-4xl leading-none text-text-primary">Editar servicio</h1>
                <p class="mt-2 font-body text-sm text-text-secondary">Modifica los datos del servicio.</p>
            </header>

            <p v-if="message" role="status" class="mb-6 rounded-md border border-brand-turquoise bg-surface-subtle p-3 font-body text-sm">{{ message }}</p>
            <p v-if="error" role="alert" class="mb-6 rounded-md border border-state-error bg-red-50 p-3 font-body text-sm text-state-error">{{ error }}</p>

            <form class="space-y-6" @submit.prevent="save">
                <UiFormField label="Categoría" required>
                    <template #default="{ inputId, describedBy, invalid }">
                        <select
                            :id="inputId"
                            v-model.number="form.service_category_id"
                            :aria-describedby="describedBy"
                            :aria-invalid="invalid"
                            required
                            class="mt-1 block min-h-11 w-full rounded-md border border-border-default px-3 py-2 font-body text-base"
                        >
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </template>
                </UiFormField>

                <UiFormField label="Nombre" required>
                    <template #default="{ inputId, describedBy, invalid }">
                        <UiInput
                            :id="inputId"
                            v-model="form.name"
                            required
                            :aria-describedby="describedBy"
                            :invalid="invalid"
                            autocomplete="off"
                        />
                    </template>
                </UiFormField>

                <UiFormField label="Descripción (opcional)">
                    <template #default="{ inputId, describedBy, invalid }">
                        <UiTextarea
                            :id="inputId"
                            v-model="form.description"
                            :aria-describedby="describedBy"
                            :invalid="invalid"
                        />
                    </template>
                </UiFormField>

                <UiFormField label="Imagen (opcional)">
                    <template #default="{ inputId, describedBy, invalid }">
                        <img
                            v-if="imagePreview || currentImageUrl"
                            :src="imagePreview || currentImageUrl || ''"
                            alt="Imagen actual del servicio"
                            class="mb-3 h-32 w-auto rounded-md border border-border-default object-cover"
                        >
                        <input
                            :id="inputId"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            :aria-describedby="describedBy"
                            :aria-invalid="invalid"
                            class="block w-full font-body text-sm"
                            @change="onImageChange"
                        >
                    </template>
                </UiFormField>

                <div class="grid gap-4 sm:grid-cols-3">
                    <UiFormField label="Duración (minutos)" required>
                        <template #default="{ inputId, describedBy, invalid }">
                            <UiInput
                                :id="inputId"
                                v-model="form.duration_minutes"
                                type="number"
                                min="1"
                                max="600"
                                required
                                :aria-describedby="describedBy"
                                :invalid="invalid"
                            />
                        </template>
                    </UiFormField>

                    <UiFormField label="Tipo de precio" required>
                        <template #default="{ inputId, describedBy, invalid }">
                            <select
                                :id="inputId"
                                v-model="form.pricing_type"
                                :aria-describedby="describedBy"
                                :aria-invalid="invalid"
                                required
                                class="mt-1 block min-h-11 w-full rounded-md border border-border-default px-3 py-2 font-body text-base"
                            >
                                <option value="fixed">Precio fijo</option>
                                <option value="starting_from">Desde</option>
                                <option value="variable">Variable</option>
                            </select>
                        </template>
                    </UiFormField>

                    <UiFormField v-if="!isVariable" label="Precio (MXN)" required>
                        <template #default="{ inputId, describedBy, invalid }">
                            <UiInput
                                :id="inputId"
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                :aria-describedby="describedBy"
                                :invalid="invalid"
                            />
                        </template>
                    </UiFormField>
                </div>

                <label class="flex items-center gap-2 font-ui text-sm font-semibold">
                    <input v-model="form.active" type="checkbox" class="rounded border-border-default text-action-primary focus:ring-2 focus:ring-focus-ring">
                    Activo
                </label>

                <div class="pt-4 border-t border-border-default flex gap-3">
                    <button type="submit" :disabled="saving" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-action-primary px-6 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg v-if="saving" class="size-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <span>{{ saving ? 'Guardando...' : 'Guardar cambios' }}</span>
                    </button>
                    <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-md border border-border-default px-6 py-2 font-ui text-sm font-bold text-text-secondary hover:bg-surface-subtle focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring focus-visible:ring-offset-2" @click="cancel">Cancelar</button>
                </div>
            </form>
        </UiCard>
    </AdminLayout>
</template>