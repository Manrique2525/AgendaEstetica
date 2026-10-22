<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import UiCard from '@/components/ui/UiCard.vue'
import UiFormField from '@/components/ui/UiFormField.vue'
import UiInput from '@/components/ui/UiInput.vue'
import UiTextarea from '@/components/ui/UiTextarea.vue'
import { ApiError } from '@/services/http'
import { adminCatalogApi } from '@/services/api/adminCatalog'

const router = useRouter()
const route = useRoute()

const categoryId = computed(() => Number(route.params.id))

const saving = ref(false)
const loading = ref(false)
const error = ref('')
const message = ref('')

const form = reactive({
    name: '',
    description: '',
    active: true,
})

async function loadCategory(): Promise<void> {
    loading.value = true
    error.value = ''

    try {
        const response = await adminCatalogApi.productCategories.list()
        const categories = response
        const category = categories.find(c => c.id === categoryId.value)
        
        if (category) {
            form.name = category.name
            form.description = category.description ?? ''
            form.active = category.active
        } else {
            error.value = 'Categoría no encontrada.'
        }
    } catch {
        error.value = 'No pudimos cargar la categoría.'
    } finally {
        loading.value = false
    }
}

async function save(): Promise<void> {
    if (saving.value) return

    if (!form.name.trim()) {
        error.value = 'El nombre es obligatorio.'
        return
    }

    saving.value = true
    error.value = ''
    message.value = ''

    const payload = {
        name: form.name.trim(),
        description: form.description || null,
        active: form.active,
    }

    try {
        await adminCatalogApi.productCategories.update(categoryId.value, payload)
        message.value = 'Categoría actualizada.'
        await router.push({ name: 'admin.catalog.product-categories' })
    } catch (exception: unknown) {
        error.value = exception instanceof ApiError ? exception.message : 'No pudimos actualizar la categoría.'
    } finally {
        saving.value = false
    }
}

async function cancel(): Promise<void> {
    router.push({ name: 'admin.catalog.product-categories' })
}

onMounted(loadCategory)
</script>

<template>
    <AdminLayout>
        <UiCard>
            <header class="mb-8">
                <h1 class="font-display text-4xl leading-none text-text-primary">Editar categoría de producto</h1>
                <p class="mt-2 font-body text-sm text-text-secondary">Modifica los datos de la categoría.</p>
            </header>

            <p v-if="message" role="status" class="mb-6 rounded-md border border-brand-turquoise bg-surface-subtle p-3 font-body text-sm">{{ message }}</p>
            <p v-if="error" role="alert" class="mb-6 rounded-md border border-state-error bg-red-50 p-3 font-body text-sm text-state-error">{{ error }}</p>

            <form class="space-y-6" @submit.prevent="save">
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

                <label class="flex items-center gap-2 font-ui text-sm font-semibold">
                    <input v-model="form.active" type="checkbox" class="rounded border-border-default text-action-primary focus:ring-2 focus:ring-focus-ring">
                    Activa
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