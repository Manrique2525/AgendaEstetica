<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AdminLayout from '@/layouts/AdminLayout.vue';
import UiButton from '@/components/ui/UiButton.vue';
import UiCard from '@/components/ui/UiCard.vue';
import UiFormField from '@/components/ui/UiFormField.vue';
import UiInput from '@/components/ui/UiInput.vue';
import UiTextarea from '@/components/ui/UiTextarea.vue';
import { ApiError } from '../../../services/http';
import { adminCatalogApi, type AdminServiceCategory } from '@/services/api/adminCatalog';

const router = useRouter();
const route = useRoute();

const categories = ref<AdminServiceCategory[]>([]);
const loading = ref(false);
const saving = ref(false);
const error = ref('');
const message = ref('');

const isVariable = computed(() => form.pricing_type === 'variable');

const form = reactive({
    service_category_id: 0,
    name: '',
    description: '',
    duration_minutes: 60,
    pricing_type: 'fixed' as 'fixed' | 'starting_from' | 'variable',
    price: '',
    active: true,
});

async function loadCategories(): Promise<void> {
    try {
        const response = await adminCatalogApi.serviceCategories.list({ active: true });
        categories.value = response.data ?? response;
        if (!form.service_category_id && categories.value.length > 0) {
            form.service_category_id = categories.value[0].id;
        }
    } catch {
        // Keep empty categories on error
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
        service_category_id: form.service_category_id,
        name: form.name.trim(),
        description: form.description || null,
        duration_minutes: Number(form.duration_minutes),
        pricing_type: form.pricing_type,
        price: form.pricing_type === 'variable' ? null : form.price,
        active: form.active,
    };

    try {
        await adminCatalogApi.services.create(payload);
        message.value = 'Servicio creado.';
        await router.push({ name: 'admin.catalog.services' });
    } catch (exception: any) {
        if (exception instanceof ApiError && exception.errors) {
            // Handle validation errors
        }
        error.value = exception instanceof ApiError ? exception.message : 'No pudimos crear el servicio.';
    } finally {
        saving.value = false;
    }
}

async function cancel(): Promise<void> {
    router.push({ name: 'admin.catalog.services' });
}

onMounted(loadCategories);
</script>

<template>
    <AdminLayout>
        <UiCard>
            <header class="mb-8">
                <h1 class="font-display text-4xl leading-none text-text-primary">Nuevo servicio</h1>
                <p class="mt-2 font-body text-sm text-text-secondary">Registra un nuevo servicio para el catálogo público.</p>
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

                <UiFormField label="Nombre" required :error="errors?.name">
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

                <div class="grid gap-4 sm:grid-cols-3">
                    <UiFormField label="Duración (minutos)" required>
                        <template #default="{ inputId, describedBy, invalid }">
                            <UiInput
                                :id="inputId"
                                v-model.number="form.duration_minutes"
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
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-action-primary px-6 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed" :disabled="saving">
                        <svg v-if="saving" class="size-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <span>{{ saving ? 'Guardando...' : 'Crear servicio' }}</span>
                    </button>
                    <button type="button" @click="cancel" class="inline-flex min-h-11 items-center justify-center rounded-md border border-border-default px-6 py-2 font-ui text-sm font-bold text-text-secondary hover:bg-surface-subtle focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring focus-visible:ring-offset-2">Cancelar</button>
                </div>
            </form>
        </UiCard>
    </AdminLayout>
</template>