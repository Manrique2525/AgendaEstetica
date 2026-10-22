<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import UiCard from '@/components/ui/UiCard.vue'
import UiFormField from '@/components/ui/UiFormField.vue'
import UiInput from '@/components/ui/UiInput.vue'
import UiTextarea from '@/components/ui/UiTextarea.vue'
import { adminCatalogApi, type AdminProductCategory } from '@/services/api/adminCatalog'

const router = useRouter()
const route = useRoute()

const productId = computed(() => Number(route.params.id))

const categories = ref<AdminProductCategory[]>([])
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const message = ref('')

const form = reactive({
    product_category_id: null as number | null,
    name: '',
    description: '',
    price: '',
    stock: '' as string,
    active: true,
})

async function loadProduct(): Promise<void> {
    loading.value = true
    error.value = ''

    try {
        const [productsResponse, categoriesResponse] = await Promise.all([
            adminCatalogApi.products.list({ active: true }),
            adminCatalogApi.productCategories.list({ active: true }),
        ])

        const products = productsResponse.data
        const categoriesData = categoriesResponse.data

        categories.value = categoriesData

        const product = products.find(p => p.id === productId.value)
        if (product) {
            form.product_category_id = product.category?.id ?? null
            form.name = product.name
            form.description = product.description ?? ''
            form.price = product.price
            form.stock = ''
            form.active = product.active
        } else {
            error.value = 'Producto no encontrado.'
        }
    } catch {
        error.value = 'No pudimos cargar el producto.'
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

    if (!form.price || Number(form.price) < 0) {
        error.value = 'El precio es obligatorio y debe ser mayor o igual a 0.'
        return
    }

    saving.value = true
    error.value = ''
    message.value = ''

    const payload = {
        product_category_id: form.product_category_id,
        name: form.name.trim(),
        description: form.description || null,
        price: String(form.price),
        stock: form.stock === '' ? null : Number(form.stock),
        active: form.active,
    }

    try {
        await adminCatalogApi.products.update(productId.value, payload)
        message.value = 'Producto actualizado.'
        await router.push({ name: 'admin.catalog.products' })
    } catch (exception: unknown) {
        error.value = exception instanceof Error ? exception.message : 'No pudimos actualizar el producto.'
    } finally {
        saving.value = false
    }
}

async function cancel(): Promise<void> {
    router.push({ name: 'admin.catalog.products' })
}

onMounted(loadProduct)
</script>

<template>
    <AdminLayout>
        <UiCard>
            <header class="mb-8">
                <h1 class="font-display text-4xl leading-none text-text-primary">Editar producto</h1>
                <p class="mt-2 font-body text-sm text-text-secondary">Modifica los datos del producto.</p>
            </header>

            <p v-if="message" role="status" class="mb-6 rounded-md border border-brand-turquoise bg-surface-subtle p-3 font-body text-sm">{{ message }}</p>
            <p v-if="error" role="alert" class="mb-6 rounded-md border border-state-error bg-red-50 p-3 font-body text-sm text-state-error">{{ error }}</p>

            <form class="space-y-6" @submit.prevent="save">
                <UiFormField label="Categoría" required>
                    <template #default="{ inputId, describedBy, invalid }">
                        <select
                            :id="inputId"
                            v-model.number="form.product_category_id"
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

                <div class="grid gap-4 sm:grid-cols-2">
                    <UiFormField label="Precio (MXN)" required>
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

                    <UiFormField label="Stock (opcional)">
                        <template #default="{ inputId, describedBy, invalid }">
                            <UiInput
                                :id="inputId"
                                v-model="form.stock"
                                type="number"
                                min="0"
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